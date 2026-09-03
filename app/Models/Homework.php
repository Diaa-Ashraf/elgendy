<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Homework extends Model
{
    use BelongsToTenant;

    protected $table = 'homeworks';

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'datetime',
        'published_at' => 'datetime',
        'total_marks' => 'decimal:2',
        'allow_late_submission' => 'boolean',
    ];

    // ─── Relations ───

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function educationalStage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class, 'stage_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'homework_questions')
            ->withPivot('marks', 'order')
            ->orderBy('homework_questions.order')
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    // ─── Scopes ───

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeActive($query)
    {
        return $query->published()
            ->where('due_date', '>=', now());
    }

    public function scopeForStage($query, int $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    // ─── Helpers ───

    public function isOverdue(): bool
    {
        return $this->due_date->isPast();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at
            && $this->published_at->isPast();
    }

    public function canAcceptSubmissions(): bool
    {
        if ($this->status === 'closed') {
            return false;
        }

        if ($this->isOverdue() && ! $this->allow_late_submission) {
            return false;
        }

        return $this->isPublished();
    }
}
