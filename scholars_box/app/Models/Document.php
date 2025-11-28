<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'student_id',
        'document_type',
        'document',
        'other_document_name',
        'scholarship_id'
    ];


    public static $documentTypes = [
        
       

        '12th' => '12th Board Marksheet',
        '11th' => '11th Board Marksheet',
        '10th' => '10th Board Marksheet',
        '9th' => '9th Board Marksheet',
        '8th' => '8th Board Marksheet',
        '7th' => '7th Board Marksheet',
        '6th' => '6th Board Marksheet',
        '5th' => '5th Board Marksheet',
        '4th' => '4th Board Marksheet',
        '3rd' => '3rd Board Marksheet',
        '2nd' => '2nd Board Marksheet',
        '1st' => '1st Board Marksheet',
        'graduation' => 'Graduation Marksheet',
        'degree' => 'Graduation Degree Certificate',
        'post-graduation' => 'Post Graduation Marksheet',

        // 'other' => 'other'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
