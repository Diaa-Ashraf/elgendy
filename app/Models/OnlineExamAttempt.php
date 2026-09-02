<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineExamAttempt extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'total_score' => 'decimal:2',
        'max_possible_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
        'student_answers' => 'array',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
