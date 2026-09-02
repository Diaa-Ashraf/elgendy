<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StudentApplication extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function educationalStage()
    {
        return $this->belongsTo(EducationalStage::class, 'stage_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
