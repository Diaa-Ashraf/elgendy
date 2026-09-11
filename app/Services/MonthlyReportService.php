<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Models\StudentPayment;
use Carbon\Carbon;

class MonthlyReportService
{
    /**
     * تجميع تقرير الأداء الشهري الشامل للطالب
     *
     * @param int $studentId
     * @param int|null $month (1 - 12)
     * @param int|null $year
     * @return array
     */
    public function getMonthlyReport(int $studentId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? (int) now()->format('m');
        $year = $year ?? (int) now()->format('Y');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $student = Student::with(['educationalStage', 'groups.subject'])->findOrFail($studentId);

        // ─── 1. سجل الحضور والغياب خلال الشهر ───
        $attendances = Attendance::where('student_id', $student->id)
            ->whereHas('groupSession', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with(['groupSession.group'])
            ->get();

        $totalSessions = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $attendanceRate = $totalSessions > 0 ? round((($presentCount + ($lateCount * 0.5)) / $totalSessions) * 100, 1) : 100;

        // ─── 2. سجل الامتحانات ونتائجها ───
        $examResults = ExamResult::where('student_id', $student->id)
            ->whereHas('exam', function ($q) use ($startDate, $endDate, $student) {
                $q->where('stage_id', $student->stage_id)
                  ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with('exam')
            ->get();

        $examsSummary = [];
        $totalExamMarksObtained = 0;
        $totalExamMarksMax = 0;

        foreach ($examResults as $res) {
            $exam = $res->exam;
            if (! $exam) continue;

            $totalExamMarksObtained += $res->marks_obtained;
            $totalExamMarksMax += $exam->total_marks;

            $percentage = $exam->total_marks > 0 ? round(($res->marks_obtained / $exam->total_marks) * 100, 1) : 0;

            // حساب ترتيب الطالب في هذا الامتحان
            $rank = ExamResult::where('exam_id', $exam->id)
                ->where('marks_obtained', '>', $res->marks_obtained)
                ->count() + 1;

            $gradeText = $percentage >= 90 ? 'ممتاز 🌟' : ($percentage >= 75 ? 'جيد جداً 👍' : ($percentage >= 50 ? 'مقبول ⚠️' : 'راسب ❌'));

            $examsSummary[] = [
                'title' => $exam->title,
                'date' => $exam->date?->format('Y-m-d') ?? '—',
                'marks_obtained' => $res->marks_obtained,
                'total_marks' => $exam->total_marks,
                'percentage' => $percentage,
                'rank' => $rank,
                'grade_text' => $gradeText,
                'notes' => $res->notes,
            ];
        }

        $overallExamPercentage = $totalExamMarksMax > 0 ? round(($totalExamMarksObtained / $totalExamMarksMax) * 100, 1) : null;

        // ─── 3. سجل تسليم الواجبات ───
        $homeworks = Homework::where('stage_id', $student->stage_id)
            ->whereBetween('due_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $submissions = HomeworkSubmission::where('student_id', $student->id)
            ->whereIn('homework_id', $homeworks->pluck('id'))
            ->get()
            ->keyBy('homework_id');

        $homeworkSummary = [];
        $submittedCount = 0;
        $missingCount = 0;

        foreach ($homeworks as $hw) {
            $sub = $submissions->get($hw->id);
            if ($sub) {
                $submittedCount++;
                $status = $sub->status === 'graded' ? "مصحح ({$sub->marks_obtained}/{$hw->total_marks})" : 'تم التسليم بنجاح';
                $isSubmitted = true;
            } else {
                $missingCount++;
                $status = 'لم يتم التسليم ❌';
                $isSubmitted = false;
            }

            $homeworkSummary[] = [
                'title' => $hw->title,
                'due_date' => $hw->due_date?->format('Y-m-d') ?? '—',
                'status' => $status,
                'is_submitted' => $isSubmitted,
                'marks' => $sub?->marks_obtained,
                'max_marks' => $hw->total_marks,
            ];
        }

        $homeworkCommitmentRate = $homeworks->count() > 0 ? round(($submittedCount / $homeworks->count()) * 100, 1) : 100;

        // ─── 4. الموقف المالي للمدفوعات ───
        $payments = StudentPayment::where('student_id', $student->id)
            ->whereBetween('paid_at', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $paidAmount = $payments->sum('amount');

        // ─── 5. التقييم العام للشهر ───
        $evalScore = 0;
        $evalFactors = 0;

        if ($totalSessions > 0) {
            $evalScore += $attendanceRate;
            $evalFactors++;
        }
        if ($overallExamPercentage !== null) {
            $evalScore += $overallExamPercentage;
            $evalFactors++;
        }
        if ($homeworks->count() > 0) {
            $evalScore += $homeworkCommitmentRate;
            $evalFactors++;
        }

        $monthlyAverage = $evalFactors > 0 ? round($evalScore / $evalFactors, 1) : 100;

        if ($monthlyAverage >= 90) {
            $generalRating = 'طالب متميز جداً (A+) — أداء استثنائي متكامل 🏆';
            $recommendation = 'يظهر الطالب التزاماً تاماً واستيعاباً ممتازاً لكافة جوانب المنهج. نوصي بالاستمرار في حل أسئلة المستويات العليا.';
            $ratingBadge = 'ممتاز';
            $ratingColor = '#10b981';
        } elseif ($monthlyAverage >= 75) {
            $generalRating = 'طالب جيد جداً (B) — أداء قوي ومنتظم 🌟';
            $recommendation = 'مستوى الطالب جيد جداً مع التزام ملحوظ. يحتاج إلى مزيد من التركيز في مراجعة الأخطاء البسيطة في الامتحانات.';
            $ratingBadge = 'جيد جداً';
            $ratingColor = '#3b82f6';
        } elseif ($monthlyAverage >= 60) {
            $generalRating = 'طالب متوسط (C) — يحتاج متابعة إضافية ⚠️';
            $recommendation = 'مستوى الطالب يحتاج إلى تكثيف ساعات المذاكرة وحل التدريبات والالتزام بكافة الحصص وعدم تكرار الغياب.';
            $ratingBadge = 'متوسط';
            $ratingColor = '#f59e0b';
        } else {
            $generalRating = 'يحتاج إلى تدخل ودعم فوري (D) ❌';
            $recommendation = 'نرجو من ولي الأمر التواصل العاجل مع إدارة المركز والمعلم لتحديد خطة علاجية لتعويض الفاقد التعليمي.';
            $ratingBadge = 'دون المستوى';
            $ratingColor = '#ef4444';
        }

        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        return [
            'student' => $student,
            'month' => $month,
            'year' => $year,
            'month_name' => ($arabicMonths[$month] ?? $month) . ' ' . $year,
            'attendance' => [
                'total_sessions' => $totalSessions,
                'present' => $presentCount,
                'late' => $lateCount,
                'absent' => $absentCount,
                'rate' => $attendanceRate,
                'records' => $attendances,
            ],
            'exams' => [
                'list' => $examsSummary,
                'total_exams' => count($examsSummary),
                'average_percentage' => $overallExamPercentage,
            ],
            'homeworks' => [
                'list' => $homeworkSummary,
                'total_homeworks' => $homeworks->count(),
                'submitted' => $submittedCount,
                'missing' => $missingCount,
                'commitment_rate' => $homeworkCommitmentRate,
            ],
            'payments' => [
                'paid_amount' => $paidAmount,
                'records' => $payments,
            ],
            'evaluation' => [
                'monthly_average' => $monthlyAverage,
                'general_rating' => $generalRating,
                'recommendation' => $recommendation,
                'rating_badge' => $ratingBadge,
                'rating_color' => $ratingColor,
            ],
        ];
    }
}
