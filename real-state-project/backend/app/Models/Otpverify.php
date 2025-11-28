<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Otpverify extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'otp_verify';
    protected $fillable = [
        'user_id',
        'phone',
        'otp',
        'otp_generated_at',
        'otp_verified_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
