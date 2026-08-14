<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalStage extends Model
{
    protected $guarded = [];

    public function groups()
    {
        return $this->hasMany(Group::class, 'stage_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'stage_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'stage_id');
    }
}
