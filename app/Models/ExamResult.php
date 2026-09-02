<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use BelongsToTenant;
       protected $guarded = [];

   public function exam()
   {
      return $this->belongsTo(Exam::class);
   }

   public function student()
   {
      return $this->belongsTo(Student::class);
   }
}
