<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriptionPaymentController extends Controller
{
    /**
     * عرض حالة الاشتراك الحالية للمدرس
     */
    public function status(string $tenantSlug, SubscriptionService $subscriptionService)
    {
        $tenant = app(TenantContext::class)->require();
        $subscription = $tenant->subscription()->with('plan')->first();
        $status = $subscriptionService->checkStatus($tenant);

        $recentPayments = SubscriptionPayment::where('tenant_id', $tenant->id)
            ->latest()
            ->take(5)
            ->get();

        return view('subscription.status', compact('tenant', 'subscription', 'status', 'recentPayments'));
    }

    /**
     * صفحة إرسال إيصال سداد الاشتراك
     */
    public function showPay(string $tenantSlug)
    {
        $tenant = app(TenantContext::class)->require();
        $subscription = $tenant->subscription()->with('plan')->first();

        return view('subscription.pay', compact('tenant', 'subscription'));
    }

    /**
     * استقبال إيصال السداد من المدرس
     */
    public function submitPay(Request $request, string $tenantSlug)
    {
        $tenant = app(TenantContext::class)->require();
        $subscription = $tenant->subscription;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:instapay,vodafone_cash',
            'sender_phone' => 'nullable|string|max:30',
            'transaction_reference' => 'nullable|string|max:100',
            'receipt_image' => 'required|image|max:4096',
        ], [
            'amount.required' => 'يرجى تحديد المبلغ المدفوع.',
            'payment_method.required' => 'يرجى اختيار وسيلة الدفع المستخدمة.',
            'receipt_image.required' => 'يرجى إرفاق صورة إيصال التحويل واضحة.',
            'receipt_image.image' => 'الملف المرفق يجب أن يكون صورة صالحة.',
            'receipt_image.max' => 'أقصى حجم للصورة المرفوعة هو 4 ميجابايت.',
        ]);

        $receiptPath = $request->file('receipt_image')->store("tenants/{$tenant->id}/subscription-receipts", 'public');

        SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription?->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'sender_phone' => $validated['sender_phone'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'receipt_image' => $receiptPath,
            'period_month' => now()->startOfMonth()->toDateString(),
            'status' => 'pending',
        ]);

        return redirect()->route('tenant.subscription.status', ['tenant' => $tenant->slug])
            ->with('success', 'تم إرسال إيصال الدفع بنجاح! سيتم مراجعته وتفعيل اشتراكك في أقرب وقت.');
    }

    /**
     * سجل المدفوعات السابقة للمدرس
     */
    public function history(string $tenantSlug)
    {
        $tenant = app(TenantContext::class)->require();
        $payments = SubscriptionPayment::where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(15);

        return view('subscription.history', compact('tenant', 'payments'));
    }
}
