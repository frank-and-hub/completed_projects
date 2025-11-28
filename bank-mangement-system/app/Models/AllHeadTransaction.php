<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class AllHeadTransaction extends Model

{

    protected $table = "all_head_transaction";

    protected $guarded = [];
    public function branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function demand_advices_fresh_expenses() {
        return $this->belongsTo(DemandAdviceExpense::class,'type_id');
    }

    public function member() {
        return $this->belongsTo(Member::class);
    }

     public function AccountHeads() {
        return $this->belongsTo(AccountHeads::class,'head_id','head_id');
    }

     public function memberCompany() {
        return $this->belongsTo(MemberCompany::class,'member_id');
    }
    public function member_investment()
    {
    	  return $this->belongsTo(Memberinvestments::class,'type_id','id');
    }
      
    public function associateMember()
    {
          return $this->belongsTo(Member::class,'associate_id','id');
    }
    public function savingAccount()
    {
        return $this->belongsTo(SavingAccount::class, 'type_id', 'id');
    }
    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class, 'type', 'type');
    }
    public function loanFromBank()
    {
        return $this->belongsTo(LoanFromBank::class, 'daybook_ref_id', 
        'daybook_ref_id') ;
    }
    public function loanEmi()
    {
        return $this->belongsTo(LoanEmi::class, 'type_transaction_id', 'id') ;
    }
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'type_transaction_id', 'id') ;
    }
    public function billExpense()
    {
        return $this->belongsTo(BillExpense::class, 'daybook_ref_id', 'daybook_refid') ;
    }
    public function bankingLedger()
    {
        return $this->belongsTo(BankingLedger::class, 'type_id', 'id') ;
    }



    /**
     * Retrieves the rent payment associated with this object.
     *
     * @return RentPayment The rent payment related to this object.
     */
    public function rentPayment()
    {
        return $this->belongsTo(RentPayment::class, 'type_transaction_id', 'id') ;
    }
    public function receivedVoucher()
    {
        return $this->belongsTo(ReceivedVoucher::class, 'type_transaction_id', 'id') ;
    }
   

    public function fundTransferBranchToHo()
    {
        return $this->belongsTo(SamraddhBank::class, 'transction_bank_to', 'id');
    }
    
    public function DemandAdvice()
    {
        return $this->belongsTo(DemandAdvice::class,'type_id')->where('is_deleted', 0);
    }
    
    public function salaryPayment()
    {
        return $this->belongsTo(EmployeeSalary::class,'type_transaction_id');
    }
    public function companybound()
    {
        return $this->belongsTo(CompanyBound::class,'daybook_ref_id');
    }
    public function companyboundtransactions()
    {
        return $this->belongsTo(CompanyBoundTransaction::class,'daybook_ref_id');
    }

    public function loans()
    {
        return $this->belongsTo(Memberloans::class,'type_id');
    }

    public function grouploans()
    {
        return $this->belongsTo(Grouploans::class,'type_id');
    }
    public function account_number() {
        return $this->belongsTo(SamraddhBankAccount::class,'bank_ac_id');
    }
    public function bankname() {
        return $this->belongsTo(SamraddhBank::class,'bank_id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
    public function tds_transfer()
    {
        return $this->belongsTo(TdsTransfer::class,'daybook_ref_id','daybook_ref_id');
    }
    public function gst_transfer()
    {
        return $this->belongsTo(GstTransfer::class,'daybook_ref_id','daybook_ref_id');
    }
    public function tds_payable()
    {
        return $this->belongsTo(TdsPayable::class,'daybook_ref_id','daybook_ref_id');
    }
    public function gst_payable()
    {
        return $this->belongsTo(GstPayable::class,'daybook_ref_id','daybook_ref_id');
    }

}