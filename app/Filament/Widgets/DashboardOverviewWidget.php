<?php

namespace App\Filament\Widgets;

use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DashboardOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $year = now()->year;
        $month = now()->month;

        $stats = Cache::remember("dashboard_overview_stats_{$year}_{$month}", 60, function () use ($year, $month) {
            $totalStudents = Student::count();
            $totalGroups = Group::where('status', 'active')->count();
            $totalStages = EducationalStage::count();
            $thisMonthRevenue = (float) StudentPayment::whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->sum('amount');

            return [
                'totalStudents' => $totalStudents,
                'totalGroups' => $totalGroups,
                'totalStages' => $totalStages,
                'thisMonthRevenue' => $thisMonthRevenue,
            ];
        });

        return [
            Stat::make('إجمالي الطلاب المسجلين', $stats['totalStudents'])
                ->description('إجمالي إحصائي في السنتر')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('المجموعات النشطة', $stats['totalGroups'])
                ->description("موزعة على {$stats['totalStages']} مراحل دراسية")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('إيرادات الشهر الحالي', number_format($stats['thisMonthRevenue'], 2) . ' ج.م')
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}

