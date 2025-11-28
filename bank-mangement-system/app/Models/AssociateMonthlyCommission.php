<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssociateMonthlyCommission extends Model
{
    protected $table = "associate_monthly_commission";
    protected $guarded = [];  


    public function member() {
        return $this->hasOne(Member::class,'id','assocaite_id');
    }

    //Relationship with member_investments table
    public function investment() {
        return $this->hasOne(Memberinvestments::class,'id','type_id');
    }
    public function loan() {
        return $this->hasOne(Memberloans::class,'id','type_id');
    }
    public function group_loan() {
        return $this->hasOne(Grouploans::class,'id','type_id');
    } 
    public function carderName(){
        return $this->belongsTo(Carder::class,'cadre_to');
    }
   
}
