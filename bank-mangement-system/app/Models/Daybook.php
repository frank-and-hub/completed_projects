<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daybook extends Model
{
    protected $table = "day_books";
    protected $guarded = [];


    public function investment() {
        return $this->belongsTo(Memberinvestments::class,'investment_id');
    }
    public function dbranch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }
    /******* Relationship with member table *******/
    /******* jack 11 may 2021 *******/	 
    public function member() {
        return $this->belongsTo(Member::class);
    } 
    public function associateMember() {
        return $this->belongsTo(Member::class,'associate_id');
    }

    public function seniorMemberCustom() {
        return $this->belongsTo(Member::class,'member_id');
    }

    public function MemberCompany() {
        return $this->belongsTo(MemberCompany::class,'member_id','id');
    }
    public function companyName() {
        return $this->belongsTo(Companies::class,'company_id');
    }
    
	
/******* jack 11 may 2021*******/	 
	
}
