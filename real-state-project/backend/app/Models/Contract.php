<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = "contracts";

    protected $guarded = [];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function getUuidAttribute()
    {
        return $this->attributes['id'];
    }

    public function getPropertyIdsAttribute()
    {
        return $this?->properties ? $this?->properties?->pluck('id')->toArray() : [];
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(InternalProperty::class, PropertyContract::class);
    }
    public function getTenantsIdsAttribute()
    {
        return $this?->tenants->pluck('id')->toArray();
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, ContractUser::class);
    }

    public function getTenantAttribute()
    {
        return $this?->tenants->first();
    }

    public function getPropertyAttribute()
    {
        return $this?->properties->first();
    }

    public function getOfflineTenantsIdsAttribute()
    {
        return $this?->offline_tenants?->pluck('id')->toArray();
    }

    public function offline_tenants(): HasMany
    {
        return $this->hasMany(ManuallyContractSend::class, 'contract_id')->where('last_name', '!=', '')->orderByDesc('id');
    }

    public function getOfflineTenantAttribute()
    {
        return $this?->offline_tenants->first();
    }

    public function records()
    {
        return $this->hasMany(ContractRecord::class, 'contract_id');
    }

    public function all_status(): HasOne
    {
        return $this->hasOne(ContractStatus::class);
    }
}
