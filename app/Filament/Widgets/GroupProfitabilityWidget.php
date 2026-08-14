<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class GroupProfitabilityWidget extends BaseWidget
{
    public ?string $month = null;

    protected static ?string $heading = 'ربحية ومداخيل المجموعات الدراسية';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $selectedMonth = $this->month ?? now()->format('Y-m');
        $reportService = app(ReportService::class);
        $data = $reportService->groupProfitability($selectedMonth);

        return $table
            ->query(
                \App\Models\Group::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المجموعة'),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة'),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('المادة'),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('الطلاب النشطين')
                    ->counts('students'),

                Tables\Columns\TextColumn::make('group_revenue')
                    ->label('إجمالي دخل المجموعة')
                    ->state(function (\App\Models\Group $record) use ($data) {
                        $item = $data->firstWhere('group_id', $record->id);
                        $rev = $item['revenue'] ?? 0.0;
                        return number_format($rev, 2) . ' ج.م';
                    }),
            ])
            ->paginated(false);
    }
}
