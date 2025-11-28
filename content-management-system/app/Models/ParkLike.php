<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkLike extends Model
{
    protected $fillable = ['park_image_id', 'user_id'];

    public function park_images(): BelongsTo
    {
        return $this->belongsTo(ParkImage::class, 'park_image_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
