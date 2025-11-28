<?php

namespace App\Models\Scholarship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipContactPerson extends Model
{
    use HasFactory;

    protected $table = 'scholarship_contact_persons';

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class, 'csr_id');
    }
}
