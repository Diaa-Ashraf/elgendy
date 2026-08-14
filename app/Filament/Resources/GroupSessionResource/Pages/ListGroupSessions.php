<?php

namespace App\Filament\Resources\GroupSessionResource\Pages;

use App\Filament\Resources\GroupSessionResource;
use App\Services\AttendanceService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListGroupSessions extends ListRecords
{
    protected static string $resource = GroupSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateSessions')
                ->label('توليد جلسات')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('group_id')
                        ->label('المجموعة')
                        ->options(\App\Models\Group::pluck('name', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\DatePicker::make('from_date')
                        ->label('من تاريخ')
                        ->default(now()->startOfMonth())
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('to_date')
                        ->label('إلى تاريخ')
                        ->default(now()->endOfMonth())
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data, AttendanceService $attendanceService): void {
                    $createdCount = $attendanceService->generateSessions(
                        (int) $data['group_id'],
                        $data['from_date'],
                        $data['to_date']
                    );

                    Notification::make()
                        ->title("تم توليد {$createdCount} جلسة جديدة بنجاح وفق جدول المجموعة")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('إضافة جلسة واحدة'),
        ];
    }
}
