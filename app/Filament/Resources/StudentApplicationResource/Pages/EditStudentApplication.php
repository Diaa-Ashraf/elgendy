<?php

namespace App\Filament\Resources\StudentApplicationResource\Pages;

use App\Filament\Resources\StudentApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentApplication extends EditRecord
{
    protected static string $resource = StudentApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
