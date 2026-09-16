<?php

namespace App\Services;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeworkService
{
    /**
     * جلب الواجبات المتاحة للطالب بناءً على مرحلته الدراسية ومجموعاته.
     */
    public function getStudentHomeworks(Student $student): Collection
    {
        if (! Schema::hasTable('homeworks')) {
            return collect();
        }

        $student->loadMissing('groups');

        $groupIds = $student->groups->pluck('id')->toArray();

        return Homework::query()
            ->select([
                'id', 'title', 'description', 'group_id', 'stage_id', 'subject_id',
                'type', 'attachment', 'due_date', 'published_at', 'total_marks', 'status',
                'allow_late_submission', 'max_attempts',
            ])
            ->with(['subject:id,name', 'group:id,name'])
            ->published()
            ->where('stage_id', $student->stage_id)
            ->where(function ($q) use ($groupIds) {
                // واجبات المجموعات المحددة + واجبات المرحلة العامة (بدون مجموعة)
                $q->whereIn('group_id', $groupIds)
                  ->orWhereNull('group_id');
            })
            ->orderBy('due_date', 'desc')
            ->get()
            ->each(function ($homework) use ($student) {
                $homework->student_submission = HomeworkSubmission::where('homework_id', $homework->id)
                    ->where('student_id', $student->id)
                    ->first();
            });
    }

    /**
     * تسليم الواجب: معالجة إجابات الأسئلة أو رفع الملف.
     */
    public function submitHomework(Student $student, Homework $homework, array $data): HomeworkSubmission
    {
        // التحقق من إمكانية التسليم
        if (! $homework->canAcceptSubmissions()) {
            throw new \RuntimeException('الواجب مغلق أو انتهى موعد التسليم.');
        }

        // التحقق من عدد المحاولات
        $existingSubmission = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission && $existingSubmission->isGraded()) {
            throw new \RuntimeException('تم تصحيح الواجب بالفعل ولا يمكن إعادة التسليم.');
        }

        $isLate = $homework->due_date->isPast();

