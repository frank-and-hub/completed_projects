<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;

class ExternalPropertyUser extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasApiTokens;

    protected $table = 'external_property_users';

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'agency_id'    => "json"
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country', 'name');
    }


    public function getUuidAttribute()
    {
        return $this->attributes['id'];
    }

    public function agencies()
    {
        return $this->belongsToMany(Admin::class, 'admin_external_property_users_pivot', 'external_property_users_id', 'admin_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function getHasAgenciesAttribute()
    {
        return $this->agencies()->exists();
    }

    public function getAgenciesIdsAttribute()
    {
        return $this->agencies->pluck('id')->toArray();
    }

    public function getAgencyAttribute()
    {
        return $this->agencies()->first();
    }
}
