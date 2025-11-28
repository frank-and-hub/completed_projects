<?php

namespace App\Http\Controllers\Api\Epassbook;
//die('stop app');
use DB;
use URL;
use Session;
use Carbon;
use DateTime;
use Validator;
use App\Services\Sms;
use App\Models\Member;
use App\Models\Memberloans;
use App\Models\Grouploans;
use Illuminate\Http\Request;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccountTransactionView;
use App\Models\Memberinvestments;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use App\Models\Plans;
use App\Services\LoanEmiPaymentService;


class AccountListingApiController extends Controller
{

    public function __construct(LoanEmiPaymentService $loanPayment, Request $request)
    {
        $this->loanPaymentService = $loanPayment;
        $this->memberId = $request->member_id;
        // $this->token = md5($this->associateNo);
        $this->memberDetail = \App\Models\Member::where(
            "member_id",
            $this->memberId
        )
            ->where("is_block", 0)
            ->with([
                "savingAccount_Custom3" => function ($q) use ($request) {
                    $q->when($request->company_id, function ($query) use ($request) {
                        $query->with([
                            'savingAccountTransactionView' => function ($q) {
                                $q->select('saving_account_id', 'opening_balance', 'id');
                            }
                        ])
                            ->whereCompanyId($request->company_id)
                            ->where("transaction_status", "1");
                    });
                },
            ])
            ->first(["id", "branch_id"]);
    }

    /**
     * Investment , SSb Loan Account listin by memeber id .
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */

    // public function getAccountDetails(Request $request)
    // {
       
    //     $response = array();
    //     $currentDate = Carbon\Carbon::now();
    //     $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));

    //     $input = $request->all(); 
    //     if (isset($input["member_id"]) && $input["member_id"] != "" && isset($input["type"]) && $input["type"] != "") {



    //         // Get Member ID
    //         $member = Member::with(['savingAccount'])->select('id', 'member_id', 'company_id')->where('member_id', $input["member_id"])->get();
    //         $company_ssb_balance = [];
           
    //         foreach ($member[0]['savingAccount'] as $value) {
                
    //             $savingAccountDetails = SavingAccount::with('getSSBAccountBalance')->where('account_no', $value->account_no)->get()->toArray();
    //             // dd($savingAccountDetails[0]['get_s_s_b_account_balance']['totalBalance']);
    //             $company_ssb_balance[] = [
    //                 'company_id' => $value->company_id??0,
    //                 'account_number' => $savingAccountDetails[0]['get_s_s_b_account_balance']['account_no']??0,
    //                 'total' => $savingAccountDetails[0]['get_s_s_b_account_balance']['totalBalance']??0,
    //             ];
    //         }

          

    //         $balance = 0;
    //         if (isset($member['savingAccount']['savingAccountBalance'])) {
    //             $balance =  $member['savingAccount'][0]['savingAccountBalance']->sum('deposit') - $member['savingAccount'][0]['savingAccountBalance']->sum('withdrawal');
    //         }




    //         $balance  =  number_format((float) $balance, 2, '.', '');

    //         if (isset($member[0]['member_id'])) {

    //             $type = $input["type"];

    //             if ($type == "ssb") {


    //                 // Get SSB Account ID
    //                 $savingAccountDetails = SavingAccount::with('getSSBAccountBalance')->where('customer_id', $member[0]['id'])->get()->toArray();
    //                 // dd($savingAccountDetails);
    //                 if (isset($savingAccountDetails)) {

    //                     $arr = [];
    //                     foreach ($savingAccountDetails as $value) {

    //                         $deposit = SavingAccountTranscation::where("saving_account_id", $value['id'])->where("type", "2")->sum("deposit");
    //                         $memberInvestment = Memberinvestments::with('getPlanCustom')->where("account_number", $value['account_no'])->first()->toArray();
    //                         $companyData = array_combine(array_column($company_ssb_balance, 'company_id'), $company_ssb_balance);
    //                         $data = [
    //                             'account_number' => $value['account_no'],
    //                             'plan' => $memberInvestment['get_plan_custom']['name']??0,
    //                             'total_balance' => $value['get_s_s_b_account_balance']['totalBalance']??0,
    //                             'deposit' => $companyData[$value['company_id']]['total'] ?? 0,
    //                             // 'deposit' => $deposit ?? 0,
    //                             'deno_amount' => isset($memberInvestment->deposite_amount) ? number_format((float)$memberInvestment->deposite_amount, 2, '.', '') : 0,
    //                             'opening_date' => isset($memberInvestment['created_at']) ?  date("d-m-Y", strtotime($memberInvestment['created_at'])) : '',
    //                             'investment_id' => isset($memberInvestment) ? (string)$memberInvestment['id'] : '',
    //                             'company_id' => (string)$value['company_id'],
    //                             'maturity_date' => '',
    //                         ];
    //                         $arraynew = array_push($arr, $data);
    //                     }





    //                     //  $arr = array("account_number" => $savingAccountDetails->account_no,
    //                     //               "plan" => "Saving Account"
    //                     //              );

    //                     //total deposit



    //                     //  $arr["deposit"] = $balance;

    //                     // get Investment details
    //                     //  $memberInvestment = Memberinvestments::where("account_number",$savingAccountDetails->account_no)->first();

    //                     //  if(isset($memberInvestment->deposite_amount)){
    //                     //      $arr["deno_amount"] = number_format((float)$memberInvestment->deposite_amount, 2, '.', '');
    //                     //  } else {
    //                     //      $arr["deno_amount"] = 0;
    //                     //  }

    //                     //  if(isset($memberInvestment->created_at)){
    //                     //      $arr["opening_date"] = $newDate = date("d-m-Y", strtotime($memberInvestment->created_at));
    //                     //  } else {
    //                     //      $arr["opening_date"] = "";
    //                     //  }

    //                     //  if(isset($memberInvestment->id)){
    //                     //      $arr["investment_id"] = (string)$memberInvestment->id;
    //                     //  } else {
    //                     //      $arr["investment_id"] = "";
    //                     //  }

    //                     //  $arr["maturity_date"] = "";


    //                     $response["status"] = "Success";
    //                     $response["currentBalance"] = (string)$balance;
    //                     $response["code"] = 200;
    //                     $response["minAmount"] = '500';
    //                     $response["messages"] = "Data";
    //                     $response["data"] = $arr;
    //                 }
    //             }

    //             if ($type == "deposit") {


    //                 $investmentArr = array();
    //                 // get Investment details
    //                 $memberInvestment = Memberinvestments::
                    
    //                 with(['plan:id,name,plan_code,plan_category_code,plan_sub_category_code','memberCompany' => function ($q) {
    //                     $q->select('id', 'member_id')
    //                         ->with(['ssb_detail' => function ($q1) {
    //                             $q1->select('id', 'account_no', 'member_id', 'customer_id')
    //                                 ->with(['getSSBAccountBalance']);
    //                         }]);
    //                 }])
    //                 ->where("customer_id", $member[0]['id'])
    //                 ->whereHas('plan', function ($query) {
    //                     $query->where('plans.plan_category_code','!=','S');
    //                   })
    //                 ->where("is_mature", 1)
    //                 ->get(['id','account_number','deposite_amount','maturity_date','current_balance','company_id','plan_id','created_at','member_id','customer_id']);

    //                 $savingAccountDetails = SavingAccount::with('getSSBAccountBalance')->where('customer_id', $member[0]['id'])->pluck('company_id')->toArray();

    //                 // dd();plan_category_code
    //                 if (count($memberInvestment) > 0) {

    //                     $memberInvestment = $memberInvestment->toArray();
    //                     $ssbCompanyIdArray = array();
    //                     for ($w = 0; $w < count($memberInvestment); $w++) {                            
    //                             $arr = array(
    //                                 "investment_id" => (string)$memberInvestment[$w]["id"],
    //                                 "account_number" => $memberInvestment[$w]["account_number"],
    //                                 "deno_amount" => number_format((float)$memberInvestment[$w]["deposite_amount"], 2, '.', ''),
    //                                 "maturity_date" => $memberInvestment[$w]["maturity_date"],
    //                                 "deposit" => number_format((float)$memberInvestment[$w]["current_balance"], 2, '.', ''),
    //                             );

    //                             if ($memberInvestment[$w]["maturity_date"] != null) {
    //                                 $arr["maturity_date"] = date("d-m-Y", strtotime($memberInvestment[$w]["maturity_date"]));
    //                             }

    //                             if (isset($memberInvestment[$w]["created_at"])) {
    //                                 $arr["opening_date"] =  date("d-m-Y", strtotime($memberInvestment[$w]["created_at"]));
    //                             } else {
    //                                 $arr["opening_date"] = "";
    //                             }
    //                             $arr["plan"] = $memberInvestment[$w]['plan']['name'] ;
                                
                                            
    //                             $arr["is_transaction_available"] = ($memberInvestment[$w]['plan']['plan_category_code'] == 'F') ? 0 : (in_array($memberInvestment[$w]['company_id'], $savingAccountDetails) ? 1 : 0);

