<?php

namespace App\Models\Scholarship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipQuestionApplication extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
    public function scholarshipOptionsApplications()
    {
        return $this->hasMany(scholarshipOptionsApplications::class, 'scholarship_question_id');
    }
}