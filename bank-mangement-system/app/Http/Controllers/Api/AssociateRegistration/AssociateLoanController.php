<?php
namespace App\Http\Controllers\Api\AssociateRegistration;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use DB;
use App\Http\Resources\AssociateLoanResource;
use App\Models\Loans;
use App\Models\Memberloans;
use App\Models\Grouploans;
use App\Models\Companies;
use App\Http\Requests\AssociateLoanRequest;
use App\Http\Requests\AssociateLoanEmiPaymentRequest;
use App\Services\LoanEmiPaymentService;
use App\Services\Sms;

class AssociateLoanController extends controller
{
    protected $associateNo;
    protected $token;
    protected $memberDetail;
    protected $limitCheck;

    public function __construct(
        Request $request,
        LoanEmiPaymentService $loanPayment
    ) {

        $this->associateNo = $request->associate_no;
        $this->token = md5($this->associateNo);
        $this->memberDetail = \App\Models\Member::where(
            "associate_no",
            $this->associateNo
        )
            ->where("associate_status", 1)
            ->where("is_block", 0)
            ->with([
                "savingAccount_Custom3" => function ($q) use ($request) {
                    $q->when($request->company_id, function ($query) use ($request) {
                        $query->with(['savingAccountTransactionView' => function ($q) {
                            $q->select('saving_account_id', 'opening_balance', 'id');
                        }])
                            ->whereCompanyId($request->company_id);
                        //  ->where("transaction_status", "1");
                    });
                },
            ])
            ->first(["id", "associate_app_status", "branch_id", "mobile_no",'first_name','last_name']);
        $this->loanPaymentService = $loanPayment;

        $this->globaldate = checkMonthAvailability(
            date("d"),
            date("m"),
            date("Y"),
            33
        );
    }

    /**
     * Fetches laons based on provided loan type and comapny ID.
     * @param Request $request
     * @return AssociateLoanResource
     * @throws \Illuminate\Validation\ValidationException if validation false
     */

    public function fetchLoans(Request $request)
    {

        // Call fetchLoan for retrive loan plan  from the loanService
        $loans = $this->loanPaymentService->fetchLoans($request);

        // Return the fetched laons wrapped in AssociateLoanResource
        return new AssociateLoanResource($loans);
    }

    /**
     * Retrive a list of loan based on specified filters
     * @param Request $request
     * @return AssociateLoanResource
     */

    public function getLoanListing(Request $request)
    {
        // Retrive loan listing from the database based on provided loanType
        return $this->loanPaymentService->getLoanRecord($request, $this->memberDetail, 'associate_member_id');
    }

    /**
     * Validates the provides toekn against the instance token.
     * @param string $requestedToken . The token sent in the request
     * @return bool return true if the provided token match with instance token
     */

    public function isValidToken($requestedToken)
    {
        if ($this->token !== $requestedToken) {
            throw new \Exception("Invalid Token");
        }
    }

    /**
     * Determine the appropriate loan  model class based on provided loan type.
     * @param string $loanType . The loanType sent in the request.
     * @return string Returns the fully qualified class name of the corrosponding loan model.
     */

    public function getModelLoanType($loanType)
    {
        return $this->loanPaymentService->getModelLoanType($loanType);
    }

    /**
     * Retrive a detail of loan based on provided loan id and loan Type
     * @param string loanType and integer loanId. The loanType and loan Id sent in the request.
     * @return array Returns the details of the loan based on provided loan id
     * @return AssociateLoanResource.
     */

