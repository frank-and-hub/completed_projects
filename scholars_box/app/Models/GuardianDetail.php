<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardianDetail extends Model
{
    public static $incomeTypes = [
        'thousands' => 'Thousands',
        'lakhs' => 'Lakhs'
    ];

    protected $fillable = [
        'student_id',
        'name',
        'relationship',
        'occupation',
        'phone_number',
        'number_of_siblings',
        'annual_income',
        'income_type'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
