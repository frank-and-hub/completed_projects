<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkImage extends Model
{
    use HasFactory;
    protected $fillable = ['park_id', 'media_id', 'set_as_banner', 'status', 'img_tmp_id', 'sort_index', 'is_verified', 'user_id', 'is_archived'];

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Get the user that owns the ParkImage
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function park(): BelongsTo
    {
        return $this->belongsTo(Parks::class, 'park_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ParkLike::class, 'park_image_id');
    }
}
