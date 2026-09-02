<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StudentPayment extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['student_id', 'amount', 'paid_at', 'payment_method', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'تم تسجيل دفعة مالية جديدة للطالب',
                'updated' => 'تم تعديل بيانات الدفعة المالية',
                'deleted' => 'تم حذف الدفعة المالية',
                default => $event,
            });
    }

    protected $casts = [
        'paid_at' => 'date',
        'period_month' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
