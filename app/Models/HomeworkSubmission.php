<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkSubmission extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
        'student_answers' => 'array',
        'score' => 'decimal:2',
        'auto_score' => 'decimal:2',
    ];

    // ─── Relations ───

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // ─── Helpers ───

    public function isGraded(): bool
    {
        return $this->status === 'graded' || $this->status === 'returned';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'returned']);
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'لم يُسلَّم بعد',
            'submitted' => 'تم التسليم ⏳',
            'graded' => 'تم التصحيح ✅',
            'returned' => 'مُعاد للمراجعة 🔄',
            default => $this->status,
        };
    }

    public function getScorePercentageAttribute(): ?float
    {
        if (is_null($this->score) || ! $this->homework) {
            return null;
        }

        $totalMarks = $this->homework->total_marks;

        return $totalMarks > 0 ? round(($this->score / $totalMarks) * 100, 1) : 0;
    }
}
