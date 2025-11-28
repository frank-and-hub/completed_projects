<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlanFeature extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['plan_id', 'planType_value', 'planType'];

    public function plan():BelongsTo
    {
        return $this->belongsTo(Plans::class, 'plan_id');
    }
}
