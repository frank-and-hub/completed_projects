<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    protected $table = "employee_salary";
    protected $guarded = [];  

    public function salary_employee() {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    
    public function salary_branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function salaryBank() {
        return $this->belongsTo(SamraddhBank::class,'company_bank');
    }
    public function salaryBankAccount() {
        return $this->belongsTo(SamraddhBankAccount::class,'company_bank_ac');
    }
    public function salarySSB() {
        return $this->belongsTo(SavingAccount::class,'employee_ssb_id');
    }
    public function salaryCheque() {
        return $this->belongsTo(SamraddhCheque::class,'company_cheque_id');
    }

    public function salaryDesignationCustom() {
        return $this->belongsTo(Designation::class,'designation_id');
    }    
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
    public function advance() {
        return $this->hasMany(AdvancedTransaction::class,'type_id','employee_id');
    }
}
