<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function beforeCreate(): void
    {
        $tenant = app(\App\Services\TenantContext::class)->get();
        if ($tenant && ! app(\App\Services\SubscriptionService::class)->canAddStudents($tenant)) {
            \Filament\Notifications\Notification::make()
                ->title('لقد استنفذت الحد الأقصى للطلاب المسموح به في خطتك الحالية! يرجى ترقية الخطة.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة بيانات الطالب بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
