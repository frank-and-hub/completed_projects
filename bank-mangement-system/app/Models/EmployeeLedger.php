<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLedger extends Model
{
    protected $table = "employee_ledgers";
    protected $guarded = [];  

    public function ledger_employee() {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    
    public function ledger_branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
    public function branch_detail()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
}