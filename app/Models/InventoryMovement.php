<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use BelongsToTenant;
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
