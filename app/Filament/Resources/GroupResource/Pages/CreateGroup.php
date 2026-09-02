<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;

    protected function beforeCreate(): void
    {
        $tenant = app(\App\Services\TenantContext::class)->get();
        if ($tenant && ! app(\App\Services\SubscriptionService::class)->canAddGroups($tenant)) {
            \Filament\Notifications\Notification::make()
                ->title('لقد استنفذت الحد الأقصى للمجموعات المسموح به في خطتك الحالية! يرجى ترقية الخطة.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء المجموعة بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
