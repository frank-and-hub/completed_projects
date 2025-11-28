<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanEmisNew extends Model
{
  protected $table = "emiloan";
  protected $guarded = [];
  public function loanEmiDetails()
  {
    return $this->belongsTo(LoanDayBooks::class, 'emi_id');
  }
  public function loanDetails()
  {
    return $this->belongsTo('App\Models\Memberloans', 'loan_id', 'id');
  }
  public function loanDetailsg()
  {
    return $this->belongsTo('App\Models\Grouploans', 'loan_id', 'id');
  }
  public function loans()
  {
    return $this->belongsTo('App\Models\Loans', 'loan_type', 'id');
  }
  public function loanType()
  {
    return $this->belongsTo('App\Models\Memberloans', 'loan_type', 'loan_type');
  }
}