    //                             $arr['is_category'] = ($memberInvestment[$w]['plan']['plan_category_code'] == 'F' || $memberInvestment[$w]['plan']['plan_sub_category_code'] == 'I') ? 0 : 1 ;

                                

    //                             $companyIds =  $memberInvestment[$w]['company_id'];


    //                             //  $currentDate = Carbon\Carbon::now(); 
    //                             $record = $this->getRecords($memberInvestment[$w], $currentDate);
    //                             $pendingEmiAMount = 0;
    //                             if (isset($record['pendingEmiAMount'])) {
    //                                 $pendingEmiAMount = $record['pendingEmiAMount'];
    //                                 if ($record['pendingEmiAMount'] > 0) {
    //                                     $pendingEmiAMount = 0;
    //                                 }
    //                             }
    //                             $pendingEmiAMount = str_replace('-', '', $pendingEmiAMount);
    //                             $arr["due_amount"] = number_format((float)$pendingEmiAMount, 2, '.', '');

    //                              $companyData = array_combine(array_column($company_ssb_balance, 'company_id'), $company_ssb_balance);

    //                             // $arr["company_id"]   = $memberInvestment[$w]["company_id"] ?? 0;
    //                             // $arr["totalAmount"]   = $memberInvestment[$w]['member_company']['ssb_detail']['get_s_s_b_account_balance']['totalBalance'] ?? 0;
    //                             // $arr["accountNumber"] = $memberInvestment[$w]['member_company']['ssb_detail']['get_s_s_b_account_balance']['account_no'] ?? 0;


    //                             $arr["company_id"]   = $companyData[$companyIds]['company_id'] ?? 0;
    //                             $arr["totalAmount"]   = $companyData[$companyIds]['total'] ?? 0;
    //                             $arr["accountNumber"] = $companyData[$companyIds]['account_number'] ?? 0;

    //                             array_push($investmentArr, $arr);
                            
    //                     }
    //                 }

    //                 $response["status"] = "Success";
    //                 $response["currentBalance"] = (string)$balance;
    //                 $response["code"] = 200;
    //                 $response["minAmount"] = '500';
    //                 $response["messages"] = "Data";
    //                 $response["data"] = $investmentArr;
    //             }
    //             // dd( $company_ssb_balance);

    //             if ($type == "loan") {
    //                 $investmentArr = array();

    //                 $memberId = $member[0]['id'];
    //                 $memberloan = Memberloans::select('id', 'applicant_id', 'account_number', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'amount', 'status', 'loan_type', 'associate_member_id', 'branch_id', 'created_at', 'approved_date', 'emi_amount', 'transfer_amount', 'customer_id', 'company_id', 'is_deleted')
    //                     ->where("customer_id", $memberId)
    //                     ->with(['loan:id,name,loan_type', 'loanTransaction'])
    //                     ->with(['memberCompany' => function ($q) {
    //                         $q->select('id', 'member_id', 'customer_id', 'company_id')
    //                             ->with(['ssb_detail' => function ($q) {
    //                                 $q->select('id', 'account_no', 'member_id', 'customer_id')
    //                                     ->with(['getSSBAccountBalance']);
    //                             }]);
    //                     }])
    //                     ->where('status', '!=', 3)
    //                     // ->whereIn('status', [0, 1, 4])
    //                     ->where('is_deleted', 0);
    //                 // dd($memberloan->first(),$memberId);
    //                 $loant = 'L';
    //                 $memberloan = $memberloan->whereHas('loan', function ($query) use ($loant) {
    //                     $query->where('loans.loan_type', '=', $loant);
    //                 });
    //                 $memberloan = $memberloan->orderby('id', 'DESC')->get();


    //                 if (count($memberloan) > 0) {
    //                     $memberloan = $memberloan->toArray();

    //                     for ($w = 0; $w < count($memberloan); $w++) {

    //                         // dd($memberloan[$w]);
    //                         $arr["account_number"] = $memberloan[$w]["account_number"];
    //                         $arr["emi_amount"] = $memberloan[$w]["emi_amount"];
    //                         $arr["loan_id"] = $memberloan[$w]["id"];
    //                         $arr["plan_name"] = $memberloan[$w]['loan']['name'];
    //                         $arr["loan_status"] = (string)$memberloan[$w]["status"];
    //                         $arr["investment_id"] = (string)$memberloan[$w]["id"];
    //                         $arr["loan_amount"] = (string)number_format((float)$memberloan[$w]["transfer_amount"], 2, '.', '');
    //                         $arr["issue_date"] = (string)date("d-m-Y", strtotime($memberloan[$w]["approve_date"]));
    //                         $arr["instalment"] = (string)$memberloan[$w]["emi_period"];
    //                         switch ($memberloan[$w]["emi_option"]) {
    //                             case 1:
    //                                 $arr["mode"] = 'Monthly';
    //                                 break;
    //                             case 2:
    //                                 $arr["mode"] = 'Weekly';
    //                                 break;
    //                             case 3:
    //                                 $arr["mode"] = 'Daily';
    //                                 break;
    //                             default:
    //                                 $arr["mode"] = '';
    //                                 break;
    //                         }
    //                         $count = \App\Models\LoanDayBooks::where('account_number', $memberloan[$w]["account_number"])->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
    //                         $arr["emi_paid"] = $count;
    //                         $arr["is_running_loan"] = 0;
    //                         if ($memberloan[$w]["status"] == 4) {
    //                             $arr["is_running_loan"] = 1;
    //                         } else {
    //                             $arr["loan_amount"] = (string)number_format((float)$memberloan[$w]["amount"], 2, '.', '');
    //                             $arr["application_date"] = (string)date("d-m-Y", strtotime($memberloan[$w]["created_at"]));
    //                             $arr["account_number"] = 'N/A';
    //                         }

    //                         $arr["type"] = 'loan';
    //                         $arr["application_no"] = getApplicantid($memberloan[$w]["applicant_id"]);



    //                         $arr["totalAmount"]   = $memberloan[$w]['member_company']['ssb_detail']['get_s_s_b_account_balance']['totalBalance'] ?? 0;
    //                         $arr["accountNumber"] = $memberloan[$w]['member_company']['ssb_detail']['get_s_s_b_account_balance']['account_no'] ?? 0;


    //                         array_push($investmentArr, $arr);
    //                     }
    //                 }

    //                 $response["status"] = "Success";
    //                 $response["currentBalance"] = (string)$balance;
    //                 $response["code"] = 200;
    //                 $response["minAmount"] = '500';
    //                 $response["messages"] = "Data";
    //                 $response["data"] = $investmentArr;
    //             }



    //             if ($type == "group_loan") {

    //                 $investmentArr = array();

    //                 $grouploan = Grouploans::select('group_loan_common_id', 'id', 'applicant_id', 'account_number', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'amount', 'status', 'loan_type', 'associate_member_id', 'branch_id', 'created_at', 'approved_date', 'emi_amount', 'transfer_amount')
    //                     ->with(['loan' => function ($q) {
    //                         $q->select('id', 'name', 'loan_type');
    //                     }])
    //                     ->with(['loanMemberCompany' => function ($q) {
    //                         $q->select('id', 'member_id')
    //                             ->with(['ssb_detail' => function ($q1) {
    //                                 $q1->select('id', 'account_no', 'member_id', 'customer_id')
    //                                     ->with(['getSSBAccountBalance']);
    //                             }]);
    //                     }])
    //                     // ->whereIn('status', [0, 1, 4])
    //                     ->where('status', '!=', 3)
    //                     ->where("customer_id", $member[0]['id']);
    //                 $loant = 'G';
    //                 $grouploan = $grouploan->whereHas('loan', function ($query) use ($loant) {
    //                     $query->where('loans.loan_type', '=', $loant);
    //                 });
    //                 $grouploan = $grouploan->orderby('id', 'DESC')->get();

