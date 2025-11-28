<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Memberloans extends Model
{
    protected $table = "member_loans";
    protected $guarded = [];
    public function memberCompany()
    {
        return $this->belongsTo(MemberCompany::class, 'applicant_id');
    }
    public function loanMemberCompany()
    {
        return $this->belongsTo(MemberCompany::class, 'applicant_id', 'id');
    }
    //Relation with member table
    public function member()
    {
        return $this->belongsTo(Member::class, 'customer_id');
    }
    public function loanMember()
    {
        return $this->belongsTo(Member::class, 'customer_id', 'id');
    }
    public function loanMemberCustom()
    {
        return $this->belongsTo(Member::class, 'associate_member_id', 'id');
    }
    public function children()
    {
        return $this->belongsTo(Member::class, 'associate_member_id', 'id');
    }
    //Relation with loan applicant table
    public function loan()
    {
        return $this->belongsTo(Loans::class, 'loan_type');
    }
    //Relation with loan applicant table
    public function LoanApplicants()
    {
        return $this->hasMany(Loanapplicantdetails::class, 'member_loan_id');
    }
    //Relation with loan co-applicant table
    public function LoanCoApplicants()
    {
        return $this->hasMany(Loanscoapplicantdetails::class, 'member_loan_id');
    }
    //Relation with loan co-applicant table
    public function LoanGuarantor()
    {
        return $this->hasMany(Loansguarantordetails::class, 'member_loan_id');
    }
    //Relation with loan co-applicant table
    public function Loanotherdocs()
    {
        return $this->hasMany(Loanotherdocs::class, 'member_loan_id');
    }
    //Relation with loan co-applicant table
    public function GroupLoanMembers()
    {
        return $this->hasMany(Grouploans::class, 'member_loan_id');
    }
    //Relation with loan co-applicant table
    public function loanInvestmentPlans()
    {
        return $this->hasMany(Loaninvestmentmembers::class, 'member_loan_id');
    }
    public function loanSavingAccount()
    {
        return $this->belongsTo(SavingAccount::class, 'applicant_id', 'member_id');
    }
    public function loanMemberAssociate()
    {
        return $this->belongsTo(Member::class, 'associate_member_id', 'id');
    }
    public function loanMemberBankDetails()
    {
        return $this->belongsTo(MemberBankDetail::class, 'applicant_id', 'member_id');
        // return $this->belongsTo(MemberBankDetail::class, 'applicant_id', 'member_id');
    }
    public function loanMemberIdProofs()
    {
        return $this->belongsTo(MemberIdProof::class, 'applicant_id', 'member_id');
    }
    public function savingAccount()
    {
        return $this->belongsTo(SavingAccount::class, 'applicant_id', 'member_id');
    }
    public function savingAccountCustom()
    {
        return $this->belongsTo(SavingAccount::class, 'associate_member_id', 'member_id');
    }
    public function loanBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function loanapplicantDetails()
    {
        return $this->belongsTo(Loanapplicantdetails::class, 'member_loan_id');
    }
    public function groupleaderMemberIDCustom()
    {
        return $this->belongsTo(Member::class, 'groupleader_member_id');
    }
    public function loanTransaction()
    {
        return $this->hasMany(LoanDayBooks::class, 'account_number', 'account_number')->where('is_deleted', 0);
    }
    public function loanTransactionNew()
    {
        return $this->hasMany(LoanDayBooks::class, 'account_number', 'account_number')->where('is_deleted', 0)->where('loan_sub_type', '!=', 2);
    }
    public function loanTransactionSum()
    {
        return $this->HasMany(LoanEmisNew::class, 'loan_id', 'id')->whereIn('loan_type', [1, 2, 4]);
    }
    public function outstandingAmount()
    {
        return $this->belomgsTo(LoanEmisNew::class, 'loan_id', 'id');
    }
    //Relation with Loans Table to fetch Loan Type Name Directly
    public function loans()
    {
        return $this->belongsTo(Loans::class, 'loan_type', 'id');
    }
    public function loanTransactionSumLoanDaybook()
    {
        return $this->HasMany(LoanDayBooks::class, 'account_number', 'account_number')->where('loan_sub_type', 0)->where('is_deleted', 0);
    }
    /**
     * Get File Chrage Based on loan Plan
     */
    public function fileCharge()
    {
        return $this->belongsTo(LoanCharge::class, 'loan_type', 'loan_type');
    }
    public function CollectorAccount()
    {
        return $this->hasOne(CollectorAccount::class, 'type_id', 'id')->where('type', 2)->where('status', 1);
    }
    public function getOutstanding()
    {
        return $this->hasMany(LoanEmisNew::class, 'loan_id', 'id')
            ->where('is_deleted', '0')
            ->where('loan_type', $this->loan_type);
    }
    public function get_outstanding()
    {
        return $this->belongsTo(LoanEmisNew::class,'id', 'loan_id')
            ->where('is_deleted', '0')
            ->orderBy('emi_date');
    }
    public function loanId()
    {
        return $this->belongsTo(LoanEmisNew::class, 'loan_id', 'id')->where('is_deleted', '0');
    }
    public function paymentMode()
    {
        return $this->belongsTo(Daybook::class);
    }
    public function paymentcheque()
    {
        return $this->belongsTo(Daybook::class);
    }
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
    //Relation with loan co-applicant tables
    public function LoanCoApplicantsOne()
    {
        return $this->hasOne(Loanscoapplicantdetails::class, 'member_loan_id', 'id');
    }
    public function LoanGuarantorOne()
    {
        return $this->hasOne(Loansguarantordetails::class, 'member_loan_id', 'id');
    }
    public function newLoanSSB()
    {
        return $this->belongsTo(SavingAccount::class, 'ssb_id', 'id');
    }
    // // Change the format of the approveDate using mutator
    // public function getApproveDateAttribute()
    // {
    //     return date('d/m/Y', strtotime(convertdate($this->attributes['approve_date'])));
    // }
    // // Change the format of the createdAt using mutator
    // public function getCreatedAtAttribute()
    // {
    //     return date('d/m/Y', strtotime(convertdate($this->attributes['created_at'])));
    // }
    public function getdueAmountAttribute()
    {
        if (array_key_exists('closing_date', $this->attributes)) {
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
            $dueAmount = (in_array($this->attributes['status'], [0, 1]))
                ? 0
                : emiAmountUotoTodaysDate($id, $accountNumber, $approveDate, $stateId, $emiOption, $emiAmount, $closingDate) ?? 0;
            return $dueAmount;
        } else {
            // Handle the case where 'closing_date' is not present in the attributes
            return 0; // or any default value
        }
    }
    public function getclosingAmountAttribute()
    {
        if (array_key_exists('closing_date', $this->attributes)) {
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
            $dueAmount = (in_array($this->attributes['status'], [0, 1]))
                ? 0
                : emiAmountUotoTodaysDate($id, $accountNumber, $approveDate, $stateId, $emiOption, $emiAmount, $closingDate) ?? 0;
            return $dueAmount;
        } else {
            // Handle the case where 'closing_date' is not present in the attributes
            return 0; // or any default value
        }
    }
    public function loanDetails()
    {
        return $this->belongsTo(Loans::class, 'loan_id', 'id');
    }
    public function loanPlans()
    {
        return $this->belongsTo(Loans::class, 'loan_type');
    }
    public function collectorAssociate()
    {
        return $this->hasOne(CollectorAccount::class, 'type_id', 'id')->where('type', 2)->where('status', 1); 
    }
}
