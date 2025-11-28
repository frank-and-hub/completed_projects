<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminExternalPropertyUsers extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'admin_external_property_users_pivot';

    public $timestamps = true;

    protected $guarded = [];


    public function getUuidAttribute()
    {
        return $this->attributes['id'];
    }

    public function agencies()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function api_property()
    {
        return $this->belongsTo(ExternalPropertyUser::class, 'external_property_users_id');
    }
}
