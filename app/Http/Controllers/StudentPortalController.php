<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    public function showLogin(string $tenant)
    {
        return view('student-portal.login');
    }

    public function login(Request $request, string $tenant)
    {
        $request->validate([
            'student_code' => 'required|numeric',
            'parent_phone' => 'required|string',
        ], [
            'student_code.required' => 'يرجى إدخال كود الطالب الخاص بك',
            'parent_phone.required' => 'يرجى إدخال رقم هاتف ولي الأمر المسجل بالسنتر',
        ]);

        $student = \App\Models\Student::where('id', $request->student_code)->first();

        if (! $student) {
            return back()->withErrors(['error' => 'كود الطالب غير موجود في هذه المنصة.']);
        }

        $inputPhone = preg_replace('/[^0-9]/', '', $request->parent_phone);
        $savedPhone = preg_replace('/[^0-9]/', '', $student->parent_phone);

        if ($inputPhone !== $savedPhone && ! str_ends_with($savedPhone, substr($inputPhone, -9))) {
            return back()->withErrors(['error' => 'رقم هاتف ولي الأمر غير مطابق لملف الطالب.']);
        }

        session(['student_portal_id' => $student->id]);

        return redirect()->route('tenant.student.dashboard', ['tenant' => $tenant]);
    }

    public function dashboard(string $tenant, \App\Services\HomeworkService $homeworkService)
    {
        $studentId = session('student_portal_id');
        if (! $studentId) {
            return redirect()->route('tenant.student.login', ['tenant' => $tenant]);
        }

        $student = \App\Models\Student::with(['educationalStage', 'groups.subject'])->findOrFail($studentId);
        $homeworks = $homeworkService->getStudentHomeworks($student);

        return view('student-portal.dashboard', compact('student', 'homeworks'));
    }

    public function showHomework(string $tenant, int $id)
    {
        $studentId = session('student_portal_id');
        if (! $studentId) {
            return redirect()->route('tenant.student.login', ['tenant' => $tenant]);
        }

        $student = \App\Models\Student::with('groups')->findOrFail($studentId);
        $homework = \App\Models\Homework::with(['subject', 'group', 'questions'])->published()->findOrFail($id);

        $submission = \App\Models\HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        return view('student-portal.homework-show', compact('student', 'homework', 'submission'));
    }

    public function submitHomework(Request $request, string $tenant, int $id, \App\Services\HomeworkService $homeworkService)
    {
        $studentId = session('student_portal_id');
        if (! $studentId) {
            return redirect()->route('tenant.student.login', ['tenant' => $tenant]);
        }

        $student = \App\Models\Student::findOrFail($studentId);
        $homework = \App\Models\Homework::findOrFail($id);

        $request->validate([
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'answers' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ], [
            'attachment.mimes' => 'يجب أن يكون الملف المرفق بصيغة PDF أو صورة (JPG, PNG).',
            'attachment.max' => 'الحد الأقصى لحجم الملف هو 10 ميجابايت.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('homeworks/submissions', 'public');
        }

        try {
            $homeworkService->submitHomework($student, $homework, [
                'student_answers' => $request->input('answers', []),
                'attachment' => $attachmentPath,
                'notes' => $request->input('notes'),
            ]);

            return redirect()->route('tenant.student.homeworks.show', ['tenant' => $tenant, 'id' => $id])
                ->with('success', 'تم تسليم الواجب بنجاح! 🚀');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function logout(string $tenant)
    {
        session()->forget('student_portal_id');
        return redirect()->route('tenant.student.login', ['tenant' => $tenant]);
    }
}
