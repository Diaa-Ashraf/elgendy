<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
