<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    protected static ?array $cachedSettings = null;

    /**
     * Load all settings at once into memory + Cache (Fastest execution)
     */
    protected function loadSettings(): array
    {
        if (static::$cachedSettings !== null) {
            return static::$cachedSettings;
        }

        static::$cachedSettings = Cache::remember('all_app_settings_map', 86400, function () {
            try {
                return Setting::pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });

        return static::$cachedSettings;
    }

    /**
     * Get setting value by key with memory cache.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->loadSettings();
        return $settings[$key] ?? $default;
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

        static::$cachedSettings = null;
        Cache::forget('all_app_settings_map');
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
        $settings = $this->loadSettings();
        $result = [];
        foreach ($defaults as $key => $default) {
            $result[$key] = $settings[$key] ?? $default;
        }

        return $result;
    }

    /**
     * Set multiple settings at once.
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        static::$cachedSettings = null;
        Cache::forget('all_app_settings_map');
    }
}

