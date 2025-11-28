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
use App\Http\Requests\AssociateBusinessCompareRequest;
use App\Services\InvestmentService;



class AssociateBusinessCompareReportController extends controller
{

   /**
   * @param AssociateBusinessCompareRequest  Validate requested parameters
   * @param associate_business_compare_report procedure that fetch data from database
   * @return AssociateLoanResource return data in json format
   * 
   */
  public function report(AssociateBusinessCompareRequest $request)
  {
    
      // Fetch all data from request
      $formData = $request->all();
      // convert dates into database format
      $current_startDate = date('Y-m-d',strtotime($formData['current_start_date']));
      $current_endDate = date('Y-m-d',strtotime($formData['current_end_date']));
      $compare_startDate = date('Y-m-d',strtotime($formData['compare_start_date']));
      $compare_endDate = date('Y-m-d',strtotime($formData['compare_end_date']));
      // Get Company Id
      $company =$formData['company_id'] ?? 0;
      // Get Branch Id
      $branch = $formData['branch_id'] ?? 0;
      // Get Associate Id
      $associate = $formData['associate_no'];

      $company_name = Companies::withoutGlobalScopes()->pluck('name','id');
      $company_name[0] = 'All Company';

      try{
          // Procedure that fetch data from database 
          $data = DB::select('call associate_business_compare_report(?,?,?,?,?,?,?,?,?)', [$current_startDate,$current_endDate,$compare_startDate,$compare_endDate,$company,$branch,$associate,1, 99999]);
          
          // If record not found 
          if(!$data)
          {
            // Create Object for the investmentService
            $investmentService = new InvestmentService();
            
            // Call handleDataNotFound function of the invesment Service
            return $investmentService->handleDataNotFound('Record Not Found');

          }
          // 'SSB_TCC' => $data[0]->tcc_ssb,
          // dd($data);
          $data2=[
            'Company' => $company_name[$request['company_id']],
            'DailyNI' => $data[0]->dnccac,
            'Daily_NCC' => $data[0]->dnccamt,
            'Daily _Renewal' => $data[0]->drenamt,
            'MonthlyNI' => $data[0]->mnccac,
            'Monthly_NCC' => $data[0]->mnccamt,
            'Monthly_Renewal' => $data[0]->mrenamt,
            'FDNI' => $data[0]->fnccac,
            'FD_NCC' => $data[0]->fnccamt,
            'NCC' => $data[0]->ncc_m,
            'TCC' => $data[0]->tcc_m,
            'SSB_NI' => $data[0]->sni,
            'SSB_NCC' => $data[0]->snccamt,
            'SSB_Renewal' => $data[0]->ssbren,
            'total_ncc' => $data[0]->ncc_ssb,
            'total_tcc' => $data[0]->tcc_ssb,
            'New_Loans (OTH)' => $data[0]->loan_ac_no,
            'Loan_Amount' => $data[0]->loan_amt,
            'Loan_Recovery' => $data[0]->loan_recv_amt,
            'New_Loan (LAD)' => $data[0]->lad_transfer_ac_no,
            'LAD_Amount' => $data[0]->lad_transfer_amount,
            'LAD_Recovery' => $data[0]->lad_rec_amount,
            'New_Members' => $data[0]->new_m,
            'New_Associates' => $data[0]->new_a,
            'sECOND_DailyNI' => $data[0]->c_dnccac,
            'Second_Daily NCC' => $data[0]->c_dnccamt,
            'Second_Daily Renewal' => $data[0]->c_drenamt,
            'Second_MonthlyNI' => $data[0]->c_mnccac,
            'Second_Monthly NCC' => $data[0]->c_mnccamt,
            'Second_Monthly Renewal' => $data[0]->c_mrenamt,
            'Second_FDNI' => $data[0]->c_fnccac,
            'Second_FDNCC' => $data[0]->c_fnccamt,
            'Second_NCC' => $data[0]->c_ncc_m,
            'Second_TCC' => $data[0]->c_tcc_m,
            'Second_SSB_NI' => $data[0]->c_sni,
            'Second_SSB NCC' => $data[0]->c_ncc_ssb,
            'Second_SSB Renewal' => $data[0]->c_ssbren,
            'Second_total NCC' => $data[0]->c_ncc_ssb,
            'Second_total TCC' => $data[0]->c_tcc_ssb,
            'Second_New Loans OTH' => $data[0]->c_loan_ac_no,
            'Second_Loan Amount' => $data[0]->c_loan_amt,
            'Second_Loan Recovery' => $data[0]->c_loan_recv_amt,
            'Second_New Loan LAD' => $data[0]->c_lad_transfer_ac_no,
            'Second_LADAmount' => $data[0]->c_lad_transfer_amount,
            'Second_LADRecovery' => $data[0]->c_lad_rec_amount,
            'Second_New Members' => $data[0]->c_new_m,
            'Second_New Associates' => $data[0]->c_new_a,
            'Difference_of_DailyNI' => $data[0]->diff_dnccac,
            'Difference_of_Daily NCC' => $data[0]->diff_dnccamt,
            'Difference_of_Daily Renewal' => $data[0]->diff_drenamt,
            'Difference_of_MonthlyNI' => $data[0]->diff_mnccac,
            'Difference_of_Monthly_NCC' => $data[0]->diff_mnccamt,
            'Difference_of_Monthly_Renewal' => $data[0]->diff_mrenamt,
            'Difference_of_FDNI' => $data[0]->diff_fnccac,
            'Difference_of_FDNCC' => $data[0]->diff_fnccamt,
            'Difference_of_NCC' => $data[0]->diff_ncc_m,
            'Difference_of_TCC' => $data[0]->diff_tcc_m,
            'Difference_of_SSB_NI' => $data[0]->diff_sni,
            'Difference_of_SSB_NCC' => $data[0]->diff_snccamt,
            'Difference_of_SSB_Renewal' => $data[0]->diff_ssbren,
            'Difference_of_total_NCC' => $data[0]->diff_ncc_ssb,
            'Difference_of_total_TCC' => $data[0]->diff_tcc_ssb,
            'Difference_of_New_Members' => $data[0]->diff_new_m,
            'Difference_of_New_Loans OTH' => $data[0]->diff_loan_ac_no,
            'Difference_of_Loan_Amount' => $data[0]->diff_loan_amt,
            'Difference_of_Loan_Recovery' => $data[0]->diff_loan_recv_amt,
            'Difference_of_New_LoanLAD' => $data[0]->diff_lad_transfer_ac_no,
            'Difference_of_LAD_Amount' => $data[0]->diff_lad_transfer_amount,
            'Difference_of_LAD_Recovery' => $data[0]->diff_lad_rec_amount,
            'Difference_of_New_Associates' => $data[0]->diff_new_a,
          ];

          $response = [
            "status"=> "success",
            "code"=> "200",
            "message"=> "Retrive Details",
            "data"=>[$data2]
          ];
          return response()->json($response);
          // return new AssociateLoanResource($data2);
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