        return DB::transaction(function () use ($student, $homework, $data, $existingSubmission, $isLate) {
            $submissionData = [
                'homework_id' => $homework->id,
                'student_id' => $student->id,
                'submitted_at' => now(),
                'is_late' => $isLate,
                'status' => 'submitted',
                'student_answers' => $data['student_answers'] ?? null,
                'attachment' => $data['attachment'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            if ($existingSubmission) {
                $existingSubmission->update($submissionData);
                $submission = $existingSubmission->fresh();
            } else {
                $submission = HomeworkSubmission::create($submissionData);
            }

            // تصحيح تلقائي للأسئلة الاختيارية
            if (in_array($homework->type, ['questions', 'mixed']) && ! empty($data['student_answers'])) {
                $this->autoGradeQuestions($submission);
            }

            return $submission->fresh();
        });
    }

    /**
     * التصحيح التلقائي للأسئلة الاختيارية في الواجب.
     */
    public function autoGradeQuestions(HomeworkSubmission $submission): void
    {
        $homework = $submission->homework()->with('questions')->firstOrFail();
        $questions = $homework->questions;
        $submittedAnswers = (array) ($submission->student_answers ?? []);

        $totalEarned = 0.0;
        $maxPossible = 0.0;
        $gradedDetails = [];

        foreach ($questions as $question) {
            $qMarks = (float) ($question->pivot->marks ?? $question->default_marks ?? 1.0);
            $maxPossible += $qMarks;

            $questionId = (int) $question->id;
            $userAns = $submittedAnswers[$questionId] ?? null;

            // تسوية الإجابات للمقارنة بدقة
            $userAnsRaw = is_array($userAns) ? $userAns : ($userAns !== null && $userAns !== '' ? [$userAns] : []);
            $userAnsArray = array_values(array_unique(array_filter(array_map(fn ($v) => trim((string) $v), $userAnsRaw))));

            $correctAnsRaw = is_array($question->correct_answers) ? $question->correct_answers : [];
            $correctAnswers = array_values(array_unique(array_filter(array_map(fn ($v) => trim((string) $v), $correctAnsRaw))));

            // ترتيب المصفوفات لضمان دقة المقارنة الحرفية
            sort($userAnsArray);
            sort($correctAnswers);

            $isCorrect = (!empty($correctAnswers) && $userAnsArray === $correctAnswers);
            $earned = $isCorrect ? $qMarks : 0.0;
            $totalEarned += $earned;

            $gradedDetails[$questionId] = [
                'question_id' => $questionId,
                'selected' => $userAnsArray,
                'correct' => $correctAnswers,
                'is_correct' => $isCorrect,
                'marks_earned' => $earned,
                'max_marks' => $qMarks,
            ];
        }

        $submission->update([
            'auto_score' => $totalEarned,
            'student_answers' => $gradedDetails,
        ]);

        // إذا الواجب كله أسئلة اختيارية، نضع الدرجة النهائية تلقائياً
        if ($homework->type === 'questions') {
            $submission->update([
                'score' => $totalEarned,
                'status' => 'graded',
                'graded_at' => now(),
            ]);
        }
    }

    /**
     * رصد وتصحيح درجة تسليم الواجب يدوياً من قبل المدرس
     */
    public function gradeSubmission(int $submissionId, float $score, ?string $feedback = null, string $status = 'graded'): HomeworkSubmission
    {
        $submission = HomeworkSubmission::with('homework')->findOrFail($submissionId);
        $totalMarks = (float) ($submission->homework->total_marks ?? 10);

        if ($score < 0 || $score > $totalMarks) {
            throw new \InvalidArgumentException("الدرجة المرصودة يجب أن تكون بين 0 و {$totalMarks}");
        }

        $submission->update([
            'score' => $score,
            'teacher_feedback' => $feedback,
            'status' => $status,
            'graded_at' => now(),
        ]);

        return $submission->fresh(['student', 'homework']);
    }

    /**
     * جلب الطلاب المستهدفين للواجب (المجموعة المحددة أو جميع طلاب المرحلة)
     */
    public function getTargetStudents(Homework $homework): Collection
    {
        if ($homework->group_id) {
            $group = $homework->group ?: \App\Models\Group::find($homework->group_id);
            if ($group) {
                $students = $group->students()->wherePivot('status', 'active')->get();
                if ($students->isNotEmpty()) {
                    return $students;
                }
                return $group->students()->get();
            }
        }

        return Student::where('stage_id', $homework->stage_id)->get();
    }

    /**
     * إحصائيات تسليمات وأداء واجب معين.
     */
    public function getSubmissionStats(Homework $homework): array
    {
        return $this->getHomeworkStats($homework->id);
    }

    /**
     * حساب إحصائيات متكاملة للواجب والطلاب المسلّمين والمقصرين.
     */
    public function getHomeworkStats(int $homeworkId): array
    {
        $homework = Homework::with(['group', 'educationalStage', 'subject'])->findOrFail($homeworkId);
        $targetStudents = $this->getTargetStudents($homework);
        $totalTarget = $targetStudents->count();

        $submissions = HomeworkSubmission::where('homework_id', $homeworkId)->get();
        $submittedStudentIds = $submissions->whereIn('status', ['submitted', 'graded', 'returned'])
            ->pluck('student_id')
            ->unique()
            ->toArray();

        $submittedCount = count($submittedStudentIds);
        $missingCount = max(0, $totalTarget - $submittedCount);
        $submissionRate = $totalTarget > 0 ? round(($submittedCount / $totalTarget) * 100, 1) : 0;

        $gradedSubmissions = $submissions->whereNotNull('score');
        $gradedCount = $gradedSubmissions->count();
        $avgScore = $gradedSubmissions->avg('score');
        $highestScore = $gradedSubmissions->max('score');
        $lowestScore = $gradedSubmissions->min('score');

        return [
            'total_target_students' => $totalTarget,
            'submitted_count' => $submittedCount,
            'missing_count' => $missingCount,
            'submission_rate' => $submissionRate,
            'graded_count' => $gradedCount,
            'pending_grading' => max(0, $submittedCount - $gradedCount),
            'average_score' => $avgScore ? round((float) $avgScore, 1) : 0,
            'highest_score' => $highestScore !== null ? (float) $highestScore : 0,
            'lowest_score' => $lowestScore !== null ? (float) $lowestScore : 0,
        ];
    }

    /**
     * الحصول على قائمة الطلاب الذين لم يسلموا الواجب (المقصرين)
     *
     * @return Collection<int, array{id: int, name: string, code: string, phone: ?string, parent_phone: ?string, stage_name: string, group_name: string, is_overdue: bool}>
     */
    public function getHomeworkMissingStudents(int $homeworkId): Collection
    {
        $homework = Homework::with(['group', 'educationalStage'])->findOrFail($homeworkId);
        $targetStudents = $this->getTargetStudents($homework);

        $submittedStudentIds = HomeworkSubmission::where('homework_id', $homeworkId)
            ->whereIn('status', ['submitted', 'graded', 'returned'])
            ->pluck('student_id')
            ->unique()
            ->toArray();

        $stageName = $homework->educationalStage?->name ?? 'المرحلة';
        $groupName = $homework->group?->name ?? 'جميع المجموعات';
        $isOverdue = $homework->isOverdue();

        return $targetStudents
            ->whereNotIn('id', $submittedStudentIds)
            ->map(function ($student) use ($stageName, $groupName, $isOverdue) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'code' => $student->qr_code ?: ('STD-' . $student->id),
                    'phone' => $student->phone,
                    'parent_phone' => $student->parent_phone ?? $student->phone,
                    'stage_name' => $stageName,
                    'group_name' => $groupName,
                    'is_overdue' => $isOverdue,
                ];
            })
            ->values();
    }

    /**
     * الحصول على قائمة الطلاب الذين سلّموا الواجب مع درجاتهم وتقييماتهم
     *
     * @return Collection<int, array{id: int, student_id: int, name: string, code: string, phone: ?string, parent_phone: ?string, submitted_at: ?string, is_late: bool, status: string, score: ?float, total_marks: float, percentage: ?float, feedback: ?string, attachment: ?string}>
     */
    public function getHomeworkSubmittedStudents(int $homeworkId): Collection
    {
        $homework = Homework::findOrFail($homeworkId);
        $totalMarks = (float) $homework->total_marks;

        $submissions = HomeworkSubmission::where('homework_id', $homeworkId)
            ->with(['student.educationalStage'])
            ->orderByRaw('CASE WHEN score IS NULL THEN 1 ELSE 0 END, score DESC, submitted_at ASC')
            ->get();

        return $submissions->map(function ($sub, $idx) use ($totalMarks) {
            $student = $sub->student;
            $score = $sub->score !== null ? (float) $sub->score : null;
            $percentage = $score !== null && $totalMarks > 0 ? round(($score / $totalMarks) * 100, 1) : null;

            return [
                'id' => $sub->id,
                'student_id' => $student?->id,
                'name' => $student?->name ?? 'طالب',
                'code' => $student?->qr_code ?: ('STD-' . ($student?->id ?? 0)),
                'phone' => $student?->phone,
                'parent_phone' => $student?->parent_phone ?? $student?->phone,
                'submitted_at' => $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('Y-m-d h:i A') : null,
                'is_late' => (bool) $sub->is_late,
                'status' => $sub->status,
                'score' => $score,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'feedback' => $sub->teacher_feedback,
                'attachment' => $sub->attachment,
                'rank' => $idx + 1,
            ];
        })->values();
    }

    /**
     * إرسال إشعارات جماعية لبوابة ولي الأمر للواجب (تنبيه بعدم التسليم أو إشعار اعتماد النتيجة)
     */
    public function notifyBulkParentPortalForHomework(int $homeworkId, string $target = 'missing'): int
    {
        $homework = Homework::with(['group', 'educationalStage', 'subject'])->findOrFail($homeworkId);
        $dueDate = $homework->due_date ? \Carbon\Carbon::parse($homework->due_date)->format('Y-m-d h:i A') : '';
        $subjectName = $homework->subject?->name ?? 'المادة';
        $count = 0;

        if ($target === 'missing') {
            $missing = $this->getHomeworkMissingStudents($homeworkId);
            foreach ($missing as $item) {
                \App\Models\ParentNotification::create([
                    'student_id' => $item['id'],
                    'type' => 'warning',
                    'title' => "⚠️ تنبيه عدم تسليم واجب: {$homework->title}",
                    'message' => "نحيطكم علماً بأن الطالب ({$item['name']}) لم يقم بتسليم الواجب المطلوب في مادة ({$subjectName}) والذي موعد تسليمه النهائي: {$dueDate}. يرجى حث الطالب على سرعة التسليم.",
                    'action_url' => '/parent/dashboard',
                ]);
                $count++;
            }
        } else {
            $submitted = $this->getHomeworkSubmittedStudents($homeworkId);
            foreach ($submitted as $item) {
                if ($item['score'] !== null) {
                    \App\Models\ParentNotification::create([
                        'student_id' => $item['student_id'],
                        'type' => 'homework',
                        'title' => "✅ تم تصحيح واجب: {$homework->title}",
                        'message' => "تم اعتماد درجة وتقييم الطالب ({$item['name']}) في واجب مادة ({$subjectName}): حصل على {$item['score']} من {$item['total_marks']} ({$item['percentage']}%).",
                        'action_url' => '/parent/dashboard',
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * إرسال رسائل واتساب جماعية للواجب (للذين لم يسلموا أو للمسلّمين)
     *
     * @return array{success: int, failed: int, total: int}
     */
    public function sendBulkWhatsAppForHomework(int $homeworkId, string $target = 'missing', ?string $customMessage = null): array
    {
        $homework = Homework::with(['group', 'educationalStage', 'subject'])->findOrFail($homeworkId);
        $centerName = app(SettingService::class)->get('center_name', 'المنظومة التعليمية');
        $subjectName = $homework->subject?->name ?? 'المادة';
        $dueDate = $homework->due_date ? \Carbon\Carbon::parse($homework->due_date)->format('Y-m-d h:i A') : '';
        $whatsappService = app(WhatsAppNotificationService::class);

        $students = $target === 'missing'
            ? $this->getHomeworkMissingStudents($homeworkId)
            : $this->getHomeworkSubmittedStudents($homeworkId);

        $success = 0;
        $failed = 0;

        foreach ($students as $item) {
            $phone = $item['parent_phone'];
            if (empty($phone)) {
                $failed++;
                continue;
            }

            if (! empty($customMessage)) {
                $message = str_replace(
                    ['{name}', '{student_name}', '{homework}', '{subject}', '{due_date}', '{center}'],
                    [$item['name'], $item['name'], $homework->title, $subjectName, $dueDate, $centerName],
                    $customMessage
                );
            } elseif ($target === 'missing') {
                $message = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$item['name']} ⚠️\n"
                    . "نود تذكيركم بأن الطالب لم يقم بعد بتسليم واجب ({$homework->title}) في مادة ({$subjectName}).\n"
                    . "⏰ آخر موعد للتسليم: {$dueDate}\n"
                    . "يرجى حث الطالب على حل الواجب وتسليمه عبر بوابة الطالب للاستفادة الكاملة.\n\n"
                    . "— {$centerName}";
            } else {
                $scoreText = $item['score'] !== null ? "{$item['score']} من {$item['total_marks']} ({$item['percentage']}%)" : 'قيد المراجعة';
                $message = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$item['name']} 📝\n"
                    . "نحيطكم علماً بأنه تم استلام وتصحيح واجب ({$homework->title}) في مادة ({$subjectName}):\n"
                    . "▪️ الدرجة: {$scoreText}\n"
                    . ($item['feedback'] ? "▪️ ملاحظات المدرس: {$item['feedback']}\n" : "")
                    . "\n— {$centerName}";
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
}

