<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Services\HomeworkService;
use Illuminate\Http\Request;

class HomeworkPortalController extends Controller
{
    /**
     * عرض تفاصيل الواجب وصفحة الحل/التسليم
     */
    public function show(int $id)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::with('educationalStage')->findOrFail($studentId);

        $homework = Homework::with(['subject', 'educationalStage', 'group', 'questions'])
            ->where('stage_id', $student->stage_id)
            ->findOrFail($id);

        $submission = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        return view('parent-portal.homework.show', [
            'student' => $student,
            'homework' => $homework,
            'submission' => $submission,
        ]);
    }

    /**
     * إرسال حل الواجب من الطالب/ولي الأمر
     */
    public function submit(int $id, Request $request, HomeworkService $homeworkService)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::findOrFail($studentId);
        $homework = Homework::where('stage_id', $student->stage_id)->findOrFail($id);

        $request->validate([
            'answers' => 'nullable|array',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string|max:1000',
        ], [
            'attachment.mimes' => 'يجب أن يكون الملف المرفوع بصيغة PDF أو صورة (PNG, JPG)',
            'attachment.max' => 'الحد الأقصى لحجم الملف هو 10 ميجابايت',
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('homework-submissions', 'public');
        }

        try {
            $homeworkService->submitHomework($student, $homework, [
                'student_answers' => $request->input('answers', []),
                'attachment' => $filePath,
                'notes' => $request->input('notes'),
            ]);

            return back()->with('success', 'تم تسليم الواجب بنجاح!');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
