<?php

namespace App\Filament\Resources\OnlinePaymentRequestResource\Pages;

use App\Filament\Resources\OnlinePaymentRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOnlinePaymentRequests extends ListRecords
{
    protected static string $resource = OnlinePaymentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
