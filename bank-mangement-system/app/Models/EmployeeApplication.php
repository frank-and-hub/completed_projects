<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeApplication extends Model
{
    protected $table = "employee_application";
    protected $guarded = [];  

    public function employeeget() {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id')->withoutGlobalScopes();

       
    }
}