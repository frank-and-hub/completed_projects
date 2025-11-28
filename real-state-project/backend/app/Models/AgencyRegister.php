<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyRegister extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'agency_register';
    protected $fillable = [
        'admin_id',
        'f_name',
        'l_name',
        'business_name',
        'id_number',
        'registration_number',
        'vat_number',
        'street_address',
        'street_address_2',
        'city',
        'province',
        'postal_code',
        'type_of_business',
        'country',
        'message',
        'agency_banner'
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function country_()
    {
        return $this->belongsTo(Country::class, 'country');
    }

    public function state_()
    {
        return $this->belongsTo(State::class, 'province');
    }

    public function city_()
    {
        return $this->belongsTo(State_City::class, 'city');
    }
}
