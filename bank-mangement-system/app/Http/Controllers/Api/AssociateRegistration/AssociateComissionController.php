<?php
namespace App\Http\Controllers\Api\AssociateRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssociateLoanResource;
use App\Models\Companies;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\AssociateComissionRequest;
use App\Services\InvestmentService;



class AssociateComissionController extends controller
{

   /**
   * @param  AssociateComissionRequest Validate requested parameters
   * @param  InvestmentService Handle request if data not found
   * @return AssociateLoanResource return data in json format
   * 
   */
  public function report(AssociateComissionRequest $request)
  {
    
      //Fetch all data from request
      $formData = $request->all();
      // Get Month
      $month = $formData['month'];
      // Get year
      $year = $formData['year'];
      // Get Company Id
      $company =$formData['company_id'] ?? 0;
      // Get associate code 
      $associateNo  = $formData['associate_no'];

      try{
          $data = DB::select('call associate_comission_report(?,?,?,?)', [$company,$month,$year,$associateNo]);
          // If record not found 
          if(!$data)
          {
            // Create Object for the investmentService
            $investmentService = new InvestmentService();
            
            // Call handleDataNotFound function of the invesment Service
            return $investmentService->handleDataNotFound('Record Not Found');

          }
          // dd($data);
          $data1 =[
            "member_id" => $data[0]->member_id ?? "",
            "total_collection" => $data[0]->total_collection ?? '0',
            "collection_qualifying_amount" => $data[0]->collection_qualifying_amount ?? '0',
            "total_commission_amount" => $data[0]->total_commission_amount ?? '0',
            "tds_amount" => $data[0]->tds_amount ?? '0',
            "net_amount" => $data[0]->net_amount ?? '0',
            "fuel_charge" => $data[0]->fuel_charge ?? '0',
          ];

          $response = [
            "status"=> "success",
            "code"=> "200",
            "message"=> "Retrive Details",
            "data"=>[$data1]
          ];
          
          // Return Data in Json Formate
          return response()->json($response);
          // return new AssociateLoanResource($data1);
      }
      catch(\Exception $ex){
          return response()->json([
            'data' => '',
            'status' => 'error',
            'message' => $ex->getMessage(),
            ]);
      }
      
  }


}