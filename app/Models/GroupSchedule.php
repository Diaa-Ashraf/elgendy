<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class GroupSchedule extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
