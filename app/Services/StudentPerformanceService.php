<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;

class StudentPerformanceService
{
    /**
     * حساب تقرير الأداء الشامل لطالب محدد مع تفعيل الكاش والاستعلام المحسن
     */
    public function getStudentAnalytics(Student|int $student): array
    {
        $studentId = is_numeric($student) ? (int) $student : $student->id;

        return Cache::remember("student_analytics_perf_{$studentId}", 60, function () use ($student, $studentId) {
            $studentModel = is_numeric($student)
                ? Student::select('id', 'name', 'phone', 'parent_phone', 'stage_id', 'qr_code')
                    ->with([
                        'educationalStage:id,name',
                        'groups:id,name,subject_id',
                        'groups.subject:id,name',
                    ])
                    ->findOrFail($studentId)
                : $student;


            // 1. إحصائيات الحضور والغياب (استعلام مجمع وسريع عبر groupBy)
            $attendanceCounts = Attendance::where('student_id', $studentId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $presentCount = $attendanceCounts['present'] ?? 0;
            $absentCount = $attendanceCounts['absent'] ?? 0;
            $lateCount = $attendanceCounts['late'] ?? 0;
            $excusedCount = $attendanceCounts['excused'] ?? 0;
            $totalSessions = array_sum($attendanceCounts);

            $attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 1) : 100;

            // 2. إحصائيات الامتحانات الورقية والإلكترونية
            $examResults = ExamResult::where('student_id', $studentId)
                ->select('id', 'student_id', 'exam_id', 'marks_obtained', 'created_at')
                ->with(['exam:id,title,total_marks,date'])
                ->orderBy('id', 'desc')
                ->get();

            $totalExams = $examResults->count();
            $totalScoreObtained = 0;
            $totalMaxScore = 0;
            $highestScorePercent = 0;
            $lowestScorePercent = $totalExams > 0 ? 100 : 0;
            $examHistory = [];

            foreach ($examResults as $res) {
                $max = $res->exam?->total_marks ?? 100;
                $score = $res->marks_obtained ?? 0;
                $percentage = $max > 0 ? round(($score / $max) * 100, 1) : 0;

                $totalScoreObtained += $score;
                $totalMaxScore += $max;

                if ($percentage > $highestScorePercent) {
                    $highestScorePercent = $percentage;
                }
                if ($percentage < $lowestScorePercent) {
                    $lowestScorePercent = $percentage;
                }

                $examHistory[] = [
                    'title' => $res->exam?->title ?? 'امتحان',
                    'date' => $res->exam?->date ?? $res->created_at?->format('Y-m-d') ?? '',
                    'score' => $score,
                    'max' => $max,
                    'percentage' => $percentage,
                ];
            }

            $averageExamScore = $totalMaxScore > 0 ? round(($totalScoreObtained / $totalMaxScore) * 100, 1) : 0;

            // 3. إحصائيات الواجبات المنزلية
            $homeworkSubmissions = HomeworkSubmission::where('student_id', $studentId)
                ->select('id', 'student_id', 'score')
                ->get();

            $submittedHomeworksCount = $homeworkSubmissions->count();
            $gradedHomeworks = $homeworkSubmissions->whereNotNull('score');
            $averageHomeworkScore = $gradedHomeworks->count() > 0 ? round($gradedHomeworks->avg('score'), 1) : null;

            // 4. التقييم العام ومؤشر الأداء (Overall Grade Index)
            // إذا لم يكن هناك امتحانات مسجلة للطالب بعد، لا نعاقبه بدرجة صفر
            if ($totalExams === 0) {
                // تقييم معتمد على الحضور (80%) والواجبات (20%)
                $overallScore = round(($attendanceRate * 0.8) + (($averageHomeworkScore ?? 100) * 0.2), 1);
            } else {
                $scoreWeight = $averageExamScore * 0.65; // 65% امتحانات
                $attendanceWeight = $attendanceRate * 0.25; // 25% حضور
                $homeworkWeight = ($averageHomeworkScore ?? $averageExamScore) * 0.10; // 10% واجبات
                $overallScore = round($scoreWeight + $attendanceWeight + $homeworkWeight, 1);
            }

            $gradeLevel = match (true) {
                $overallScore >= 90 => ['label' => 'ممتاز ومرتفع', 'color' => 'success', 'badge' => 'A+'],
                $overallScore >= 80 => ['label' => 'جيد جداً متفوق', 'color' => 'primary', 'badge' => 'A'],
                $overallScore >= 70 => ['label' => 'جيد ومجتهد', 'color' => 'info', 'badge' => 'B'],
                $overallScore >= 55 => ['label' => 'متوسط ويحتاج متابعة', 'color' => 'warning', 'badge' => 'C'],
                default => ['label' => 'يحتاج اهتمام ومتابعة', 'color' => 'danger', 'badge' => 'D'],
            };

            return [
                'student' => $studentModel,
                'attendance' => [
                    'total' => $totalSessions,
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'excused' => $excusedCount,
                    'rate' => $attendanceRate,
                ],
                'exams' => [
                    'total' => $totalExams,
                    'average' => $averageExamScore,
                    'highest' => $highestScorePercent,
                    'lowest' => $lowestScorePercent,
                    'history' => array_reverse($examHistory),
                ],
                'homeworks' => [
                    'submitted' => $submittedHomeworksCount,
                    'average' => $averageHomeworkScore,
                ],
                'overall' => [
                    'score' => $overallScore,
                    'grade' => $gradeLevel,
                ],
            ];
        });
    }

