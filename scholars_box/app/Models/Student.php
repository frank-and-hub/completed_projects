<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    const RESERVATIONS = [
        'general' => 'General',
        'obc c' => 'OBC C',
        'obc nc' => 'OBC NC',
        'sc' => 'SC',
        'st' => 'ST',
        'other reservation' => 'Other Reservation'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function educationDetails()
    {
        return $this->hasMany(EducationDetail::class);
    }

    public function employmentDetails()
    {
        return $this->hasOne(EmploymentDetail::class);
    }

    public function guardianDetails()
    {
        return $this->hasOne(GuardianDetail::class);
    }

    public function addressDetails()
    {
        return $this->hasMany(AddressDetail::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function draft()
    {
        return $this->hasOne(Draft::class);
    }

  

    public function category()
    {
        return $this->category !== null && $this->category !== '';
    }    
  
    
}
