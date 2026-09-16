<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\Student;
use App\Services\StudentLedgerService;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    /**
     * صفحة تسجيل دخول الطالب
     */
    public function showLogin()
    {
        return view('student-portal.login');
    }

    /**
     * تسجيل دخول الطالب الذكي
     * يدعم الدخول برقم هاتف الطالب أو رقم هاتف ولي الأمر مع كود الطالب
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'student_id' => 'required|string',
        ], [
            'phone.required' => 'يرجى إدخال رقم هاتفك أو رقم هاتف ولي الأمر',
            'student_id.required' => 'يرجى إدخال كود الطالب الخاص بك',
        ]);

        $toStandardDigits = function ($str) {
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $standard = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $str = str_replace($arabic, $standard, (string) $str);
            return str_replace($persian, $standard, $str);
        };

        $cleanPhone = preg_replace('/[^0-9]/', '', $toStandardDigits($request->phone));
        $phoneLast9 = strlen($cleanPhone) >= 9 ? substr($cleanPhone, -9) : $cleanPhone;

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

        $matchesPhone = function ($phoneField, $inputDigits, $inputLast9) use ($toStandardDigits) {
            if (empty($phoneField)) return false;
            $dbDigits = preg_replace('/[^0-9]/', '', $toStandardDigits($phoneField));
            if (empty($dbDigits)) return false;
            if ($dbDigits === $inputDigits) return true;
            if (!empty($inputLast9) && str_ends_with($dbDigits, $inputLast9)) return true;
            if (strlen($dbDigits) >= 9 && !empty($inputDigits) && str_ends_with($inputDigits, substr($dbDigits, -9))) return true;
            return false;
        };

        if ($student) {
            $parentMatch = $matchesPhone($student->parent_phone, $cleanPhone, $phoneLast9);
            $studentPhoneMatch = $matchesPhone($student->phone, $cleanPhone, $phoneLast9);

            if ($parentMatch || $studentPhoneMatch) {
                session(['student_portal_id' => $student->id]);
                session(['parent_student_id' => $student->id]);
                return redirect()->route('student.dashboard');
            }

            return back()->withInput()->withErrors([
                'error' => "⚠️ كود الطالب صحيح لـ ({$student->name})، ولكن رقم الهاتف المدخل غير مطابق لرقمك أو رقم ولي أمرك المسجل بملفك."
            ]);
        }

        // إذا لم يعثر على الطالب بالكود، نبحث بالهاتف
        $studentsByPhone = collect();
        if (!empty($phoneLast9)) {
            $studentsByPhone = Student::where(function ($q) use ($cleanPhone, $phoneLast9, $request) {
                $q->where('parent_phone', 'like', '%' . $phoneLast9)
                  ->orWhere('phone', 'like', '%' . $phoneLast9)
                  ->orWhere('parent_phone', $request->phone)
                  ->orWhere('phone', $request->phone);
            })->get();
        }

        if ($studentsByPhone->isNotEmpty()) {
            $names = $studentsByPhone->pluck('name')->implode('، ');
            $suggestedCode = $studentsByPhone->first()->id;
            return back()->withInput()->withErrors([
                'error' => "⚠️ رقم الهاتف مسجل لدينا لـ ({$names})، ولكن كود الطالب المدخل غير صحيح (كود الطالب هو: #$suggestedCode)."
            ]);
        }

        return back()->withInput()->withErrors([
            'error' => '❌ بيانات الدخول غير مسجلة لدينا. يرجى التثبت من رقم الهاتف وكود الطالب أو مراجعة إدارة السنتر.'
        ]);
    }

    /**
     * لوحة تحكم الطالب التفاعلية
     */
    public function dashboard(StudentLedgerService $ledgerService)
    {
        $studentId = session('student_portal_id') ?? session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('student.login');
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

        // الاختبارات الإلكترونية المتاحة
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
            ->take(10)
            ->get();

        // الواجبات والتكليفات المتاحة
        $homeworks = \App\Models\Homework::where('stage_id', $student->stage_id)
            ->with(['subject', 'group', 'submissions' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // المجموعات والمواعيد
        $groups = $student->groups()->with(['subject', 'schedules'])->get();

        // إحصائيات سريعة
        $stats = [
            'total_attended' => Attendance::where('student_id', $student->id)->where('status', 'present')->count(),
            'total_absent' => Attendance::where('student_id', $student->id)->where('status', 'absent')->count(),
            'total_exams' => $examResults->count(),
            'average_score' => $examResults->isNotEmpty() ? round($examResults->avg(fn($r) => ($r->score / max(1, $r->exam?->max_score ?? 100)) * 100), 1) : 0,
        ];

        return view('student-portal.dashboard', [
            'student' => $student,
            'attendances' => $attendances,
            'examResults' => $examResults,
            'onlineExams' => $onlineExams,
            'homeworks' => $homeworks,
            'groups' => $groups,
            'stats' => $stats,
        ]);
    }

    /**
     * تسجيل خروج الطالب
     */
    public function logout()
    {
        session()->forget(['student_portal_id', 'parent_student_id']);
        return redirect()->route('student.login');
    }
}
