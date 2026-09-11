<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\EducationalStage;
use App\Models\ExamResult;
use App\Models\Expense;
use App\Models\Group;
use App\Models\GroupSession;
use App\Models\Salary;
use App\Models\Student;
use App\Models\StudentPayment;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdvancedAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'التحليلات المتقدمة';

    protected static ?string $title = 'التحليلات المالية والأكاديمية المتقدمة';

    protected static ?string $navigationGroup = 'التقارير والإحصائيات';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.advanced-analytics';

    public static function canAccess(): bool
    {
        return (bool) Auth::user();
    }

    protected ?array $memoizedAnalytics = null;

    public function getAnalyticsData(): array
    {
        if ($this->memoizedAnalytics !== null) {
            return $this->memoizedAnalytics;
        }

        $cacheKey = 'admin_advanced_analytics_dashboard_v3';

        return $this->memoizedAnalytics = Cache::remember($cacheKey, 60, function () {
            // 1. الإيرادات والمصروفات والرواتب خلال 6 أشهر ماضية

            $monthlyChart = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthDate = now()->subMonths($i);
                $year = $monthDate->year;
                $month = $monthDate->month;

                $rev = (float) StudentPayment::whereYear('paid_at', $year)->whereMonth('paid_at', $month)->sum('amount');
                $exp = (float) Expense::whereYear('date', $year)->whereMonth('date', $month)->sum('amount');
                $sal = (float) Salary::whereYear('paid_at', $year)->whereMonth('paid_at', $month)->sum('amount_paid');
                $totalExp = $exp + $sal;

                $monthlyChart[] = [
                    'month' => $monthDate->format('Y-m'),
                    'label' => $monthDate->translatedFormat('F Y'),
                    'revenue' => $rev,
                    'expenses' => $totalExp,
                    'profit' => $rev - $totalExp,
                ];
            }

            // 2. توزيع الطلاب والمجموعات حسب المراحل الدراسية
            $stageDistribution = EducationalStage::withCount(['students', 'groups'])->get()->map(function ($stg) {
                return (object) [
                    'name' => $stg->name,
                    'count' => $stg->students_count,
                    'groups_count' => $stg->groups_count,
                ];
            });

            // 3. تحليل أداء المجموعات الدراسية وربحيتها (Group Profitability & Capacity)
            $groupsAnalytics = Group::with(['educationalStage', 'subject'])
                ->withCount('students')
                ->where('status', 'active')
                ->get()
                ->map(function ($grp) {
                    $expectedMonthly = (float) ($grp->students_count * ($grp->price_per_month ?? 0));
                    return [
                        'name' => $grp->name,
                        'stage' => $grp->educationalStage?->name ?? '-',
                        'subject' => $grp->subject?->name ?? '-',
                        'students_count' => $grp->students_count,
                        'price' => (float) $grp->price_per_month,
                        'expected_revenue' => $expectedMonthly,
                    ];
                });

            // 4. مؤشرات الامتحانات ومستوى التحصيل العام (Academic Quality Metrics)
            $totalExamsCount = \App\Models\Exam::count();
            $totalExamResults = ExamResult::count();
            $avgExamScore = 0;
            if ($totalExamResults > 0) {
                $results = ExamResult::with('exam')->select('marks_obtained', 'exam_id')->get();
                $sumPercent = 0;
                $validCount = 0;
                foreach ($results as $res) {
                    $max = $res->exam?->total_marks ?? 100;
                    if ($max > 0) {
                        $sumPercent += (($res->marks_obtained / $max) * 100);
                        $validCount++;
                    }
                }
                $avgExamScore = $validCount > 0 ? round($sumPercent / $validCount, 1) : 0;
            }

            // 5. اللوجيك الدقيق للحضور والغياب (حساب الحصص التي تمت فعلياً أو مضى تاريخها)
            $heldSessionsCount = GroupSession::where('status', 'held')
                ->orWhere(function ($q) {
                    $q->where('status', 'scheduled')->where('date', '<', now()->toDateString());
                })
                ->count();

            $totalAttendances = Attendance::whereHas('groupSession', function ($q) {
                $q->where('status', 'held')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'scheduled')->where('date', '<', now()->toDateString());
                  });
            })->count();

            $presentAttendances = Attendance::where('status', 'present')
                ->whereHas('groupSession', function ($q) {
                    $q->where('status', 'held')
                      ->orWhere(function ($sq) {
                          $sq->where('status', 'scheduled')->where('date', '<', now()->toDateString());
                      });
                })->count();

            $overallAttendanceRate = $totalAttendances > 0 
                ? round(($presentAttendances / $totalAttendances) * 100, 1) 
                : 0;

            // 6. معدل سداد اشتراكات الشهر الحالي
            $totalActiveStudents = Student::count();
            $paidThisMonthCount = StudentPayment::whereYear('paid_at', now()->year)
                ->whereMonth('paid_at', now()->month)
                ->select('student_id')
                ->pluck('student_id')
                ->unique()
                ->count();

            $paymentCollectionRate = $totalActiveStudents > 0
                ? round(($paidThisMonthCount / $totalActiveStudents) * 100, 1)
                : 0;

            return [
                'monthlyChart' => $monthlyChart,
                'stageDistribution' => $stageDistribution,
                'groupsAnalytics' => $groupsAnalytics,
                'academicMetrics' => [
                    'total_exams' => $totalExamsCount,
                    'avg_exam_score' => $avgExamScore,
                    'overall_attendance_rate' => $overallAttendanceRate,
                    'total_students' => $totalActiveStudents,
                    'held_sessions' => $heldSessionsCount,
                    'present_count' => $presentAttendances,
                    'payment_rate' => $paymentCollectionRate,
                ],
            ];
        });
    }
}
