<?php

namespace App\Filament\Resources\HomeworkResource\Pages;

use App\Filament\Resources\HomeworkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomework extends CreateRecord
{
    protected static string $resource = HomeworkResource::class;

    protected function getRedirectUrl(): string
    {
        // التحويل تلقائياً لصفحة التعديل لإضافة الأسئلة إن كان نوع الواجب يتضمن أسئلة
        if (in_array($this->record->type, ['questions', 'mixed'])) {
            return $this->getResource()::getUrl('edit', ['record' => $this->record]);
        }

        return $this->getResource()::getUrl('index');
    }
}
