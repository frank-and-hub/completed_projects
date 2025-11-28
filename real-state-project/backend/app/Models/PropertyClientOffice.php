<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyClientOffice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'property_client_office';
    protected $fillable = [
        'clientOfficeID',
        'name',
        'tel',
        'fax',
        'email',
        'website',
        'logo',
        'officereference',
        'sourceId',
        'profile',
        'physicalAddress'
    ];
    
    public function properties()
    {
        return $this->hasMany(Property::class, 'client_office_id');
    }

}
