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
     * Get statistics for a specific exam.
     */
    public function getExamStats(int $examId): array
    {
        $exam = Exam::with('examResults')->findOrFail($examId);
        $results = $exam->examResults;

        if ($results->isEmpty()) {
            return [
                'total_students' => 0,
                'average_mark' => 0,
                'highest_mark' => 0,
                'lowest_mark' => 0,
                'pass_count' => 0,
            ];
        }

        $halfMarks = $exam->total_marks / 2;

        return [
            'total_students' => $results->count(),
            'average_mark' => round($results->avg('marks_obtained'), 2),
            'highest_mark' => $results->max('marks_obtained'),
            'lowest_mark' => $results->min('marks_obtained'),
            'pass_count' => $results->where('marks_obtained', '>=', $halfMarks)->count(),
        ];
    }
}
