<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ECSTransaction extends Model
{
    protected $table = "ecs_transactions";
    protected $guarded = [];
    public function memberLoanDetails()
    {
        return $this->belongsTo(Memberloans::class,'loan_id');
    }
    public function memberGroupLoanDetails()
    {
        return $this->belongsTo(Grouploans::class, 'loan_id');
    }
    public function loanBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function CollectorAccount() {
        return $this->belongsTo(CollectorAccount::class,'loan_id','type_id');
    }
    public function loanTransactionLoanDaybook()
    {
        return $this->belongsTo(LoanDayBooks::class, 'loan_id', 'loan_id')->where('loan_sub_type', 3);
    }
    public function memberAssocite()
    {
        return $this->belongsTo(Member::class,'associate_id');
    }
}
