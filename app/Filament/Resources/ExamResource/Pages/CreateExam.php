<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExam extends CreateRecord
{
    protected static string $resource = ExamResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة بيانات الامتحان بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        // إذا كان الامتحان أونلاين، نوجهه لصفحة التعديل فوراً لإضافة الأسئلة
        if ($this->record->is_online) {
            return $this->getResource()::getUrl('edit', ['record' => $this->record]);
        }

        return $this->getResource()::getUrl('index');
    }
}
