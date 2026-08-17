<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'default_marks' => 'decimal:2',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function educationalStage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class, 'stage_id');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
            ->withPivot('marks', 'order')
            ->withTimestamps();
    }
}
