<?php

namespace App\Filament\Resources\ParentNotificationResource\Pages;

use App\Filament\Resources\ParentNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParentNotification extends EditRecord
{
    protected static string $resource = ParentNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
