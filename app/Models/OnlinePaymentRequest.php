<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class OnlinePaymentRequest extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_month' => 'date',
        'approved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
