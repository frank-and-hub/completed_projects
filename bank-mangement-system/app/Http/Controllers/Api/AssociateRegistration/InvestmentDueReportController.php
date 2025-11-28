<?php
namespace App\Http\Controllers\Api\AssociateRegistration;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssociateLoanResource;
use App\Models\Companies;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Services\InvestmentService;
use App\Models\Memberinvestments;
use App\Models\Member;
use Carbon;
use App\Http\Controllers\CommonController\Investment\InvestmentReportController;
class InvestmentDueReportController extends controller
{
    /**
     * @param InvestmentService Handle request if data not available
     * @param getDailyDepositeRecord Function where query will be Execute
     * @param listingData Get Only Desired data from this function 
     * @return AssociateLoanResource Return data in json format
     */
    public function report(Request $request)    
    {
        $validator = Validator::make($request->all(),[
            'plan_category'=>'required'
        ],[
            'required'=>'This :attribute field is required'
        ]);

        if($validator->fails())
        {
            return response()->json(['error'=>$validator->errors()],400);
        }
        
        // plan id from plans table
        $planId = $request->input("plan_id");
        // plan category whether its daily or monthly
        $planCategory = $request->input("plan_category");
        // Get associated no.
        $associateNo = $request->input("associate_no");
        // Get Account No.
        $accountNo = $request->input("account_no");
        // Get Customer Id
        $customerId = $request->input("customer_id");
        // Get page no. If null start with 0
        $page = $request->input("page_no", "1");
        // Limit Per page
        $limit = 20;
        // Start
        $start = ($page-1) * $limit;

        // $request->validate([
        //     'plan_category' => 'required'
        // ]);
        // Get all data in one query from all the tables
        $dailyDepositeRecord = $this->getDailyDepositeRecord(
            $planCategory,
            $associateNo,
            $planId,
            $accountNo,
            $customerId,
            $start,
            $limit
        );

        
        // Check if the daily deposit record is empty.
        // If it is empty, return a new investmentService with the given data.
        
        // If record not found 
        if(!$dailyDepositeRecord)
        {
            // Create Object for the investmentService
            $investmentService = new InvestmentService();
            
            // Call handleDataNotFound function of the invesment Service
          return $investmentService->handleDataNotFound('Record Not Found');

        }
        // Get Only desired data
        $data = $this->listingData($dailyDepositeRecord[0]);

        // Return all the data in json format
        return response()->json([
            'Status' => 'success',
            'Message' => "Retrive Details",
            'Data' => $data,
            'Total Records'=>$dailyDepositeRecord[1],
            'Length' => $limit,
            'Page' => $page
         ],200);
        // return new AssociateLoanResource($data);
    }

