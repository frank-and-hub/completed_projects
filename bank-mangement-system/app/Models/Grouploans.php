<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Grouploans extends Model
{
    protected $table = "group_loans";
    protected $guarded = [];
    public function loanMember()
    {
        return $this->belongsTo(Member::class, 'customer_id', 'id');
    }
    public function loanMembers()
    {
        return $this->belongsTo(Member::class, 'customer_id', 'id');
    }
    //Relation with loan applicant table
    public function LoanApplicants()
    {
        return $this->hasMany(Loanapplicantdetails::class, 'member_loan_id', 'member_loan_id');
    }
    //Relation with loan co-applicant table
    public function LoanCoApplicants()
    {
        return $this->hasMany(Loanscoapplicantdetails::class, 'member_loan_id', 'member_loan_id');
    }
    //Relation with loan co-applicant table
    public function LoanGuarantor()
    {
        return $this->hasMany(Loansguarantordetails::class, 'member_loan_id', 'member_loan_id');
    }
    public function savingAccount()
    {
        return $this->belongsTo(SavingAccount::class, 'applicant_id', 'member_id');
    }
    public function loanSavingAccount()
    {
        return $this->belongsTo(SavingAccount::class, 'applicant_id', 'member_id');
    }
    public function loanSavingAccount2()
    {
        return $this->belongsTo(SavingAccount::class, 'member_id', 'member_id');
    }
    public function gloanBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function loanBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function loanMemberAssociate()
    {
        return $this->belongsTo(Member::class, 'associate_member_id', 'id');
    }
    public function loanMemberAssociatec()
    {
        return $this->belongsTo(Member::class, 'customer_id', 'id');
    }
    public function loanMemberBankDetails()
    {
        return $this->belongsTo(MemberBankDetail::class, 'applicant_id', 'member_id');
    }
    public function loanMemberBankDetails2()
    {
        return $this->belongsTo(MemberBankDetail::class, 'member_id', 'member_id');
    }
    /*
       public function loanMemberBankDetails2()
       {
           return $this->belongsTo(MemberBankDetail::class, 'member_id', 'member_id');
       }
       */
    public function loan()
    {
        return $this->belongsTo(Loans::class, 'loan_type');
    }
    public function groupleaderMemberIDCustom()
    {
        return $this->belongsTo(Member::class, 'groupleader_member_id');
    }
    public function MemberApplicantCustom()
    {
        return $this->belongsTo(Member::class, 'applicant_id');
    }
    public function loanTransaction()
    {
        return $this->hasMany(LoanDayBooks::class, 'account_number', 'account_number')->where('is_deleted', 0);
    }
    public function loanTransactionNew()
    {
        return $this->hasMany(LoanDayBooks::class, 'account_number', 'account_number')->where('is_deleted', 0)->where('loan_sub_type', '!=', 2);
    }
    public function grpLoan()
    {
        return $this->belongsTo(self::class, 'member_loan_id', 'member_loan_id');
    }
    public function loanTransactionSum()
    {
        return $this->HasMany(LoanEmisNew::class, 'loan_id', 'id')->whereIn('loan_type', [3]);
    }
    public function children()
    {
        return $this->belongsTo(Member::class, 'associate_member_id', 'id');
    }
    public function loans()
    {
        return $this->belongsTo(Loans::class, 'loan_type', 'id');
    }
    public function CollectorAccount()
    {
        return $this->hasOne(CollectorAccount::class, 'type_id', 'id')->where('type', 3)->where('status', 1);
    }
    // public function getOutstanding()
    // {
    //     return $this->belongsTo(LoanEmisNew::class, 'id', 'loan_id')->whereIn('loan_type', [3])->where('is_deleted', '0');
    // }
    //Relation with member table
    public function member()
    {
        return $this->belongsTo(Member::class, 'customer_id');
    }
    public function loanMemberCompany()
    {
        return $this->belongsTo(MemberCompany::class, 'applicant_id', 'id');
    }
    public function loanMemberCompanyid()
    {
        return $this->belongsTo(MemberCompany::class, 'member_id', 'id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id', 'id');
    }
    public function memberCompany()
    {
        return $this->belongsTo(MemberCompany::class, 'applicant_id');
    }
    //Relation with loan co-applicant table
    public function LoanCoApplicantsOne()
    {
        return $this->hasOne(Loanscoapplicantdetails::class, 'member_loan_id', 'member_loan_id');
    }
    //Relation with loan co-applicant table
    public function LoanGuarantorOne()
    {
        return $this->hasOne(Loansguarantordetails::class, 'member_loan_id', 'member_loan_id');
    }
    public function newLoanSSB()
    {
        return $this->belongsTo(SavingAccount::class, 'ssb_id', 'id');
    }
    public function getOutstanding()
    {
        return $this->belongsTo(LoanEmisNew::class, 'id', 'loan_id')
            ->where('loan_type', $this->loan_type)
            ->where('is_deleted', '0');
    }
    public function get_outstanding()
    {
        return $this->belongsTo(LoanEmisNew::class,'id', 'loan_id')
            ->where('is_deleted', '0')
            ->orderBy('emi_date');
    }
    public function loanTransactionSumLoanDaybook()
    {
        return $this->HasMany(LoanDayBooks::class, 'account_number', 'account_number')->where('loan_sub_type', 0)->where('is_deleted', 0);
    }
    public function savingAccountCustom()
    {
        return $this->belongsTo(SavingAccount::class, 'associate_member_id', 'member_id');
    }
    public function savingAccountCustomNew()
    {
        return $this->belongsTo(GroupLoan::class, 'customer_id', 'customer_id');
    }

    // Change the format of the approveDate using mutator
    // public function getApproveDateAttribute()
    // {
    //     return date('d/m/Y',strtotime($this->attributes['approve_date']));
    // }
    // Change the format of the createdAt using mutator
    // public function getCreatedAtAttribute()
    // {
    //     return date('d/m/Y',strtotime($this->attributes['created_at']));
    // }

    // Change the format of the createdAt using mutator
    public function getdueAmountAttribute()
    {
        // Retrive the value of th id attribute
        $id = $this->attributes['id'];
        // Retrive the value of the account_number attribute
        $accountNumber = $this->attributes['account_number'];
        // Retrive the value of the approve_date attribute
        $approveDate = $this->attributes['approve_date'];
        // Retrive the value of the emi_option attribute
        $emiOption = $this->attributes['emi_option'];
        // Retrive the value of the emi_amount attribute
        $emiAmount = $this->attributes['emi_amount'];
         // Retrive the value of the closing_date attribute
        $closingDate = $this->attributes['closing_date'];
        //Retrive stateId form the loanBranch relation
        $stateId = $this->loanBranch->state_id;
        // Calculate dueAmount
        $dueAmount = (in_array($this->attributes['status'],[0,1])) ?  :
         emiAmountUotoTodaysDate($id,$accountNumber,$approveDate,$stateId,$emiOption,$emiAmount,$closingDate) ?? 0;

         return $dueAmount;
    }
    public function loanApplicantBankDetails()
    {
        return $this->belongsTo(Loanapplicantdetails::class, 'member_id', 'member_id');
    }
    
    public function loanPlans()
    {
        return $this->belongsTo(Loans::class, 'loan_type');
    }
    public function collectorAssociate()
    {
        return $this->hasOne(CollectorAccount::class, 'type_id', 'id')->where('type', 3)->where('status', 1); 
    }
}
