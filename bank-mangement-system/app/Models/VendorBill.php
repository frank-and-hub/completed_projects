<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorBill extends Model
{
    protected $table = "vendor_bills";
    protected $guarded = [];  

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vendorBranchDetail()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function vendorAccountHeads()
    {
        return $this->belongsTo(AccountHeads::class, 'id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class);
    }
}
 