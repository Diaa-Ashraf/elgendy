<?php

namespace App\Http\Controllers;

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
}
