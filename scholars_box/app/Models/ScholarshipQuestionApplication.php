<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ScholarshipOptionsApplications;

class ScholarshipQuestionApplication extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'scholarship_question_applications';

    public function options()
    {
        return $this->hasMany(ScholarshipOptionsApplications::class, 'scholarship_question_id');
    }
}
