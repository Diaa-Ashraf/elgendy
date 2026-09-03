<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Services\StudentLedgerService;
use Illuminate\Http\Request;

class ParentPortalController extends Controller
{
    public function showLogin()
    {
        return view('parent-portal.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'parent_phone' => 'required|string',
            'student_id' => 'required|numeric',
        ], [
            'parent_phone.required' => 'يرجى إدخال رقم هاتف ولي الأمر',
            'student_id.required' => 'يرجى إدخال كود الطالب الخاص',
            'student_id.numeric' => 'كود الطالب يجب أن يكون أرقاماً فقط',
        ]);

        $inputPhone = preg_replace('/[^0-9]/', '', $request->parent_phone);
        $studentId = (int) $request->student_id;

        // 1. البحث بالرقم للتحقق هل الهاتف مسجل بالنظام أم لا
        $studentsByPhone = Student::where(function ($q) use ($inputPhone, $request) {
            $q->where('parent_phone', $request->parent_phone)
              ->orWhere('parent_phone', $inputPhone)
              ->orWhere('parent_phone', 'like', '%' . substr($inputPhone, -9));
        })->get();

        // 2. البحث بالكود للتحقق هل الطالب موجود بالنظام أم لا
        $studentById = Student::find($studentId);

        // سيناريو 1: كود الطالب غير موجود إطلاقاً بالسنتر
        if (! $studentById && $studentsByPhone->isEmpty()) {
            return back()->withInput()->withErrors([
                'error' => '❌ بيانات الدخول غير مسجلة لدينا. يرجى التثبت من رقم الهاتف وكود الطالب أو التواصل مع إدارة السنتر.'
            ]);
        }

        // سيناريو 2: رقم الهاتف صح ومسجل بالسنتر، لكن كود الطالب خطأ أو غير مرتبط بهذا الرقم
        if ($studentsByPhone->isNotEmpty() && (! $studentById || ! $studentsByPhone->contains('id', $studentId))) {
            return back()->withInput()->withErrors([
                'error' => '⚠️ رقم هاتف ولي الأمر صحيح ومسجل، ولكن كود الطالب غير صحيح أو لا ينتمي لهذا الرقم.'
            ]);
        }

        // سيناريو 3: كود الطالب صح وموجود بالسنتر، ولكن رقم الهاتف المدخل غير مطابق للرقم المسجل للطالب
        if ($studentById && $studentsByPhone->isEmpty()) {
            return back()->withInput()->withErrors([
                'error' => "⚠️ كود الطالب صحيح للـ ({$studentById->name})، ولكن رقم هاتف ولي الأمر المدخل غير مطابق للرقم المسجل بملف الطالب."
            ]);
        }

        // إذا وصل هنا، فالبيانات صحيحة ومطابقة بالكامل
        $student = $studentById;

        session(['parent_student_id' => $student->id]);

        $tenantSlug = $request->route('tenant') ?? app(\App\Services\TenantContext::class)->get()?->slug;

        if ($tenantSlug) {
            return redirect()->route('tenant.parent.dashboard', ['tenant' => $tenantSlug]);
        }

