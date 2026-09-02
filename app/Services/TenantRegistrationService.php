<?php

namespace App\Services;

use App\Models\EducationalStage;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantRegistrationService
{
    /**
     * تسجيل مدرس جديد وإنشاء بيئة المنصة التعليمية له بالكامل.
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. إنشاء الـ Tenant
            $slug = Str::slug($data['slug'] ?? $data['name']);
            // التأكد من فريدة الـ slug
            $originalSlug = $slug;
            $count = 1;
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }

            $tenant = Tenant::create([
                'name' => $data['center_name'] ?? $data['name'],
                'slug' => $slug,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'is_active' => true,
                'trial_ends_at' => now()->addDays(7), // أسبوع تجربة مجاني بالكامل
                'settings' => [
                    'center_name' => $data['center_name'] ?? $data['name'],
                    'center_phone' => $data['phone'],
                    'teacher_name' => $data['name'],
                    'teacher_title' => $data['title'] ?? 'مدرس معتمد',
                    'academic_year' => '2026/2027',
                    'currency_symbol' => 'ج.م',
                ],
            ]);

            // 2. إنشاء حساب المستخدم كمدير رئيسي للسنتر
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ]);

            // إعطاؤه دور admin إذا كان نظام Spatie محملاً
            try {
                if (\Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
                    $user->assignRole('admin');
                }
            } catch (\Throwable $e) {
                // Ignore if roles table not yet seeded
            }

            // 3. ربط الخطة وإنشاء اشتراك التجربة المجانية
            $plan = null;
            if (!empty($data['plan_id'])) {
                $plan = Plan::find($data['plan_id']);
            }
            if (!$plan) {
                $plan = Plan::where('slug', 'growth')->first() ?? Plan::first();
            }

            if ($plan) {
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'trial',
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays(7),
                ]);
            }

            // 4. إنشاء مراحل دراسية افتراضية لتسهيل بدء العمل للمدرس فوراً
            $defaultStages = ['المرحلة الابتدائية', 'المرحلة الإعدادية', 'المرحلة الثانوية'];
            foreach ($defaultStages as $stageName) {
                EducationalStage::create([
                    'tenant_id' => $tenant->id,
                    'name' => $stageName,
                ]);
            }

            return [
                'tenant' => $tenant,
                'user' => $user,
            ];
        });
    }
}
