<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // 1. Global Scope: قصر النتائج دوماً على الـ tenant الحالي
        static::addGlobalScope('tenant', function ($query) {
            if ($tenantId = app(TenantContext::class)->id()) {
                $query->where($query->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        // 2. Creating Observer: حقن tenant_id تلقائياً عند إنشاء أي سجل
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $tenantId = app(TenantContext::class)->id();
                if (! $tenantId && class_exists(\Filament\Facades\Filament::class)) {
                    $tenantId = \Filament\Facades\Filament::getTenant()?->id;
                }
                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
