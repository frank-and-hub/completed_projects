<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillExpense extends Model
{

    protected $table = 'bill_expenses';
    protected $guarded = []; 

    public function expenses()
    {
        return $this->belongsTo(Expense::class,'bill_no','bill_no');
    } 

    public function getBranchCustom()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function getChequeCustom()
    {
        return $this->belongsTo(SamraddhCheque::class,'cheque_id');
    }
    public function companyName(){
        return $this->belongsTo(Companies::class,'company_id');
    }
}
