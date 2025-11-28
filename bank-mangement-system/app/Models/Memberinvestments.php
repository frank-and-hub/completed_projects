<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Plans;
use App\Models\Member;
use App\Scopes\ActiveScope;
class Memberinvestments extends Model
{
    protected $table = "member_investments";
    protected $guarded = [];

    /******* Relationship with member table  get customer_id , first name, last name  *******/
    public function member() {
        return $this->belongsTo(Member::class,'customer_id','id');
    }

     /******* Relationship with member table  get memebrId  *******/
    public function memberCompany() {
        return $this->belongsTo(MemberCompany::class,'member_id','id');
    }

    /******* Relationship with plan table *******/
    public function plan() {
        return $this->belongsTo(Plans::class)->withoutGlobalScope(ActiveScope::class);
    }
   
    /******* Relationship with Memberinvestmentsnominees table *******/
    public function investmentNomiees() {
        return $this->hasMany(Memberinvestmentsnominees::class, 'investment_id');
    }

    /******* Relationship with Memberinvestmentspayments table *******/
    public function investmentPayment() {
        return $this->hasMany(Memberinvestmentspayments::class, 'investment_id');
    }

    public function ssb() {
        return $this->hasOne(SavingAccount::class,'member_id','member_id');
    }
    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    /******* Relationship with member table *******/
    public function associateMember() {
        return $this->belongsTo(Member::class,'associate_id');
    }

    public function memberBankDetail() {
        return $this->belongsTo(MemberBankDetail::class,'member_id','member_id');
    }

    public function ssb_detail() {
        return $this->hasMany(SavingAccount::class,'member_investments_id');
    }
    public function demandadvice() {
        return $this->hasOne(DemandAdvice::class,'investment_id','id');
    }
    public function eliaccount() {
        return $this->hasOne(Daybook::class,'investment_id','id');
    }
    public function sumdeposite() {
        return $this->hasMany(Daybook::class,'investment_id','id')->whereIn('transaction_type',[2,4]);
    }

    public function TransactionTypeDate() {
        return $this->hasMany(Daybook::class,'investment_id','id')->where('transaction_type',17);
    }

    public function maturityRecord() {
        return $this->belongsTo(Daybook::class,'id','investment_id')->where('transaction_type',17);
    }

    public function eliAccountd() {
        return $this->belongsTo(Daybook::class,'investment_id','id')->where('is_eli',1);
    }

    public function getPlanCustom() {
        return $this->belongsTo(Plans::class,'plan_id');
    }
	
    public function getAllHeadTransaction() {
        return $this->belongsTo(AllHeadTransaction::class,'plan_id')->where('head_id',92);
    }
   
    // public function loanExists() {
    //     return $this->belongsTo(MeberLoans::class,'plan_id')->where('head_id',92);
    // }
	
	 public function ssb_detailb() {
        return $this->hasMany(SavingAccount::class,'member_investments_id','id');
    }

    public function ssb_detailb_customer() {
        return $this->hasMany(SavingAccount::class,'customer_id','customer_id');
    }

    public function investmentNominee() {
        return $this->belongsTo(MemberNominee::class,'member_id','member_id')->where('is_deleted',0);
    }

    public function deployments()
    {
        return $this->hasManyThrough(AllHeadTransaction::class,DemandAdvice::class,'investment_id','type_id','id','id');
    }

    public function getBankDetails()
    {
        return $this->belongsTo(SamraddhBankDaybook::class,'id','type_id')->where('type',3)->where('sub_type',31);
    }



    // Loan Collector Required Models

    public function CollectorAccount() {
        return $this->hasOne(CollectorAccount::class,'type_id','id')->where('type',1)->where('status',1);
    }

    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }

    public function memberbymemberid() {
        return $this->belongsTo(Member::class,'member_id','id');
    } 

    public function ssbAccount() {
        return $this->hasMany(SavingAccount::class,'member_id','associate_id');
    }


    public function ssbBalanceView() {
        return $this->belongsTo(SavingAccountBalannce::class,'account_number','account_no');
    }

    public function loanAgainstPlan()
    {
        return $this->belongsTo(Loaninvestmentmembers::class,'plan_id','id');
    }
    public function InvestmentBalance()
    {
        return $this->belongsTo(InvestmentBalance::class,'account_number','account_number');
    }

    public function loan_against_deposits()
    {
        return $this->belongsTo(LoanAgainstDeposit::class,'plan_id','plan_id');
    }

    public function assoCiateSSbAccount() {
        return $this->hasMany(SavingAccount::class,'customer_id','associate_id');
    }

    public function memberloanA()
    {
        return $this->hasMany(Memberloans::class,'applicant_id','member_id');
    }
    public function memberloanM()
    {
        return $this->hasMany(Memberloans::class,'member_id','member_id');
    }
}
