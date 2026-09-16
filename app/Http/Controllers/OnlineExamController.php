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

        $student = Student::with(['educationalStage', 'groups'])->findOrFail($studentId);
        $exam = Exam::with(['subject', 'educationalStage', 'questions', 'group'])
            ->where('is_online', true)
            ->where('stage_id', $student->stage_id)
            ->findOrFail($id);

        // التحقق من انتماء الطالب للمجموعة المحددة للامتحان
        $groupIds = $student->groups->pluck('id')->toArray();
        if ($exam->group_id && ! in_array($exam->group_id, $groupIds)) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'عذراً، هذا الاختبار مخصص لطلاب مجموعة معينة.');
        }

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

        $student = Student::with('groups')->findOrFail($studentId);
        $exam = Exam::with(['subject', 'questions', 'group'])
            ->where('is_online', true)
            ->where('stage_id', $student->stage_id)
            ->findOrFail($id);

        // التحقق من انتماء الطالب للمجموعة المحددة للامتحان
        $groupIds = $student->groups->pluck('id')->toArray();
        if ($exam->group_id && ! in_array($exam->group_id, $groupIds)) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'عذراً، هذا الاختبار مخصص لطلاب مجموعة معينة.');
        }

        if ($exam->questions->isEmpty()) {
            return back()->with('error', 'عذراً، لم يتم إضافة أسئلة لهذا الاختبار بعد.');
        }

        // إذا تخطى موعد إغلاق الامتحان العام أصلاً
        if ($exam->ends_at && now()->greaterThan($exam->ends_at)) {
            $existingAttempt = OnlineExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existingAttempt) {
                if ($existingAttempt->status === 'in_progress') {
                    $examService->submitAttempt($existingAttempt, (array) ($existingAttempt->student_answers ?? []));
                }
                return redirect()->route('parent.exams.result', ['id' => $exam->id])
                    ->with('info', 'انتهت الفترة المحددة للاختبار وتم اعتماد النتيجة.');
            }

            return redirect()->route('parent.exams.show', ['id' => $exam->id])
                ->with('error', 'عذراً، انتهت الفترة المحددة لأداء هذا الاختبار ولا يمكن البدء فيه.');
        }

        $attempt = $examService->startAttempt($student, $exam);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('parent.exams.result', ['id' => $exam->id]);
        }

        // حساب الوقت المتبقي بالثواني بدقة (الحد الأدنى بين مدة محاولة الطالب وموعد إغلاق الامتحان العام)
        $remainingSeconds = null;
        $possibleEndTimes = [];

        if ($exam->duration_minutes && $attempt->started_at) {
            $possibleEndTimes[] = $attempt->started_at->copy()->addMinutes((int) $exam->duration_minutes);
        }

        if ($exam->ends_at) {
            $possibleEndTimes[] = $exam->ends_at;
        }

        if (! empty($possibleEndTimes)) {
            $earliestEndTime = collect($possibleEndTimes)->min();
            $diffInSeconds = now()->diffInSeconds($earliestEndTime, false);
            $remainingSeconds = (int) round($diffInSeconds);

            // إذا انتهى الوقت المسموح فور الدخول، يتم قفل الامتحان وعرض النتيجة فوراً
            if ($remainingSeconds <= 0) {
                $examService->submitAttempt($attempt, (array) ($attempt->student_answers ?? []));
                return redirect()->route('parent.exams.result', ['id' => $exam->id])
                    ->with('info', 'انتهى الوقت المحدد للاختبار وتم اعتماد النتيجة تلقائياً.');
            }
        }

        $questions = $examService->getQuestionsForModel($exam, $attempt->exam_model ?? 'أ');

        return view('parent-portal.exams.take', [
            'student' => $student,
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $questions,
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

        // إذا تم تسليم الامتحان مسبقاً، تحويل مباشر لصفحة النتيجة
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('parent.exams.result', ['id' => $exam->id]);
        }

        $submittedAnswers = $request->input('answers', []);

        $gradedAttempt = $examService->submitAttempt($attempt, (array) $submittedAnswers);

        return redirect()->route('parent.exams.result', ['id' => $exam->id])
            ->with('success', 'تم تسليم الامتحان وتصحيحه تلقائياً بنجاح!');
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
