<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\Student;
use App\Models\StudentPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExecutiveStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $today = now()->toDateString();

        // 1. الطلاب
        $totalStudents = Student::count();

        // 2. الحضور اليوم عبر علاقة الجلسة (whereHas groupSession)
        $todayPresent = Attendance::whereHas('groupSession', function ($query) use ($today) {
            $query->whereDate('date', $today);
        })->where('status', 'present')->count();

        // 3. المدفوع هذا الشهر
        $paidThisMonth = StudentPayment::whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        // 4. المصروفات والرواتب هذا الشهر
        $expensesThisMonth = Expense::whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('amount');
        $salariesThisMonth = Salary::whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount_paid');
        $totalOutflow = $expensesThisMonth + $salariesThisMonth;

        // 5. غياب اليوم
        $todayAbsent = Attendance::whereHas('groupSession', function ($query) use ($today) {
            $query->whereDate('date', $today);
        })->where('status', 'absent')->count();

        // 6. الطلاب المتأخرين في الدفع
        $paidStudentIds = StudentPayment::whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->pluck('student_id')
            ->unique();
        
        $unpaidStudentsCount = Student::whereNotIn('id', $paidStudentIds)->count();

        // 7. طلبات التقديم أونلاين المعلقة
        $pendingApplicationsCount = \App\Models\StudentApplication::where('status', 'pending')->count();

        return [
            Stat::make('طلبات التقديم أونلاين', number_format($pendingApplicationsCount))
                ->description('طلبات جديدة تحتاج مراجعة وقبول')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('warning')
                ->url(url('/admin/student-applications')),

            Stat::make('الطلاب المقيدين', number_format($totalStudents))
                ->description('إجمالي الطلاب المسجلين بالسنتر')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('الحضور اليوم', "حضر {$todayPresent}")
                ->description("غائب {$todayAbsent} طالب")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('المتحصلات (المدفوع)', number_format($paidThisMonth, 0) . ' ج.م')
                ->description('إيرادات الشهر الحالي')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('emerald'),

            Stat::make('المصروفات والرواتب', number_format($totalOutflow, 0) . ' ج.م')
                ->description('مصروفات الشهر الحالي')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('غياب اليوم', number_format($todayAbsent) . ' طالب')
                ->description('يحتاجون متابعة ولي الأمر')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('purple'),
        ];
    }
}
