<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingAccount extends Model
{
    protected $table = "saving_accounts";
    protected $guarded = [];
    public function savingBranch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function ssbMember() {
        return $this->belongsTo(Member::class,'member_id');
    }

    public function associate() {
        return $this->belongsTo(Member::class,'associate_id','id');
    }
    public function savingBranchDetailCustom() {
        return $this->belongsTo(Branch::class,'branch_id');
    }   

    public function savingSeniorDataCustom() {
        return $this->belongsTo(Member::class,'member_id');
    }

    public function savingAcApplicantidCustom() {
        return $this->belongsTo(Member::class,'member_id');
    }
    public function savingAccountBalance() {
        return $this->hasMany(SavingAccountTranscation::class, 'saving_account_id', 'id')->where('is_deleted',0);
    }

    public function ssbaccountMember() {
        return $this->belongsTo(Member::class,'member_id');
    }
	public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }    
    public function ssbMemberCustomer() {
        return $this->belongsTo(MemberCompany::class,'member_id');
    }
    /************* get custome ID(1011CI000001) , First name , Last name from member table >> By Durgesh  *********** */
    public function ssbcustomerDataGet() {
        return $this->belongsTo(Member::class,'customer_id');
    }

    public function customerSSB() {
        return $this->belongsTo(Member::class,'customer_id');
    }

    
    /************* get member  ID(101170100001) rom member_companies  table >> By Durgesh  *********** */
    public function ssbmembersDataGet() {
        return $this->belongsTo(MemberCompany::class,'member_id');
    }

    public function getSSBAccountBalance(){
        return $this->hasOne(SavingAccountBalannce::class,'saving_account_id');
    }

    public function getMemberinvestments(){
        return $this->belongsTo(Memberinvestments::class,'member_investments_id' );
    }

    public function SavingAccountBalannce2(){
        return $this->belongsTo(SavingAccountBalannce::class,'id','saving_account_id');
    }
	 public function ssbMemberCustomer2() {
        return $this->belongsTo(Member::class,'customer_id');
    }

    public function getPlanCompany(){
        return $this->belongsTo(Plans::class,'company_id','company_id')->where('plan_category_code',"S");
    }
    public function getRegisterAmount(){
        return $this->belongsTo(SavingAccountTranscation::class,'id','saving_account_id')->where('is_deleted',0)->where('type',1);
    }
    /*
    public function savingAccountTransactionViewOrderBy()
     {
        return $this->belongsTo(SavingAccountTransactionView::class,'id','saving_account_id')->orderByDesc('id');
     }
     */
     public function savingAccountTransactionViewOrderBy()
     {
        return $this->belongsTo(SavingAccountTransactionView::class,'id','saving_account_id');
     }

     public function savingAccountTransactionViewOrderByDes()
     {
         return $this->belongsTo(SavingAccountTransactionView::class, 'id', 'saving_account_id')
             ->orderByDesc('id');
     }

       public function savingAccountTransactionView()
     {
        return $this->belongsTo(SavingAccountTransactionView::class,'id','saving_account_id');
     }
}
