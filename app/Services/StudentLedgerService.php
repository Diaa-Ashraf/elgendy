<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Student;
use App\Models\StudentPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentLedgerService
{
    /**
     * حساب المبالغ المستحقة على الطالب بناءً على المجموعات المسجل فيها.
     * يحسب لكل شهر من تاريخ انضمامه حتى الشهر الحالي.
     *
     * @return Collection<int, array{group_id: int, group_name: string, month: string, amount_due: float}>
     */
    public function calculateDueAmounts(Student $student): Collection
    {
        $student->load('groups');
        $dues = collect();

        foreach ($student->groups as $group) {
            $joinedAt = $group->pivot->joined_at
                ? Carbon::parse($group->pivot->joined_at)->startOfMonth()
                : $student->created_at->startOfMonth();

            $leftAt = $group->pivot->left_at
                ? Carbon::parse($group->pivot->left_at)->endOfMonth()
                : now()->endOfMonth();

            $status = $group->pivot->status ?? 'active';
            if ($status === 'withdrawn') {
                $leftAt = $group->pivot->left_at
                    ? Carbon::parse($group->pivot->left_at)->endOfMonth()
                    : now()->endOfMonth();
            }

            $monthlyPrice = (float) $group->price_per_month;
            
            // تطبيق الخصم إن وجد على الطالب
            if ($student->discount_id && $student->discount && $student->discount->is_active) {
                $disc = $student->discount;
                if ($disc->type === 'percentage') {
                    $monthlyPrice = $monthlyPrice - ($monthlyPrice * ($disc->value / 100));
                } else {
                    $monthlyPrice = max(0, $monthlyPrice - $disc->value);
                }
            }

            if ($monthlyPrice <= 0) {
                continue;
            }

            $currentMonth = $joinedAt->copy();
            while ($currentMonth->lte($leftAt) && $currentMonth->lte(now()->endOfMonth())) {
                $dues->push([
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'subject' => $group->subject?->name ?? '',
                    'month' => $currentMonth->format('Y-m'),
                    'month_label' => $this->arabicMonth($currentMonth),
                    'amount_due' => $monthlyPrice,
                    'type' => 'due',
                ]);
                $currentMonth->addMonth();
            }
        }

        return $dues;
    }

    /**
     * جلب المدفوعات الفعلية للطالب.
     *
     * @return Collection<int, array{id: int, group_name: string, amount: float, paid_at: string, type: string, notes: ?string}>
     */
    public function getPayments(Student $student): Collection
    {
        return StudentPayment::where('student_id', $student->id)
            ->with('group')
            ->orderBy('paid_at', 'asc')
            ->get()
            ->map(fn (StudentPayment $payment) => [
                'id' => $payment->id,
                'group_id' => $payment->group_id,
                'group_name' => $payment->group?->name ?? 'غير محدد',
                'amount' => (float) $payment->amount,
                'paid_at' => $payment->paid_at,
                'month' => $payment->period_month ? Carbon::parse($payment->period_month)->format('Y-m') : null,
                'type' => $payment->type ?? 'month',
                'notes' => $payment->notes,
            ]);
    }

    /**
     * حساب كشف الحساب الكامل مع الرصيد.
     */
    public function getFullLedger(Student $student): array
    {
        $dues = $this->calculateDueAmounts($student);
        $payments = $this->getPayments($student);

        $totalDue = $dues->sum('amount_due');
        $totalPaid = $payments->sum('amount');
        $balance = $totalPaid - $totalDue;

        // تجميع المستحقات حسب المجموعة
        $groupSummaries = [];
        foreach ($student->groups as $group) {
            $groupDues = $dues->where('group_id', $group->id)->sum('amount_due');
            $groupPaid = $payments->where('group_id', $group->id)->sum('amount');
            $groupSummaries[] = [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'subject' => $group->subject?->name ?? '',
                'monthly_fee' => (float) $group->price_per_month,
                'total_due' => $groupDues,
                'total_paid' => $groupPaid,
                'balance' => $groupPaid - $groupDues,
                'status' => $group->pivot->status ?? 'active',
            ];
        }

        // بناء السجل الزمني (Timeline)
        $timeline = collect();

        foreach ($dues as $due) {
            $timeline->push([
                'date' => $due['month'] . '-01',
                'description' => "اشتراك شهر {$due['month_label']} — {$due['group_name']}",
                'debit' => $due['amount_due'],
                'credit' => 0,
                'type' => 'due',
            ]);
        }

        foreach ($payments as $payment) {
            $timeline->push([
                'date' => $payment['paid_at'],
                'description' => "سداد — {$payment['group_name']}" . ($payment['notes'] ? " ({$payment['notes']})" : ''),
                'debit' => 0,
                'credit' => $payment['amount'],
                'type' => 'payment',
            ]);
        }

        $timeline = $timeline->sortBy('date')->values();

        // حساب الرصيد التراكمي
        $runningBalance = 0;
        $timeline = $timeline->map(function ($item) use (&$runningBalance) {
            $runningBalance = $runningBalance - $item['debit'] + $item['credit'];
            $item['running_balance'] = $runningBalance;
            return $item;
        });

        return [
            'student' => $student,
            'total_due' => $totalDue,
            'total_paid' => $totalPaid,
            'balance' => $balance,
            'group_summaries' => $groupSummaries,
            'timeline' => $timeline,
        ];
    }

    private function arabicMonth(Carbon $date): string
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        return ($months[$date->month] ?? $date->month) . ' ' . $date->year;
    }
}
