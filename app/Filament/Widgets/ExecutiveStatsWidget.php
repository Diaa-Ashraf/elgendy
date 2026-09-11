<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\StudentPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ExecutiveStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $today = now()->toDateString();
        $year = now()->year;
        $month = now()->month;

        $statsData = Cache::remember("executive_stats_widget_data_{$today}", 60, function () use ($today, $year, $month) {
            // 1. الطلاب
            $totalStudents = Student::count();

            // 2. الحضور والغياب اليوم عبر علاقة الجلسة (whereHas groupSession)
            $todayAttendance = Attendance::whereHas('groupSession', function ($query) use ($today) {
                $query->whereDate('date', $today);
            })
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

            $todayPresent = $todayAttendance['present'] ?? 0;
            $todayAbsent = $todayAttendance['absent'] ?? 0;

            // 3. المدفوع هذا الشهر
            $paidThisMonth = (float) StudentPayment::whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->sum('amount');

            // 4. المصروفات والرواتب هذا الشهر
            $expensesThisMonth = (float) Expense::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $salariesThisMonth = (float) Salary::whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->sum('amount_paid');

            $totalOutflow = $expensesThisMonth + $salariesThisMonth;

            // 5. طلبات التقديم أونلاين المعلقة
            $pendingApplicationsCount = StudentApplication::where('status', 'pending')->count();

            return [
                'pendingApplicationsCount' => $pendingApplicationsCount,
                'totalStudents' => $totalStudents,
                'todayPresent' => $todayPresent,
                'todayAbsent' => $todayAbsent,
                'paidThisMonth' => $paidThisMonth,
                'totalOutflow' => $totalOutflow,
            ];
        });

        return [
            Stat::make('طلبات التقديم أونلاين', number_format($statsData['pendingApplicationsCount']))
                ->description('طلبات جديدة تحتاج مراجعة وقبول')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('warning')
                ->url(url('/admin/student-applications')),

            Stat::make('الطلاب المقيدين', number_format($statsData['totalStudents']))
                ->description('إجمالي الطلاب المسجلين بالسنتر')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('الحضور اليوم', "حضر {$statsData['todayPresent']}")
                ->description("غائب {$statsData['todayAbsent']} طالب")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('المتحصلات (المدفوع)', number_format($statsData['paidThisMonth'], 0) . ' ج.م')
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('emerald'),

            Stat::make('المصروفات والرواتب', number_format($statsData['totalOutflow'], 0) . ' ج.م')
                ->description('مصروفات الشهر الحالي')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('غياب اليوم', number_format($statsData['todayAbsent']) . ' طالب')
                ->description('يحتاجون متابعة ولي الأمر')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('purple'),
        ];
    }
}

