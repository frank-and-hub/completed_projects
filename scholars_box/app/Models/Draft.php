<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    
    protected $guarded = [];
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}