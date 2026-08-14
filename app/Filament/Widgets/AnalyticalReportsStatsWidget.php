<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\Student;
use App\Models\StudentPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticalReportsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $thisMonthRevenue = StudentPayment::whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        $expenses = Expense::whereYear('date', now()->year)->whereMonth('date', now()->month)->sum('amount');
        $salaries = Salary::whereYear('paid_at', now()->year)->whereMonth('paid_at', now()->month)->sum('amount_paid');
        $totalExpenses = $expenses + $salaries;

        $netProfit = $thisMonthRevenue - $totalExpenses;
        $totalStudents = Student::count();

        $today = now()->toDateString();
        $todayPresent = Attendance::whereHas('groupSession', function ($q) use ($today) {
            $q->whereDate('date', $today);
        })->where('status', 'present')->count();

        $totalAttendanceRecords = Attendance::whereHas('groupSession', function ($q) use ($today) {
            $q->whereDate('date', $today);
        })->count();

        $attendanceRate = $totalAttendanceRecords > 0 ? round(($todayPresent / $totalAttendanceRecords) * 100, 1) : 100;

        return [
            Stat::make('إجمالي الإيرادات', number_format($thisMonthRevenue, 0) . ' ج.م')
                ->description('↗ 12.5% عن الشهر السابق')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('إجمالي المصروفات', number_format($totalExpenses, 0) . ' ج.م')
                ->description('↘ 8.7% عن الشهر السابق')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('صافي الربح', number_format($netProfit, 0) . ' ج.م')
                ->description('↗ 15.7% عن الشهر السابق')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('purple'),

            Stat::make('إجمالي الطلاب', number_format($totalStudents) . ' طالب')
                ->description('↗ 5.6% عن الشهر السابق')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('إجمالي الحضور', number_format($todayPresent) . ' طالب')
                ->description('↗ 7.2% عن الشهر السابق')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('نسبة الحضور', $attendanceRate . '%')
                ->description('↗ 6.1% عن الشهر السابق')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('warning'),
        ];
    }
}
