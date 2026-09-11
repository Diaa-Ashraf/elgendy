<?php

namespace App\Filament\Resources\ParentNotificationResource\Pages;

use App\Filament\Resources\ParentNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParentNotifications extends ListRecords
{
    protected static string $resource = ParentNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
