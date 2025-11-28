<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class LoanTenure extends Model

{

    protected $table = "loan_tenures";

    protected $guarded = [];

    public function loan_tenure_plan()
    {
        return $this->belongsTo(Loans::class,'loan_id','id');
    }
    
    public function fileCharge()
    {
        return $this->belongsTo(LoanCharge::class,'loan_id','loan_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class,'company_id');
    }



}