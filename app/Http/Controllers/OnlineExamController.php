<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\OnlineExamAttempt;
use App\Models\Student;
use App\Services\OnlineExamService;
use Illuminate\Http\Request;

class OnlineExamController extends Controller
{
    /**
     * Show the exam lobby/instructions before starting.
     */
    public function show(int $id)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::with('educationalStage')->findOrFail($studentId);
        $exam = Exam::with(['subject', 'educationalStage', 'questions'])
            ->where('is_online', true)
            ->where('stage_id', $student->stage_id)
            ->findOrFail($id);

        $attempt = OnlineExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        // فحص هل الامتحان مفتوح حالياً أم لا
        $now = now();
        $isAvailable = true;
        $availabilityMessage = null;

        if ($exam->starts_at && $now->lessThan($exam->starts_at)) {
            $isAvailable = false;
            $availabilityMessage = 'هذا الاختبار لم يبدأ بعد. موعد الفتح: ' . $exam->starts_at->format('Y-m-d h:i A');
        } elseif ($exam->ends_at && $now->greaterThan($exam->ends_at)) {
            $isAvailable = false;
            $availabilityMessage = 'لقد انتهت الفترة المحددة لأداء هذا الاختبار في: ' . $exam->ends_at->format('Y-m-d h:i A');
        }

        return view('parent-portal.exams.show', [
            'student' => $student,
            'exam' => $exam,
            'attempt' => $attempt,
            'isAvailable' => $isAvailable,
            'availabilityMessage' => $availabilityMessage,
        ]);
    }

    /**
     * Start the exam and enter the exam runner UI.
     */
    public function start(int $id, OnlineExamService $examService)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::findOrFail($studentId);
        $exam = Exam::with(['subject', 'questions'])
            ->where('is_online', true)
            ->where('stage_id', $student->stage_id)
            ->findOrFail($id);

        if ($exam->questions->isEmpty()) {
            return back()->with('error', 'عذراً، لم يتم إضافة أسئلة لهذا الاختبار بعد.');
        }

        $attempt = $examService->startAttempt($student, $exam);

        if ($attempt->status === 'completed') {
            return redirect()->route('parent.exams.result', ['id' => $exam->id]);
        }

        // حساب الوقت المتبقي بالثواني
        $remainingSeconds = null;
        if ($exam->duration_minutes) {
            $totalDurationSeconds = $exam->duration_minutes * 60;
            $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
            $remainingSeconds = max(0, $totalDurationSeconds - $elapsedSeconds);
        }

        return view('parent-portal.exams.take', [
            'student' => $student,
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $exam->questions,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    /**
     * Submit and auto-grade the student's exam.
     */
    public function submit(Request $request, int $id, OnlineExamService $examService)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::findOrFail($studentId);
        $exam = Exam::where('is_online', true)->findOrFail($id);

        $attempt = OnlineExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $submittedAnswers = $request->input('answers', []);

        $gradedAttempt = $examService->submitAttempt($attempt, (array) $submittedAnswers);

        return redirect()->route('parent.exams.result', ['id' => $exam->id])
            ->with('success', 'تم تسليم الامتحان وتصحيحه تلقائياً بنجاح! 🎉');
    }

    /**
     * Show the detailed instant result and explanations.
     */
    public function result(int $id)
    {
        $studentId = session('parent_student_id');
        if (! $studentId) {
            return redirect()->route('parent.login');
        }

        $student = Student::findOrFail($studentId);
        $exam = Exam::with(['subject', 'questions'])->findOrFail($id);

        $attempt = OnlineExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        return view('parent-portal.exams.result', [
            'student' => $student,
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $exam->questions->keyBy('id'),
        ]);
    }
}
