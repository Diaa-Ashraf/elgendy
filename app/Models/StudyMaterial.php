<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StudyMaterial extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function educationalStage()
    {
        return $this->belongsTo(EducationalStage::class, 'stage_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function deliveries()
    {
        return $this->hasMany(StudentMaterialDelivery::class);
    }
}
