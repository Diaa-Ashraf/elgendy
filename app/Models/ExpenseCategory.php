<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
