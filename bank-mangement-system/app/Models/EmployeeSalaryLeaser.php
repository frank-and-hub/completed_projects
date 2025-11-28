<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryLeaser extends Model
{
    protected $table = "employee_salary_leasers";
    protected $guarded = [];  

     public function salary_leaser_branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id')->withoutGlobalScopes();

    }
}