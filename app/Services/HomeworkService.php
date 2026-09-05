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

            $userAnsArray = is_array($userAns) ? $userAns : ($userAns !== null ? [$userAns] : []);
            $correctAnswers = is_array($question->correct_answers) ? $question->correct_answers : [];

            sort($userAnsArray);
            sort($correctAnswers);

            $isCorrect = ($userAnsArray === $correctAnswers && ! empty($correctAnswers));
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
     * إحصائيات تسليمات واجب معين.
     */
    public function getSubmissionStats(Homework $homework): array
    {
        $submissions = $homework->submissions()->get();

        $total = $submissions->count();
        $submitted = $submissions->whereIn('status', ['submitted', 'graded', 'returned'])->count();
        $graded = $submissions->whereIn('status', ['graded', 'returned'])->count();
        $late = $submissions->where('is_late', true)->count();
        $avgScore = $submissions->whereNotNull('score')->avg('score');

        return [
            'total_submissions' => $total,
            'submitted_count' => $submitted,
            'graded_count' => $graded,
            'late_count' => $late,
            'average_score' => $avgScore ? round($avgScore, 1) : null,
            'pending_grading' => $submitted - $graded,
        ];
    }
}
