<?php

namespace App\Filament\Resources\EducationalStageResource\Pages;

use App\Filament\Resources\EducationalStageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEducationalStage extends EditRecord
{
    protected static string $resource = EducationalStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تعديل المرحلة الدراسية بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
