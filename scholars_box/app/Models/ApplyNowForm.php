<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplyNowForm extends Model
{
    use HasFactory;
    protected $table = 'apply_now_forms';
    protected $guarded = [];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function scholarship()
    {
        return $this->belongsToMany(\App\Models\Scholarship\Scholarship::class);
    }
}
