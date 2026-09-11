<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\GroupSession;
use App\Models\Student;
use App\Services\StudentLedgerService;

class StudentPdfController extends Controller
{
    public function printLedger(int $studentId, StudentLedgerService $ledgerService)
    {
        $student = Student::with(['educationalStage', 'groups.subject'])->findOrFail($studentId);
        $ledger = $ledgerService->getFullLedger($student);

        return view('pdf.student-ledger', [
            'student' => $student,
            'ledger' => $ledger,
        ]);
    }

    public function printCard(int $studentId)
    {
        $student = Student::with(['educationalStage'])->findOrFail($studentId);

        return view('pdf.student-card', [
            'student' => $student,
        ]);
    }

    public function printExam(int $examId, \App\Services\ExamService $examService)
    {
        $modelsCount = (int) request('models_count', 1);
        $includeAnswerKey = filter_var(request('include_answer_key', 1), FILTER_VALIDATE_BOOLEAN);
        $shuffleOptions = filter_var(request('shuffle_options', 1), FILTER_VALIDATE_BOOLEAN);

        $data = $examService->getExamPrintModels($examId, $modelsCount, $shuffleOptions);

        return view('pdf.exam-paper', [
            'exam' => $data['exam'],
            'models' => $data['models'],
            'includeAnswerKey' => $includeAnswerKey,
            'modelsCount' => $modelsCount,
        ]);
    }

    /**
     * طباعة كارنيهات جميع طلاب مجموعة معينة (جماعية)
     */
    public function printBatchCards(int $groupId)
    {
        $group = Group::findOrFail($groupId);
        $students = $group->students()
            ->with('educationalStage')
            ->orderBy('name')
            ->get();

        return view('pdf.batch-student-cards', [
            'group' => $group,
            'students' => $students,
        ]);
    }

    /**
     * طباعة كشف حضور لحصة معينة
     */
    public function printAttendanceSheet(int $sessionId)
    {
        $session = GroupSession::with(['group.subject', 'group.educationalStage'])->findOrFail($sessionId);

        $groupStudents = $session->group->students()
            ->select('students.id', 'students.name', 'students.qr_code', 'students.parent_phone')
            ->orderBy('students.name')
            ->get();

        $attendedIds = Attendance::where('group_session_id', $session->id)
            ->where('status', 'present')
            ->pluck('student_id')
            ->toArray();

        $attendanceData = $groupStudents->map(fn (Student $s) => [
            'name' => $s->name,
            'code' => $s->qr_code,
            'phone' => $s->parent_phone,
            'status' => in_array($s->id, $attendedIds) ? 'present' : 'absent',
        ]);

        $presentCount = count($attendedIds);
        $totalCount = $groupStudents->count();
        $absentCount = $totalCount - $presentCount;
        $rate = $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0;

        return view('pdf.attendance-sheet', [
            'session' => $session,
            'attendanceData' => $attendanceData,
            'stats' => [
                'present' => $presentCount,
                'absent' => $absentCount,
                'total' => $totalCount,
                'rate' => $rate,
            ],
        ]);
    }

    /**
     * طباعة شهادة تقدير لطالب
     */
    public function printCertificate(int $studentId)
    {
        $student = Student::with(['educationalStage', 'groups'])->findOrFail($studentId);

        return view('pdf.honor-certificate', [
            'student' => $student,
            'rankBadge' => request('badge', ' شهادة تميز وتفوق أكاديمي'),
        ]);
    }

    /**
     * طباعة كارت تقرير الأداء الشهري الشامل لولي الأمر
     */
    public function printMonthlyReport(int $studentId, \App\Services\MonthlyReportService $monthlyReportService)
    {
        $month = request('month') ? (int) request('month') : null;
        $year = request('year') ? (int) request('year') : null;

        $report = $monthlyReportService->getMonthlyReport($studentId, $month, $year);

        return view('pdf.monthly-report-card', [
            'report' => $report,
        ]);
    }
}
