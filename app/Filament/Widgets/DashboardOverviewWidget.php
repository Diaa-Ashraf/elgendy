<?php

namespace App\Filament\Widgets;

use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalStudents = Student::count();
        $totalGroups = Group::where('status', 'active')->count();
        $totalStages = EducationalStage::count();
        $thisMonthRevenue = StudentPayment::whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        return [
            Stat::make('إجمالي الطلاب المسجلين', $totalStudents)
                ->description('إجمالي إحصائي في السنتر')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('المجموعات النشطة', $totalGroups)
                ->description("موزعة على {$totalStages} مراحل دراسية")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('إيرادات الشهر الحالي', number_format($thisMonthRevenue, 2) . ' ج.م')
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
