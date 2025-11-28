<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class AllTransaction extends Model

{

    protected $table = "all_transaction";

    protected $guarded = [];

    public function branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function demand_advices_fresh_expenses() {
        return $this->belongsTo(DemandAdviceExpense::class,'type_id');
    }

}