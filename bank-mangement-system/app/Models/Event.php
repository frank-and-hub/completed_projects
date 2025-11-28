<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['state_id','title','start_date','end_date','month'];

    public function state() {
        return $this->belongsTo(States::class);
    }
}
