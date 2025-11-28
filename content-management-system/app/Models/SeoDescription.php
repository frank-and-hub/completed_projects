<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoDescription extends Model
{
    use HasFactory;

    protected $fillable = ['description'];

    public function metable()
    {
        return $this->morphTo();
    }
}
