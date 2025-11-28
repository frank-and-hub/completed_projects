<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BlogLike;

class Blog extends Model
{
    use HasFactory;
    
public function likes()
{
    return $this->hasMany(BlogLike::class);
}
}
