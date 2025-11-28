<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchDaybook extends Model
{
    protected $table = "branch_daybook";
    protected $guarded = [];
    
    
    public function member_investment()
    {
    	  return $this->belongsTo(Memberinvestments::class,'type_id','id');
    }

    public function member_loan()
    {
        return $this->belongsTo(Memberloans::class,'type_id','id');
    }

     public function group_member_loan()
    {
        return $this->belongsTo(Grouploans::class,'type_id','member_loan_id');
    }
    
     public function day_book()
    {
    	  return $this->belongsTo(Daybook::class,'member_id','member_id');
    }

     public function demand_advice()
    {
          return $this->belongsTo(DemandAdvice::class,'type_id','id');
    }
     public function day_book_data()
    {
          return $this->belongsTo(Daybook::class,'type_transaction_id','id');
    }

    public function type()
    {
          return $this->hasMany(TransactionType::class,'type','type_id');
    }
    public function member()
    {
          return $this->belongsTo(Member::class,'type_id','id');
    }
    
     public function associateMember()
    {
          return $this->belongsTo(Member::class,'associate_id','id');
    }
    public function memberMemberId()
    {
          return $this->belongsTo(Member::class,'member_id','id');
    }
    public function memberMemberTyprTransactionId()
    {
          return $this->belongsTo(Member::class,'type_transaction_id','id');
    }

    public function receivedvoucherbytype_transaction_id()
    {
          return $this->belongsTo(ReceivedVoucher::class,'type_transaction_id','id');
    }

    public function receivedvoucherbytype_id()
    {
          return $this->belongsTo(ReceivedVoucher::class,'type_id','id');
    }

    public function SavingAccountTranscation()
    {
        return $this->belongsTo(SavingAccountTranscation::class,'type_id','saving_account_id');
    }

    public function SavingAccountTranscationtype_trans_id()
    {
        return $this->belongsTo(SavingAccountTranscation::class,'type_transaction_id','id');
    }

    public function SamraddhBank()
    {
        return $this->belongsTo(SamraddhBank::class,'transction_bank_to','id');
    }
    public function VoucherSamraddhBank()
    {
        return $this->belongsTo(SamraddhBank::class,'receive_bank_ac_id','id');
    }

    public function VoucherSamraddhBankbank_ac_id()
    {
        return $this->belongsTo(SamraddhBank::class,'bank_ac_id','id');
    }

    public function accountHead()
    {
        return $this->belongsTo(AccountHeads::class,'type_id','head_id');
    }

    public function loan_from_bank()
    {
        return $this->belongsTo(LoanFromBank::class,'daybook_ref_id','daybook_ref_id');
    }

    public function company_bound()
    {
        return $this->belongsTo(CompanyBound::class,'daybook_ref_id','daybook_ref_id');
    }

    public function bill_expense()
    {
        return $this->belongsTo(Expense::class,'type_id','id');
    }
    
     public function BillExpense()
    {
        return $this->belongsTo(BillExpense::class,'daybook_ref_id','daybook_refid');
    }

    public function RentLiabilityLedger()
    {
        return $this->belongsTo(RentLiabilityLedger::class,'type_transaction_id','id');
    }

    public function EmployeeSalary()
    {
        return $this->belongsTo(EmployeeSalary::class,'type_transaction_id','id');
    }

    public function RentPayment()
    {
        return $this->belongsTo(RentPayment::class,'type_id','id');
    }

    public function EmployeeSalaryBytype_id()
    {
        return $this->belongsTo(EmployeeSalary::class,'type_id','id');
    }

    public function debitcard()
    {
        return $this->belongsTo(SavingAccount::class,'member_id','member_id');
    }

    public function memberCompany()
    {
        return $this->belongsTo(MemberCompany::class,'type_id','id');
    }

    public function memberCompanybyMemberId()
    {
        return $this->belongsTo(MemberCompany::class,'member_id','id');
    }
}