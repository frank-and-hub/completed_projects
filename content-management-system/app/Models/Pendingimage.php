<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pendingimage extends Model
{
    use HasFactory;
    protected $fillable =['park_id','user_id','total_pending_image','total_verify_image'];


    /**
     * Get the user that owns the Pendingimage
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
}
