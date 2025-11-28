<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Companies;

class CompanyController extends Controller
{
    public function __construct()
    {       
    }        
    public function companyname(){
        dd("hi");
        $company =  Companies::select('id', 'name' ,'short_name')->where('status','1')->where('delete','0')->get();
        $rowReturn = array();
        foreach ($company as $key) {
            $val['id'] = $key->id;
            $val['name'] = $key->name;
            $val['short_name'] = $key->short_name;
            $rowReturn[] = $val;
        }
        // $output = array($rowReturn);
        return $rowReturn;
    }
}
