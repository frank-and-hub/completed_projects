<?php

namespace App\Http\Traits;
use Auth;

trait BranchPermissionRoleWiseTrait{

    public function getDataRolewise($model)
    {   
        $authUser = Auth::user()->branch_type;
        if(!empty($authUser))
        {
            if($authUser == 'Z')
            {
                $compareColumn = 'zone_code';

            }
            else if($authUser == 'R')
            {
                $compareColumn = 'region_code';
                
            }
            else if($authUser == 'S')
            {
                $compareColumn = 'sector_code';
                
            }
            else if($authUser == 'B')
            {
                $compareColumn = 'branch_code';
               
                
            }
            $getDatId = $model::where('id',Auth::user()->branch_id)->first($compareColumn);
           
            $getDatIds = $model::where($compareColumn,$getDatId[$compareColumn])->pluck('id')->toArray();
            
            return $getDatIds;
        }   
    }
}