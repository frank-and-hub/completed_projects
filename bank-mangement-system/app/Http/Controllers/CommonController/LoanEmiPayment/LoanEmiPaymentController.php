<?php
namespace App\Http\Controllers\CommonController\LoanEmiPayment;
use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Validator;
use Carbon\Carbon;
use DB;
use URL;
use App\Models\CollectorAccount;
use App\Models\LoanEmisNew;
use App\Services\LoanEmiPaymentService;

class LoanEmiPaymentController extends Controller
{
    protected $loanService;

    public function __construct(LoanEmiPaymentService $loanService)
    {
        $this->loanService = $loanService;
    }
    // View Page
    public function index()
    {
        if(Auth::user()->role_id != 3){
            if (check_my_permission(Auth::user()->id, "254") != "1") {
                return redirect()->route('admin.dashboard');
            }
        }else{
            if(!in_array('EMI Deposit', auth()->user()->getPermissionNames()->toArray())){
                return redirect()->route('branch.dashboard');
            }
        }
        $data["title"] = "EMI Payment";
        return view("templates.CommonViews.LoanEmiPayment.index", $data);
    }

    public function getAccountDetails(Request $request)
    {
        $account_no = $request->account_number;
        $companyId = 0;
        $loanType = $request->loan_type;

        // Retrive Model Name
        $loanRecord = $this->loanService
            ->getLoanListing($request)
            ->when($loanType === "L", function ($q) {
                $q->addSelect("applicant_id");
            })
            ->when($loanType === "G", function ($q) {
                $q->addSelect("member_id");
            })
            ->where("account_number", $request["account_number"])
            ->first();

        // If Loan account doest not exist
        if ($loanRecord == "") {
            $data = 1;
            return response()->json($data);
        }
        else {
            // when loan is Approved
            if ($loanRecord->status == 1) {
                $data = 2;
                return response()->json($data);
            }
            // When Loan Is rejected
            elseif ($loanRecord->status == 2) {
                $data = 3;
                return response()->json($data);
            }
            // when loan is clear
            elseif ($loanRecord->status == 3) {
                $data = 4;
                return response()->json($data);
            }
            // loan reject
            elseif ($loanRecord->status == 5) {
                $data = 5;
                return response()->json($data);
            }
            // loan on old
            elseif ($loanRecord->status == 6) {
                $data = 6;
                return response()->json($data);
            }
            // approved but hold
            elseif ($loanRecord->status == 7) {
                $data = 7;
                return response()->json($data);
            }
            // loan is in running stage
            elseif ($loanRecord->status == 4) {
                $recoverdAmount = loanOutsandingAmountNew(
                    $loanRecord->id,
                    $loanRecord->account_number
                );
                $outstandingAmountData = LoanEmisNew::where(
                    "loan_id",
                    $loanRecord->id
                )
                    ->where("loan_type", $loanRecord->loan_type)
                    ->where("is_deleted", "0")
                    ->orderBy("id", "desc")
                    ->first(["emi_date", "out_standing_amount"]);

                $outstandingAmount = isset(
                    $outstandingAmountData->out_standing_amount
                )
                    ? ($outstandingAmountData->out_standing_amount > 0
                        ? $outstandingAmountData->out_standing_amount
                        : 0)
                    : $loanRecord->amount;

                $lastEmidate = isset($outstandingAmountData->emi_date)
                    ? date("d/m/Y", strtotime($outstandingAmountData->emi_date))
                    : date("d/m/Y", strtotime($loanRecord->approve_date));

                $stateId = $loanRecord["loanBranch"]->state_id;
                $ssbRelation =
                    $loanType === "L"
                        ? $loanRecord->savingAccount
                        : $loanRecord->loanSavingAccount2;
                $ssbAccount = $ssbRelation->account_no ?? null;
                $ssbId = $ssbRelation->id ?? null;
                // $ssbBalance = $ssbRelation->savingAccountTransactionViewOrderBy->opening_balance ?? null;
                // $ssbBalance = $ssbRelation->balance ?? 0.0; // code is commetned by sourab on 02-02-24
				$ssbBalance = $ssbRelation->getSSBAccountBalance->totalBalance ?? 0.0;

                $closerAmount = calculateCloserAmount(
                    $outstandingAmount,
                    $lastEmidate,
                    $loanRecord->ROI,
                    $stateId
                );
                $branch = branchName();
                $lastrecoversAmount = lastLoanRecoveredAmount(
                    $loanRecord->id,
                    "loan_id"
                );
                $due_amount =  emiAmountUotoTodaysDate($loanRecord->id, $loanRecord->account_number, $loanRecord->approve_date, $stateId, $loanRecord->emi_option, $loanRecord->emi_amount, $loanRecord->closing_date);
                $associate = CollectorAccount::with('member_collector')->whereStatus(1)
					->when($loanType === "L", function ($q) use($loanRecord){
						$q->whereType(2)->whereTypeId($loanRecord->id);
					})
					->when($loanType === "G", function ($q) use($loanRecord) {
						$q->whereType(3)->whereTypeId($loanRecord->id);
					})
                    ->whereStatus('1')
					->first();

				$associate_name = $associate ? (($associate->member_collector->first_name) . ' ' . ($associate->member_collector->last_name??'')) : null;
				$associate_code = $associate ? $associate->member_collector->associate_no : null;
				$loan_associate_id = $associate ? $associate->member_collector->id : null;

                $data = [
                    "closerAmount" => $closerAmount,
                    "outstandingAmount" => $outstandingAmount,
                    "recoverdAmount" => $recoverdAmount,
                    "loanRecord" => $loanRecord,
                    "ssbBalance" => $ssbBalance,
                    "ssbAccount" => $ssbAccount,
                    "ssbId" => $ssbId,
                    "loginBranch" => $branch,
                    "lastrecoversAmount" => $lastrecoversAmount,
                    'due_amount'=>$due_amount,
                    "loan_associate_code" => $associate_code??NULL,
					"loan_associate_name" => $associate_name??NULL,
					'loan_associate_id' => $loan_associate_id ?? NULL,
                ];

                return response()->json($data);
            }
        }
    }
}
