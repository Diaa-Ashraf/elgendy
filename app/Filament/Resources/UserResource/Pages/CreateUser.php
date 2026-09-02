<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeCreate(): void
    {
        $tenant = app(\App\Services\TenantContext::class)->get();
        if ($tenant && ! app(\App\Services\SubscriptionService::class)->canAddTeachers($tenant)) {
            \Filament\Notifications\Notification::make()
                ->title('لقد استنفذت الحد الأقصى للمساعدين المسموح به في خطتك الحالية! يرجى ترقية الخطة.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة المستخدم وإسناد الأدوار بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