    //                 if (count($grouploan) > 0) {
    //                     $grouploan = $grouploan->toArray();
    //                     for ($w = 0; $w < count($grouploan); $w++) {
    //                         // echo '<pre>'; print_r($grouploan[$w]); echo '</pre>'; exit();
    //                         $arr["account_number"] = $grouploan[$w]["account_number"];
    //                         $arr["emi_amount"] = $grouploan[$w]["emi_amount"];
    //                         $arr["loan_id"] = $grouploan[$w]["id"];
    //                         $arr["plan_name"] = $grouploan[$w]['loan']['name'];
    //                         $arr["loan_status"] = (string)$grouploan[$w]["status"];
    //                         $arr["investment_id"] = (string)$grouploan[$w]["id"];
    //                         $arr["loan_amount"] = (string)number_format((float)$grouploan[$w]["transfer_amount"], 2, '.', '');
    //                         $arr["issue_date"] = (string)date("d-m-Y", strtotime($grouploan[$w]["approve_date"]));
    //                         $arr["instalment"] = (string)$grouploan[$w]["emi_period"];
    //                         $arr["mode"] = '';
    //                         if ($grouploan[$w]["emi_option"] == 1) {
    //                             $arr["mode"] = 'Monthly';
    //                         }
    //                         if ($grouploan[$w]["emi_option"] == 2) {
    //                             $arr["mode"] = 'Weekly';
    //                         }
    //                         if ($grouploan[$w]["emi_option"] == 3) {
    //                             $arr["mode"] = 'Daily';
    //                         }
    //                         $count = \App\Models\LoanDayBooks::where('account_number', $grouploan[$w]["account_number"])->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
    //                         $arr["emi_paid"] = $count;
    //                         $arr["is_running_loan"] = 0;
    //                         if ($grouploan[$w]["status"] == 4) {
    //                             $arr["is_running_loan"] = 1;
    //                         } else {
    //                             $arr["loan_amount"] = (string)number_format((float)$grouploan[$w]["amount"], 2, '.', '');;
    //                             $arr["application_date"] = (string)date("d-m-Y", strtotime($grouploan[$w]["created_at"]));
    //                             $arr["account_number"] = 'N/A';
    //                         }
    //                         $arr["application_no"] = $grouploan[$w]["group_loan_common_id"];
    //                         $arr["type"] = 'group_loan';

    //                         $arr["totalAmount"]   = $grouploan[$w]['loan_member_company']['ssb_detail']['get_s_s_b_account_balance']['totalBalance'] ?? 0;
    //                         $arr["accountNumber"] = $grouploan[$w]['loan_member_company']['ssb_detail']['get_s_s_b_account_balance']['account_no'] ?? 0;


    //                         array_push($investmentArr, $arr);
    //                     }
    //                 }

    //                 $response["status"] = "Success";
    //                 $response["currentBalance"] = (string)$balance;
    //                 $response["code"] = 200;
    //                 $response["minAmount"] = '500';
    //                 $response["messages"] = "Data";
    //                 $response["data"] = $investmentArr;
    //             }
    //         } else {
    //             $response["status"] = "Error";
    //             $response["code"] = 201;
    //             $response["minAmount"] = '500';
    //             $response["messages"] = "Enter Valid Customer Id";
    //             $response["data"] = array();
    //         }
    //     } else {
    //         $response["status"] = "Error";
    //         $response["code"] = 201;
    //         $response["minAmount"] = '500';
    //         $response["messages"] = "Input parameter missing";
    //         $response["data"] = array();
    //     }

    //     return response()->json($response);
    // }

