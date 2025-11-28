<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmountDistribution extends Model
{
    use HasFactory;

    protected $table = 'amount_distribution';
    protected $fillable = [
        'user_id',
        'scholarship_id',
        'amount',
        'account_number',
        'account_holder_name',
        'receipt',
   
    ];
    
    
     public function scholorshiop()
    {
        return $this->belongsTo(Scholarship::class,'scholarship_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');

    }
}
