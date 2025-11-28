<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyNeedsApiUser extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'property_needs_apiuser';
    protected $fillable = [
        'name',
        'email',
        'password',
        'contact',
        'dial_code',
        'country',
        'user_name',
        'admin_id',
        'suburb_name',
        'city',
        'property_type',
        'created_at',
        'updated_at'
    ];

        public function agency(): BelongsTo
        {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
