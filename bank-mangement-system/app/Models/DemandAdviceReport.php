<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandAdviceReport extends Model
{
    protected $table = "demand_report";
    protected $guarded = [];  

    public function demandAmountHead() {
        return $this->hasOne(AllHeadTransaction::class, 'type_id','id')->where('head_id',92);
    }

}
 