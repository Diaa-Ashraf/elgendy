<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\OnlineExamAttempt;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OnlineExamService
{
    /**
     * Start an online exam attempt for a student.
     */
    public function startAttempt(Student $student, Exam $exam): OnlineExamAttempt
    {
        $modelsCount = max(1, (int) ($exam->models_count ?? 1));
        $modelLetters = ['أ', 'ب', 'ج', 'د'];
        $modelLetters = array_slice($modelLetters, 0, min($modelsCount, 4));

        $modelIndex = abs($student->id) % count($modelLetters);
        $assignedModel = $modelLetters[$modelIndex] ?? 'أ';

        // التحقق من وجود محاولة جارية مسبقاً
        $existingAttempt = OnlineExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttempt) {
            // تحديث النموذج إن لم يكن معيناً
            if (empty($existingAttempt->exam_model)) {
                $existingAttempt->update(['exam_model' => $assignedModel]);
            }

            // إذا كانت مكتملة نرجعها كما هي
            if ($existingAttempt->status !== 'in_progress') {
                return $existingAttempt;
            }

            // فحص هل تخطت المحاولة الوقت المسموح (مدة الامتحان أو موعد إغلاق الامتحان العام)
            $isExpired = false;
            if ($exam->duration_minutes && $existingAttempt->started_at) {
                $endTime = $existingAttempt->started_at->copy()->addMinutes((int) $exam->duration_minutes);
                if (now()->greaterThan($endTime)) {
                    $isExpired = true;
                }
            }

            if ($exam->ends_at && now()->greaterThan($exam->ends_at)) {
                $isExpired = true;
            }

            if ($isExpired) {
                $this->finalizeAttempt($existingAttempt, (array) ($existingAttempt->student_answers ?? []));
                return $existingAttempt->fresh();
            }

            return $existingAttempt;
        }

        // حساب الدرجة القصوى لأسئلة الامتحان
        $maxScore = (float) $exam->questions()->sum('exam_questions.marks');
        if ($maxScore <= 0) {
            $maxScore = (float) $exam->total_marks;
        }

        return OnlineExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'max_possible_score' => $maxScore,
            'status' => 'in_progress',
            'student_answers' => [],
            'exam_model' => $assignedModel,
        ]);
    }

    /**
     * ترتيب أسئلة الامتحان بناءً على نموذج الامتحان (أ / ب / ج / د).
     */
    public function getQuestionsForModel(Exam $exam, ?string $model = 'أ'): Collection
    {
        $questions = $exam->questions;
        if ($questions->isEmpty() || empty($model) || $model === 'أ') {
            return $questions;
        }

        // للموديلات (ب، ج، د) نقوم بعمل ترتيب عشوائي ثابت (Deterministic Permutation)
        // حتى يرى جميع الطلاب الذين لديهم نفس النموذج نفس الترتيب بدقة
        $seed = crc32($exam->id . '_' . $model);
        $items = $questions->all();

        mt_srand($seed);
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $temp = $items[$i];
            $items[$i] = $items[$j];
            $items[$j] = $temp;
        }
        mt_srand(); // reset seed

        return collect($items);
    }

    /**
     * Submit and auto-grade the student's exam attempt.
     */
    public function submitAttempt(OnlineExamAttempt $attempt, array $submittedAnswers): OnlineExamAttempt
    {
        if ($attempt->status !== 'in_progress') {
            return $attempt;
        }

        return $this->finalizeAttempt($attempt, $submittedAnswers);
    }

    /**
     * Core Auto-Grading calculation.
     */
    protected function finalizeAttempt(OnlineExamAttempt $attempt, array $submittedAnswers): OnlineExamAttempt
    {
        $exam = $attempt->exam()->with('questions')->firstOrFail();
        $questions = $exam->questions;

        $totalEarnedScore = 0.0;
        $maxPossibleScore = 0.0;
        $gradedAnswersDetails = [];

        foreach ($questions as $question) {
            $qMarks = (float) ($question->pivot->marks ?? $question->default_marks ?? 1.0);
            $maxPossibleScore += $qMarks;

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
            $totalEarnedScore += $earned;

            $gradedAnswersDetails[$questionId] = [
                'question_id' => $questionId,
                'selected' => $userAnsArray,
                'correct' => $correctAnswers,
                'is_correct' => $isCorrect,
                'marks_earned' => $earned,
                'max_marks' => $qMarks,
                'topic' => $question->topic ?? 'عام',
                'explanation' => $question->explanation,
            ];
        }

        if ($maxPossibleScore <= 0) {
            $maxPossibleScore = (float) ($exam->total_marks > 0 ? $exam->total_marks : 100);
        }

        $percentage = ($maxPossibleScore > 0) ? round(($totalEarnedScore / $maxPossibleScore) * 100, 2) : 0;
        $passPercentage = $exam->pass_percentage ?? 50;
        $passed = $percentage >= $passPercentage;

        DB::transaction(function () use ($attempt, $exam, $totalEarnedScore, $maxPossibleScore, $percentage, $passed, $gradedAnswersDetails) {
            // تحديث المحاولة
            $attempt->update([
                'submitted_at' => now(),
                'total_score' => $totalEarnedScore,
                'max_possible_score' => $maxPossibleScore,
                'percentage' => $percentage,
                'passed' => $passed,
                'status' => 'completed',
                'student_answers' => $gradedAnswersDetails,
            ]);

            // مزامنة فورية مع كشف درجات الامتحانات العام ExamResult
            ExamResult::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $attempt->student_id,
                ],
                [
                    'marks_obtained' => $totalEarnedScore,
                    'notes' => "تصحيح إلكتروني أوتوماتيك ({$percentage}%)",
                ]
            );
        });

        // إرسال إشعار في جرس الإشعارات باللوحة للمديرين والمعلمين
        try {
            $student = $attempt->student;
            \App\Services\NotificationService::notifyStudentCompletedOnlineExam(
                $student?->name ?? 'طالب',
                $exam->title,
                $totalEarnedScore,
                $maxPossibleScore,
                $percentage,
                $exam->id
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Exam Completion Notification Error: ' . $e->getMessage());
        }

        return $attempt->fresh();
    }

    /**
     * Calculate weak points and misconception analytics across all attempts of an exam.
     */
    public function getWeakPointsAnalytics(Exam $exam): array
    {
        $attempts = OnlineExamAttempt::where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->with('student.groups')
            ->get();

        if ($attempts->isEmpty()) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'pass_rate' => 0,
                'questions_analysis' => [],
                'topics_analysis' => [],
                'most_missed_questions' => [],
            ];
        }

        $totalAttempts = $attempts->count();
        $passCount = $attempts->where('passed', true)->count();
        $averageScore = round($attempts->avg('percentage'), 1);

        $examQuestions = $exam->questions()->get()->keyBy('id');
        $questionStats = [];
        $topicStats = [];

        foreach ($attempts as $attempt) {
            $answers = (array) ($attempt->student_answers ?? []);

            foreach ($answers as $qId => $detail) {
                if (! isset($questionStats[$qId])) {
                    $qModel = $examQuestions->get($qId);
                    $questionStats[$qId] = [
                        'id' => $qId,
                        'text' => $qModel?->question_text ?? 'سؤال #' . $qId,
                        'topic' => $detail['topic'] ?? ($qModel?->topic ?? 'عام'),
                        'difficulty' => $qModel?->difficulty ?? 'medium',
                        'total_answers' => 0,
                        'wrong_answers' => 0,
                        'correct_answers' => 0,
                        'error_rate' => 0,
                    ];
                }

                $questionStats[$qId]['total_answers']++;
                if (empty($detail['is_correct'])) {
                    $questionStats[$qId]['wrong_answers']++;
                } else {
                    $questionStats[$qId]['correct_answers']++;
                }

                // تجميع نقاط الضعف حسب المفهوم / Topic
                $topic = $questionStats[$qId]['topic'];
                if (! isset($topicStats[$topic])) {
                    $topicStats[$topic] = [
                        'topic' => $topic,
                        'total_questions_answered' => 0,
                        'total_errors' => 0,
                        'mastery_rate' => 0,
                    ];
                }

                $topicStats[$topic]['total_questions_answered']++;
                if (empty($detail['is_correct'])) {
                    $topicStats[$topic]['total_errors']++;
                }
            }
        }

        // حساب النسب المئوية للأسئلة
        foreach ($questionStats as &$q) {
            $q['error_rate'] = $q['total_answers'] > 0
                ? round(($q['wrong_answers'] / $q['total_answers']) * 100, 1)
                : 0;
        }
        unset($q);

        // حساب نسب الإتقان للمواضيع
        foreach ($topicStats as &$t) {
            $t['mastery_rate'] = $t['total_questions_answered'] > 0
                ? round((($t['total_questions_answered'] - $t['total_errors']) / $t['total_questions_answered']) * 100, 1)
                : 0;
            $t['error_rate'] = round(100 - $t['mastery_rate'], 1);
        }
        unset($t);

        // ترتيب الأسئلة حسب الأكثر خطأً (الأعلى في معدل الخطأ)
        $sortedQuestions = collect($questionStats)->sortByDesc('error_rate')->values()->all();
        $sortedTopics = collect($topicStats)->sortByDesc('error_rate')->values()->all();

        return [
            'total_attempts' => $totalAttempts,
            'average_score' => $averageScore,
            'pass_rate' => round(($passCount / $totalAttempts) * 100, 1),
            'questions_analysis' => $sortedQuestions,
            'topics_analysis' => $sortedTopics,
            'most_missed_questions' => array_slice($sortedQuestions, 0, 5),
        ];
    }
}
