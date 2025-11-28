<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateLandlord extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'privatelandlord_verify';
    protected $fillable = [
        'admin_id',
        'phone_otp',
        'email_otp',
        'phone_otp_generated_at',
        'email_otp_generated_at',
        'phone_otp_verified_at',
        'email_otp_verified_at'
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
