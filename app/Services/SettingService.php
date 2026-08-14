<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get setting value by key with cache.
     */
    public function get(string $key, mixed $default = null): mixed
    {
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
     * Set setting value by key and clear cache.
     */
    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("settings:{$key}");
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
