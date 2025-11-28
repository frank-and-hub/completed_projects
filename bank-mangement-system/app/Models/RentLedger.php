<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentLedger extends Model
{
    protected $table = "rent_ledgers";
    protected $guarded = [];  

    public function RentPayments()
    {
        return $this->hasMany(RentPayment::class,'ledger_id');
    }
    
    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }
 
}