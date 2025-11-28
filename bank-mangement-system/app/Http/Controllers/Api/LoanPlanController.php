<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\InvestmentService;
use App\Http\Resources\AssociateLoanResource;
use App\Models\Loans;
use Validator;

Class LoanPlanController extends Controller
{
    public function planListing(Request $request)
    {
        // if the request has associate_no value then assigned the value to associateNo variable otherwise assigned empty value
        $associateNo = $request->input('associate_no','');

        // if the request has loan_type value then assigned the value to loanType variable otherwise assigned empty value
        $loanType = $request->input('loan_type','');

        $validator = Validator::make($request->all(),[
            'loan_type'=>'required',
        ],[
            'required'=>'The :attribute field is required.'
        ]);

        if($validator->fails())
        {
            return response()->json(['error'=>$validator->errors()],400);
        }

        try
        {
            $plans = Loans::has('company')->where('loan_type',$loanType)->get();
            
            return new AssociateLoanResource($plans);

        }
        catch(\Exception $ex)
        {
            return response()->json([
                'Line' => $ex->getLine(),
                'message'=>$ex->getMessage(),
            ]);
        }

    }
}
?>