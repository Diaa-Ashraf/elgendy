<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $guarded = [];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function educationalStage()
    {
        return $this->belongsTo(EducationalStage::class, 'stage_id');
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot('marks', 'order')
            ->orderBy('exam_questions.order')
            ->withTimestamps();
    }

    public function onlineAttempts()
    {
        return $this->hasMany(OnlineExamAttempt::class);
    }
}
