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
            'student_id' => 'required|numeric',
        ], [
            'phone.required' => 'يرجى إدخال رقم هاتفك أو رقم هاتف ولي الأمر',
            'student_id.required' => 'يرجى إدخال كود الطالب الخاص بك',
            'student_id.numeric' => 'كود الطالب يجب أن يكون أرقاماً فقط',
        ]);

        $inputPhone = preg_replace('/[^0-9]/', '', $request->phone);
        $studentId = (int) $request->student_id;

        // 1. البحث عن الطالب بواسطة الكود
        $student = Student::find($studentId);

        if (! $student) {
            return back()->withInput()->withErrors([
                'error' => '❌ كود الطالب غير مسجل لدينا في السنتر. يرجى مراجعة الكود المطبوع على الكارنيه.'
            ]);
        }

        // 2. التحقق من مطابقة الهاتف (هاتف الطالب أو هاتف ولي الأمر)
        $studentPhone = preg_replace('/[^0-9]/', '', (string) $student->phone);
        $parentPhone = preg_replace('/[^0-9]/', '', (string) $student->parent_phone);

        $phoneMatches = false;

        // مطابقة كاملة أو بآخر 9 أرقام
        if ($inputPhone === $studentPhone || (!empty($studentPhone) && str_ends_with($studentPhone, substr($inputPhone, -9)))) {
            $phoneMatches = true;
        } elseif ($inputPhone === $parentPhone || (!empty($parentPhone) && str_ends_with($parentPhone, substr($inputPhone, -9)))) {
            $phoneMatches = true;
        }

        if (! $phoneMatches) {
            return back()->withInput()->withErrors([
                'error' => "⚠️ كود الطالب صحيح للـ ({$student->name})، ولكن رقم الهاتف المدخل غير مطابق لرقمك أو رقم ولي أمرك المسجل بملفك."
            ]);
        }

        // تسجيل الدخول في الجلسة
        session(['student_portal_id' => $student->id]);
        session(['parent_student_id' => $student->id]); // لتمكين الاختبارات والواجبات المشتركة

        return redirect()->route('student.dashboard');
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
        $onlineExams = \App\Models\Exam::where('stage_id', $student->stage_id)
            ->where('is_online', true)
            ->with(['subject', 'onlineAttempts' => function ($q) use ($student) {
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
