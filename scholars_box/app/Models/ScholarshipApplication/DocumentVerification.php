<?php

namespace App\Models\ScholarshipApplication;

use App\Models\ScholarshipApplication\ScholarshipApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentVerification extends Model
{
    use HasFactory;

    protected $table = 'document_verifications';
    protected $fillable = [
        'application_id',
        'document_type',
        'document',
        'verified_by_id',
        'verified_on',
        'status',
    ];

    const STATUS_OPTIONS = [
        'verified' => 'Verified',
        'incorrect' => 'Incorrect',
        'missing' => 'Missing',
        'blurred' => 'Blurred',
        'ineligible' => 'Ineligible'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function scholarshipApplication()
    {
        return $this->belongsTo(ScholarshipApplication::class, 'application_id');
    }
}
