<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyContact extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'properties_contacts';
    protected $fillable = [
        'properties_id','clientPropertyID', 'clientOfficeID', 'officeName', 'officeTel', 'officeFax', 'officeEmail', 'clientAgentID',
        'fullName', 'cell', 'email', 'profile', 'logo'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'properties_id');
    }
}
