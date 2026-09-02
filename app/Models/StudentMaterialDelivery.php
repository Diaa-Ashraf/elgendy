<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StudentMaterialDelivery extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studyMaterial()
    {
        return $this->belongsTo(StudyMaterial::class);
    }
}
