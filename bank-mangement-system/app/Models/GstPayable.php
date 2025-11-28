<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstPayable extends Model {
    protected $table = "gst_payables";
    protected $guarded = [];
    public function challan(){
        return $this->belongsTo(Files::class,'challan_id','id');
    }
}
