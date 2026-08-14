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
        ]);

        $student = Student::where('id', $request->student_id)
            ->where(function ($q) use ($request) {
                $q->where('parent_phone', $request->parent_phone)
                  ->orWhere('parent_phone', '0' . ltrim($request->parent_phone, '0'))
                  ->orWhere('parent_phone', 'like', '%' . substr($request->parent_phone, -9));
            })
            ->first();

        if (! $student) {
            return back()->withErrors(['parent_phone' => 'بيانات الدخول غير صحيحة، يرجى التأكد من الكود ورقم الهاتف']);
        }

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