    /**
     * كشف الطلاب المعرضين للتعثر أو الهبوط (Early Warning Engine)
     */
    public function getAtRiskStudents(int $limit = 15): array
    {
        return Cache::remember('at_risk_students_list_v1', 60, function () use ($limit) {
            $students = Student::select('id', 'name', 'phone', 'parent_phone', 'stage_id', 'qr_code')
                ->with(['educationalStage:id,name', 'groups:id,name'])
                ->get();

            $atRiskList = [];

            foreach ($students as $student) {
                // 1. فحص آخر 5 حصص
                $recentAttendances = Attendance::where('student_id', $student->id)
                    ->orderBy('id', 'desc')
                    ->take(5)
                    ->pluck('status')
                    ->toArray();

                $recentAbsentCount = count(array_filter($recentAttendances, fn($s) => $s === 'absent'));
                $consecutiveAbsences = 0;
                foreach ($recentAttendances as $st) {
                    if ($st === 'absent') {
                        $consecutiveAbsences++;
                    } else {
                        break;
                    }
                }

                // 2. فحص آخر امتحانين
                $recentExams = ExamResult::where('student_id', $student->id)
                    ->with('exam')
                    ->orderBy('id', 'desc')
                    ->take(2)
                    ->get();

                $examPercentages = [];
                foreach ($recentExams as $re) {
                    if ($re->exam && $re->exam->total_marks > 0) {
                        $examPercentages[] = round(($re->marks_obtained / $re->exam->total_marks) * 100, 1);
                    }
                }

                $examDrop = false;
                $lowExams = false;
                if (count($examPercentages) >= 2 && ($examPercentages[1] - $examPercentages[0]) >= 20) {
                    $examDrop = true; // هبوط في الدرجة بأكثر من 20%
                }
                if (count($examPercentages) > 0 && end($examPercentages) < 50) {
                    $lowExams = true; // رسوب أو دون 50%
                }

                // تحديد أسباب الإنذار
                $reasons = [];
                $riskSeverity = 'low';

                if ($consecutiveAbsences >= 2) {
                    $reasons[] = "غياب متتالي في آخر {$consecutiveAbsences} حصص";
                    $riskSeverity = 'high';
                } elseif ($recentAbsentCount >= 2) {
                    $reasons[] = "تكرار الغياب ({$recentAbsentCount} من آخر 5 حصص)";
                    $riskSeverity = $riskSeverity === 'high' ? 'high' : 'medium';
                }

                if ($lowExams) {
                    $reasons[] = "درجة الامتحان الأخير أقل من 50% ({$examPercentages[0]}%)";
                    $riskSeverity = 'high';
                } elseif ($examDrop) {
                    $dropDiff = round($examPercentages[1] - $examPercentages[0]);
                    $reasons[] = "تراجع في درجة الامتحان الأخير بمقدار {$dropDiff}%";
                    $riskSeverity = $riskSeverity === 'high' ? 'high' : 'medium';
                }

                if (! empty($reasons)) {
                    $atRiskList[] = [
                        'student' => $student,
                        'reasons' => $reasons,
                        'severity' => $riskSeverity,
                        'severity_label' => $riskSeverity === 'high' ? 'إنذار مرتفع 🚨' : 'تنبيه متابعة ⚠️',
                        'severity_color' => $riskSeverity === 'high' ? 'danger' : 'warning',
                        'last_attendance_status' => $recentAttendances[0] ?? '—',
                        'last_exam_percentage' => $examPercentages[0] ?? null,
                    ];
                }
            }

            // ترتيب القائمة بحيث يظهر الخطر المرتفع أولاً
            usort($atRiskList, function ($a, $b) {
                if ($a['severity'] === $b['severity']) {
                    return 0;
                }
                return $a['severity'] === 'high' ? -1 : 1;
            });

            return array_slice($atRiskList, 0, $limit);
        });
    }
}

