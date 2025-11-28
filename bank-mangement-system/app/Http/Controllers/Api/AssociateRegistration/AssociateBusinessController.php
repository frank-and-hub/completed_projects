<?php
namespace App\Http\Controllers\Api\AssociateRegistration;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssociateLoanResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use DB;
use App\Http\Requests\AssociateBusinessRequest;
use App\Services\InvestmentService;
use App\Models\Companies;


class AssociateBusinessController extends Controller
{

  /**
   * @param AssociateBusinessRequest  validate requested parameters
   * @param associate_business_report procedure that fetch data from database
   * @param InvestmentService handle request if data not available
   * @return AssociateLoanResource return data in json format
   */
  public function report(AssociateBusinessRequest $request)
  {
    
      // Fetch All Data From Request  
      $formData = $request->all();
      // Convert date accordidng to database formate
      $startDate = date('Y-m-d',strtotime($formData['start_date']));
      $endDate = date('Y-m-d',strtotime($formData['end_date']));
      // dd($startDate,$endDate);
      // get Company Id
      $company =$formData['company_id'] ?? 0;
      
      $company_name = Companies::withoutGlobalScopes()->pluck('name','id');
      $company_name[0] = 'All Company';
      //Get Branch Id
      $branch = $formData['branch_id'] ?? 0;
      // Get Associate Id
      $associate = $formData['associate_no'];

      try{
          // This Procedure fetch all data from database
          $data = DB::select('call associate_business_report(?,?,?,?,?,?,?)', [$startDate, $endDate, $associate,$company, $branch, 1,99999]);
          
          // If record not found 
          if(!$data)
          {
             // Create Object for the investmentService
             $investmentService = new InvestmentService();
             
             // Call handleDataNotFound function of the invesment Service
             return $investmentService->handleDataNotFound('Record Not Found');

          }
      //    dd($data);
          $data1=[
            "Company" => $company_name[$company],
            "DailyNI" => $data[0]->dnccac,
            "Daily NCC" => $data[0]->dnccamt,
            "Daily Renewal" => $data[0]->drenamt,
            "MonthlyNI" => $data[0]->mnccac,
            "Monthly NCC" => $data[0]->mnccamt,
            "Monthly Renewal"=>$data[0]->mrenamt,
            "FDNI" => $data[0]->fnccac ,
            "FD NCC" => $data[0]->fnccamt,
            "NCC" => $data[0]->ncc_m,
            "TCC" => $data[0]->tcc_m,
            "SSBNI" => $data[0]->sni ,
            "SSB NCC" => $data[0]->snccamt,
            // "SSB TCC" => $data[0]->tcc_ssb,
            "SSB Renewal" => $data[0]->ssbren ,
            "total_ncc" => $data[0]->ncc_ssb ,
            "total_tcc" => $data[0]->tcc_ssb ,
            "New Loans (OTH)" => $data[0]->loan_ac_no ,
            "Loan Amount" => $data[0]->loan_amt ,
            "Loan Recovery" => $data[0]->loan_recv_amt ,
            "New Loan (LAD)" => $data[0]->lad_transfer_ac_no ,
            "LAD Amount" => $data[0]->lad_transfer_amount ,
            "LAD Recovery" => $data[0]->lad_rec_amount ,
            "Maturity Payment" => $data[0]->dem_amt ,
            "New Members" => $data[0]->new_m,
            "New Associates" => $data[0]->new_a ,
          ];
          
          
          // Return Data in json format
          $response = [
            "status"=> "success",
            "code"=> "200",
            "message"=> "Retrive Details",
            "data"=>[$data1]
          ];
          
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