<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $guarded = [];

    public function groupSession()
    {
        return $this->belongsTo(GroupSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
