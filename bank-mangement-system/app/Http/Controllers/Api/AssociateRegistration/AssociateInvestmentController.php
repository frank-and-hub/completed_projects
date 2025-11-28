<?php
namespace App\Http\Controllers\Api\AssociateRegistration;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvestmentPlanRequest;
use App\Http\Requests\SsbExistRequest;
use App\Http\Resources\AssociateInvestmentResource;
use App\Models\Member;
use App\Models\PlanDenos;
use App\Models\SavingAccountBalannce;
use App\Models\SystemDefaultSettings;
use App\Models\Memberinvestments;

use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\MaturityCalculation;
use App\Interfaces\RepositoryInterface;
use App\Http\Resources\AssociateLoanResource;

class AssociateInvestmentController extends Controller
{
    // const INVESTMENT_STATIONARY_CHARGE = "investment_stationary_charges";
    // const MEMBERSHIP_CHARGE = "membership_charge";
    // const STATIONARY_CHARGE = "stationary_charge";

    const INVESTMENT_STATIONARY_CHARGE = "50";
    const MEMBERSHIP_CHARGE = "10";
    const STATIONARY_CHARGE = "90";

    protected $investmentService;
    protected $memberDetails;
    protected $associateDetails;
    protected $token;

    public function __construct(
        InvestmentService $investmentService,
        Request $request,RepositoryInterface $repository
    ) {
        $this->investmentService = $investmentService;
        $this->associateNo = $request->associate_no;
        $this->repository = $repository;
        
        $this->memberDetail = Member::where(
            "associate_no",
            $this->associateNo
        )
            ->where("associate_status", 1)
            ->where("is_block", 0)
            ->first(["id", "associate_app_status", "branch_id"]);
        $this->globaldate = checkMonthAvailability(
            date("d"),
            date("m"),
            date("Y"),
            $this->memberDetail->branch->state_id
        );
    }

    /**
     * Retrive the associate customer details based on provided customer id
     * @package App\Member Model
     * @param customer_id
     * @return AssociateInvestmentResource
     */

