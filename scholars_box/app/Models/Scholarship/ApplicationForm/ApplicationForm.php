<?php

// ApplicationForm.php
namespace App\Models\Scholarship\ApplicationForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationForm extends Model
{
    use HasFactory;
    protected $fillable = ['csr_id', 'name', 'description', 'is_published'];

    public function eligibilityChecks()
    {
        return $this->hasMany(EligibilityCheck::class);
    }
}


