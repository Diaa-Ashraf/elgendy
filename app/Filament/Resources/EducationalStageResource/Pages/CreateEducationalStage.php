<?php

namespace App\Filament\Resources\EducationalStageResource\Pages;

use App\Filament\Resources\EducationalStageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEducationalStage extends CreateRecord
{
    protected static string $resource = EducationalStageResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة المرحلة الدراسية بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
