<?php

namespace App\Filament\Widgets;

use App\Models\Group;
use App\Services\ReportService;
use Filament\Widgets\ChartWidget;

class GroupAttendanceChartWidget extends ChartWidget
{
    public ?string $month = null;

    protected static ?string $heading = 'نسبة الحضور لكل مجموعة نشطة (%)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $selectedMonth = $this->month ?? now()->format('Y-m');
        $reportService = app(ReportService::class);

        $groups = Group::where('status', 'active')->get();
        $labels = [];
        $values = [];

        foreach ($groups as $group) {
            $labels[] = $group->name;
            $values[] = $reportService->attendanceRate($group->id, $selectedMonth);
        }

        return [
            'datasets' => [
                [
                    'label' => 'نسبة الحضور %',
                    'data' => $values,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
