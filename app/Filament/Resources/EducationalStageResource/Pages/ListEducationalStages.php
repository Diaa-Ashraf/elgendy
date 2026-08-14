<?php

namespace App\Filament\Resources\EducationalStageResource\Pages;

use App\Filament\Resources\EducationalStageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEducationalStages extends ListRecords
{
    protected static string $resource = EducationalStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مرحلة دراسية'),
        ];
    }
}
