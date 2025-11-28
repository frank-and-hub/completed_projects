<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Microsite extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $table = 'microsites';


    public function company(){
        return $this->hasOne(User::class,'id','company_id');
    }
    
}
