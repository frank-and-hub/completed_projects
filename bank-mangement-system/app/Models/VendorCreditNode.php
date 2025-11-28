<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorCreditNode extends Model
{
    protected $table = "vendor_credit_node";
    protected $guarded = [];  

    
    public function bill_detail()
    {
        return $this->belongsTo(VendorBill::class,'bill_id');
    }

    public function vendor_detail()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

}
 