<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TdsPayable extends Model {
    protected $table = "tds_payables";
    protected $guarded = [];
    public function challan(){
        return $this->belongsTo(Files::class,'challan_id','id');
    }
    public function tdstransfer(){
        return $this->belongsTo(TdsTransfer::class,'daybook_ref_id','daybook_ref_id');
    }
}
