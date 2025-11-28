<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class LoanEmisNew1 extends Model

{

    protected $table = "emiloan";

    protected $guarded = [];

  public function loanEmiDetails()
  {
      return $this->belongsTo(LoanDayBooks::class,'emi_id');
  }
  public function loanDetails()
    {
    	return $this->belongsTo('App\Models\Memberloans','loan_id','id');
    }

}