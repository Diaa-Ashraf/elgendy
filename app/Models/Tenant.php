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

    public function educationalStages(): HasMany
    {
        return $this->hasMany(EducationalStage::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function groupSessions(): HasMany
    {
        return $this->hasMany(GroupSession::class);
    }

    public function groupSchedules(): HasMany
    {
        return $this->hasMany(GroupSchedule::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function studentPayments(): HasMany
    {
        return $this->hasMany(StudentPayment::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function studentApplications(): HasMany
    {
        return $this->hasMany(StudentApplication::class);
    }

    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class);
    }

    public function studentMaterialDeliveries(): HasMany
    {
        return $this->hasMany(StudentMaterialDelivery::class);
    }

    public function onlinePaymentRequests(): HasMany
    {
        return $this->hasMany(OnlinePaymentRequest::class);
    }

    public function onlineExamAttempts(): HasMany
    {
        return $this->hasMany(OnlineExamAttempt::class);
    }

    public function studentImports(): HasMany
    {
        return $this->hasMany(StudentImport::class);
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
