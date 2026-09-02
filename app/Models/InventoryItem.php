<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use BelongsToTenant;
   protected $guarded = []; 
}
