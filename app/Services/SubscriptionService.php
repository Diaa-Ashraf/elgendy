<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * التحقق من حالة الاشتراك الحالية للـ Tenant
     */
    public function checkStatus(Tenant $tenant): string
    {
        $subscription = $tenant->subscription;

        if (! $subscription) {
            return 'expired';
        }

        if ($subscription->status === 'trial') {
            if ($subscription->trial_ends_at && $subscription->trial_ends_at->isPast()) {
                $subscription->update(['status' => 'expired']);
                return 'expired';
            }
            return 'trial';
        }

        if ($subscription->status === 'active') {
            if ($subscription->ends_at && $subscription->ends_at->isPast()) {
                $subscription->update(['status' => 'past_due']);
                return 'past_due';
            }
            return 'active';
        }

        return $subscription->status;
    }

    /**
     * تفعيل أو تمديد الاشتراك بناءً على اعتماد إيصال دفع
     */
    public function activateFromPayment(SubscriptionPayment $payment, int $approvedById): void
    {
        DB::transaction(function () use ($payment, $approvedById) {
            $payment->update([
                'status' => 'approved',
                'approved_by' => $approvedById,
                'approved_at' => now(),
            ]);

            $subscription = $payment->subscription;
            $tenant = $payment->tenant;

            // تحديد تاريخ البداية والنهاية الجديد (شهر إضافي)
            $currentEndsAt = $subscription->ends_at;
            $startDate = ($currentEndsAt && $currentEndsAt->isFuture()) ? $currentEndsAt : now();
            $newEndsAt = (clone $startDate)->addMonth();

            $subscription->update([
                'status' => 'active',
                'starts_at' => $subscription->starts_at ?? now(),
                'ends_at' => $newEndsAt,
            ]);

            // تفعيل الـ Tenant في حالة كان معطلاً
            $tenant->update(['is_active' => true]);
        });
    }

    /**
     * تجديد الاشتراك لخطة معينة
     */
    public function renew(Subscription $subscription, Plan $plan, int $months = 1): void
    {
        DB::transaction(function () use ($subscription, $plan, $months) {
            $currentEndsAt = $subscription->ends_at;
            $startDate = ($currentEndsAt && $currentEndsAt->isFuture()) ? $currentEndsAt : now();
            $newEndsAt = (clone $startDate)->addMonths($months);

            $subscription->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $subscription->starts_at ?? now(),
                'ends_at' => $newEndsAt,
            ]);

            $subscription->tenant->update(['is_active' => true]);
        });
    }

    /**
     * التحقق مما إذا كان المدرس يستطيع إضافة طلاب جدد وفقاً للخطة
     */
    public function canAddStudents(Tenant $tenant, int $count = 1): bool
    {
        $plan = $tenant->subscription?->plan;
        if (! $plan) {
            return false;
        }

        // إذا كان الحد الأقصى 0 أو فارغ يعتبر غير محدود
        if (! $plan->max_students || $plan->max_students <= 0) {
            return true;
        }

        $currentCount = $tenant->students()->count();
        return ($currentCount + $count) <= $plan->max_students;
    }

    /**
     * التحقق من إمكانية إضافة مجموعات جديدة
     */
    public function canAddGroups(Tenant $tenant, int $count = 1): bool
    {
        $plan = $tenant->subscription?->plan;
        if (! $plan) {
            return false;
        }

        if (! $plan->max_groups || $plan->max_groups <= 0) {
            return true;
        }

        $currentCount = $tenant->groups()->count();
        return ($currentCount + $count) <= $plan->max_groups;
    }

    /**
     * التحقق من إمكانية إضافة مساعدين / معلمين جدد
     */
    public function canAddTeachers(Tenant $tenant, int $count = 1): bool
    {
        $plan = $tenant->subscription?->plan;
        if (! $plan) {
            return false;
        }

        if (! $plan->max_teachers || $plan->max_teachers <= 0) {
            return true;
        }

        $currentCount = $tenant->users()->count();
        return ($currentCount + $count) <= $plan->max_teachers;
    }
}
