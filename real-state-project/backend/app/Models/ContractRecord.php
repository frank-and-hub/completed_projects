<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractRecord extends Model
{
    use HasFactory, HasUuids;

    protected $table = "contract_records";

    protected $fillable = [
        'contract_id',
        'type',
        'internal_property_id',
        'tenant_id',
        'user_id',
        'status',
        'title',
        'description',
        'date_time'
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(InternalProperty::class, 'internal_property_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
