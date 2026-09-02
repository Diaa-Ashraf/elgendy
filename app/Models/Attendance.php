<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use BelongsToTenant;

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
