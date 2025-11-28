<?php

namespace App\Models\Scholarship;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipLocation extends Model
{
    use HasFactory;

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class, 'csr_id');
    }
}
