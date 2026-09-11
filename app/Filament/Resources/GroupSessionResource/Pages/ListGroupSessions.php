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
                    $group = \App\Models\Group::find((int) $data['group_id']);
                    if (!$group || $group->schedules()->count() === 0) {
                        Notification::make()
                            ->title('تنبيه: لا يوجد جدول مواعيد لهذه المجموعة!')
                            ->body('يرجى تعديل المجموعة وإضافة مواعيد الحصص الأسبوعية أولاً.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $createdCount = $attendanceService->generateSessions(
                        (int) $data['group_id'],
                        $data['from_date'],
                        $data['to_date']
                    );

                    if ($createdCount > 0) {
                        Notification::make()
                            ->title("تم توليد {$createdCount} جلسة جديدة بنجاح وفق جدول المجموعة")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('لم يتم توليد جلسات جديدة (موجودة مسبقاً)')
                            ->body('جميع حصص هذه المجموعة في الفترة المحددة مطابقة للجدول وتم توليدها مسبقاً.')
                            ->info()
                            ->send();
                    }
                }),

            Actions\CreateAction::make()
                ->label('إضافة جلسة واحدة'),
        ];
    }
}
