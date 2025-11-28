<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssociateCommission extends Model
{
    protected $table = "associate_commissions";
    protected $guarded = [];  

    //Relationship with members table
    public function member() {
        return $this->hasOne(Member::class,'id','member_id');
    }

    //Relationship with member_investments table
    public function investment() {
        return $this->hasOne(Memberinvestments::class,'id','type_id');
    }
}
