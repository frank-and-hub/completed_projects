<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipOptionsApplications extends Model
{
    use HasFactory;
    protected $table = 'scholarship_options_applications';

    protected $fillable = ['scholarship_question_id', 'options','keys_name'];
    public function question()
    {
        return $this->belongsTo(ScholarshipQuestionApplication::class, 'scholarship_question_id');
    }
}
