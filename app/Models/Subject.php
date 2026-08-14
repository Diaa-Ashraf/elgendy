<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
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
