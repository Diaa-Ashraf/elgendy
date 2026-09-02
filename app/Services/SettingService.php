<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\TenantSetting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get setting value by key with tenant-aware cache.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $tenant = app(TenantContext::class)->get();

        // 1. إذا وُجد سياق Tenant حالي (SaaS Mode)
        if ($tenant) {
            $tenantId = $tenant->id;
            return Cache::remember("tenant:{$tenantId}:settings:{$key}", 86400, function () use ($tenant, $key, $default) {
                // فحص الإعدادات السريعة في json settings أولاً
                if (isset($tenant->settings[$key])) {
                    return $tenant->settings[$key];
                }

                // فحص جدول tenant_settings
                $ts = TenantSetting::where('tenant_id', $tenant->id)->where('key', $key)->first();
                if ($ts) {
                    return $ts->value;
                }

                return $default;
            });
        }

        // 2. نمط التوافق القديم (Fallback)
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return $default;
            }
        } catch (\Throwable $e) {
            return $default;
        }

        return Cache::remember("settings:{$key}", 86400, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value by key and clear tenant cache.
     */
    public function set(string $key, mixed $value): void
    {
        $tenant = app(TenantContext::class)->get();

        if ($tenant) {
            $tenantId = $tenant->id;

            // إذا كان المفتاح من مفاتيح الإعدادات السريعة الأساسية
            $quickKeys = [
                'center_name', 'center_phone', 'center_whatsapp', 'center_address',
                'academic_year', 'currency_symbol', 'center_logo', 'site_favicon',
                'teacher_name', 'teacher_title', 'teacher_subject', 'teacher_image',
                'online_payment_enabled', 'vodafone_cash_number', 'instapay_username',
                'instapay_qr_code', 'default_session_capacity'
            ];

            if (in_array($key, $quickKeys)) {
                $tenant->setSetting($key, $value);
            } else {
                TenantSetting::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'key' => $key],
                    ['value' => $value, 'group' => 'general']
                );
            }

            Cache::forget("tenant:{$tenantId}:settings:{$key}");
            return;
        }

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("settings:{$key}");
    }

    /**
     * Get image full URL for settings like logo, favicon & teacher portrait.
     */
    public function url(string $key, ?string $default = null): ?string
    {
        $path = $this->get($key);
        if (! $path) {
            return $default;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, 'images/') || str_starts_with($path, '/images/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Get multiple settings with fallback defaults.
     */
    public function allWithDefaults(array $defaults): array
    {
        $result = [];
        foreach ($defaults as $key => $default) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * Set multiple settings at once.
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }
}
