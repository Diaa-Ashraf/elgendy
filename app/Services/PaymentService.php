<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Student;
use App\Models\StudentPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a student payment.
     */
    public function recordPayment(array $data, int $receivedByUserId): StudentPayment
    {
        return DB::transaction(function () use ($data, $receivedByUserId) {
            return StudentPayment::create([
                'student_id' => $data['student_id'],
                'group_id' => $data['group_id'],
                'amount' => $data['amount'],
                'type' => $data['type'],
                'sessions_count' => $data['type'] === 'session' ? ($data['sessions_count'] ?? 1) : null,
                'period_month' => $data['type'] === 'month' ? ($data['period_month'] ?? now()->format('Y-m-01')) : null,
                'paid_at' => $data['paid_at'] ?? now()->format('Y-m-d'),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'received_by' => $receivedByUserId,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Calculate student dues and payment status per group for a specific month.
     */
    public function calculateStudentDues(int $studentId, ?string $month = null): Collection
    {
        $targetMonth = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $student = Student::with('groups')->findOrFail($studentId);

        return $student->groups->map(function (Group $group) use ($studentId, $targetMonth) {
            // Total payments made for this month
            $monthlyPaid = StudentPayment::where('student_id', $studentId)
                ->where('group_id', $group->id)
                ->where('type', 'month')
                ->whereYear('period_month', $targetMonth->year)
                ->whereMonth('period_month', $targetMonth->month)
                ->sum('amount');

            // Total session payments
            $sessionsPaid = StudentPayment::where('student_id', $studentId)
                ->where('group_id', $group->id)
                ->where('type', 'session')
                ->whereYear('paid_at', $targetMonth->year)
                ->whereMonth('paid_at', $targetMonth->month)
                ->sum('amount');

            $isMonthlyFullyPaid = $monthlyPaid >= $group->price_per_month && $group->price_per_month > 0;

            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'price_per_month' => $group->price_per_month,
                'price_per_session' => $group->price_per_session,
                'monthly_paid' => $monthlyPaid,
                'sessions_paid' => $sessionsPaid,
                'total_paid' => $monthlyPaid + $sessionsPaid,
                'is_monthly_paid' => $isMonthlyFullyPaid,
                'due_amount' => max(0, $group->price_per_month - $monthlyPaid),
            ];
        });
    }
}