        return redirect()->route('tenant.parent.dashboard', ['tenant' => 'mr-diaa']);
    }

    public function dashboard(StudentLedgerService $ledgerService)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::with(['educationalStage', 'groups.subject', 'groups.schedules'])->findOrFail($studentId);

        // الحضور والغياب
        $attendances = Attendance::where('student_id', $student->id)
            ->with(['groupSession.group'])
            ->orderBy('id', 'desc')
            ->take(15)
            ->get();

        // نتائج الامتحانات
        $examResults = ExamResult::where('student_id', $student->id)
            ->with(['exam'])
            ->orderBy('id', 'desc')
            ->get();

        // الاختبارات الإلكترونية المتاحة للمرحلة
        $onlineExams = \App\Models\Exam::where('stage_id', $student->stage_id)
            ->where('is_online', true)
            ->with(['subject', 'onlineAttempts' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->orderBy('date', 'desc')
            ->get();

        // الملازم والمطبوعات المستلمة
        $materials = \App\Models\StudentMaterialDelivery::where('student_id', $student->id)
            ->with('studyMaterial')
            ->orderBy('delivered_at', 'desc')
            ->get();

        // الحساب المالي كشف الحساب
        $ledger = $ledgerService->getFullLedger($student);

        // طلبات السداد الإلكتروني السابقة
        $onlinePaymentRequests = \App\Models\OnlinePaymentRequest::where('student_id', $student->id)
            ->with('group')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        // إعدادات الدفع الإلكتروني من النظام
        $settingService = app(\App\Services\SettingService::class);
        $paymentSettings = [
            'enabled' => (bool) $settingService->get('online_payment_enabled', true),
            'vodafone_cash' => $settingService->get('vodafone_cash_number', ''),
            'instapay_username' => $settingService->get('instapay_username', ''),
            'instapay_qr' => $settingService->url('instapay_qr_code'),
            'instructions' => $settingService->get('online_payment_instructions', 'يرجى إرسال المبلغ ثم إرفاق صورة إشعار التحويل لتأكيد السداد.'),
        ];

        // الواجبات المنزلية
        $homeworkService = app(\App\Services\HomeworkService::class);
        $homeworks = $homeworkService->getStudentHomeworks($student);

        return view('parent-portal.dashboard', [
            'student' => $student,
            'attendances' => $attendances,
            'examResults' => $examResults,
            'onlineExams' => $onlineExams,
            'homeworks' => $homeworks,
            'ledger' => $ledger,
            'materials' => $materials,
            'onlinePaymentRequests' => $onlinePaymentRequests,
            'paymentSettings' => $paymentSettings,
        ]);
    }

    public function submitPayment(Request $request)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::findOrFail($studentId);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:vodafone_cash,instapay,wallet',
            'group_id' => 'nullable|exists:groups,id',
            'sender_phone' => 'nullable|string|max:50',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'receipt' => 'required|image|mimes:jpeg,png,jpg,webp,heic|max:5120',
        ], [
            'amount.required' => 'يرجى إدخال المبلغ المحول بدقة.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً صحيحاً.',
            'amount.min' => 'المبلغ المحول يجب ألا يقل عن 1 ج.م.',
            'receipt.required' => 'صورة إيصال التحويل (Screenshot) مطلوبة لتأكيد الدفع.',
            'receipt.image' => 'الملف المرفق يجب أن يكون صورة صالحة.',
            'receipt.max' => 'حجم الصورة لا يجب أن يتعدى 5 ميجابايت.',
        ]);

        $receiptPath = $request->file('receipt')->store('payment-receipts', 'public');

        $onlinePayment = \App\Models\OnlinePaymentRequest::create([
            'student_id' => $student->id,
            'group_id' => $request->group_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'sender_phone' => $request->sender_phone,
            'transaction_reference' => $request->transaction_reference,
            'receipt_image' => $receiptPath,
            'type' => 'month',
            'period_month' => now()->startOfMonth(),
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        // إرسال إشعار في جرس الإشعارات باللوحة للمديرين والمحاسبين
        try {
            \App\Services\NotificationService::notifyNewOnlinePaymentRequest(
                $student->name,
                (float) $request->amount,
                $request->payment_method,
                $onlinePayment->id
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Online Payment Bell Notification Error: ' . $e->getMessage());
        }

        $tenantSlug = $request->route('tenant') ?? app(\App\Services\TenantContext::class)->get()?->slug;

        return redirect()->route('tenant.parent.dashboard', ['tenant' => $tenantSlug ?? 'mr-diaa'])
            ->with('payment_success', 'تم إرسال إيصال التحويل بنجاح! سيتم مراجعة الإيصال من إدارة السنتر وتأكيد نزول المبلغ في حساب الطالب فوراً مع إرسال إشعار واتساب لكم 🚀');
    }

    public function logout(Request $request)
    {
        session()->forget('parent_student_id');
        $tenantSlug = $request->route('tenant') ?? app(\App\Services\TenantContext::class)->get()?->slug;
        return redirect()->route('tenant.parent.login', ['tenant' => $tenantSlug ?? 'mr-diaa']);
    }
}
