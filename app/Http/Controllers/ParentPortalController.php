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

        // إذا وصل هنا، فالبيانات صحيحة ومطباقة بالكامل
        $student = $studentById;

        session(['parent_student_id' => $student->id]);

        return redirect()->route('parent.dashboard');
    }

    public function dashboard(StudentLedgerService $ledgerService)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::with(['educationalStage', 'groups.subject'])->findOrFail($studentId);

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

        // الملازم والمطبوعات المستلمة
        $materials = \App\Models\StudentMaterialDelivery::where('student_id', $student->id)
            ->with('studyMaterial')
            ->orderBy('delivered_at', 'desc')
            ->get();

        // الحساب المالي كشف الحساب
        $ledger = $ledgerService->getFullLedger($student);

        return view('parent-portal.dashboard', [
            'student' => $student,
            'attendances' => $attendances,
            'examResults' => $examResults,
            'ledger' => $ledger,
            'materials' => $materials,
        ]);
    }

    public function logout()
    {
        session()->forget('parent_student_id');
        return redirect()->route('parent.login');
    }
}
