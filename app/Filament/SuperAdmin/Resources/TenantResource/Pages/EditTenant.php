<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\Pages;

use App\Filament\SuperAdmin\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $subscription = $this->record->subscription;
        if ($subscription) {
            $data['plan_id'] = $subscription->plan_id;
            $data['subscription_status'] = $subscription->status;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();
        $subscription = $this->record->subscription;

        if ($subscription && isset($data['plan_id'])) {
            $subscription->update([
                'plan_id' => $data['plan_id'],
                'status' => $data['subscription_status'] ?? $subscription->status,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
