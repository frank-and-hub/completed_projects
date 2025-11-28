<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentDetail extends Model
{
    protected $fillable = [
        'student_id',
        'employment_type',
        'company_name',
        'designation',
        'joining_date',
        'end_date',
        'job_role',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
