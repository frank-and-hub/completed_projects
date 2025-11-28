<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = "vendors";
    protected $guarded = [];  
    public function company(){
        return $this->belongsTo(Companies::class);
    }
    public function vendorbill(){
        return $this->hasmany(VendorBill::class);
    }

    public function vendortransaction(){
        return $this->hasmany(VendorTransaction::class);
    }

    public function vendorcategory(){
        return $this->belongsto(VendorCategory::class);
    }

}
 