    public function getAccountDetails(Request $request)
    {
        $response = array();
        $currentDate = Carbon\Carbon::now();
        $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));
        $input = $request->all();
        if (isset($input["member_id"]) && $input["member_id"] != "" && isset($input["type"]) && $input["type"] != "") {
            // Get Member ID
            $member = Member::with(['savingAccount'])->select('id', 'member_id', 'company_id')->where('member_id', $input["member_id"])->get();
            $company_ssb_balance = [];
            foreach ($member[0]['savingAccount'] as $value) {
                $savingAccountDetails = SavingAccount::whereHas('company')->with('getSSBAccountBalance')->where('account_no', $value->account_no)->get()->toArray();
                // dd($savingAccountDetails[0]['get_s_s_b_account_balance']['totalBalance']);
                $company_ssb_balance[] = [
                    'company_id' => $value->company_id ?? 0,
                    'account_number' => $savingAccountDetails[0]['get_s_s_b_account_balance']['account_no'] ?? 0,
                    'total' => $savingAccountDetails[0]['get_s_s_b_account_balance']['totalBalance'] ?? 0,
                ];
            }
            $balance = 0;
            if (isset($member['savingAccount']['savingAccountBalance'])) {
                $balance = $member['savingAccount'][0]['savingAccountBalance']->sum('deposit') - $member['savingAccount'][0]['savingAccountBalance']->sum('withdrawal');
            }
            $balance = number_format((float) $balance, 2, '.', '');
            if (isset($member[0]['member_id'])) {
                $type = $input["type"];
                if ($type == "ssb") {
                    // Get SSB Account ID
                    $savingAccountDetails = SavingAccount::whereHas('company')->with('getSSBAccountBalance')->where('customer_id', $member[0]['id'])->get()->toArray();
                    if (isset($savingAccountDetails)) {
                        $arr = [];
                        $amountnew = [];
                        foreach ($savingAccountDetails as $key => $value) {
                            $deposit = SavingAccountTranscation::where("saving_account_id", $value['id'])->where("type", "2")->sum("deposit");
                            $memberInvestment = Memberinvestments::with('getPlanCustom')->where("account_number", $value['account_no'])->first()->toArray();
                            $companyData = array_combine(array_column($company_ssb_balance, 'company_id'), $company_ssb_balance);
                            foreach ($companyData as $val) {
                                $amount = SavingAccountTransactionView::where('account_no', $val['account_number'])->first(); // saving account vie
                                $amountnew[] = $amount->opening_balance ?? 0;
                            }
                            $data = [
                                'account_number' => $value['account_no'],
                                'is_transaction_available' => $value['transaction_status'],
                                'plan' => $memberInvestment['get_plan_custom']['name'] ?? 0,
                                'total_balance' => $amountnew[$key],
                                'deposit' => $companyData[$value['company_id']]['total'] ?? 0,
                                // 'deposit' => $deposit ?? 0,
                                'deno_amount' => isset($memberInvestment->deposite_amount) ? number_format((float) $memberInvestment->deposite_amount, 2, '.', '') : 0,
                                'opening_date' => isset($memberInvestment['created_at']) ? date("d-m-Y", strtotime($memberInvestment['created_at'])) : '',
                                'investment_id' => isset($memberInvestment) ? (string) $memberInvestment['id'] : '',
                                'company_id' => (string) $value['company_id'],
                                'maturity_date' => '',
                            ];
                            if ($value['transaction_status'] == 1) {
                                $arraynew = array_push($arr, $data);
                            }
                        }
                        //  $arr = array("account_number" => $savingAccountDetails->account_no,
                        //               "plan" => "Saving Account"
                        //              );
                        //total deposit
                        //  $arr["deposit"] = $balance;
                        // get Investment details
                        //  $memberInvestment = Memberinvestments::where("account_number",$savingAccountDetails->account_no)->first();
                        //  if(isset($memberInvestment->deposite_amount)){
                        //      $arr["deno_amount"] = number_format((float)$memberInvestment->deposite_amount, 2, '.', '');
                        //  } else {
                        //      $arr["deno_amount"] = 0;
                        //  }
                        //  if(isset($memberInvestment->created_at)){
                        //      $arr["opening_date"] = $newDate = date("d-m-Y", strtotime($memberInvestment->created_at));
                        //  } else {
                        //      $arr["opening_date"] = "";
                        //  }
                        //  if(isset($memberInvestment->id)){
                        //      $arr["investment_id"] = (string)$memberInvestment->id;
                        //  } else {
                        //      $arr["investment_id"] = "";
                        //  }
                        //  $arr["maturity_date"] = "";
                        $response["status"] = "Success";
                        $response["currentBalance"] = (string) $balance;
                        $response["code"] = 200;
                        $response["minAmount"] = '500';
                        $response["messages"] = "Data";
                        $response["data"] = $arr;
                    }
                }
                if ($type == "deposit") {
                    $investmentArr = array();
                    // get Investment details
                    $memberInvestment = Memberinvestments::with([
                        'plan:id,name,plan_code,plan_category_code,plan_sub_category_code',
                        'memberCompany' => function ($q) {
                            $q->select('id', 'member_id')
                                ->with([
                                    'ssb_detail' => function ($q1) {
                                        $q1->select('id', 'account_no', 'member_id', 'customer_id')
                                            ->with(['getSSBAccountBalance']);
                                    }
                                ]);
                        }
                    ])
                        ->whereHas('company')
                        ->whereHas('plan', function ($query) {
                            $query->where('plan_category_code', '!=', 'S');
                        })
                        ->with('company')
                        ->where("customer_id", $member[0]['id'])
                        ->where("is_mature", 1)
                        ->get(['id', 'account_number', 'deposite_amount', 'maturity_date', 'current_balance', 'company_id', 'plan_id', 'created_at', 'member_id', 'customer_id']);
                    // $savingAccountDetails = SavingAccount::with('getSSBAccountBalance')->where('customer_id', $member[0]['id'])->pluck('company_id')->toArray();
                    $response = [];
                    $savingAccountDetails = SavingAccount::select('company_id', 'transaction_status')
                        ->with('getSSBAccountBalance')
                        ->where('customer_id', $member[0]['id'])
                        ->get();
                    $balance = 0; // You'll need to set the actual balance value here
                    $investmentArr = [];
                    if (count($memberInvestment) > 0) {
                        $memberInvestment = $memberInvestment->toArray();
                        foreach ($memberInvestment as $investment) {
                            $arr = [
                                "investment_id" => (string) $investment["id"],
                                "account_number" => $investment["account_number"],
                                "deno_amount" => number_format((float) $investment["deposite_amount"], 2, '.', ''),
                                "maturity_date" => $investment["maturity_date"],
                                "deposit" => number_format((float) $investment["current_balance"], 2, '.', ''),
                            ];
                            if ($investment["maturity_date"] != null) {
                                $arr["maturity_date"] = date("d-m-Y", strtotime($investment["maturity_date"]));
                            }
                            if (isset($investment["created_at"])) {
                                $arr["opening_date"] = date("d-m-Y", strtotime($investment["created_at"]));
                            } else {
                                $arr["opening_date"] = "";
                            }
                            $arr["plan"] = $investment['plan']['name'];
                            if (
                                $investment['plan']['plan_category_code'] == 'F' ||
                                $investment['company']['status'] == 0 ||
                                (!$savingAccountDetails->contains('company_id', $investment['company_id']) || $savingAccountDetails->where('company_id', $investment['company_id'])->first()->transaction_status != '1')
                            ) {
                                $arr["is_transaction_available"] = 0;
                            } else {
                                $arr["is_transaction_available"] = 1;
                            }
                            $arr['is_category'] = ($investment['plan']['plan_category_code'] == 'F' || $investment['plan']['plan_sub_category_code'] == 'I') ? 0 : 1;
                            $companyIds = $investment['company_id'];
                            // $currentDate = Carbon\Carbon::now();
                            $record = $this->getRecords($investment, $currentDate);
                         
                            $pendingEmiAmount = 0;
                            if (isset($record['pendingEmiAMount']) && $record['pendingEmiAMount'] < 0) {
                                $pendingEmiAmount = abs($record['pendingEmiAMount']);
                            } else {
                                $pendingEmiAmount = 0;
                            }
                            // if (isset($record['pendingEmiAMount'])) {
                            
                            //     $pendingEmiAmount = $record['pendingEmiAMount'];
                            //     if ($pendingEmiAmount > 0) {
                            //         $pendingEmiAmount = 0;
                            //     }
                            // }
                            $pendingEmiAmount = str_replace('-', '', $pendingEmiAmount);
                            $arr["due_amount"] = number_format((float) $pendingEmiAmount, 2, '.', '');
                            $companyData = array_combine(array_column($company_ssb_balance, 'company_id'), $company_ssb_balance);
                            $arr["company_id"] = $companyData[$companyIds]['company_id'] ?? 0;
                            $arr["totalAmount"] = $companyData[$companyIds]['total'] ?? 0;
                            $arr["accountNumber"] = $companyData[$companyIds]['account_number'] ?? 0;
                            array_push($investmentArr, $arr);
                        }
                    }
                    $response["status"] = "Success";
                    $response["currentBalance"] = (string) $balance;
                    $response["code"] = 200;
                    $response["minAmount"] = '500';
                    $response["messages"] = "Data";
                    $response["data"] = $investmentArr;
                }
                // $response now contains the updated data and information.
                // dd( $company_ssb_balance);
                if ($type == "loan") {
                    $investmentArr = array();
                    // $memberId = $member[0]['id'];
                    // $memberloan = Memberloans::select('id', 'applicant_id', 'account_number', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'amount', 'status', 'loan_type', 'associate_member_id', 'branch_id', 'created_at', 'approved_date', 'emi_amount', 'transfer_amount', 'customer_id', 'company_id', 'is_deleted')
                    //     ->where("customer_id", $memberId)
                    //     ->with(['loan:id,name,loan_type', 'loanTransaction'])
                    //     ->with(['memberCompany' => function ($q) {
                    //         $q->select('id', 'member_id', 'customer_id', 'company_id')
                    //             ->with(['ssb_detail' => function ($q) {
                    //                 $q->select('id', 'account_no', 'member_id', 'customer_id')
                    //                     ->with(['getSSBAccountBalance']);
                    //             }]);
                    //     }])
                    //     ->where('status', '!=', 3)
                    //     // ->whereIn('status', [0, 1, 4])
                    //     ->where('is_deleted', 0);
                    // // dd($memberloan->first(),$memberId);
                    // $loant = 'L';
                    // $memberloan = $memberloan->whereHas('loan', function ($query) use ($loant) {
                    //     $query->where('loans.loan_type', '=', $loant);
                    // });
                    // $memberloan = $memberloan->orderby('id', 'DESC')->get();
                    $request->merge(['loan_type' => 'L']);
                    $loans = $this->loanPaymentService->getLoanRecordEpass($request, $this->memberDetail, 'customer_id');
                    $loans = $loans->original['data'];
                    // dd($loans[1]['member']['savingAccount_Custom3']['transaction_status']);
                    if (count($loans) > 0) {
                        $memberloan = $loans;
                        // dd($memberloan);
                        for ($w = 0; $w < count($memberloan); $w++) {
                            $emiDetail = \App\Models\LoanEmisNew::select('out_standing_amount','emi_date')->where('loan_id', $loans[$w]['id'])->where('loan_type', $loans[$w]['loan_type'])->where('is_deleted','0')->orderBY('id','desc')->first();
                            $lastEmidate =isset($emiDetail->emi_date)  ? date('d/m/Y',strtotime($emiDetail->emi_date)) : date('d/m/Y',strtotime($loans[$w]['approve_date']));
                            $outstandingAmount = isset($emiDetail->out_standing_amount) 
                            ? ($emiDetail->out_standing_amount > 0 ? round($emiDetail->out_standing_amount) : 0)
                            : $loans[$w]['amount'];
                            $stateId = $loans[$w]['loanBranch']->state_id;
                            $dueAmount = emiAmountUotoTodaysDate($loans[$w]['id'],$loans[$w]['account_number'],$loans[$w]['approve_date'],$stateId,$loans[$w]['emi_option'],$loans[$w]['emi_amount'],$loans[$w]['closing_date']);
                            $closer_amount = calculateCloserAmount($outstandingAmount,$lastEmidate,$loans[$w]['ROI'],$stateId);
                            $statucCheck = $loans[$w]['member']->savingAccount_Custom3()->where('company_id', $loans[$w]['company_id'])->first();
                            if (
                                isset($loans[$w]['company']['status']) &&
                                $loans[$w]['company']['status'] === 1 &&
                                isset($loans[$w]['company']['plans'][$w]['status']) &&
                                $loans[$w]['company']['plans'][$w]['status'] === 1 &&
                                isset($statucCheck->transaction_status) &&
                                $statucCheck->transaction_status == '1'
                            ) {
                                $is_transaction_available = 1;
                            } else {
                                $is_transaction_available = 0;
                            }
                            $arr["account_number"] = $loans[$w]["account_number"];
                            // $arr["account_number"] = $loans[$w]['member']['savingAccount_Custom3']["account_no"];
                            $arr["emi_amount"] = $loans[$w]["emi_amount"];
                            $arr["loan_id"] = strval($loans[$w]["id"]);
                            $arr["plan_name"] = $loans[$w]['loan']['name'];
                            $arr["loan_status"] = (string) $loans[$w]["status"];
                            $arr["is_transaction_available"] = $is_transaction_available;
                            $arr["investment_id"] = (string) $loans[$w]["id"];
                            $arr["loan_amount"] = (string) number_format((float) $loans[$w]["amount"], 2, '.', '');
                            $originalDate = $loans[$w]["approve_date"];
                            $dateTime = DateTime::createFromFormat('d/m/Y', $originalDate);
                            if ($dateTime !== false) {
                                $formattedDate = $dateTime->format('d-m-Y');
                            } else {
                                $formattedDate = 'N/A';
                            }
                            $arr["issue_date"] = ($loans[$w]['status'] == 4) ? (string) $formattedDate : 'N/A';
                            $arr["instalment"] = (string) $loans[$w]["emi_period"];
                            $applicationOriginalDate = $loans[$w]["created_at"];
                            $applicationDateTime = DateTime::createFromFormat('d/m/Y', $applicationOriginalDate);
                            if ($dateTime !== false) {
                                $formattedDate = $applicationDateTime->format('d-m-Y');
                            } else {
                                $formattedDate = 'N/A';
                            }
                            $arr["application_date"] = (string) $formattedDate;
                            // $arr["application_date"] = (string)date("d-m-Y", strtotime($loans[$w]["created_at"]));
                            switch ($loans[$w]["emi_option"]) {
                                case 1:
                                    $arr["mode"] = 'Monthly';
                                    break;
                                case 2:
                                    $arr["mode"] = 'Weekly';
                                    break;
                                case 3:
                                    $arr["mode"] = 'Daily';
                                    break;
                                default:
                                    $arr["mode"] = '';
                                    break;
                            }
                            $count = \App\Models\LoanDayBooks::where('account_number', $loans[$w]["account_number"])->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                            $arr["emi_paid"] = $count;
                            $arr["is_running_loan"] = 0;
                            if ($loans[$w]["status"] == 4) {
                                $arr["is_running_loan"] = strval(1);
                            } else {
                                $arr["loan_amount"] = (string) number_format((float) $loans[$w]["amount"], 2, '.', '');
                                $applicationOriginalDate = $loans[$w]["created_at"];
                                $applicationDateTime = DateTime::createFromFormat('d/m/Y', $applicationOriginalDate);
                                if ($dateTime !== false) {
                                    $formattedDate = $applicationDateTime->format('d-m-Y');
                                } else {
                                    $formattedDate = 'N/A';
                                }
                                $arr["application_date"] = (string) $formattedDate;
                                $arr["account_number"] = 'N/A';
                            }
                            $arr["type"] = 'loan';
                            $arr["application_no"] = getApplicantid($loans[$w]["applicant_id"]);
                            $arr["company_id"] = (string) $loans[$w]["company_id"];
                            // $arr["due_amount"] = (string) $loans[$w]["due_amount"];
                            $arr["due_amount"] = (string) $dueAmount;
                            // $arr["closer_amount"] = (string) $loans[$w]["closer_amount"];
                            $arr["closer_amount"] = (string) $closer_amount;
                            // dd($loans[$w]->memberCompany->savingAccountNew->savingAccountTransactionViewOrderByDes);
                            // $arr["totalAmount"] = $loans[$w]['loan_saving_account2']['saving_account_transaction_view_order_by']['opening_balance'] ?? 0;
                            
                            $arr["totalAmount"] = $loans[$w]->memberCompany->savingAccountNew->savingAccountTransactionViewOrderByDes->opening_balance ?? 0;
                            $arr["accountNumber"] = $loans[$w]->memberCompany->savingAccountNew->account_no ?? 0;
                            array_push($investmentArr, $arr);
                        }
                    }
                    $response["status"] = "Success";
                    $response["currentBalance"] = (string) $balance;
                    $response["code"] = 200;
                    $response["minAmount"] = '500';
                    $response["messages"] = "Data";
                    $response["data"] = $investmentArr;
                    // $request->merge(['loan_type'=>'L']);    
                    // $loans  = $this->loanPaymentService->getLoanRecordEpass($request, $this->memberDetail,'customer_id');
                    // $response["status"] = "Success";
                    // $response["currentBalance"] = (string)$balance;
                    // $response["code"] = 200;
                    // $response["minAmount"] = '500';
                    // $response["messages"] = "Data";
                    // $response["data"] = $loans->original['data'];
                }
                if ($type == "group_loan") {
                    $investmentArr = array();
                    // $grouploan = Grouploans::select('group_loan_common_id', 'id', 'applicant_id', 'account_number', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'amount', 'status', 'loan_type', 'associate_member_id', 'branch_id', 'created_at', 'approved_date', 'emi_amount', 'transfer_amount')
                    //     ->with(['loan' => function ($q) {
                    //         $q->select('id', 'name', 'loan_type');
                    //     }])
                    //     ->with(['loanMemberCompany' => function ($q) {
                    //         $q->select('id', 'member_id')
                    //             ->with(['ssb_detail' => function ($q1) {
                    //                 $q1->select('id', 'account_no', 'member_id', 'customer_id')
                    //                     ->with(['getSSBAccountBalance']);
                    //             }]);
                    //     }])
                    //     // ->whereIn('status', [0, 1, 4])
                    //     ->where('status', '!=', 3)
                    //     ->where("customer_id", $member[0]['id']);
                    // $loant = 'G';
                    // $grouploan = $grouploan->whereHas('loan', function ($query) use ($loant) {
                    //     $query->where('loans.loan_type', '=', $loant);
                    // });
                    // $grouploan = $grouploan->orderby('id', 'DESC')->get();
                    $request->merge(['loan_type' => 'G']);
                    $grouploans = $this->loanPaymentService->getLoanRecordEpass($request, $this->memberDetail, 'customer_id');
                    $grouploan = $grouploans->original['data'];
                    if (count($grouploan) > 0) {
                        $grouploan = $grouploan->toArray();
                        for ($w = 0; $w < count($grouploan); $w++) {
                            $emiDetail = \App\Models\LoanEmisNew::select('out_standing_amount','emi_date')->where('loan_id', $grouploan[$w]['id'])->where('loan_type', $grouploan[$w]['loan_type'])->where('is_deleted','0')->orderBY('id','desc')->first();
                            $lastEmidate =isset($emiDetail->emi_date)  ? date('d/m/Y',strtotime($emiDetail->emi_date)) : date('d/m/Y',strtotime($grouploan[$w]['approve_date']));
                            $outstandingAmount = isset($emiDetail->out_standing_amount) 
                            ? ($emiDetail->out_standing_amount > 0 ? round($emiDetail->out_standing_amount) : 0)
                            : $grouploan[$w]['amount'];
                            $stateId = $grouploan[$w]['loanBranch']->state_id;
                            $dueAmount = emiAmountUotoTodaysDate($grouploan[$w]['id'],$grouploan[$w]['account_number'],$grouploan[$w]['approve_date'],$stateId,$grouploan[$w]['emi_option'],$grouploan[$w]['emi_amount'],$grouploan[$w]['closing_date']);
                            $closer_amount = calculateCloserAmount($outstandingAmount,$lastEmidate,$grouploan[$w]['ROI'],$stateId);
                            // $company = $grouploan[$w]['company']; // Assuming $company is an object
                            $statucCheck = $grouploan[$w]['member'];
                            // echo '<pre>'; print_r($grouploan[$w]); echo '</pre>'; exit();
                            // if(isset($grouploan[$w]['company']) && $grouploan[$w]['company']['status'] == 1 && $grouploan[$w]['company']['plans'][$w]['status'] == 1 && $grouploan[$w]['member']['saving_account__custom3']['transaction_status'] && $grouploan[$w]['member']['saving_account__custom3']['transaction_status'] == 1){
                            //     $is_transaction_available  = 1;
                            // } else{
                            //     $is_transaction_available  = 0;
                            // }
                            // dd($statucCheck['saving_account__custom3']['transaction_status']);
                            if (
                                isset($grouploan[$w]['company']) &&
                                $grouploan[$w]['company']['status'] == 1 &&
                                isset($grouploan[$w]['company']['plans'][$w]['status']) &&
                                $grouploan[$w]['company']['plans'][$w]['status'] == 1 &&
                                isset($grouploan[$w]['loan_saving_account2']['transaction_status']) &&
                                $grouploan[$w]['loan_saving_account2']['transaction_status'] == '1' 
                                // && $statucCheck['saving_account__custom3']['comspany_id'] == $grouploan[$w]['company']['id']
                            ) {
                                $is_transaction_available = 1;
                            } else {
                                $is_transaction_available = 0;
                            }
                            $arr["account_number"] = $grouploan[$w]["account_number"];
                            $arr["emi_amount"] = $grouploan[$w]["emi_amount"];
                            $arr["loan_id"] = $grouploan[$w]["id"];
                            $arr["plan_name"] = $grouploan[$w]['loans']['name'];
                            $arr["is_transaction_available"] = $is_transaction_available;
                            $arr["loan_status"] = (string) $grouploan[$w]["status"];
                            $arr["investment_id"] = (string) $grouploan[$w]["id"];
                            $arr["loan_amount"] = (string) number_format((float) $grouploan[$w]["amount"], 2, '.', '');
                            $originalDate = $grouploan[$w]["approve_date"];
                            $dateTime = DateTime::createFromFormat('d/m/Y', $originalDate);
                            if ($dateTime !== false) {
                                $formattedDate = $dateTime->format('d-m-Y');
                            } else {
                                $formattedDate = 'N/A';
                            }
                            // $arr["issue_date"] = (string)date("d-m-Y", strtotime($grouploan[$w]["approve_date"]));
                            $arr["issue_date"] = ($grouploan[$w]['status'] == 4) ? (string) $formattedDate : 'N/A';
                            $applicationOriginalDate = $grouploan[$w]["created_at"];
                            $applicationDateTime = DateTime::createFromFormat('d/m/Y', $applicationOriginalDate);
                            if ($dateTime !== false) {
                                $formattedDate = $applicationDateTime->format('d-m-Y');
                            } else {
                                $formattedDate = 'N/A';
                            }
                            $arr["application_date"] = (string) $formattedDate;
                            $arr["instalment"] = (string) $grouploan[$w]["emi_period"];
                            $arr["mode"] = '';
                            if ($grouploan[$w]["emi_option"] == 1) {
                                $arr["mode"] = 'Monthly';
                            }
                            if ($grouploan[$w]["emi_option"] == 2) {
                                $arr["mode"] = 'Weekly';
                            }
                            if ($grouploan[$w]["emi_option"] == 3) {
                                $arr["mode"] = 'Daily';
                            }
                            $count = \App\Models\LoanDayBooks::where('account_number', $grouploan[$w]["account_number"])->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                            $arr["emi_paid"] = $count;
                            $arr["is_running_loan"] = 0;
                            if ($grouploan[$w]["status"] == 4) {
                                $arr["is_running_loan"] = 1;
                            } else {
                                $arr["loan_amount"] = (string) number_format((float) $grouploan[$w]["amount"], 2, '.', '');
                                ;
                                $applicationOriginalDate = $grouploan[$w]["created_at"];
                                $applicationDateTime = DateTime::createFromFormat('d/m/Y', $applicationOriginalDate);
                                if ($dateTime !== false) {
                                    $formattedDate = $applicationDateTime->format('d-m-Y');
                                } else {
                                    $formattedDate = 'N/A';
                                }
                                $arr["application_date"] = (string) $formattedDate;
                                $arr["account_number"] = 'N/A';
                            }
                            $arr["application_no"] = $grouploan[$w]["applicant_id"];
                            $arr["type"] = 'group_loan';
                            $arr["company_id"] = (string) $grouploan[$w]["company_id"];
                            // $arr["due_amount"] = (string) $grouploan[$w]["due_amount"];
                            $arr["due_amount"] = (string) $dueAmount;


                           
                            // $arr["closer_amount"] = (string) $grouploan[$w]["closer_amount"];
                            $arr["closer_amount"] = (string) $closer_amount;
                            // $arr["totalAmount"]   = $grouploan[$w]['loan_member_company']['ssb_detail']['get_s_s_b_account_balance']['totalBalance'] ?? 0;
                            // $arr["accountNumber"] = $grouploan[$w]['loan_member_company']['ssb_detail']['get_s_s_b_account_balance']['account_no'] ?? 0;
                            //   dd($grouploan[$w]['loan_saving_account2']['balance']);
                            // $arr["totalAmount"]   = $grouploan[$w]['loan_saving_account2']['saving_account_transaction_view_order_by']['opening_balance']??0;
                            $arr["totalAmount"] = $grouploan[$w]['loan_saving_account2']['saving_account_transaction_view_order_by']['opening_balance'] ?? 0;
                           
                            $arr["accountNumber"] = $grouploan[$w]['loan_saving_account2']['account_no'] ?? 0;
                            array_push($investmentArr, $arr);
                        }
                    }
                    // $request->merge(['loan_type'=>'G']);    
                    // $loans  = $this->loanPaymentService->getLoanRecord($request, $this->memberDetail,'customer_id');
                    // $response["status"] = "Success";
                    // $response["currentBalance"] = (string)$balance;
                    // $response["code"] = 200;
                    // $response["minAmount"] = '500';
                    // $response["messages"] = "Data";
                    // $response["data"] = $loans->original['data'];
                    $response["status"] = "Success";
                    $response["currentBalance"] = (string) $balance;
                    $response["code"] = 200;
                    $response["minAmount"] = '500';
                    $response["messages"] = "Data";
                    $response["data"] = $investmentArr;
                }
            } else {
                $response["status"] = "Error";
                $response["code"] = 201;
                $response["minAmount"] = '500';
                $response["messages"] = "Enter Valid Customer Id";
                $response["data"] = array();
            }
        } else {
            $response["status"] = "Error";
            $response["code"] = 201;
            $response["minAmount"] = '500';
            $response["messages"] = "Input parameter missing";
            $response["data"] = array();
        }
        return response()->json($response);
    }

    



    // public static function getRecords($dailyDepositeRecord,$currentDate)
    // {
    //        //print_r($dailyDepositeRecord['plan_id']);die;

    //         switch ($dailyDepositeRecord['plan_id']) {
    //             case '7':
    //             case '13':    
    //                 $pendingEmiAMount=0;
    //                 $pendingEmi=0;
    //                 $investCreatedDate =strtotime($dailyDepositeRecord['created_at']);

    //                 $CURRENTDATE = strtotime($currentDate);

    //                 $totalBetweendays = abs($investCreatedDate-$CURRENTDATE);
    //                 $totalBetweendays = ceil(floatval($totalBetweendays/86400));
    //                 $totalAmount = ($totalBetweendays + 1) * $dailyDepositeRecord['deposite_amount'];
    //                 $getRenewalReceivedAmount= \App\Models\Daybook::whereIn('transaction_type',[2,4])->where('account_no',$dailyDepositeRecord['account_number'])->sum('deposit');

    //                 if($getRenewalReceivedAmount != $totalAmount)
    //                 {
    //                     $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount ;
    //                     $pendingEmi =  $pendingEmiAMount/ $dailyDepositeRecord['deposite_amount'];
    //                 } 
    //                 else{
    //                     $pendingEmiAMount = 0;
    //                     $pendingEmi =  0;
    //                 }
    //                 return ['pendingEmiAMount'=>$pendingEmiAMount,'pendingEmi'=>$pendingEmi];
    //                 break;
    //             case '2':
    //             case '3':
    //             case '5':
    //             case '10':
    //             case '11':
    //                 $pendingEmiAMount=0;
    //                 $pendingEmi=0;
    //                 $investCreatedDate = Carbon\Carbon::parse($dailyDepositeRecord['created_at']);
    //                 $totalBetweenmonth = $investCreatedDate->diffInMonths($currentDate);
    //                 $totalAmount = ($totalBetweenmonth + 1) *  $dailyDepositeRecord['deposite_amount'];
    //                 $getRenewalReceivedAmount= \App\Models\Daybook::whereIn('transaction_type',[2,4])->where('account_no',$dailyDepositeRecord['account_number'])->sum('deposit');


    //                 if($getRenewalReceivedAmount != $totalAmount)
    //                 {
    //                     $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount ;

    //                     $pendingEmi =  $pendingEmiAMount/ $dailyDepositeRecord['deposite_amount'];
    //                 } 
    //                  else{
    //                     $pendingEmiAMount = 0;
    //                     $pendingEmi =  0;
    //                 }
    //                 return ['pendingEmiAMount'=>$pendingEmiAMount,'pendingEmi'=>$pendingEmi];

    //                 break;
    //         }

    // }

    public static function getRecords($dailyDepositeRecord, $currentDate)
    {
        $planCategory = Plans::findorFail($dailyDepositeRecord['plan_id']);
        
        $planCategory =$planCategory->plan_category_code;
      

        switch ($planCategory) {
            case 'D':
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = strtotime($dailyDepositeRecord['created_at']);

                $CURRENTDATE = strtotime($currentDate);

                $totalBetweendays = abs($investCreatedDate - $CURRENTDATE);
                $totalBetweendays = ceil(floatval($totalBetweendays / 86400));
                $totalAmount = ($totalBetweendays ) * $dailyDepositeRecord['deposite_amount'];
                $getRenewalReceivedAmount = \App\Models\Daybook::whereIn('transaction_type', [2, 4])->where('is_deleted', 0)->where('account_no', $dailyDepositeRecord['account_number'])->sum('deposit');
                // dd($getRenewalReceivedAmount,$totalAmount);

                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;
                    $pendingEmi =  $pendingEmiAMount / $dailyDepositeRecord['deposite_amount'];
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi =  0;
                }
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];
                break;
            case 'S':
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = strtotime($dailyDepositeRecord['created_at']);

                $CURRENTDATE = strtotime($currentDate);

                $totalBetweendays = abs($investCreatedDate - $CURRENTDATE);
                $totalBetweendays = ceil(floatval($totalBetweendays / 86400));
             
            

                $totalAmount = ($totalBetweendays) * $dailyDepositeRecord['deposite_amount'];
                $getRenewalReceivedAmount = \App\Models\Daybook::whereIn('transaction_type', [2, 4])->where('account_no', $dailyDepositeRecord['account_number'])->where('is_deleted', 0)->sum('deposit');

                // dd($getRenewalReceivedAmount, $totalAmount);
                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;
                    $pendingEmi =  $pendingEmiAMount / $dailyDepositeRecord['deposite_amount'];
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi =  0;
                }
                // dd($pendingEmiAMount);
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];
                break;
            case 'M':
           
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = Carbon\Carbon::parse($dailyDepositeRecord['created_at']);
                $totalBetweenmonth = $investCreatedDate->diffInMonths($currentDate);
                $totalAmount = ($totalBetweenmonth + 1) * $dailyDepositeRecord['deposite_amount'];
                $getRenewalReceivedAmount = \App\Models\Daybook::whereIn('transaction_type', [2, 4])->where('account_no', $dailyDepositeRecord['account_number'])->where('is_deleted', 0)->sum('deposit');


                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;

                    $pendingEmi =  $dailyDepositeRecord['deposite_amount'] > 0 ? ($pendingEmiAMount / $dailyDepositeRecord['deposite_amount']) : 0;
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi =  0;
                }

              
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];

                break;
        }
    }


    public function getAccountDetailsNew(Request $request)
    {
       
        $response = array();
        $currentDate = Carbon\Carbon::now();
        $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));

        $input = $request->all(); 
        if (isset($input["member_id"]) && $input["member_id"] != "" && isset($input["type"]) && $input["type"] != "") {



            // Get Member ID
            $member = Member::with(['savingAccount'])->select('id', 'member_id', 'company_id')->where('member_id', $input["member_id"])->get();
            $company_ssb_balance = [];
           
            foreach ($member[0]['savingAccount'] as $value) {
                
                $savingAccountDetails = SavingAccount::with('getSSBAccountBalance')->where('account_no', $value->account_no)->get()->toArray();
                // dd($savingAccountDetails[0]['get_s_s_b_account_balance']['totalBalance']);
                $company_ssb_balance[] = [
                    'company_id' => $value->company_id??0,
                    'account_number' => $savingAccountDetails[0]['get_s_s_b_account_balance']['account_no']??0,
                    'total' => $savingAccountDetails[0]['get_s_s_b_account_balance']['totalBalance']??0,
                ];
            }

          

            $balance = 0;
            if (isset($member['savingAccount']['savingAccountBalance'])) {
                $balance =  $member['savingAccount'][0]['savingAccountBalance']->sum('deposit') - $member['savingAccount'][0]['savingAccountBalance']->sum('withdrawal');
            }




            $balance  =  number_format((float) $balance, 2, '.', '');

            if (isset($member[0]['member_id'])) {

                $type = $input["type"];

                if ($type == "ssb") {


                    // Get SSB Account ID
                    $savingAccountDetails = SavingAccount::with('getSSBAccountBalance')->where('customer_id', $member[0]['id'])->get()->toArray();
                    // dd($savingAccountDetails);
                    if (isset($savingAccountDetails)) {

                        $arr = [];
                        foreach ($savingAccountDetails as $value) {

                            $deposit = SavingAccountTranscation::where("saving_account_id", $value['id'])->where("type", "2")->sum("deposit");
                            $memberInvestment = Memberinvestments::with('getPlanCustom')->where("account_number", $value['account_no'])->first()->toArray();
                            $companyData = array_combine(array_column($company_ssb_balance, 'company_id'), $company_ssb_balance);
                            $data = [
                                'account_number' => $value['account_no'],
                                'plan' => $memberInvestment['get_plan_custom']['name']??0,
                                'total_balance' => $value['get_s_s_b_account_balance']['totalBalance']??0,
                                'deposit' => $companyData[$value['company_id']]['total'] ?? 0,
                                // 'deposit' => $deposit ?? 0,
                                'deno_amount' => isset($memberInvestment->deposite_amount) ? number_format((float)$memberInvestment->deposite_amount, 2, '.', '') : 0,
                                'opening_date' => isset($memberInvestment['created_at']) ?  date("d-m-Y", strtotime($memberInvestment['created_at'])) : '',
                                'investment_id' => isset($memberInvestment) ? (string)$memberInvestment['id'] : '',
                                'company_id' => (string)$value['company_id'],
                                'maturity_date' => '',
                            ];
                            $arraynew = array_push($arr, $data);
                        }





                        //  $arr = array("account_number" => $savingAccountDetails->account_no,
                        //               "plan" => "Saving Account"
                        //              );

                        //total deposit



                        //  $arr["deposit"] = $balance;

                        // get Investment details
                        //  $memberInvestment = Memberinvestments::where("account_number",$savingAccountDetails->account_no)->first();

                        //  if(isset($memberInvestment->deposite_amount)){
                        //      $arr["deno_amount"] = number_format((float)$memberInvestment->deposite_amount, 2, '.', '');
                        //  } else {
                        //      $arr["deno_amount"] = 0;
                        //  }

                        //  if(isset($memberInvestment->created_at)){
                        //      $arr["opening_date"] = $newDate = date("d-m-Y", strtotime($memberInvestment->created_at));
                        //  } else {
                        //      $arr["opening_date"] = "";
                        //  }

                        //  if(isset($memberInvestment->id)){
                        //      $arr["investment_id"] = (string)$memberInvestment->id;
                        //  } else {
                        //      $arr["investment_id"] = "";
                        //  }

                        //  $arr["maturity_date"] = "";


                        $response["status"] = "Success";
                        $response["currentBalance"] = (string)$balance;
                        $response["code"] = 200;
                        $response["minAmount"] = '500';
                        $response["messages"] = "Data";
                        $response["data"] = $arr;
                    }
                }

                if ($type == "deposit") {


                    $investmentArr = array();
                    // get Investment details
                    $memberInvestment = Memberinvestments::
                    
                    with(['plan:id,name,plan_code,plan_category_code,plan_sub_category_code','memberCompany' => function ($q) {
                        $q->select('id', 'member_id')
                            ->with(['ssb_detail' => function ($q1) {
                                $q1->select('id', 'account_no', 'member_id', 'customer_id')
                                    ->with(['getSSBAccountBalance']);
                            }]);
                    }])
                    ->where("customer_id", $member[0]['id'])
                    ->whereHas('plan', function ($query) {
                        $query->where('plans.plan_category_code','!=','S');
                      })
                    ->where("is_mature", 1)
                    ->get(['id','account_number','deposite_amount','maturity_date','current_balance','company_id','plan_id','created_at','member_id','customer_id']);

                    $savingAccountDetails = SavingAccount::with('getSSBAccountBalance')->where('customer_id', $member[0]['id'])->pluck('company_id')->toArray();

                    // dd();plan_category_code
                    if (count($memberInvestment) > 0) {

                        $memberInvestment = $memberInvestment->toArray();
                        $ssbCompanyIdArray = array();
                        for ($w = 0; $w < count($memberInvestment); $w++) {                            
                                $arr = array(
                                    "investment_id" => (string)$memberInvestment[$w]["id"],
                                    "account_number" => $memberInvestment[$w]["account_number"],
                                    "deno_amount" => number_format((float)$memberInvestment[$w]["deposite_amount"], 2, '.', ''),
                                    "maturity_date" => $memberInvestment[$w]["maturity_date"],
                                    "deposit" => number_format((float)$memberInvestment[$w]["current_balance"], 2, '.', ''),
                                );

                                if ($memberInvestment[$w]["maturity_date"] != null) {
                                    $arr["maturity_date"] = date("d-m-Y", strtotime($memberInvestment[$w]["maturity_date"]));
                                }

                                if (isset($memberInvestment[$w]["created_at"])) {
                                    $arr["opening_date"] =  date("d-m-Y", strtotime($memberInvestment[$w]["created_at"]));
                                } else {
                                    $arr["opening_date"] = "";
                                }
                                //pd($memberInvestment[$w]);
                                $arr["plan"] = $memberInvestment[$w]['plan']['name'] ;
                                
                                            /*1 -> Show
                                            0 -> Hide*/

                                // if($memberInvestment[0]['company_id'] == $member['company_id']){
                                $arr["is_transaction_available"] = ($memberInvestment[$w]['plan']['plan_category_code'] == 'F') ? 0 : (in_array($memberInvestment[$w]['company_id'], $savingAccountDetails) ? 1 : 0);

                                $arr['is_category'] = ($memberInvestment[$w]['plan']['plan_category_code'] == 'F' || $memberInvestment[$w]['plan']['plan_sub_category_code'] == 'I') ? 0 : 1 ;

                                // }else{
                                //     $arr["is_transaction_available"] = 0;
                                // }

                                $companyIds =  $memberInvestment[$w]['company_id'];


                                //  $currentDate = Carbon\Carbon::now(); 
                                $record = $this->getRecords($memberInvestment[$w], $currentDate);
                                $pendingEmiAMount = 0;
                                if (isset($record['pendingEmiAMount'])) {
                                    $pendingEmiAMount = $record['pendingEmiAMount'];
                                    // if ($record['pendingEmiAMount'] > 0) {
                                    //     $pendingEmiAMount = 0;
                                    // }
                                }
                                $pendingEmiAMount = str_replace('-', '', $pendingEmiAMount);
                                $arr["due_amount"] = number_format((float)$pendingEmiAMount, 2, '.', '');

                                 $companyData = array_combine(array_column($company_ssb_balance, 'company_id'), $company_ssb_balance);

                                // $arr["company_id"]   = $memberInvestment[$w]["company_id"] ?? 0;
                                // $arr["totalAmount"]   = $memberInvestment[$w]['member_company']['ssb_detail']['get_s_s_b_account_balance']['totalBalance'] ?? 0;
                                // $arr["accountNumber"] = $memberInvestment[$w]['member_company']['ssb_detail']['get_s_s_b_account_balance']['account_no'] ?? 0;


                                $arr["company_id"]   = $companyData[$companyIds]['company_id'] ?? 0;
                                $arr["totalAmount"]   = $companyData[$companyIds]['total'] ?? 0;
                                $arr["accountNumber"] = $companyData[$companyIds]['account_number'] ?? 0;

                                array_push($investmentArr, $arr);
                            
                        }
                    }

                    $response["status"] = "Success";
                    $response["currentBalance"] = (string)$balance;
                    $response["code"] = 200;
                    $response["minAmount"] = '500';
                    $response["messages"] = "Data";
                    $response["data"] = $investmentArr;
                }
                // dd( $company_ssb_balance);

                if ($type == "loan") {
                    $investmentArr = array();

                    $memberId = $member[0]['id'];
                    $memberloan = Memberloans::select('id', 'applicant_id', 'account_number', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'amount', 'status', 'loan_type', 'associate_member_id', 'branch_id', 'created_at', 'approved_date', 'emi_amount', 'transfer_amount', 'customer_id', 'company_id', 'is_deleted')
                        ->where("customer_id", $memberId)
                        ->with(['loan:id,name,loan_type', 'loanTransaction'])
                        ->with(['memberCompany' => function ($q) {
                            $q->select('id', 'member_id', 'customer_id', 'company_id')
                                ->with(['ssb_detail' => function ($q) {
                                    $q->select('id', 'account_no', 'member_id', 'customer_id')
                                        ->with(['getSSBAccountBalance']);
                                }]);
                        }])
                        ->where('status', '!=', 3)
                        // ->whereIn('status', [0, 1, 4])
                        ->where('is_deleted', 0);
                    // dd($memberloan->first(),$memberId);
                    $loant = 'L';
                    $memberloan = $memberloan->whereHas('loan', function ($query) use ($loant) {
                        $query->where('loans.loan_type', '=', $loant);
                    });
                    $memberloan = $memberloan->orderby('id', 'DESC')->get();


                    if (count($memberloan) > 0) {
                        $memberloan = $memberloan->toArray();

                        for ($w = 0; $w < count($memberloan); $w++) {

                            // dd($memberloan[$w]);
                            $arr["account_number"] = $memberloan[$w]["account_number"];
                            $arr["emi_amount"] = $memberloan[$w]["emi_amount"];
                            $arr["loan_id"] = $memberloan[$w]["id"];
                            $arr["plan_name"] = $memberloan[$w]['loan']['name'];
                            $arr["loan_status"] = (string)$memberloan[$w]["status"];
                            $arr["investment_id"] = (string)$memberloan[$w]["id"];
                            $arr["loan_amount"] = (string)number_format((float)$memberloan[$w]["transfer_amount"], 2, '.', '');
                            $arr["issue_date"] = (string)date("d-m-Y", strtotime($memberloan[$w]["approve_date"]));
                            $arr["instalment"] = (string)$memberloan[$w]["emi_period"];
                            switch ($memberloan[$w]["emi_option"]) {
                                case 1:
                                    $arr["mode"] = 'Monthly';
                                    break;
                                case 2:
                                    $arr["mode"] = 'Weekly';
                                    break;
                                case 3:
                                    $arr["mode"] = 'Daily';
                                    break;
                                default:
                                    $arr["mode"] = '';
                                    break;
                            }
                            $count = \App\Models\LoanDayBooks::where('account_number', $memberloan[$w]["account_number"])->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                            $arr["emi_paid"] = $count;
                            $arr["is_running_loan"] = 0;
                            if ($memberloan[$w]["status"] == 4) {
                                $arr["is_running_loan"] = 1;
                            } else {
                                $arr["loan_amount"] = (string)number_format((float)$memberloan[$w]["amount"], 2, '.', '');
                                $arr["application_date"] = (string)date("d-m-Y", strtotime($memberloan[$w]["created_at"]));
                                $arr["account_number"] = 'N/A';
                            }

                            $arr["type"] = 'loan';
                            $arr["application_no"] = getApplicantid($memberloan[$w]["applicant_id"]);



                            $arr["totalAmount"]   = $memberloan[$w]['member_company']['ssb_detail']['get_s_s_b_account_balance']['totalBalance'] ?? 0;
                            $arr["accountNumber"] = $memberloan[$w]['member_company']['ssb_detail']['get_s_s_b_account_balance']['account_no'] ?? 0;


                            array_push($investmentArr, $arr);
                        }
                    }

                    $response["status"] = "Success";
                    $response["currentBalance"] = (string)$balance;
                    $response["code"] = 200;
                    $response["minAmount"] = '500';
                    $response["messages"] = "Data";
                    $response["data"] = $investmentArr;
                }



                if ($type == "group_loan") {

                    $investmentArr = array();

                    $grouploan = Grouploans::select('group_loan_common_id', 'id', 'applicant_id', 'account_number', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'amount', 'status', 'loan_type', 'associate_member_id', 'branch_id', 'created_at', 'approved_date', 'emi_amount', 'transfer_amount')
                        ->with(['loan' => function ($q) {
                            $q->select('id', 'name', 'loan_type');
                        }])
                        ->with(['loanMemberCompany' => function ($q) {
                            $q->select('id', 'member_id')
                                ->with(['ssb_detail' => function ($q1) {
                                    $q1->select('id', 'account_no', 'member_id', 'customer_id')
                                        ->with(['getSSBAccountBalance']);
                                }]);
                        }])
                        // ->whereIn('status', [0, 1, 4])
                        ->where('status', '!=', 3)
                        ->where("customer_id", $member[0]['id']);
                    $loant = 'G';
                    $grouploan = $grouploan->whereHas('loan', function ($query) use ($loant) {
                        $query->where('loans.loan_type', '=', $loant);
                    });
                    $grouploan = $grouploan->orderby('id', 'DESC')->get();

                    if (count($grouploan) > 0) {
                        $grouploan = $grouploan->toArray();
                        for ($w = 0; $w < count($grouploan); $w++) {
                            // echo '<pre>'; print_r($grouploan[$w]); echo '</pre>'; exit();
                            $arr["account_number"] = $grouploan[$w]["account_number"];
                            $arr["emi_amount"] = $grouploan[$w]["emi_amount"];
                            $arr["loan_id"] = $grouploan[$w]["id"];
                            $arr["plan_name"] = $grouploan[$w]['loan']['name'];
                            $arr["loan_status"] = (string)$grouploan[$w]["status"];
                            $arr["investment_id"] = (string)$grouploan[$w]["id"];
                            $arr["loan_amount"] = (string)number_format((float)$grouploan[$w]["transfer_amount"], 2, '.', '');
                            $arr["issue_date"] = (string)date("d-m-Y", strtotime($grouploan[$w]["approve_date"]));
                            $arr["instalment"] = (string)$grouploan[$w]["emi_period"];
                            $arr["mode"] = '';
                            if ($grouploan[$w]["emi_option"] == 1) {
                                $arr["mode"] = 'Monthly';
                            }
                            if ($grouploan[$w]["emi_option"] == 2) {
                                $arr["mode"] = 'Weekly';
                            }
                            if ($grouploan[$w]["emi_option"] == 3) {
                                $arr["mode"] = 'Daily';
                            }
                            $count = \App\Models\LoanDayBooks::where('account_number', $grouploan[$w]["account_number"])->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                            $arr["emi_paid"] = $count;
                            $arr["is_running_loan"] = 0;
                            if ($grouploan[$w]["status"] == 4) {
                                $arr["is_running_loan"] = 1;
                            } else {
                                $arr["loan_amount"] = (string)number_format((float)$grouploan[$w]["amount"], 2, '.', '');;
                                $arr["application_date"] = (string)date("d-m-Y", strtotime($grouploan[$w]["created_at"]));
                                $arr["account_number"] = 'N/A';
                            }
                            $arr["application_no"] = $grouploan[$w]["group_loan_common_id"];
                            $arr["type"] = 'group_loan';

                            $arr["totalAmount"]   = $grouploan[$w]['loan_member_company']['ssb_detail']['get_s_s_b_account_balance']['totalBalance'] ?? 0;
                            $arr["accountNumber"] = $grouploan[$w]['loan_member_company']['ssb_detail']['get_s_s_b_account_balance']['account_no'] ?? 0;


                            array_push($investmentArr, $arr);
                        }
                    }

                    $response["status"] = "Success";
                    $response["currentBalance"] = (string)$balance;
                    $response["code"] = 200;
                    $response["minAmount"] = '500';
                    $response["messages"] = "Data";
                    $response["data"] = $investmentArr;
                }
            } else {
                $response["status"] = "Error";
                $response["code"] = 201;
                $response["minAmount"] = '500';
                $response["messages"] = "Enter Valid Customer Id";
                $response["data"] = array();
            }
        } else {
            $response["status"] = "Error";
            $response["code"] = 201;
            $response["minAmount"] = '500';
            $response["messages"] = "Input parameter missing";
            $response["data"] = array();
        }

        return response()->json($response);
    }

    public function epassbookLogout(Request $request)
    {
        // dd($request->all());
        $response = array();
        try {
            // Find the member record
            $logout = Member::where('member_id', $request->member_id)->first();
            if ($logout) {
                // Update the record to reflect the logout status
                $logout->update([
                    'e_passbook_mobile_token' => null,
                    'device_id' => null,
                ]);
                $response["status"] = "Success";
                $response["code"] = 200;
                $response["messages"] = "User Logout Successfully!!";
            } else {
                $response["status"] = "Error";
                $response["code"] = 201;
                $response["messages"] = "User not found!";
            }
        } catch (\Exception $e) {
            $response["status"] = "Error";
            $response["code"] = 500;
            $response["messages"] = "Internal Server Error: " . $e->getMessage();
        }
        return response()->json($response);
    }
}