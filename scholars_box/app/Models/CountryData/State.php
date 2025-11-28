<?php

namespace App\Models\CountryData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;


class State extends Model
{
    use SoftDeletes;

   protected $guarded = [];

   public function users()
   {
       return $this->hasMany(User::class,'state','name');
   }

    
}
