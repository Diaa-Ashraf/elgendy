<?php

namespace App\Services;

use App\Models\Expense;

class ExpenseService
{
    /**
     * Get total expense amount between dates or for category.
     */
    public function getTotalExpenses(?string $fromDate = null, ?string $toDate = null, ?int $categoryId = null): float
    {
        return (float) Expense::query()
            ->when($fromDate, fn ($q) => $q->whereDate('date', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('date', '<=', $toDate))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->sum('amount');
    }
}
