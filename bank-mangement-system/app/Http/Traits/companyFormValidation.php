<?php

namespace App\Http\Traits;
use App\Http\Requests\CompanyRequest;

use Carbon\Carbon;
use DB;

trait companyFormValidation
{
    // public function CompanyFormValidation(CompanyRequest $request)
    // {   
    //     $validated = $request->validated();
    //     dd($validated);
    //    return $validated;
    // }
    public function CompanyFormValidation($modelName,$array)
    {   
        DB::enableQueryLog();
		
        foreach($array as $key=>$val){
            $data = DB::table($modelName)->where($key,$val)->whereStatus('1')->get()->toArray();
        }
        if($data){
            return 1;
        }else{
            return 0;
        }
        \DB::getQueryLog(); // Show results of log
        
    }

  
  
}
