<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TdsTransfer extends Model {
    protected $table = "tds_transfer";
    protected $guarded = [];
    public function company() {
        return $this->belongsTo(Companies::class);
    }
    public function AllHeadTransaction() {
        return $this->hasMany(AllHeadTransaction::class,'head_id','head_id');
    }
    public function AllHeadTransactionSum() {
        return $this->hasMany(AllHeadTransaction::class,'head_id','head_id')->groupby('head_id')->sum('amount');
    }
    public function payable(){
        return $this->belongsTo(TdsPayable::class,'daybook_ref_id','daybook_ref_id');
    }
    
}
