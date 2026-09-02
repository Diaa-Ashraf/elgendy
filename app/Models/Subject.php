<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
