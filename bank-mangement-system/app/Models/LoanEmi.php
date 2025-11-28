<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class LoanEmi extends Model

{

    protected $table = "loan_emis";

    protected $guarded = [];

    public function loanBank() {

        return $this->belongsTo(LoanFromBank::class,'loan_from_bank_id');

    }

    public function loanSamraddhBank() {

        return $this->belongsTo(SamraddhBank::class,'received_bank');

    }

    public function loanSamraddhBankAccount() {

        return $this->belongsTo(SamraddhBankAccount::class,'received_bank_account');

    }
    public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }

    


}