    public function fetchLoanDetails(AssociateLoanRequest $request)
    {

        try {

            //Extract loan_type,loan_id,token  from the request
            $requestData = $request->only(["loan_type", "token", "loan_ids"]);

            // Check token is valid or not
            $this->isValidToken($requestData["token"]);

            // Retrive a loan Account Details based on provided loanId and loanType.

            $loanDetails = $this->loanPaymentService->fetchLoanRecord(
                $requestData["loan_type"],
                $requestData["loan_ids"]
            );


            // Populates the 'data' array with loan details, associate's saving account number, and opening balance (defaulting to 0 if unavailable).

            $data = ['loanDetails' => $loanDetails, 'associateSavingNumber' => $this->memberDetail["savingAccount_Custom3"]->account_no, 'associateSavingBalance' => $this->memberDetail["savingAccount_Custom3"]['savingAccountTransactionView']->opening_balance ?? 0];
            // Return the  loan data wrapped in AssociateLoanResource
            return new AssociateLoanResource($data);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => "Failed to fetch loan details",
                    "error" => $e->getMessage() . " " . $e->getLine(),
                ],
                500
            );
        }
    }



    /**
     * Retrieve the loan EMI (Equated Monthly Installment) based on the provided loanId and loanType .
     * The specified amount will be deducted from the associated savings account based on provided company_id using SSb payment mode.
     * @param array data
     * @param string token
     * @param string loan_type
     * @param string company_id
     * @return AssociateLoanResource
     */

    public function depositeLoanEmi(AssociateLoanEmiPaymentRequest $request)
    {
        try {

            // Get only the specific key form the request data
            $requestData = $request->only(
                "token",
                "loan_type",
                "data",
                "company_id"
            );

            // Retrive the account number key from the request data
            $accountNumber = $requestData["data"];


            // Check if the token is valid or not (Now it is not required in other  controller we already using middleware)
            $this->isValidToken($requestData["token"]);

            // Check status of the saving account , is it active or not
            $status = $this->loanPaymentService->checkSavingAccountStatus($this->memberDetail);


            // Checks if the given object has a method "getStatusCode" and if the status code is equal to 404.
            // If both conditions are true, returns a new instance of the AssociateLoanResource class with the given status object.
            if (method_exists($status, 'getStatusCode') && $status->getStatusCode() === 404) {
                return new AssociateLoanResource($status);

            }

            // Decode the json string on to array 
            $accountNumberDetails = json_decode($accountNumber, true);
            // dd($accountNumberDetails);

            // Retrive model name based on the provided loan_type
            $model = $this->loanPaymentService->getModelLoanType($requestData["loan_type"]);

            // Deposite loan EMi
            $data = $this->loanPaymentService->depositeLoanEmi(
                $model,
                $accountNumberDetails,
                $this->globaldate,
                $this->memberDetail

            );

            // Return the response wrapped in resource
            return new AssociateLoanResource($data);

        } catch (\Exception $e) {

            // Return exception error
            return $this->errorResponse($e->getMessage(), 401);
        }
    }



    /**
     * Generate a JSON error response with a specific message and HTTP status code.
     *
     * @param string $message The error message to include in the response.
     * @param int $statusCode The HTTP status code for the error response.
     *
     * @return JsonResponse The JSON response containing the error information.
     */
    private function errorResponse($message, $statusCode)
    {
        return response()->json(
            [
                "status" => "error",
                "message" => $message,
                "data" => null,
            ],
            $statusCode
        );
    }

    /**
     * Get All Companies from the comapnies table
     * @return array 
     */

    public function companyDetails(Request $request)
    {
        try {
            //Extract loan_type,loan_id,token  from the request
            // 0 InActive and 1 For Active
            $requestData = $request->only(["token", 'type']);
            // Check token is valid or not
            $this->isValidToken($requestData["token"]);

            // Retrive Company Details 
            $company = Companies::select('id', 'name', 'short_name');
            $allCompany = $company->where('delete', '0')->get();
            //  ->when($requestData['type'] == 0,function($q){
            //     $q->withoutGlobalScopes();
            //  });
            $rowReturn = array();
            $loanType = array();
            $loanStatus = array();

            // Iterate each company
            foreach ($company as $key) {
                $companyDetails = new \stdClass;
                $val['id'] = $key->id;
                $val['name'] = $key->name;
                $val['short_name'] = $key->short_name;
                $rowReturn[] = $val;
            }

            // Set array loanType
            $loanType = [
                ['id' => 'L', 'name' => 'Loan'],
                ['id' => 'G', 'name' => 'Group']
            ];

            // Set array loanStatus
            $loanStatus = [
                ['id' => '0', 'name' => 'Pending'],
                ['id' => '1', 'name' => 'Approved'],
                ['id' => '4', 'name' => 'Due'],
                ['id' => '5', 'name' => 'Rejected'],
                ['id' => '6', 'name' => 'Hold'],
                ['id' => '8', 'name' => 'Cancel']
            ];

            $data = ['company_detail' => $allCompany, 'loan_type' => $loanType, 'loan_status' => $loanStatus];


            return new AssociateLoanResource($data);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    /**
     * Check saving accoutn balance on send otp
     * @param emi
     * 
     */

    public function sendOtp(Request $request)
    {
        $rules = [
            'token' => 'required',
            'totalAmount' => 'required|numeric|min:0'
        ];

        $message = [
            'token.required' => 'Token is Required',
            'totalAmount.required' => 'Total Amount is Required'
        ];

        $request->validate($rules, $message);

        $requestData = $request->only(["totalAmount", "token"]);
        $this->isValidToken($requestData["token"]);
        $checkStatus = $this->loanPaymentService->checkSavingAccountStatus($this->memberDetail);
        if (method_exists($checkStatus, 'getStatusCode') && $checkStatus->getStatusCode() === 400) {
            return new AssociateLoanResource($checkStatus);
        }
        $status = $this->loanPaymentService->getSavingBalance(
            $this->memberDetail["savingAccount_Custom3"],
            date("Y-m-d", strtotime($this->globaldate)),
            $requestData["totalAmount"]
        );
        if (method_exists($status, 'getStatusCode') && $status->getStatusCode() === 400) {

            return new AssociateLoanResource($status);
        }
        $otp = generateAppOtp();
        // $sms_text = 'Your OTP is ' . urlencode($otp) . ' From SBMFA cVrLIN9TEJ5';
        $sms_text = 'Your Associate APP Payment OTP is ' . urlencode($otp) . ' From Samraddh Bestwin Micro finance Association X9VGltZrsl0';
        // $template_id = "1207168578819998483";
        $template_id = "1207170289144902118";
        $contactNumber[] = $this->memberDetail->mobile_no;
        $sendToMember = new Sms();
        $sendToMember->sendSms($contactNumber, $sms_text, $template_id);
        $response = [
            'status' => 'success',
            'message' => 'Otp Send SuccessFully',
            'otp' => $otp
        ];
        return response()->json($response);


    }



    /**
     * Retrive a detail of loan based on provided loan id and loan Type
     * @param string loanType and integer loanId. The loanType and loan Id sent in the request.
     * @return array Returns the details of the loan based on provided loan id
     * @return AssociateLoanResource.
     */

    public function fetchLoanDetailsByAccountNumber(Request $request)
    {
        try {
            //Extract loan_type,loan_id,token  from the request
            $requestData = $request->only(["loan_type", "token", "account_numbers", "company_id"]);

            $accountNumbers = explode(',', $requestData['account_numbers']);
            // Check token is valid or not
            $this->isValidToken($requestData["token"]);

            // Retrive a loan Account Details based on provided loanId and loanType.
            $model = $this->loanPaymentService->getModelLoanType($requestData['loan_type']);

            // Retrive loan Record based on provided companyId
            $loanRecords = $this->loanPaymentService->getLoanListing($request)->where('status', 4);

            // Populate the account number condition for retrive data based on provided accountNumber

            $loanDetails = $loanRecords
                ->whereIn('account_number', explode(',', $requestData['account_numbers']))
                ->get();

            $loanDetailsArray = [];

            // Create an array of account numbers retrieved from the database
            $accountNumbersInDb = $loanDetails->pluck('account_number')->all();


            // Iterate through the requested account numbers
            $requestedAccountNumbers = explode(',', $requestData['account_numbers']);

            foreach ($requestedAccountNumbers as $requestedAccountNumber) {

                if (!in_array($requestedAccountNumber, $accountNumbersInDb)) {
                    $loanDetailsArray[] = ['message' => 'Invalid Account Number', 'account_number' => $requestedAccountNumber];
                }

            }






            // Return the  loan data wrapped in AssociateLoanResource
            return new AssociateLoanResource($loanDetailsArray);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => "Failed to fetch loan details",
                    "error" => $e->getMessage() . " " . $e->getLine(),
                ],
                500
            );
        }
    }



}
