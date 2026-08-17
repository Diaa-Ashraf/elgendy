<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importStudents')
                ->label('استيراد من Excel 📥')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->url(\App\Filament\Pages\StudentImports::getUrl()),

            Actions\CreateAction::make()
                ->label('إضافة طالب جديد'),
        ];
    }
}
