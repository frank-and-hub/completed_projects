<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = ['bookmark_type_id','park_id','user_id'];

    // protected $guarded;

    /**
     * Get the user that owns the Bookmark
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bookmarkType(): BelongsTo
    {
        return $this->belongsTo(BookmarkType::class, 'bookmark_type_id');
    }

    public function park(): BelongsTo
    {
        return $this->belongsTo(Parks::class, 'park_id');
    }


}
