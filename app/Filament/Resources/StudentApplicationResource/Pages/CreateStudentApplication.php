<?php

namespace App\Filament\Resources\StudentApplicationResource\Pages;

use App\Filament\Resources\StudentApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentApplication extends CreateRecord
{
    protected static string $resource = StudentApplicationResource::class;
}
