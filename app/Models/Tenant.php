<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function tenantSettings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    // Helper لقراءة الإعدادات السريعة
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): void
    {
        $current = $this->settings ?? [];
        data_set($current, $key, $value);
        $this->update(['settings' => $current]);
    }

    public function isTrialing(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
