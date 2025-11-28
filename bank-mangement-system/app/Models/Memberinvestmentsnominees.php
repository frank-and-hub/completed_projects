<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Memberinvestmentsnominees extends Model
{
    protected $table = "member_investments_nominees";
    protected $guarded = [];
    public function memberinvestments(){
        return $this->belongsTo(Memberinvestments::class,'investment_id','id');
    } 
    public function investmentRelation() {
        return $this->belongsTo(Relations::class,'relation','id');
    }
}
