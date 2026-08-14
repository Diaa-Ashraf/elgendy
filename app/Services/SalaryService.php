<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Salary;
use App\Models\StudentPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    /**
     * Calculate teacher/staff salary for a specific month and group if percentage.
     */
    public function calculateSalary(int $userId, string $type, ?float $baseAmount, ?float $percentage, ?int $groupId, string $month): float
    {
        if ($type === 'fixed') {
            return (float) ($baseAmount ?? 0);
        }

        if ($type === 'percentage' && $groupId && $percentage) {
            $targetMonth = Carbon::parse($month)->startOfMonth();

            // Calculate total income from student payments for this group during this month
            $totalGroupIncome = StudentPayment::where('group_id', $groupId)
                ->where(function ($q) use ($targetMonth) {
                    $q->where(function ($q1) use ($targetMonth) {
                        $q1->where('type', 'month')
                            ->whereYear('period_month', $targetMonth->year)
                            ->whereMonth('period_month', $targetMonth->month);
                    })->orWhere(function ($q2) use ($targetMonth) {
                        $q2->where('type', 'session')
                            ->whereYear('paid_at', $targetMonth->year)
                            ->whereMonth('paid_at', $targetMonth->month);
                    });
                })
                ->sum('amount');

            return round(($totalGroupIncome * $percentage) / 100, 2);
        }

        return 0.0;
    }

    /**
     * Pay salary record in DB transaction.
     */
    public function paySalary(array $data): Salary
    {
        return DB::transaction(function () use ($data) {
            return Salary::create([
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'base_amount' => $data['type'] === 'fixed' ? ($data['base_amount'] ?? null) : null,
                'percentage' => $data['type'] === 'percentage' ? ($data['percentage'] ?? null) : null,
                'group_id' => $data['type'] === 'percentage' ? ($data['group_id'] ?? null) : null,
                'month' => $data['month'] ?? now()->startOfMonth()->format('Y-m-01'),
                'amount_paid' => $data['amount_paid'],
                'paid_at' => $data['paid_at'] ?? now()->format('Y-m-d'),
            ]);
        });
    }
}
