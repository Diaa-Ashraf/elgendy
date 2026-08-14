<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Student;
use App\Models\StudentPayment;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function getAnalyticsData(): array
    {
        // 1. الإيرادات والمصروفات خلال 6 أرقام شهرية
        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $year = $monthDate->year;
            $month = $monthDate->month;

            $rev = StudentPayment::whereYear('paid_at', $year)->whereMonth('paid_at', $month)->sum('amount');
            $exp = Expense::whereYear('date', $year)->whereMonth('date', $month)->sum('amount');

            $monthlyChart[] = [
                'month' => $monthDate->format('Y-m'),
                'label' => $monthDate->translatedFormat('M Y'),
                'revenue' => (float) $rev,
                'expenses' => (float) $exp,
                'profit' => (float) ($rev - $exp),
            ];
        }

        // 2. توزيع الطلاب على المراحل الدراسية
        $stageDistribution = DB::table('students')
            ->join('educational_stages', 'students.stage_id', '=', 'educational_stages.id')
            ->select('educational_stages.name', DB::raw('count(students.id) as count'))
            ->whereNull('students.deleted_at')
            ->groupBy('educational_stages.name')
            ->get();

        return [
            'monthlyChart' => $monthlyChart,
            'stageDistribution' => $stageDistribution,
        ];
    }
}
