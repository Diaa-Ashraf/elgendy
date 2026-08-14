<?php

namespace App\Filament\Resources\StudentMaterialDeliveryResource\Pages;

use App\Filament\Resources\StudentMaterialDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentMaterialDelivery extends EditRecord
{
    protected static string $resource = StudentMaterialDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
