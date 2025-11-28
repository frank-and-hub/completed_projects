<?php

namespace App\Models\Scholarship;

use App\Models\Scholarship\ApplicationForm\ApplicationForm;
use App\Models\ScholarshipApplication\ScholarshipApplication;
use App\Models\ApplyNowForm;
use App\Models\AmountDistribution;
use App\Models\User;
use App\Models\SaveScholorship;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function ApplicationForm()
    {
        return $this->hasOne(ApplicationForm::class, 'csr_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contactPersons()
    {
        return $this->hasMany(ScholarshipContactPerson::class, 'csr_id');
    }

    public function locations()
    {
        return $this->hasMany(ScholarshipLocation::class, 'csr_id');
    }

    public function educations()
    {
        return $this->hasMany(ScholarshipEducation::class, 'csr_id');
    }

    public function scholarshipDetails()
    {
        return $this->hasMany(ScholarshipScholarshipDetail::class, 'csr_id');
    }

    public function scholarshipApplication()
    {
        return $this->hasMany(ScholarshipApplication::class, 'id');
    }
    
    public function scholarshipQuestionApplication()
    {
        return $this->hasMany(ScholarshipQuestionApplication::class, 'scholarship_id');
    }
    
    public function apply_now()
    {
        return $this->hasOne(ApplyNowForm::class, 'scholarship_id');
    }
    public function company()
    {
        return $this->hasOne(User::class, 'id','company_id');
    }
    
    
    public function distributionAmount(){
        return $this->hasMany(AmountDistribution::class, 'scholarship_id');
    }
    
    public function savescholorship()
    {
        return $this->hasMany(SaveScholorship::class, 'schId');
    }

}
