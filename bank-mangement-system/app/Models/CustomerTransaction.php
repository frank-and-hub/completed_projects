<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class CustomerTransaction extends Model

{

    protected $table = "customer_transaction";

    protected $guarded = [];
    public function branch_detail()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function bill_detail()
    {
        return $this->belongsTo(VendorBill::class,'vendor_bill_id');
    }

    public function vendor_detail()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

}