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
            'student_id' => 'required|string',
        ], [
            'parent_phone.required' => 'يرجى إدخال رقم هاتف ولي الأمر أو الطالب',
            'student_id.required' => 'يرجى إدخال كود الطالب الخاص',
        ]);

        // وظيفة تحويل الأرقام العربية والفارسية إلى أرقام قياسية إنجليزية
        $toStandardDigits = function ($str) {
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $standard = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $str = str_replace($arabic, $standard, (string) $str);
            return str_replace($persian, $standard, $str);
        };

        // استخراج الأرقام فقط بعد التوحيد
        $cleanPhone = preg_replace('/[^0-9]/', '', $toStandardDigits($request->parent_phone));
        $phoneLast9 = strlen($cleanPhone) >= 9 ? substr($cleanPhone, -9) : $cleanPhone;

        // تجهيز كود الطالب للبحث
        $rawStudentCode = trim($toStandardDigits($request->student_id));
        $numericStudentId = preg_replace('/[^0-9]/', '', $rawStudentCode);

        // 1. البحث عن الطالب بواسطة الكود (ID أو qr_code)
        $student = null;
        if (!empty($numericStudentId) && is_numeric($numericStudentId)) {
            $student = Student::find((int) $numericStudentId);
        }

        if (!$student && !empty($rawStudentCode)) {
            $student = Student::where('qr_code', $rawStudentCode)
                ->orWhere('qr_code', 'STD-' . $rawStudentCode)
                ->first();
        }

        // دالة مساعدة لمطابقة رقم الهاتف بمرونة تامة (مقارنة آخر 9 أرقام، أو تطابق تام، أو احتواء)
        $matchesPhone = function ($phoneField, $inputDigits, $inputLast9) use ($toStandardDigits) {
            if (empty($phoneField)) return false;
            $dbDigits = preg_replace('/[^0-9]/', '', $toStandardDigits($phoneField));
            if (empty($dbDigits)) return false;
            if ($dbDigits === $inputDigits) return true;
            if (!empty($inputLast9) && str_ends_with($dbDigits, $inputLast9)) return true;
            if (strlen($dbDigits) >= 9 && !empty($inputDigits) && str_ends_with($inputDigits, substr($dbDigits, -9))) return true;
            return false;
        };

        // 2. إذا تم العثور على الطالب بالكود، نتحقق هل الرقم المدخل يطابق parent_phone أو phone
        if ($student) {
            $parentMatch = $matchesPhone($student->parent_phone, $cleanPhone, $phoneLast9);
            $studentPhoneMatch = $matchesPhone($student->phone, $cleanPhone, $phoneLast9);

            if ($parentMatch || $studentPhoneMatch) {
                session(['parent_student_id' => $student->id]);
                return redirect()->route('parent.dashboard');
            }

            // الكود صحيح ولكن الهاتف غير مطابق
            return back()->withInput()->withErrors([
                'error' => "⚠️ كود الطالب صحيح لـ ({$student->name})، ولكن رقم الهاتف المدخل غير مطابق لرقم ولي الأمر أو الطالب المسجل بملفه. يرجى مراجعة الرقم أو التواصل مع إدارة السنتر."
            ]);
        }

        // 3. إذا لم يتم العثور على الطالب بالكود، نبحث بالهاتف لمعرفة هل ولي الأمر مسجل برقم آخر ونعطيه كود الطالب الصحيح
        $studentsByPhone = collect();
        if (!empty($phoneLast9)) {
            $studentsByPhone = Student::where(function ($q) use ($cleanPhone, $phoneLast9, $request) {
                $q->where('parent_phone', 'like', '%' . $phoneLast9)
                  ->orWhere('phone', 'like', '%' . $phoneLast9)
                  ->orWhere('parent_phone', $request->parent_phone)
                  ->orWhere('phone', $request->parent_phone);
            })->get();
        }

        if ($studentsByPhone->isNotEmpty()) {
            $names = $studentsByPhone->pluck('name')->implode('، ');
            $suggestedCode = $studentsByPhone->first()->id;
            return back()->withInput()->withErrors([
                'error' => "⚠️ رقم الهاتف مسجل بالسنتر لـ ({$names})، ولكن كود الطالب المدخل غير صحيح (كود الطالب هو: #$suggestedCode)."
            ]);
        }

        return back()->withInput()->withErrors([
            'error' => '❌ بيانات الدخول غير مسجلة لدينا. يرجى التثبت من رقم الهاتف وكود الطالب أو التواصل مع إدارة السنتر.'
        ]);
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

        // الاختبارات الإلكترونية المتاحة للمرحلة أو المجموعة
        $groupIds = $student->groups->pluck('id')->toArray();
        $onlineExams = \App\Models\Exam::where('stage_id', $student->stage_id)
            ->where('is_online', true)
            ->where(function ($q) use ($groupIds) {
                $q->whereIn('group_id', $groupIds)
                  ->orWhereNull('group_id');
            })
            ->with(['subject', 'group', 'onlineAttempts' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->orderBy('date', 'desc')
            ->get();

        // الواجبات والتكليفات المنزلية
        $homeworkService = app(\App\Services\HomeworkService::class);
        $homeworks = $homeworkService->getStudentHomeworks($student);

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

        // تحليلات الأداء ولوحة الشرف والإشعارات
        $performanceService = app(\App\Services\StudentPerformanceService::class);
        $analytics = $performanceService->getStudentAnalytics($student);

        $notifications = \App\Models\ParentNotification::where('student_id', $student->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

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
            'analytics' => $analytics,
            'notifications' => $notifications,
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
            'receipt' => 'required|file|mimes:jpeg,png,jpg,webp,heic,heif|max:10240',
        ], [
            'amount.required' => 'يرجى إدخال المبلغ المحول بدقة.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً صحيحاً.',
            'amount.min' => 'المبلغ المحول يجب ألا يقل عن 1 ج.م.',
            'receipt.required' => 'صورة إيصال التحويل (Screenshot) مطلوبة لتأكيد الدفع.',
            'receipt.mimes' => 'صورة الإيصال يجب أن تكون بصيغة (JPG, PNG, WEBP, HEIC).',
            'receipt.max' => 'حجم الصورة لا يجب أن يتعدى 10 ميجابايت.',
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

        return redirect()->route('parent.dashboard')
            ->with('payment_success', 'تم إرسال إيصال التحويل بنجاح! سيتم مراجعة الإيصال من إدارة السنتر وتأكيد نزول المبلغ في حساب الطالب فوراً مع إرسال إشعار واتساب لكم.');
    }

    public function markAllNotificationsAsRead()
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
        }

        \App\Models\ParentNotification::where('student_id', $studentId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function markNotificationAsRead(int $id)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
        }

        \App\Models\ParentNotification::where('student_id', $studentId)
            ->where('id', $id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function logout()
    {
        session()->forget('parent_student_id');

        return redirect()->route('parent.login');
    }
}
