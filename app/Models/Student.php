<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    use SoftDeletes, BelongsToTenant, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'parent_phone', 'stage_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'تم إضافة طالب جديد',
                'updated' => 'تم تعديل بيانات الطالب',
                'deleted' => 'تم حذف الطالب',
                default => $event,
            });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->qr_code)) {
                $student->qr_code = 'STD-' . strtoupper(Str::random(8));
            }
        });
    }

    public function educationalStage()
    {
        return $this->belongsTo(EducationalStage::class, 'stage_id');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_student')
            ->withPivot('joined_at', 'left_at', 'status')
            ->withTimestamps()
            ->using(GroupStudentPivot::class);
    }

    public function payments()
    {
        return $this->hasMany(StudentPayment::class);
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }
}
