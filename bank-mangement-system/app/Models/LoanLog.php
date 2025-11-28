<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanLog extends Model
{
    protected $table = 'loan_logs';
    protected $guarded = [];
    
    public function loanCategory()
    {
        return $this->belongsTo(Loans::class,'loan_category', 'id');
    }
    public function scopeLogs($query,$loanType,$loanId)
    {
        return $query->where('loan_type', $loanType)->where('loanId', $loanId);
    }
}
