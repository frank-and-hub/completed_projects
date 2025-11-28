<?php

// EligibilityCheck.php
namespace App\Models\Scholarship\ApplicationForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_form_id',
        'section',
        'title',
        'description'
    ];

    public function applicationForm()
    {
        return $this->belongsTo(ApplicationForm::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
