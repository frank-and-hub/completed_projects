<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManuallyContractSend extends Model
{
    use HasFactory, HasUuids;
    protected $table = "manually_contracts_send";
    protected $fillable = [
        'first_name',
        'last_name',
        'contact_no',
        'country',
        'phonecode',
        'email',
        'contract_id',
        'admin_id',
        'country'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }


    public function getPhoneAttribute()
    {
        return '' . $this->attributes['phonecode'] . '' . $this->attributes['contact_no'] . '';
    }
}
