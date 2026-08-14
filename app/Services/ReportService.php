<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Salary;
use App\Models\StudentPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    /**
     * Get monthly total revenue from student payments with 15-min cache.
     */
    public function monthlyRevenue(string $month): float
    {
        $cacheKey = "reports:monthly-revenue:{$month}";

        return Cache::remember($cacheKey, 900, function () use ($month) {
            $targetMonth = Carbon::parse($month)->startOfMonth();

            return (float) StudentPayment::query()
                ->selectRaw('SUM(amount) as total_amount')
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
                ->value('total_amount') ?? 0.0;
        });
    }

    /**
     * Get monthly total expenses (general expenses + salaries) with 15-min cache.
     */
    public function monthlyExpenses(string $month): float
    {
        $cacheKey = "reports:monthly-expenses:{$month}";

        return Cache::remember($cacheKey, 900, function () use ($month) {
            $targetMonth = Carbon::parse($month)->startOfMonth();

            $expensesTotal = (float) Expense::query()
                ->selectRaw('SUM(amount) as total')
                ->whereYear('date', $targetMonth->year)
                ->whereMonth('date', $targetMonth->month)
                ->value('total') ?? 0.0;

            $salariesTotal = (float) Salary::query()
                ->selectRaw('SUM(amount_paid) as total')
                ->whereYear('month', $targetMonth->year)
                ->whereMonth('month', $targetMonth->month)
                ->value('total') ?? 0.0;

            return $expensesTotal + $salariesTotal;
        });
    }

    /**
     * Get net profit for the month.
     */
    public function netProfit(string $month): float
    {
        return $this->monthlyRevenue($month) - $this->monthlyExpenses($month);
    }

    /**
     * Get profitability broken down by group for the month with 15-min cache.
     */
    public function groupProfitability(string $month): Collection
    {
        $cacheKey = "reports:group-profitability:{$month}";

        return Cache::remember($cacheKey, 900, function () use ($month) {
            $targetMonth = Carbon::parse($month)->startOfMonth();

            return Group::query()
                ->select('id', 'name')
                ->withCount(['students as active_students_count' => function ($q) {
                    $q->where('group_student.status', 'active');
                }])
                ->get()
                ->map(function ($group) use ($targetMonth) {
                    $revenue = (float) StudentPayment::query()
                        ->where('group_id', $group->id)
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

                    return [
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'active_students' => $group->active_students_count,
                        'revenue' => $revenue,
                    ];
                });
        });
    }

    /**
     * Get attendance rate percentage for a group in a month with 15-min cache.
     */
    public function attendanceRate(int $groupId, string $month): float
    {
        $cacheKey = "reports:attendance-rate:{$groupId}:{$month}";

        return Cache::remember($cacheKey, 900, function () use ($groupId, $month) {
            $targetMonth = Carbon::parse($month)->startOfMonth();

            $totalRecords = Attendance::query()
                ->whereHas('groupSession', function ($q) use ($groupId, $targetMonth) {
                    $q->where('group_id', $groupId)
                        ->whereYear('date', $targetMonth->year)
                        ->whereMonth('date', $targetMonth->month);
                })
                ->count();

            if ($totalRecords === 0) {
                return 0.0;
            }

            $presentRecords = Attendance::query()
                ->whereHas('groupSession', function ($q) use ($groupId, $targetMonth) {
                    $q->where('group_id', $groupId)
                        ->whereYear('date', $targetMonth->year)
                        ->whereMonth('date', $targetMonth->month);
                })
                ->where('status', 'present')
                ->count();

            return round(($presentRecords / $totalRecords) * 100, 1);
        });
    }
}
