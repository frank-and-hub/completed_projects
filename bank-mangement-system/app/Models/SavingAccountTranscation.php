<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SavingAccountTranscation extends Model
{
    protected $table = "saving_account_transctions";
    protected $guarded = [];
    public function savingAc() {
        return $this->belongsTo(SavingAccount::class,'saving_account_id');
    }
 /******* jack 11 may 2021 *******/	 
    public function associateMember() {
        return $this->belongsTo(Member::class,'associate_id');
    }
    public function dbranch() {
        return $this->belongsTo(Branch::class,'branch_id');
    } 
	public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }

    public function memberCompany() {
        return $this->belongsTo(MemberCompany::class,'customer_id','customer_id');
    }

    
    public function SavingAccountBalannce2(){
        return $this->belongsTo(SavingAccountBalannce::class,'saving_account_id','saving_account_id');
    }

/******* jack 11 may 2021*******/	
}