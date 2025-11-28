<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Scholarship\Scholarship;
class SaveScholorship extends Model
{
 
    protected $table = 'savescholorsips';

     public function savescholorship()
    {
        return $this->belongsTo(Scholarship::class, 'schId');
    }
   
}