    // Get all data in one query from all the tables
    private function getDailyDepositeRecord($planCategory,$associateNo,$planId,$accountNo,$customerId,$start,$limit) 
    {
        // $memberInvestmentDetails = Memberinvestments::has("company")
        //     ->with(["plan"])
        //     ->whereHas("associateMember", function ($query) use ($associateNo) {
        //         // Get data according to given associate no.
        //         $query->where("associate_no", $associateNo);
        //     })
        //     ->whereHas("getPlanCustom", function ($q) use ($planCategory) {
        //         // Get data according to plan category 
        //         $q->where("plan_category_code", $planCategory);
        //     })
        //     ->when($planId, function ($q, $planId) {
        //         // when the planId is not empty then fetching the data as per the planId
        //         $q->where("plan_id", $planId);
        //     })
        //     ->when($accountNo, function ($q, $accountNo) {
        //         // when the accountNo is not empty then fetching the data as per the accountNo
        //         $q->where("account_number", $accountNo);
        //     })
        //     ->when($customerId, function ($q, $customerId) {
        //         // when the customerId is not empty then fetching the data as per the customerId
        //         $q->whereHas("member", function ($q) use ($customerId) {
        //             $q->where("member_id", $customerId);
        //         });
        //     })
        //     ->where("is_mature", 1)
        //     ->where("is_deleted", 0);


        $memberInvestmentDetails = Memberinvestments::select('id', 'created_at', 'account_number', 'deposite_amount', 'member_id', 'plan_id', 'associate_id', 'branch_id', 'tenure', 'customer_id', 'company_id')
                    ->whereHas('company')   
                    ->with(['plan' => function ($q) {
                        $q->select('id', 'name', 'plan_category_code');
                    }])
                    ->with(['member' => function ($q) {
                        $q->select('id', 'first_name', 'last_name', 'member_id', 'mobile_no');
                    }])
                    ->with('memberCompany:id,member_id')
                    ->with('company:id,name')
                    ->with(['associateMember' => function ($q) {
                        $q->select('id', 'first_name', 'last_name', 'associate_no');
                    }])
                    ->with(['branch' => function ($q) {
                        $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }])->with(['investmentBalance' => function ($q) {
                        $q->select('totalBalance', 'account_number');
                    }])
                    ->where('is_deleted', 0)->where('is_mature', 1)
                    ->when($planId != '',function($q) use($planId){
                        $q->where('plan_id',$planId);
                    })
                    ->when($accountNo !='',function($q) use($accountNo){
                        $q->where('account_number',$accountNo);
                    })
                    
                    ->whereHas("getPlanCustom", function ($q) use ($planCategory) {
                        // Get data according to plan category 
                        $q->where("plan_category_code", $planCategory);
                    })
                    ->whereHas('associateMember', function ($query) use ($associateNo) {

                        $query->where('associate_no',$associateNo);
                    });
                    ;
                    if($customerId !='')
                    {
                        $memberInvestmentDetails = $memberInvestmentDetails->whereHas('member',function($q) use ($customerId){
                                $q->where('member_id',$customerId);
                            });

                    }
                                    
                $currentDate = Carbon\Carbon::now();
                $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));
                //// marurity date ciondition 
                $memberInvestmentDetails = $memberInvestmentDetails->whereDate('maturity_date', '>=', $currentDate)->whereDate('created_at', '!=', $currentDate);



            $totalRecords = $memberInvestmentDetails->count('id');
            $memberInvestmentDetails = $memberInvestmentDetails->offset($start)->limit($limit)->get();
            return [$memberInvestmentDetails,$totalRecords];

    }
    private function listingData($data)
    {
        $rowReturn = [];  // Initialize an array to store the result
        $sno = 1;  // Initialize a counter for serial numbers
        $currentDate = Carbon\Carbon::now();
        $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));
        // Loop through each item in the data array
        foreach ($data as $row) {
            // Get Emi due an due payment record using InvestmentReportController Controller from::getRecords function(public static function)
            $record = InvestmentReportController::getRecords($row, $currentDate);
    
            // Create an array to store the data for this row
            $val = [
                 // Increment the serial number
                'sno' => $sno++,  
                // Fetch Branch Name
                'branch_name' => "{$row['branch']->name} - {$row['branch']->branch_code}",
                // Fetch Customer id
                'customer_id' => $row['member']->first_name ? $row['member']->member_id : 'N/A',
                // Get name of customer
                'name' => $row['member']->first_name ? "{$row['member']->first_name} {$row['member']->last_name}" : 'N/A',
                // Get Mobile Number of customer
                'mobile_no' => $row['member']->mobile_no ?? 'N/A',
                // Get Account No.
                'account_no' => $row->account_number,
                // Get tenure
                'tenure' => $row['plan']->plan_category_code == 'S' ? 'N/A' : number_format($row->tenure, 2, '.', '') . ' Year',
                // Get plan name
                'plan_name' => "{$row['plan']->name} - {$row['tenure']} Year",
                // Get account Opening date
                'opening_date' => date('d/m/Y', strtotime($row['created_at'])),
                // Get Deno amount
                'deno_amount' => $row->deposite_amount,
                // Check for pending EMI or default to 0
                'due_emi' => $record['pendingEmi'] ? number_format($record['pendingEmi'], 2, '.', ''): 0,
                // Check for pending EMI amount or default to 0
                'due_emi_amount' => $record['pendingEmiAMount'] ?? 0, 
            ];
    
            // Add the row data to the result array
            $rowReturn[] = $val;
        }
            // Return the array of formatted data
        return $rowReturn;
    }
    
}