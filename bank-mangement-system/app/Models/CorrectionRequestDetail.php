<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequestDetail extends Model {
    protected $table = "correction_request_details";
    protected $guarded = [];

    // Releationships
    public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }
    public function customer(){
        return $this->belongsTo(Member::class,'type_id');
    }
    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function correction_type(){
        return $this->belongsTo(CorrectionType::class,'correction_type_Id');
    }
    public function user(){
        return $this->belongsTo(User::class,'created_by_id');
    }
}
