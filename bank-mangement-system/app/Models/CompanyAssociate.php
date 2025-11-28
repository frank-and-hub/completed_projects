<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAssociate extends Model
{
    protected $table = "company_associate_setting";
    protected $guarded = []; 

    public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }
}
