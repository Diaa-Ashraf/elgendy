<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_subscription_lifecycle_and_limit_enforcement(): void
    {
        Storage::fake('public');

        // 1. إنشاء خطة محددة بـ 2 طلاب
        $plan = Plan::create([
            'name' => 'خطة تجريبية',
            'slug' => 'test-plan',
            'price_monthly' => 200,
            'max_students' => 2,
            'max_teachers' => 1,
            'max_groups' => 2,
            'features' => ['support' => 'true'],
            'is_active' => true,
        ]);

        // 2. إنشاء Tenant واشتراك تجريبي
        $tenant = Tenant::create([
            'name' => 'سنتر النور',
            'slug' => 'al-noor',
            'email' => 'noor@test.com',
            'phone' => '01000000001',
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'starts_at' => now(),
            'trial_ends_at' => now()->addDays(7),
        ]);

        app(TenantContext::class)->set($tenant);
        $subscriptionService = app(SubscriptionService::class);

        // التحقق من حالة الاشتراك
        $this->assertEquals('trial', $subscriptionService->checkStatus($tenant));

        // 3. التحقق من إمكانية إضافة طلاب حتى الحد الأقصى (2)
        $this->assertTrue($subscriptionService->canAddStudents($tenant));

        $stage = \App\Models\EducationalStage::create(['name' => 'مرحلة 1']);

        \App\Models\Student::create(['name' => 'طالب 1', 'phone' => '0101', 'parent_phone' => '0102', 'stage_id' => $stage->id]);
        $this->assertTrue($subscriptionService->canAddStudents($tenant));

        \App\Models\Student::create(['name' => 'طالب 2', 'phone' => '0103', 'parent_phone' => '0104', 'stage_id' => $stage->id]);

        // الآن وصل للحد الأقصى (2 من 2) -> لا يمكن إضافة طالب ثالث
        $this->assertFalse($subscriptionService->canAddStudents($tenant));

        // 4. رفع إيصال سداد للاشتراك
        $file = UploadedFile::fake()->image('receipt.jpg');
        $response = $this->post(route('tenant.subscription.pay.submit', ['tenant' => $tenant->slug]), [
            'payment_method' => 'instapay',
            'amount' => 200,
            'sender_phone' => '01055555555',
            'receipt_image' => $file,
        ]);

        $response->assertRedirect(route('tenant.subscription.status', ['tenant' => $tenant->slug]));

        $payment = SubscriptionPayment::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);

        // 5. اعتماد الإيصال عبر SubscriptionService (محاكاة موافقة Super Admin)
        $superAdminUser = \App\Models\User::factory()->create();
        $subscriptionService->activateFromPayment($payment, $superAdminUser->id);

        $payment->refresh();
        $subscription->refresh();

        $this->assertEquals('approved', $payment->status);
        $this->assertEquals('active', $subscription->status);
        $this->assertTrue($subscription->ends_at->isFuture());
    }
}
