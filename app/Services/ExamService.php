<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ExamService
{
    /**
     * Bulk record exam marks for students.
     *
     * @param int $examId
     * @param array $studentsMarks Array of ['student_id' => int, 'marks_obtained' => float, 'notes' => ?string]
     */
    public function recordBulkResults(int $examId, array $studentsMarks): void
    {
        DB::transaction(function () use ($examId, $studentsMarks) {
            foreach ($studentsMarks as $row) {
                if (! isset($row['student_id']) || $row['marks_obtained'] === null || $row['marks_obtained'] === '') {
                    continue;
                }

                ExamResult::updateOrCreate(
                    [
                        'exam_id' => $examId,
                        'student_id' => $row['student_id'],
                    ],
                    [
                        'marks_obtained' => (float) $row['marks_obtained'],
                        'notes' => $row['notes'] ?? null,
                    ]
                );
            }
        });
    }

    /**
     * جلب الطلاب المستهدفين للامتحان (المجموعة المحددة أو جميع طلاب المرحلة)
     */
    public function getTargetStudents(Exam $exam): \Illuminate\Support\Collection
    {
        if ($exam->group_id) {
            $group = $exam->group ?: \App\Models\Group::find($exam->group_id);
            if ($group) {
                $students = $group->students()->wherePivot('status', 'active')->get();
                if ($students->isNotEmpty()) {
                    return $students;
                }
                return $group->students()->get();
            }
        }

        return Student::where('stage_id', $exam->stage_id)->get();
    }

    /**
     * Get statistics for a specific exam.
     */
    public function getExamStats(int $examId): array
    {
        $exam = Exam::with(['examResults', 'group'])->findOrFail($examId);
        $results = $exam->examResults;

        $targetStudents = $this->getTargetStudents($exam);
        $totalTargetStudents = $targetStudents->count();
        $attendedCount = $results->count();
        $absentCount = max(0, $totalTargetStudents - $attendedCount);

        if ($results->isEmpty()) {
            return [
                'total_stage_students' => $totalTargetStudents,
                'total_students' => 0,
                'attended_count' => 0,
                'absent_count' => $absentCount,
                'attendance_rate' => 0,
                'average_mark' => 0,
                'highest_mark' => 0,
                'lowest_mark' => 0,
                'pass_count' => 0,
                'fail_count' => 0,
                'pass_rate' => 0,
            ];
        }

        $halfMarks = $exam->total_marks / 2;
        $passCount = $results->where('marks_obtained', '>=', $halfMarks)->count();
        $failCount = $attendedCount - $passCount;

        return [
            'total_stage_students' => $totalTargetStudents,
            'total_students' => $attendedCount,
            'attended_count' => $attendedCount,
            'absent_count' => $absentCount,
            'attendance_rate' => $totalTargetStudents > 0 ? round(($attendedCount / $totalTargetStudents) * 100, 1) : 0,
            'average_mark' => round($results->avg('marks_obtained'), 2),
            'highest_mark' => $results->max('marks_obtained'),
            'lowest_mark' => $results->min('marks_obtained'),
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'pass_rate' => $attendedCount > 0 ? round(($passCount / $attendedCount) * 100, 1) : 0,
        ];
    }

    /**
     * الحصول على قائمة الطلاب الغائبين عن أداء الامتحان
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, name: string, code: string, phone: ?string, parent_phone: ?string, stage_name: string, group_name: string, exam_title: string, exam_date: string, subject_name: string}>
     */
    public function getExamAbsentees(int $examId): \Illuminate\Support\Collection
    {
        $exam = Exam::with(['educationalStage', 'subject', 'group'])->findOrFail($examId);
        $targetStudents = $this->getTargetStudents($exam);

        $attendedStudentIds = ExamResult::where('exam_id', $examId)->pluck('student_id')->toArray();

        $absentStudents = $targetStudents->whereNotIn('id', $attendedStudentIds);

        return $absentStudents->map(function ($st) use ($exam) {
            return [
                'id' => $st->id,
                'name' => $st->name,
                'code' => $st->qr_code ?: ('STD-' . $st->id),
                'phone' => $st->phone,
                'parent_phone' => $st->parent_phone ?? $st->phone,
                'stage_name' => $exam->educationalStage?->name ?? 'المرحلة',
                'group_name' => $exam->group?->name ?? 'جميع المجموعات',
                'subject_name' => $exam->subject?->name ?? 'المادة',
                'exam_title' => $exam->title,
                'exam_date' => $exam->date ? \Carbon\Carbon::parse($exam->date)->format('Y-m-d') : now()->toDateString(),
                'total_marks' => $exam->total_marks,
            ];
        })->values();
    }

    /**
     * الحصول على قائمة الطلاب الذين أدوا الامتحان مع درجاتهم وترتيبهم
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, student_id: int, name: string, code: string, phone: ?string, parent_phone: ?string, marks_obtained: float, total_marks: float, percentage: float, grade_text: string, rank: int, passed: bool, notes: ?string}>
     */
    public function getExamAttendees(int $examId): \Illuminate\Support\Collection
    {
        $exam = Exam::with(['educationalStage', 'subject'])->findOrFail($examId);

        $results = ExamResult::where('exam_id', $examId)
            ->with('student')
            ->orderBy('marks_obtained', 'desc')
            ->get();

        $attempts = \App\Models\OnlineExamAttempt::where('exam_id', $examId)
            ->get()
            ->keyBy('student_id');

        $totalMarks = $exam->total_marks > 0 ? (float) $exam->total_marks : 100.0;
        $halfMarks = $totalMarks / 2;

        return $results->map(function ($res, $idx) use ($exam, $totalMarks, $halfMarks, $attempts) {
            $student = $res->student;
            $marks = (float) $res->marks_obtained;
            $percentage = round(($marks / $totalMarks) * 100, 1);
            $passed = $marks >= $halfMarks;
            $model = $student ? ($attempts->get($student->id)?->exam_model) : null;

            $gradeText = match (true) {
                $percentage >= 85 => 'ممتاز 🌟',
                $percentage >= 75 => 'جيد جداً 🟢',
                $percentage >= 65 => 'جيد 🔵',
                $percentage >= 50 => 'مقبول 🟡',
                default => 'راسب 🔴',
            };

            return [
                'result_id' => $res->id,
                'student_id' => $student?->id ?? 0,
                'name' => $student?->name ?? 'طالب غير محدد',
                'code' => $student?->qr_code ?: ('STD-' . ($student?->id ?? '')),
                'phone' => $student?->phone,
                'parent_phone' => $student?->parent_phone ?? $student?->phone,
                'marks_obtained' => $marks,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'grade_text' => $gradeText,
                'rank' => $idx + 1,
                'passed' => $passed,
                'notes' => $res->notes,
                'exam_model' => $model,
                'exam_title' => $exam->title,
                'subject_name' => $exam->subject?->name ?? 'المادة',
                'exam_date' => $exam->date ? \Carbon\Carbon::parse($exam->date)->format('Y-m-d') : now()->toDateString(),
            ];
        });
    }

    /**
     * إرسال إشعارات جماعية لبوابة ولي الأمر للامتحان (للغائبين أو الحاصلين على درجات)
     */
    public function notifyBulkParentPortalForExam(int $examId, string $target = 'absent'): int
    {
        $exam = Exam::with(['educationalStage', 'subject'])->findOrFail($examId);
        $subjectName = $exam->subject?->name ?? 'المادة';
        $examDate = $exam->date ? \Carbon\Carbon::parse($exam->date)->format('Y-m-d') : now()->toDateString();
        $count = 0;

        if ($target === 'absent') {
            $absentees = $this->getExamAbsentees($examId);
            foreach ($absentees as $item) {
                \App\Models\ParentNotification::create([
                    'student_id' => $item['id'],
                    'type' => 'warning',
                    'title' => "⚠️ تنبيه عدم أداء امتحان {$exam->title}",
                    'message' => "نحيطكم علماً بأن الطالب ({$item['name']}) لم يؤدِ امتحان ({$exam->title}) في مادة ({$subjectName}) المقررة بتاريخ {$examDate}. يرجى المتابعة والتنسيق مع إدارة السنتر.",
                    'action_url' => '/parent/dashboard',
                ]);
                $count++;
            }
        } else {
            $attendees = $this->getExamAttendees($examId);
            foreach ($attendees as $item) {
                \App\Models\ParentNotification::create([
                    'student_id' => $item['student_id'],
                    'type' => 'exam',
                    'title' => "📊 نتيجة امتحان {$exam->title} ({$item['marks_obtained']}/{$exam->total_marks})",
                    'message' => "حصل الطالب ({$item['name']}) على درجة ({$item['marks_obtained']} من {$exam->total_marks}) بنسبة {$item['percentage']}% ({$item['grade_text']}) والترتيب (#{$item['rank']}) في امتحان ({$exam->title}).",
                    'action_url' => '/parent/dashboard',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * إرسال رسائل واتساب جماعية للطلاب الغائبين أو الحاصلين على نتائج
     *
     * @return array{success: int, failed: int, total: int}
     */
    public function sendBulkWhatsAppForExam(int $examId, string $target = 'absent', ?string $customMessage = null): array
    {
        $exam = Exam::with(['educationalStage', 'subject'])->findOrFail($examId);
        $centerName = app(SettingService::class)->get('center_name', 'المنظومة التعليمية');
        $whatsappService = app(WhatsAppNotificationService::class);
        $subjectName = $exam->subject?->name ?? 'المادة';
        $examDate = $exam->date ? \Carbon\Carbon::parse($exam->date)->format('Y-m-d') : now()->toDateString();

        $students = $target === 'absent'
            ? $this->getExamAbsentees($examId)
            : $this->getExamAttendees($examId);

        $success = 0;
        $failed = 0;

        foreach ($students as $student) {
            $phone = $student['parent_phone'];
            if (empty($phone)) {
                $failed++;
                continue;
            }

            if (! empty($customMessage)) {
                $message = str_replace(
                    ['{name}', '{student_name}', '{exam}', '{subject}', '{date}', '{center}', '{marks}', '{total}', '{percentage}', '{rank}'],
                    [
                        $student['name'],
                        $student['name'],
                        $exam->title,
                        $subjectName,
                        $examDate,
                        $centerName,
                        $student['marks_obtained'] ?? '',
                        $exam->total_marks,
                        $student['percentage'] ?? '',
                        $student['rank'] ?? '',
                    ],
                    $customMessage
                );
            } elseif ($target === 'absent') {
                $message = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$student['name']} ⚠️\n"
                    . "نحيطكم علماً بأن الطالب تغيب عن أداء امتحان ({$exam->title}) في مادة ({$subjectName}) بتاريخ: {$examDate}.\n"
                    . "يرجى التواصل مع إدارة المركز للمتابعة وتحديد موعد الإعادة.\n\n"
                    . "— {$centerName}";
            } else {
                $statusIcon = ($student['passed'] ?? true) ? '🎉' : '⚠️';
                $message = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$student['name']} 📊\n"
                    . "نرسل لكم بطاقة نتيجة امتحان ({$exam->title}) في مادة ({$subjectName}):\n\n"
                    . "▪️ الدرجة: {$student['marks_obtained']} من {$exam->total_marks}\n"
                    . "▪️ النسبة المئوية: {$student['percentage']}%\n"
                    . "▪️ التقدير والتقييم: {$student['grade_text']}\n"
                    . "▪️ الترتيب على الدفعة: المركز #{$student['rank']} {$statusIcon}\n\n"
                    . "— {$centerName}";
            }

            $sent = $whatsappService->sendMessage($phone, $message);
            if ($sent) {
                $success++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'total' => count($students),
        ];
    }

    /**
     * توليد امتحان آلي ذكي من بنك الأسئلة وفق معايير وتوزيع صعوبة محدد
     */
    public function generateExamFromQuestionBank(array $criteria): Exam
    {
        $stageId = (int) $criteria['stage_id'];
        $subjectId = (int) $criteria['subject_id'];
        $easyCount = (int) ($criteria['easy_count'] ?? 0);
        $mediumCount = (int) ($criteria['medium_count'] ?? 0);
        $hardCount = (int) ($criteria['hard_count'] ?? 0);
        $topics = $criteria['topics'] ?? [];
        $marksPerQuestion = (float) ($criteria['marks_per_question'] ?? 1.00);

        $selectedQuestions = collect();

        // 1. استخراج الأسئلة السهلة
        if ($easyCount > 0) {
            $easyQuery = \App\Models\Question::where('stage_id', $stageId)
                ->where('subject_id', $subjectId)
                ->where('difficulty', 'easy');

            if (! empty($topics)) {
                $easyQuery->whereIn('topic', (array) $topics);
            }

            $easyQuestions = $easyQuery->inRandomOrder()->limit($easyCount)->get();
            $selectedQuestions = $selectedQuestions->merge($easyQuestions);
        }

        // 2. استخراج الأسئلة المتوسطة
        if ($mediumCount > 0) {
            $mediumQuery = \App\Models\Question::where('stage_id', $stageId)
                ->where('subject_id', $subjectId)
                ->where('difficulty', 'medium');

            if (! empty($topics)) {
                $mediumQuery->whereIn('topic', (array) $topics);
            }

            $mediumQuestions = $mediumQuery->inRandomOrder()->limit($mediumCount)->get();
            $selectedQuestions = $selectedQuestions->merge($mediumQuestions);
        }

        // 3. استخراج أسئلة المتفوقين
        if ($hardCount > 0) {
            $hardQuery = \App\Models\Question::where('stage_id', $stageId)
                ->where('subject_id', $subjectId)
                ->where('difficulty', 'hard');

            if (! empty($topics)) {
                $hardQuery->whereIn('topic', (array) $topics);
            }

            $hardQuestions = $hardQuery->inRandomOrder()->limit($hardCount)->get();
            $selectedQuestions = $selectedQuestions->merge($hardQuestions);
        }

        if ($selectedQuestions->isEmpty()) {
            throw new \InvalidArgumentException('لم يتم العثور على أي أسئلة مطابقة للشروط المحددة في بنك الأسئلة.');
        }

        $totalMarks = $selectedQuestions->count() * $marksPerQuestion;

        return DB::transaction(function () use ($criteria, $selectedQuestions, $totalMarks, $marksPerQuestion) {
            $exam = Exam::create([
                'title' => $criteria['title'],
                'stage_id' => $criteria['stage_id'],
                'group_id' => ! empty($criteria['group_id']) ? $criteria['group_id'] : null,
                'subject_id' => $criteria['subject_id'],
                'exam_type' => $criteria['exam_type'] ?? ($criteria['type'] ?? 'quiz'),
                'date' => $criteria['date'] ?? now()->toDateString(),
                'total_marks' => $totalMarks,
                'duration_minutes' => (int) ($criteria['duration_minutes'] ?? 45),
                'is_online' => (bool) ($criteria['is_online'] ?? false),
                'models_count' => (int) ($criteria['models_count'] ?? 1),
                'starts_at' => ! empty($criteria['starts_at']) ? $criteria['starts_at'] : null,
                'ends_at' => ! empty($criteria['ends_at']) ? $criteria['ends_at'] : null,
                'pass_percentage' => (int) ($criteria['pass_percentage'] ?? 50),
                'show_correct_answers_after_submission' => isset($criteria['show_correct_answers_after_submission']) ? (bool) $criteria['show_correct_answers_after_submission'] : true,
                'shuffle_questions' => (bool) ($criteria['shuffle_questions'] ?? true),
                'status' => 'published',
            ]);

            $order = 1;
            $attachData = [];
            foreach ($selectedQuestions as $question) {
                $attachData[$question->id] = [
                    'marks' => $marksPerQuestion,
                    'order' => $order++,
                ];
            }

            $exam->questions()->sync($attachData);

            return $exam;
        });
    }

    /**
     * تجهيز نماذج الامتحان المطبوعة (أ / ب / ج) ونماذج الإجابة
     */
    public function getExamPrintModels(int $examId, int $modelsCount = 1, bool $shuffleOptions = true): array
    {
        $exam = Exam::with(['subject', 'educationalStage', 'questions'])->findOrFail($examId);
        $baseQuestions = $exam->questions;

        $modelLetters = ['أ', 'ب', 'ج', 'د'];
        $models = [];

        for ($i = 0; $i < min(max(1, $modelsCount), 4); $i++) {
            $modelCode = $modelLetters[$i] ?? ('M' . ($i + 1));

            // النموذج الأول يأخذ الترتيب الأصلي، وباقي النماذج يتم خلط ترتيب الأسئلة
            $questionsCollection = $i === 0 ? $baseQuestions : $baseQuestions->shuffle();

            $modelQuestions = [];
            $answerKey = [];
            $qNum = 1;

            foreach ($questionsCollection as $q) {
                $options = is_array($q->options) ? $q->options : json_decode($q->options ?? '[]', true);
                $correctAnswers = is_array($q->correct_answers) ? $q->correct_answers : json_decode($q->correct_answers ?? '[]', true);

                $processedOptions = [];
                $optionLetters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و'];

                if ($shuffleOptions && $i > 0 && ! empty($options) && count($options) > 1) {
                    $shuffledRaw = collect($options)->shuffle()->values();
                    $newCorrect = [];

                    foreach ($shuffledRaw as $idx => $opt) {
                        $oldKey = is_array($opt) ? ($opt['key'] ?? '') : '';
                        $text = is_array($opt) ? ($opt['text'] ?? '') : (string) $opt;
                        $newKey = $optionLetters[$idx] ?? chr(65 + $idx);

                        $isCorrect = in_array($oldKey, $correctAnswers);
                        if ($isCorrect) {
                            $newCorrect[] = $newKey;
                        }

                        $processedOptions[] = [
                            'key' => $newKey,
                            'text' => $text,
                            'is_correct' => $isCorrect,
                        ];
                    }
                    $modelCorrectAnswers = $newCorrect;
                } else {
                    foreach ($options as $idx => $opt) {
                        $key = is_array($opt) ? ($opt['key'] ?? ($optionLetters[$idx] ?? '')) : ($optionLetters[$idx] ?? '');
                        $text = is_array($opt) ? ($opt['text'] ?? '') : (string) $opt;
                        $isCorrect = in_array($key, $correctAnswers);

                        $processedOptions[] = [
                            'key' => $key,
                            'text' => $text,
                            'is_correct' => $isCorrect,
                        ];
                    }
                    $modelCorrectAnswers = $correctAnswers;
                }

                // العثور على نص الإجابة الصحيحة
                $correctTexts = [];
                foreach ($processedOptions as $opt) {
                    if (in_array($opt['key'], $modelCorrectAnswers)) {
                        $correctTexts[] = "({$opt['key']}) " . $opt['text'];
                    }
                }

                $modelQuestions[] = [
                    'number' => $qNum,
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'question_image' => $q->question_image,
                    'type' => $q->type,
                    'difficulty' => $q->difficulty,
                    'topic' => $q->topic,
                    'marks' => $q->pivot?->marks ?? $q->default_marks ?? 1,
                    'options' => $processedOptions,
                    'correct_answers' => $modelCorrectAnswers,
                    'explanation' => $q->explanation,
                ];

                $answerKey[] = [
                    'number' => $qNum,
                    'correct_keys' => implode(', ', $modelCorrectAnswers),
                    'correct_text' => implode(' | ', $correctTexts),
                    'marks' => $q->pivot?->marks ?? $q->default_marks ?? 1,
                    'explanation' => $q->explanation,
                    'topic' => $q->topic,
                ];

                $qNum++;
            }

            $models[] = [
                'model_code' => $modelCode,
                'model_name' => "نموذج ({$modelCode})",
                'questions' => $modelQuestions,
                'answer_key' => $answerKey,
                'total_marks' => $exam->total_marks,
                'questions_count' => count($modelQuestions),
            ];
        }

        return [
            'exam' => $exam,
            'models' => $models,
        ];
    }
}
