<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyTimeSlot extends Model
{
    use HasUuids;
    protected $fillable = [
        'user_id',
        'property_id',
        'internal_property_id',
        'start_time',
        'end_time',
        'start_day_of_week',
        'end_day_of_week',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function internalProperty(): BelongsTo
    {
        return $this->belongsTo(InternalProperty::class, 'internal_property_id');
    }
}
