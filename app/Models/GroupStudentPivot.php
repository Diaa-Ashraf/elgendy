<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupStudentPivot extends Pivot
{
    protected $table = 'group_student';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pivot) {
            if (empty($pivot->joined_at)) {
                $pivot->joined_at = now()->toDateString();
            }
        });
    }
}
