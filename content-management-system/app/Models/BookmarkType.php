<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookmarkType extends Model
{
    use HasFactory;

    protected $fillable=['user_id','type'];

    /**
     * Get the user that owns the BookmarkType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parks(){
        return $this->hasMany(Bookmark::class, "bookmark_type_id");
    }
}
