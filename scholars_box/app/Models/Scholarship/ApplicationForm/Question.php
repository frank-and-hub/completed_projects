<?php

// Question.php
namespace App\Models\Scholarship\ApplicationForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'eligibility_check_id',
        'question_type',
        'question_text',
        'is_required'
    ];

    const QUESTION_TYPES = ['Multiple Choice', 'Checkboxes', 'Dropdown', 'Short answer', 'Paragraph', 'Document Upload'];

    public function eligibilityCheck()
    {
        return $this->belongsTo(EligibilityCheck::class);
    }
}
