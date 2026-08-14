<?php

namespace App\Filament\Resources\GroupSessionResource\Pages;

use App\Filament\Resources\GroupSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGroupSession extends CreateRecord
{
    protected static string $resource = GroupSessionResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة الجلسة بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
