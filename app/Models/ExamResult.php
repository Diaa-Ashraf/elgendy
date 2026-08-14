<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
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
