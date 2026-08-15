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
     * Get image full URL for settings like logo & favicon.
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

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
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
