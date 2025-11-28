<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\ImageUpload;
class Employee extends Model
{
    protected $table = "employees";
    protected $guarded = [];
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')->withoutGlobalScopes();
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
    public function oldDesignation()
    {
        return $this->belongsTo(Designation::class, 'old_designation_id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
    public function loan()
    {
        return $this->belongsTo(Memberloans::class, 'employee_code', 'emp_code')->where('status', '!=', 3);
    }
    public function getSsb()
    {
        return $this->belongsTo(SavingAccount::class, 'ssb_id', 'id');
    }
    public function getssbaccountnumber()
    {
        return $this->belongsTo(SavingAccount::class, 'ssb_id');
    }
    public function empApp()
    {
        return $this->belongsTo(EmployeeApplication::class, 'id', 'employee_id');
    }
    public function designations() {
        return $this->belongsTo(Designation::class,'designation_id','id');
    }
    public function getImagesAttribute(){
        $folderName = 'employee/' . $this->attributes['photo'];
        if (ImageUpload::fileExists($folderName) ) {
            $photo_url = ImageUpload::generatePreSignedUrl($folderName);
        } else {
            $photo_url = url('/') . '/asset/images/user.png';
        }
        return $photo_url;
    }
}