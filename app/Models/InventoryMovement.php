<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
   protected $guarded = [];

   public function item()
   {
      return $this->belongsTo(InventoryItem::class);
   }

   public function user()
   {
      return $this->belongsTo(User::class);
   }
}
