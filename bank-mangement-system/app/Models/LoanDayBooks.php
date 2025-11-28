<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanDayBooks extends Model
{
    protected $table = "loan_day_books";
    protected $guarded = [];


    public function loan_plan()
    {
    	return $this->belongsTo('App\Models\Loans','loan_type');
    }

    public function loanDetail()
    {
    	return $this->belongsTo('App\Models\Memberloans','account_number','account_number');
    }

    public function loanBranch()
    {
    	return $this->belongsTo('App\Models\Branch','branch_id');
    }
    public function loan_member()
    {
    	return $this->belongsTo(Member::class,'applicant_id');
    }
    public function loan_member_company()
    {
    	return $this->belongsTo('App\Models\MemberCompany','applicant_id');
    }
    public function member_loan(){
		return $this->belongsTo('App\Models\Memberloans','loan_id');
	}

    public function group_member_loan(){
		return $this->belongsTo(Grouploans::class,'account_number','account_number');
	}
    public function group_member_loan_via_id(){
		return $this->belongsTo(Grouploans::class,'loan_id');
	}
    public function loanMemberAssociate(){
        return $this->belongsTo(Member::class,'associate_id','id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class,'company_id');
    }
    public function allHeadTransaction()
    {
        return $this->hasMany('App\Models\AllHeadTransaction','daybook_ref_id','daybook_ref_id');

    }
}
