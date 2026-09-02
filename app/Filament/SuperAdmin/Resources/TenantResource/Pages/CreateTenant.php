<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\Pages;

use App\Filament\SuperAdmin\Resources\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function afterCreate(): void
    {
        $tenant = $this->record;
        $data = $this->form->getRawState();

        $planId = $data['plan_id'] ?? \App\Models\Plan::first()?->id;
        $status = $data['subscription_status'] ?? 'active';

        // 1. إنشاء الاشتراك فوراً
        \App\Models\Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $planId,
            'status' => $status,
            'starts_at' => now(),
            'ends_at' => $status === 'active' ? now()->addMonth() : null,
            'trial_ends_at' => $status === 'trial' ? now()->addDays(7) : null,
        ]);

        // 2. إنشاء المستخدم الإداري للمدرس
        if (! empty($tenant->email)) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $tenant->email],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $data['admin_name'] ?? $tenant->name,
                    'password' => \Illuminate\Support\Facades\Hash::make($data['admin_password'] ?? '123456789'),
                    'is_active' => true,
                ]
            );

            // إعطاؤه صلاحية admin إذا كان الرول موجوداً
            $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $user->assignRole($adminRole);
        }
    }
}
