<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payable extends Model {
    protected $table = "payables";
    protected $guarded = [];
    public function challan(){
        return $this->belongsTo(Files::class,'challan_id','id');
    }
}
