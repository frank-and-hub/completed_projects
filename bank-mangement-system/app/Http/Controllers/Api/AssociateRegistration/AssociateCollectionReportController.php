<?php
namespace App\Http\Controllers\Api\AssociateRegistration;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssociateLoanResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use DB;
use App\Services\InvestmentService;
use App\Http\Requests\AssociateCollectionRequest;
use App\Models\CommissionLeaserMonthly;




class AssociateCollectionReportController extends Controller
{

  /**
   * @param AssociateCollectionRequest  validate requested parameters
   * @param associteCollectionList procedure that fetch data from database
   * @param InvestmentService handle request if data not available
   * @return AssociateLoanResource return data in json format
   */
  public function report(AssociateCollectionRequest $request)
  {

      // Get start date and convert it into desired format
      $startDate = convertDate($request->from_date);
      // Get end date and convert it into desired format
      $endDate = convertDate($request->to_date);
      // Get branch id of login associate
      $branch_id = $request->branch_id ?: 0;
      // Get associate code of logged in associate
      $associate_code = $request->associate_no ?: '';
      // Get company
      $companyId = $request->company_id ?: 0;

      // Page number that will be static because we get only 3 record maximum
      $pageNo = 0;
      // It will be static too 
      $perPageRecord = 10;

      
      // startDate date
      $toDay = date("d", strtotime($startDate));
      // StartDate month
      $toMonth = date("m", strtotime($startDate));
      // StartDate year
      $toYear = date("Y", strtotime($startDate));
      // EndDate date
      $fromDay = date("d", strtotime($endDate));
      // EndDate Month
      $fromMonth = date("m", strtotime($endDate));
      // EndDate Year
      $fromYear = date("Y", strtotime($endDate));
  

      try{
          // procedure that get data from database
          $data = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branch_id, $associate_code, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, $pageNo, $perPageRecord, $companyId]);

          // If record not found 
          // if(!$data)
          // {
          //   // Create Object for the investmentService
          //   $investmentService = new InvestmentService();
            
          //   // Call handleDataNotFound function of the invesment Service
          //   return $investmentService->handleDataNotFound('Record Not Found');

          // }
          // get data in format
          $rowReturn = [];
          foreach ($data as $row) {
              $val['company_id'] = $row->com_name ?? 'N/A';
              $val['branch_name'] = isset($row->branch_code) ? "$row->name ($row->branch_code)" : 'N/A';
              $val['total_collection'] = $row->totalsum;
              $rowReturn[] = $val;
          }
          
          // Return Data in json format
          return new AssociateLoanResource($rowReturn);
      }
      catch(\Exception $ex){
        // Handhle data if it will Throw any Error
        return response()->json([
          'data' => '',
          'status' => 'error',
          'message' => $ex->getMessage(),
          ]);
      }
      
  }

  public function getYear()
  {
    $data = \App\Models\CommissionLeaserMonthly::select('year')
            ->where('is_deleted', 0)
            ->where('year', '!=', 2022)
            ->distinct()
            ->get('year');

      return new AssociateLoanResource($data);
  }

  
  public function getMonth(Request $request)
  {
      $year = $request->year;
      $currentYear = date('Y');
      $currentMonth = date('n');

      $data = [];
/*
      if ($year == $currentYear) {
          // If it's the current year, fetch data up to the previous month
          $endMonth = $currentMonth - 1;
      } elseif ($year >= 2021 && $year < $currentYear) {
          // For years after 2020 and before the current year, fetch data for all months
          $endMonth = 12;
      } else {
          // For the year 2020, start from April (4th month) onwards
          $endMonth = 12;
      }

      for ($i = 1; $i <= $endMonth; $i++) {
          $data[] = [
              'month' => $i,
              'month_name' => date('F', strtotime("$year-$i-01"))
          ];
      }
*/
        $dates = CommissionLeaserMonthly::select('month', 'year')
                ->where('is_deleted', 0)
                ->distinct()
                ->where('year','!=',2022)
                ->get();
        foreach($dates as $k => $val){            
            $data[$k]= [
                'month' => $val->month,
                'month_name' => date('F', strtotime("$year-$val->month-01"))
            ];
        }
      $response = [
          "status" => "success",
          "code" => 200,
          "message" => "Retrieve Details",
          "data" => $data
      ];

      // Create a JSON response with the data
      return response()->json($response);
  }
  public function companyBranch(Request $request)
  {
      $company_id = $request->company_id;

      $data = \App\Models\CompanyBranch::whereHas('company', function ($query) {
              $query->where('status', 1);
          })
          ->where('company_id', $company_id)
          ->where('status', 1)
          ->with('branch:id,name,branch_code')
          ->get();
      
      $data1 = [];
      foreach($data as $row) {
        $val['id'] =$row->branch_id;
        $val['name'] =$row->branch->name;
        $val['branch_code'] =$row->branch->branch_code;
        $data1[]= $val;
      }

      return new AssociateLoanResource($data1);
  }



  
}