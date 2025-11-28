<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssociateLoanResource;
use App\Models\PlanCategory;
use DB;


Class AssociatePlanCategoryController extends Controller
{
    public function getPlanCategory(Request $request)
    {
        $planCategory = PlanCategory::whereIn('code', ['D', 'M'])->get(['name', 'code AS ID']);
        // $rowReturn = [];
        // foreach($planCategory as $value)
        // {
        //     $val = [
        //         'name'=>$value->name,
        //         'id'=>$value->code
        //     ];
        //     $rowReturn [] = $val;
        // }
        
        
        return new AssociateLoanResource($planCategory);
        
    }
}
?>