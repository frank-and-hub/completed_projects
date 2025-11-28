<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentLiabilityLedger extends Model
{
    protected $table = "rent_liability_ledgers";
    protected $guarded = [];  


    public function rentLibL() {
        return $this->belongsTo(RentLiability::class,'rent_liability_id');
    }
    public function rentLib() {
        return $this->belongsTo(RentLiability::class,'rent_liability_id');
    }
    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }

    
 
}