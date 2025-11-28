<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = 'ratings';

    protected $fillable = ['rating','review','user_id','park_id','is_verified'];

    public function park()
    {
        return $this->belongsTo(Parks::class, "park_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
