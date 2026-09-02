<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function require(): Tenant
    {
        return $this->tenant ?? throw new \RuntimeException('لا يوجد سياق للمؤسسة/المدرس حالياً (Tenant context missing).');
    }
}
