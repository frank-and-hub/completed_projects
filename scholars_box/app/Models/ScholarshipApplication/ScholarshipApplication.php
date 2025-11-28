<?php

namespace App\Models\ScholarshipApplication;

use App\Models\Scholarship\Scholarship;
use App\Models\ScholarshipApplication\DocumentVerification;
use App\Models\ScholarshipApplication\ScholarshipApplicationDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scholarshipstatus;

class ScholarshipApplication extends Model
{
    use HasFactory;

    protected $table = 'scholarship_applications';

    protected $guarded = [];

    const STATUS_OPTIONS = [
        'application_submitted' => 'Application Submitted',
        'application_shortlisted' => 'Application Shortlisted',
        'application_rejected' => 'Application Rejected',
        'application_verified' => 'Application Verified',
        'application_disqualified' => 'Application Disqualified',
        'selected_for_telephonic_interview' => 'Selected for Telephonic Interview',
        'rejected_in_telephonic_interview' => 'Rejected in Telephonic Interview',
        'selected_in_f2f_interview' => 'Selected in F2F Interview',
        'rejected_in_f2f_interview' => 'Rejected in F2F Interview',
        'awarded' => 'Awarded',
        'under_review' => 'Under Review',
        'screening_staged' => 'Screening Stage',
        'verification_and_due_diligence_stage' => 'Verification and Due Diligence Stage',
        'selected' => 'Selected',
        'not_selected' => 'Not Selected',
        'waitlisted' => 'Waitlisted',
    ];

    const STATUS_APPLICATION = [
        'under_review' => 'Under Review',
        'screening_staged' => 'Screening Stage',
        'verification_and_due_diligence_stage' => 'Verification and Due Diligence Stage',
        'selected' => 'Selected',
        'not_selected' => 'Not Selected',
        'waitlisted' => 'Waitlisted',
       
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function documentVerifications()
    {
        return $this->hasMany(DocumentVerification::class, 'application_id');
    }

    public function scholarshipApplicationDetails()
    {
        return $this->hasOne(ScholarshipApplicationDetail::class, 'application_id');
    }
    
     public function applicationStatuus()
    {
        return $this->hasMany(Scholarshipstatus::class, 'scholarship_application_id');
    }
}
