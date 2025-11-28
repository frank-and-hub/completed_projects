<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserEmployment extends Model
{
    use HasUuids;
    protected $fillable = [
        'user_id',
        'emplyee_type',
        'live_with',
    ];

    protected $casts = [
        'user_id' => 'string',
        'emplyee_type' => 'string',
        'live_with' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
