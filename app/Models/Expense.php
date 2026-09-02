<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
