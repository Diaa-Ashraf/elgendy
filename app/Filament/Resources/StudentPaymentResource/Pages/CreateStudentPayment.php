<?php

namespace App\Filament\Resources\StudentPaymentResource\Pages;

use App\Filament\Resources\StudentPaymentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStudentPayment extends CreateRecord
{
    protected static string $resource = StudentPaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['received_by'] = Auth::id();
        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم تسجيل عملية السداد بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
