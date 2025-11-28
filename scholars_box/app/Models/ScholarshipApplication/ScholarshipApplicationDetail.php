<?php

namespace App\Models\ScholarshipApplication;

use App\Models\ScholarshipApplication\ScholarshipApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipApplicationDetail extends Model
{
    use HasFactory;

    protected $table = 'scholarship_application_details';

    protected $fillable = [
        'application_id',
        'mother_tongue',
        'disability',
        'account_holder_name',
        'account_number',
        'bank_name',
        'branch',
        'ifsc',
    ];

    public function scholarshipApplication()
    {
        return $this->belongsTo(ScholarshipApplication::class, 'application_id');
    }
}
