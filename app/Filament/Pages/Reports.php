<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\Student;
use App\Models\StudentPayment;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'التقارير التحليلية';

    protected static ?string $title = 'التقارير التحليلية والمالية';

    protected static ?string $navigationGroup = 'التقارير والإحصائيات';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.analytical-reports';

    public ?string $from_date = null;
    public ?string $to_date = null;
    public ?string $period_type = 'this_month';

    public function mount(): void
    {
        $this->from_date = now()->startOfMonth()->toDateString();
        $this->to_date = now()->toDateString();
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::user();
    }

    public function updatedPeriodType($value): void
    {
        $this->memoizedAnalytics = null;
        if ($value === 'this_month') {
            $this->from_date = now()->startOfMonth()->toDateString();
            $this->to_date = now()->toDateString();
        } elseif ($value === 'last_month') {
            $this->from_date = now()->subMonth()->startOfMonth()->toDateString();
            $this->to_date = now()->subMonth()->endOfMonth()->toDateString();
        } elseif ($value === 'this_year') {
            $this->from_date = now()->startOfYear()->toDateString();
            $this->to_date = now()->toDateString();
        }
    }

    public function updatedFromDate(): void
    {
        $this->memoizedAnalytics = null;
        $this->period_type = 'custom';
    }

    public function updatedToDate(): void
    {
        $this->memoizedAnalytics = null;
        $this->period_type = 'custom';
    }

    protected function getViewData(): array
    {
        return [
            'data' => $this->getAnalyticsData(),
        ];
    }

    protected ?array $memoizedAnalytics = null;

    public function getAnalyticsData(): array
    {
        if ($this->memoizedAnalytics !== null) {
            return $this->memoizedAnalytics;
        }

        $from = $this->from_date ?? now()->startOfMonth()->toDateString();
        $to = $this->to_date ?? now()->toDateString();

        $cacheKey = "admin_reports_analytics_{$from}_{$to}";

        return $this->memoizedAnalytics = Cache::remember($cacheKey, 60, function () use ($from, $to) {
            // 1. حساب الإيرادات الفعلية مع select محدد
            $revenue = (float) StudentPayment::whereBetween('paid_at', [$from, $to])->sum('amount');


            // 2. حساب المصروفات والرواتب الفعلية
            $expenses = (float) Expense::whereBetween('date', [$from, $to])->sum('amount');
            $salaries = (float) Salary::whereBetween('paid_at', [$from, $to])->sum('amount_paid');
            $totalExpenses = $expenses + $salaries;

            // 3. صافي الربح
            $netProfit = $revenue - $totalExpenses;

            // 4. إجمالي الطلاب المسجلين بالسيستم
            $totalStudents = Student::count();

            // 5. حساب الحضور مع eager loading و select محددين
            $todayPresent = Attendance::whereHas('groupSession', function ($q) use ($from, $to) {
                $q->whereBetween('date', [$from, $to]);
            })->where('status', 'present')->count();

            $totalAttendanceRecords = Attendance::whereHas('groupSession', function ($q) use ($from, $to) {
                $q->whereBetween('date', [$from, $to]);
            })->count();

            $attendanceRate = $totalAttendanceRecords > 0 
                ? round(($todayPresent / $totalAttendanceRecords) * 100, 1) 
                : 0;

            // 6. متوسط الرسوم ومعدل السداد
            $avgFee = $totalStudents > 0 ? round($revenue / max($totalStudents, 1), 2) : 0;
            
            $paidStudentIds = StudentPayment::whereBetween('paid_at', [$from, $to])
                ->select('student_id')
                ->pluck('student_id')
                ->unique();
            
            $paidStudentsCount = $paidStudentIds->count();
            $lateStudentsCount = max(0, $totalStudents - $paidStudentsCount);
            $paymentRate = $totalStudents > 0 ? round(($paidStudentsCount / $totalStudents) * 100, 1) : 0;

            // 7. إجمالي العمليات المعاملات المالية
            $totalTransactions = StudentPayment::whereBetween('paid_at', [$from, $to])->count() 
                + Expense::whereBetween('date', [$from, $to])->count();

            return [
                'revenue' => $revenue,
                'expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'total_students' => $totalStudents,
                'today_present' => $todayPresent,
                'attendance_rate' => $attendanceRate,
                'avg_fee' => $avgFee,
                'payment_rate' => $paymentRate,
                'late_students' => $lateStudentsCount,
                'total_transactions' => $totalTransactions,
            ];
        });
    }
}
