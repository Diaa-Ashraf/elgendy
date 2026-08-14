<?php

namespace App\Filament\Resources\StudentMaterialDeliveryResource\Pages;

use App\Filament\Resources\StudentMaterialDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentMaterialDeliveries extends ListRecords
{
    protected static string $resource = StudentMaterialDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
