<?php

namespace App\Services;

use App\Models\EducationalStage;
use App\Models\ExamResult;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    /**
     * حساب لوحة الشرف للأوائل والمتميزين مع تفعيل الكاش والاستعلام الأمثل
     */
    public function getTopStudents(?int $stageId = null, ?int $groupId = null, int $limit = 10): Collection
    {
        $cacheKey = "leaderboard_top_students_{$stageId}_{$groupId}_{$limit}";

        return Cache::remember($cacheKey, 60, function () use ($stageId, $groupId, $limit) {
            $query = Student::query()
                ->select('id', 'name', 'phone', 'stage_id', 'qr_code')
                ->with([
                    'educationalStage:id,name',
                    'groups:id,name',
                    'attendances:id,student_id,status',
                ]);

            if ($stageId) {
                $query->where('stage_id', $stageId);
            }

            if ($groupId) {
                $query->whereHas('groups', function ($q) use ($groupId) {
                    $q->where('groups.id', $groupId);
                });
            }

            $students = $query->get();

            if ($students->isEmpty()) {
                return collect([]);
            }

            $studentIds = $students->pluck('id')->toArray();

            // جلب جميع نتائج الامتحانات لجميع الطلاب المعنيين دفعة واحدة لمنع N+1
            $allExamResults = ExamResult::whereIn('student_id', $studentIds)
                ->select('id', 'student_id', 'exam_id', 'marks_obtained')
                ->with('exam:id,title,total_marks')
                ->get()
                ->groupBy('student_id');

            // حساب النقاط والنسبة لكل طالب
            $rankedStudents = $students->map(function ($student) use ($allExamResults) {
                $examResults = $allExamResults->get($student->id, collect([]));
                $attendances = $student->attendances;

                $totalMax = 0;
                $totalScore = 0;

                foreach ($examResults as $res) {
                    $max = $res->exam?->total_marks ?? 100;
                    $score = $res->marks_obtained ?? 0;
                    $totalMax += $max;
                    $totalScore += $score;
                }

                $examPercentage = $totalMax > 0 ? ($totalScore / $totalMax) * 100 : 0;
                $examsCount = $examResults->count();

                $totalSessions = $attendances->count();
                $presentCount = $attendances->where('status', 'present')->count();
                $attendanceRate = $totalSessions > 0 ? ($presentCount / $totalSessions) * 100 : 100;

                // النقاط الكلية المحسوبة = النسبة المئوية للامتحانات + مكافأة الالتزام بالحضور
                $totalPoints = round(($examPercentage * 0.75) + ($attendanceRate * 0.25) + ($examsCount * 2), 1);

                return [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'qr_code' => $student->qr_code,
                    'total_points' => $totalPoints,
                    'exam_average' => round($examPercentage, 1),
                    'exams_count' => $examsCount,
                    'attendance_rate' => round($attendanceRate, 1),
                    'stage_name' => $student->educationalStage?->name ?? 'غير محدد',
                    'group_name' => $student->groups->first()?->name ?? 'عام',
                ];
            });

            return $rankedStudents
                ->filter(fn ($item) => $item['exams_count'] > 0 || $item['attendance_rate'] > 0)
                ->sortByDesc('total_points')
                ->values()
                ->take($limit)
                ->map(function ($item, $index) {
                    $item['rank'] = $index + 1;
                    $item['badge'] = match ($index + 1) {
                        1 => 'المركز الأول (الذهبي)',
                        2 => 'المركز الثاني (الفضي)',
                        3 => 'المركز الثالث (البرونزي)',
                        default => 'المركز ' . ($index + 1),
                    };
                    return $item;
                });
        });
    }
}