    public function customerDetails(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            "customer_id" => "required",
        ]);

        // If validation fails , return  a JSON response with a  validation errors
        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation error",
                    "errors" => $validator->errors(),
                ],
                422
            );
        }

        // Extract customer Id from the request
        $customerId = $request->input("customer_id");

        // Retrive detail from the investment Service
        $this->memberDetails = $this->investmentService
            ->getMember($customerId)
            ->select(
                "id",
                "member_id",
                "first_name",
                "last_name",
                "mobile_no",
                "father_husband",
                "branch_id",'address'
            )
            ->with(["branch:id,name,branch_code"])
            ->withCount("customerInvestment")
            ->withCount("memberCompanyRecord")
            ->first();

        $memberDetails = $this->memberDetails;
        // Assign the branch name to the memberDetails object for easy access

        // If the member details are not found , return the JSON response indicating 'Customer Not Found'.
        if (!$this->memberDetails) {
            return $this->investmentService->handleDataNotFound(
                "Record Not Found on Provided Customer Id"
            );
            return response()->json(
                [
                    "message" => "Customer not found.",
                ],
                404
            );
        }

        // Assign stationary charge to the memberDetails object for easy access

        $memberDetails->branchName = $this->memberDetails["branch"]->name;
     
        // Return the member details as a JSON response using the AssociateInvestmentResource
        return new AssociateInvestmentResource($this->memberDetails);
    }

    /**
     * Calculate Maturity Date based on provided tenure
     * @param tenure
     * @return string maturityDate
     */

    protected function maturityDate(Request $request)
    {
        $request->validate([
            "tenure" => "required",
            'plan_category_code' => 'required',
            'plan_sub_category_code' => 'required',
            'amount' => 'required',
            'rate' => 'required'
        ]);

        // Calculate maturity date and return
        $data = date(
            "d/m/Y",
            strtotime(
                "+ " . $request["tenure"]  . "months",
                strtotime($this->globaldate)
            )
        );
        
        if (!$data) {
            return $this->investmentService->handleDataNotFound(
                "Record Not Found"
            );
        }
        // Create a Object for the service MaturityCalculation
        $maturityCalculation =  new MaturityCalculation();
        // Calcualte Maturity Amount
        $maturityAmount = $maturityCalculation->calculateMaturityAmount($request);

        // Set actualResponse
        $actualResponse = ['date' => $data,'maturityAmount' => convertToDecimal($maturityAmount)];
        return new AssociateInvestmentResource($actualResponse);
    }

    /**
     * Retrive Nominee Details based on customer_id
     * @param Request $request
     * @return AssociateInvestmentResource
     */

    public function getNomineeDetails(Request $request)
    {
        // Extract customer_id from incoming request data
        $requestData = $request->only("customer_id");

        // Call the investment service to retrive nominee details.
        $nomineeDetail = $this->investmentService->getNomineeDetails(
            $requestData["customer_id"]
        );

        // If nomineeDetail is not found, return an error response indicating the record was not found for the provided customer ID.
        if (!$nomineeDetail) {
            return $this->investmentService->handleDataNotFound(
                "Record Not Found on Provided Customer Id"
            );
        }
        // Return $nomineeDetail
        return new AssociateInvestmentResource($nomineeDetail);
    }

    /**
     * Retrive all the relation
     * @package call a Investment Service
     * @return mixed
     */

    public function getRelation()
    {
        // Return all the relations
        $relations = $this->investmentService->getRelation();
        // If relations is empty then , return error resposne
        if (!$relations) {
            return $this->investmentService->handleDataNotFound(
                "Relation not Found"
            );
        }

        // If relations found then return the relation using  Resource
        return new AssociateInvestmentResource($relations);
    }

    /**
     * Retrive tenure based on provided data
     * @param plan_id
     * @method App\Models\PlanTenures
     * @return $mixed AssociateInvestmentResource
     */

    public function planTenure(Request $request)
    {
        // Validation using Form Request
        $validatedData = $this->investmentService->validatePlanTenureRequest(
            $request
        );
        //Retrive data from plan_tenures table
        $planTenures = $this->investmentService->fetchPlanTenure(
            $validatedData
        );

        $planTenures = $planTenures->whereColumn("tenure", "month_to")->get();
        
        // Retrive stationary charge from system_default_setting
        $shortNames = [
            self::STATIONARY_CHARGE,
            self::MEMBERSHIP_CHARGE,
            self::INVESTMENT_STATIONARY_CHARGE,
        ];
        // // Retrive default charges
        // $stationaryCharge = $this->investmentService->fetchCharge(
        //     $validatedData,
        //     $shortNames
        // );

        // check user exists in current company
        $newUser = checkNewUser($validatedData);

        // Get Customer Details 
        $customerDetail = $this->investmentService->getMemberId($request['customer_id'])->first()->savingAccount()->where('company_id',$request['company_id'])->first();
        //Retrive Associate SSb Details
        $ssbDetail =  $this->memberDetail->savingAccount()->where('company_id',$request['company_id'])->first();
        $ssbbalance = SavingAccountBalannce::whereSaving_account_id($ssbDetail->id)->value('totalBalance');
        // return data
        $data = [
            "planTenures" => $planTenures,
            // "mi_charge" => convertToDecimal(
            //     $stationaryCharge[self::MEMBERSHIP_CHARGE]->amount ?? 0
            // ),
            // "stn_charge" => convertToDecimal(
            //     $stationaryCharge[self::STATIONARY_CHARGE]->amount ?? 0
            // ),
            "newUser" => $newUser,
            // "investment_stationary_charge" => convertToDecimal(
            //     $stationaryCharge[self::INVESTMENT_STATIONARY_CHARGE]->amount ??
            //         0
            // ),
            'investment_stationary_charge' => self::INVESTMENT_STATIONARY_CHARGE,
            'mi_charge' => self::MEMBERSHIP_CHARGE,
            'stn_charge' => self::STATIONARY_CHARGE,
            "associate_ssb_ac" =>$ssbDetail->account_no,
            // "associate_ssb_balance" => $ssbDetail->savingAccountTransactionViewOrderBy->opening_balance,
            "associate_ssb_balance" => $ssbbalance,
            'customer_ssb' => $customerDetail->account_no??'',
            
        ];
        // return plan tenures
        return new AssociateInvestmentResource($data);
    }

    /**
     * Register an investment based on the provided request
     * Utilizes  Request Validation to validate attributes
     * @param Request $request
     * @return JsonResponse using AssociateInvestmentResource
     *
     */

    public function registerInvestment(InvestmentPlanRequest $request)
    {
        $request->merge([
            'company_id' => $request->company_id??1,
            'is_app' => $this->memberDetail->id,
            'created_at' =>$this->globaldate,
            'create_application_date' => $this->globaldate,
            'associatemid'=>$this->memberDetail->id 
        ]);
        $response = $this->investmentService->storeInvestment($request, $this->repository);
        // dd($response);
        
        $accountNumber = Memberinvestments::where('id',$response['insertedid'])->value('account_number');
      
       
        if($response['status'] && $response['status'] == "200") {
            return response()->json([
                'status' => '200',
                'message' => 'Account created successfully and your account number is '. $accountNumber,
                'insertId' => $response['insertedid'] 
            ],200);
        } else if(!($response['status'])) {
            return response()->json($response);
        }else if($response['status'] == "201") {
            return response()->json($response);
        }
        
    }

    /**
     * Retrive Kandhan Plan Details 
     * @param Request $request
     * @return Response
     */
    public function calculateAgeTenure(Request $request)
    {
        // Extract plan_id from the incoming request
        $plan_id = $request->plan_id;
        // Extract tenure from the incoming request
        $tenure = $request->tenure;
        // Calculate account opening date
        $account_open_date = date('Y-m-d',strtotime(convertDate($this->globaldate)));
        // Retrive kanyadhan plan details
        $investmentAmount = PlanDenos::whereHas('planTenure',function($q) use($tenure){
        $q->select('roi','plan_id')->where('tenure', $tenure);
        })->with(['planTenure'=>function($q) use($tenure){
        $q->select('roi','plan_id','compounding')->where('tenure', $tenure);
        }])->where('plan_id', $plan_id)->where('tenure', $tenure)->where('effective_from','<=',$account_open_date)->where(function($q) use($account_open_date){
        $q->where('effective_to','>=',$account_open_date)->orWhere('effective_to',NULL);
        })->select('denomination','plan_id')->first();
        // Validated incoming request
        $validatedData = $this->investmentService->validatePlanTenureRequest(
            $request
        );
        
        // Retrive stationary charge from system_default_setting
        $shortNames = [
            self::STATIONARY_CHARGE,
            self::MEMBERSHIP_CHARGE,
            self::INVESTMENT_STATIONARY_CHARGE,
        ];
        
        // Retrive default charges
        // $stationaryCharge = $this->investmentService->fetchCharge(
        //     $validatedData,
        //     $shortNames
        // );

         //Retrive Associate SSb Details
        $ssbDetail =  $this->memberDetail->savingAccount()->where('company_id',$request['company_id'])->first();
        // Check Member is Present in MemberComopany tabke or not
        $newUser = checkNewUser($validatedData);
        $data = [
            'denomination' => $investmentAmount->denomination,
            'plan_id' => $investmentAmount->plan_id,
            'roi'  => $investmentAmount['planTenure']->roi,
            'compounding'  => $investmentAmount['planTenure']->compounding,
            // "mi_charge" => convertToDecimal(
            //     $stationaryCharge[self::MEMBERSHIP_CHARGE]->amount ?? 0
            // ),
            // "stn_charge" => convertToDecimal(
            //     $stationaryCharge[self::STATIONARY_CHARGE]->amount ?? 0
            // ),
            "newUser" => $newUser,
            // "investment_stationary_charge" => convertToDecimal(
            //     $stationaryCharge[self::INVESTMENT_STATIONARY_CHARGE]->amount ??
            //         0
            // ),
            'investment_stationary_charge' => self::INVESTMENT_STATIONARY_CHARGE,
            'mi_charge' => self::MEMBERSHIP_CHARGE,
            'stn_charge' => self::STATIONARY_CHARGE,
            "associate_ssb_ac" =>$ssbDetail->account_no,
            "associate_ssb_balance" => $ssbDetail->balance,

        ];
        // return response
        return new AssociateInvestmentResource($data);

    }

    public function ssb_chk(SsbExistRequest $request){
        $customerId = $request->customer_id;
        $company_id = $request->company_id;
        $chk = Member::where('member_id', $customerId)->with(['savingAccount_Customnew' => function ($query) use ($company_id){
        $query->where('company_id', $company_id)->select('id','account_no','customer_id');
        }])
        ->select('member_id','id')->first()->toArray();
        $count = count($chk['saving_account__customnew']);
        if(isset($chk['saving_account__customnew'][0])){
            return response()->json([
                'status' => '201',
                'message' => 'SSB Account Already Exist!',
                'insertId' => ''
            ],200);
        } else {
            return response()->json([
                'status' => '200',
                'message' => 'SSB Account Not Found!',
                'insertId' => '' 
            ],200);
        }
    }
}
