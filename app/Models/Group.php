<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected static function booted()
    {
        static::deleted(function (Group $group) {
            $group->schedules()->delete();
        });
    }

    public function educationalStage()
    {
        return $this->belongsTo(EducationalStage::class, 'stage_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'group_student')
            ->withPivot('joined_at', 'left_at', 'status')
            ->withTimestamps();
    }

    public function schedules()
    {
        return $this->hasMany(GroupSchedule::class);
    }

    public function sessions()
    {
        return $this->hasMany(GroupSession::class);
    }

    public function payments()
    {
        return $this->hasMany(StudentPayment::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }
}
