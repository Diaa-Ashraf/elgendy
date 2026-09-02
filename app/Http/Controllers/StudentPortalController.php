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

    public function dashboard(string $tenant)
    {
        $studentId = session('student_portal_id');
        if (! $studentId) {
            return redirect()->route('tenant.student.login', ['tenant' => $tenant]);
        }

        $student = \App\Models\Student::with(['educationalStage', 'groups.subject'])->findOrFail($studentId);

        return view('student-portal.dashboard', compact('student'));
    }

    public function logout(string $tenant)
    {
        session()->forget('student_portal_id');
        return redirect()->route('tenant.student.login', ['tenant' => $tenant]);
    }
}
