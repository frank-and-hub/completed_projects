<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class AllHeadTransactionNew extends Model

{

protected $table = "all_head_transaction_new";

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
        return $this->belongsTo(AccountHeads::class,'head_id');
    }

    
   

}