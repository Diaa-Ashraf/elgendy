<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('platform.home', compact('plans'));
    }

    public function pricing()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('platform.pricing', compact('plans'));
    }

    public function showRegister(Request $request)
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $selectedPlanId = $request->query('plan');
        return view('platform.register', compact('plans', $selectedPlanId ? 'selectedPlanId' : 'plans'));
    }

    public function register(Request $request, \App\Services\TenantRegistrationService $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'center_name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|alpha_dash|unique:tenants,slug',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'plan_id' => 'nullable|exists:plans,id',
        ], [
            'name.required' => 'يرجى إدخال اسمك الكريم (المدرس)',
            'center_name.required' => 'يرجى إدخال اسم السنتر أو المنظومة التعليمية',
            'slug.required' => 'يرجى تحديد الرابط المختصر لسنترك',
            'slug.unique' => 'هذا الرابط مستخدم بالفعل، يرجى اختيار رابط آخر',
            'slug.alpha_dash' => 'الرابط يجب أن يحتوي على أحرف إنجليزية وأرقام وعلامة شرطة فقط',
            'email.required' => 'البريد الإلكتروني مطلوب لتسجيل الدخول',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'phone.required' => 'رقم الهاتف مطلوب للتواصل وتفعيل الحساب',
            'password.required' => 'يرجى تعيين كلمة مرور قوية',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 خانات',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $result = $service->register($validated);

        // تسجيل الدخول للمستخدم فورياً
        auth()->login($result['user']);

        // توجيهه فوراً للوحة الإدارة الخاصة بسنتره الجديد
        return redirect('/admin/' . $result['tenant']->slug)
            ->with('success', 'أهلاً بك في منظومتك السحابية! بدأت الآن فترتك التجريبية المجانية لمدة 7 أيام بالكامل 🚀');
    }
}
