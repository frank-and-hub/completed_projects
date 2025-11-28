<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedemandDemandAdvice extends Model
{
    protected $table = "redemand_demand_advice";
    protected $guarded = [];  

    //Relationship with DemandAdvice table
    public function demand() {
        return $this->belongsTo(DemandAdvice::class,'id','demand_id');
    }

    //Relationship with member_investments table

    public function demandReason()
    {
        return $this->belongsTo(DemandAdvice::class,'demand_id','id');
    }
   
}
