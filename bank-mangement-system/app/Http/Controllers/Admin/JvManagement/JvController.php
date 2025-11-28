<?php
namespace App\Http\Controllers\Admin\JvManagement;
use App\Http\Controllers\Admin\CommanController;
use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Vendor;
use App\Http\Controllers\Controller;
use App\Models\CreditCradTransaction;
use App\Models\VendorTransaction;
use Illuminate\Http\Request;
use App\Models\AssociateTransaction;
use App\Models\CustomerTransaction;
use App\Models\SavingAccountTransactionView;
use Illuminate\Support\Collection;
use Validator;
use App\Models\Files;
use App\Models\Designation;
use App\Models\AccountHeads;
use App\Models\LoanDayBooks;
use App\Models\Branch;
use App\Models\SavingAccount;
use App\Models\Member;
use App\Models\Memberloans;
use App\Models\Memberinvestments;
use App\Models\Employee;
use App\Models\ShareHolder;
use App\Models\JvJournals;
use App\Models\Daybook;
use App\Models\SavingAccountTranscation;
use App\Models\JvJournalHeads;
use App\Models\SamraddhBankAccount;
use App\Models\Grouploans;
use App\Models\RentLiability;
use App\Models\EmployeeLedger;
use App\Models\RentLiabilityLedger;
use App\Models\Transcation;
use App\Models\TranscationLog;
use App\Models\AllHeadTransaction;
use App\Models\BranchDaybook;
use App\Models\SamraddhBankDaybook;
use App\Models\MemberTransaction;
use App\Models\LoanFromBank;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use URL;
use DB;
use Session;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Demand Advice DemandAdviceController
    |--------------------------------------------------------------------------
    |
    | This controller handles demand advice all functionlity.
*/
class JvController extends Controller
{
    /*
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "150") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Journal Voucher Listing';
        return view('templates.admin.jv_management.index', $data);
    }
    /**
     * create a form
     */
    public function create()
    {
        if (check_my_permission(Auth::user()->id, "149") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Create Journal Entry';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();
        $data['account_heads'] = AccountHeads::select('id', 'parent_id', 'head_id', 'sub_head')->where('labels', 1)->where('status', 0)->get();
        $jvAutoId = JvJournals::select('id', 'jv_auto_id')->where('is_deleted', 0)->orderby('id', 'desc')->first();
        if ($jvAutoId) {
            $data['jv_auto_id'] = $jvAutoId->jv_auto_id + 1;
        } else {
            $data['jv_auto_id'] = 1;
        }
        return view('templates.admin.jv_management.create', $data);
    }
    public function saveJV(Request $request)
    {
        DB::beginTransaction();
        try {
            $jvAutoId = JvJournals::select('jv_auto_id')->orderby('id', 'desc')->first();
            if ($jvAutoId) {
                $data['jv_auto_id'] = $jvAutoId->jv_auto_id + 1;
            } else {
                $data['jv_auto_id'] = 1;
            }
            $companyId = $request['company_id'];
            $data['branch_id']  =   $request['branch'];
            $data['company_id']  =   $request['company_id'];
            $data['date']       =        date("Y-m-d", strtotime(convertDate($request['cre_date'])));
            $data['reference']  =   $request['reference'];
            $data['notes']      =       $request['notes'];
            $data['created_at'] =  date("Y-m-d", strtotime(convertDate($request['cre_date'])));
            $res = JvJournals::create($data);
            $type_id = $res->id;
            $amount = Collection::make($request['credit'])->filter(function ($value) { return $value !== null;})->sum();
            $dayBookRef = CommanController::createBranchDayBookReference($amount);
            foreach ($request['account_head'] as $key => $value) {
                if ($request['account_head'][$key]) {
                    $entryTime = $entry_time = date("H:i:s");
                    Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['cre_date']))));
                    if ($request['debit'][$key] > 0) {
                        $payment_type = 'DR';
                        $amount = $request['debit'][$key];
                        $dr_amount = $request['debit'][$key];
                        $cr_amount = NULL;
                        $description_dr = 'JV Journal Transfer DR ' . $amount;
                        $description_cr = NULL;
                    } elseif ($request['credit'][$key] > 0) {
                        $payment_type = 'CR';
                        $amount = $request['credit'][$key];
                        $dr_amount = NULL;
                        $cr_amount = $request['credit'][$key];
                        $description_cr = 'JV Amount Transfer CR ' . $amount;
                        $description_dr = NULL;
                    }
                    $transaction_date = $created_at = date("Y-m-d", strtotime(convertDate($request['cre_date'])));
                    $entry_date = date("Y-m-d", strtotime(convertDate($request['cre_date'])));
                    if ($request['sub_head4'][$key]) {
                        $head = $request['sub_head4'][$key];
                    } elseif ($request['sub_head3'][$key]) {
                        $head = $request['sub_head3'][$key];
                    } elseif ($request['sub_head2'][$key]) {
                        $head = $request['sub_head2'][$key];
                    } elseif ($request['sub_head1'][$key]) {
                        $head = $request['sub_head1'][$key];
                    } elseif ($request['account_head'][$key]) {
                        $head = $request['account_head'][$key];
                    }
                    $acoountHeadId = $request['account_head'][$key];
                    $parentId = AccountHeads::where('head_id', $head)->first('parent_id');
                    // Saving Heads
                    if ($head == 56 || $parentId->parent_id == 406 || $parentId->parent_id == 403) {
                        $sAccount = getSavingAccountMemberId($request['contact'][$key]);
                        $contectId = $sAccount->id;
                        $jvtype = 3;
                        $jv_sub_type = NULL;
                        $contacttype = 4;
                        $jv_type_id = $sAccount->id;
                        $type = 4;
                        $sub_type = 412;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $sAccount->associate_id;
                        $member_id = $sAccount->member_id;
                        $branch_id_to = $sAccount->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($sAccount->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $sAccount->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        if ($request['debit'][$key]) {
                            $ssbAccountAmount = $sAccount->balance - $amount;
                        } elseif ($request['credit'][$key]) {
                            $ssbAccountAmount = $sAccount->balance + $amount;
                        }
                        $ssb_id = $collectionSSBId = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $record1 = SavingAccountTransactionView::where('account_no', $sAccount->account_no)->whereDate('opening_date', '<=', date("Y-m-d", strtotime(convertDate($request['cre_date']))))->orderby('opening_date', 'desc')->first();
                        if ($record1) {
                            if ($request['debit'][$key]) {
                                if ($record1->opening_balance >= $amount) {
                                    $ssb['opening_balance'] = $record1->opening_balance - $amount;
                                } else {
                                    throw new \Exception("Insufficient balance in account no".$sAccount->account_no);
                                }
                            } elseif ($request['credit'][$key]) {
                                $ssb['opening_balance'] = $record1->opening_balance + $amount;
                            }
                        } else {
                            if ($request['debit'][$key]) {
                                if ($sAccount->balance >= $amount) {
                                    $ssb['opening_balance']  = $ssbAccountAmount;
                                } else {
                                    throw new \Exception("Insufficient balance in account no".$sAccount->account_no);
                                }
                            } elseif ($request['credit'][$key]) {
                                $ssb['opening_balance']  = $sAccount->balance + $amount;
                            }
                        }
                        if ($request['debit'][$key]) {
                            $ssb['withdrawal'] = $amount;
                            $ssb['payment_type'] = 'DR';
                            $ssb['type'] = 12;
                            $ssb['deposit'] = NULL;
                            $deposit = $amount;
                            $withdrawal = NULL;
                        } elseif ($request['credit'][$key]) {
                            $ssb['deposit'] = $amount;
                            $ssb['payment_type'] = 'CR';
                            $ssb['type'] = 13;
                            $ssb['withdrawal'] = NULL;
                            $withdrawal = $amount;
                            $deposit = NULL;
                        }
                        if ($request['branch'] != $sAccount->branch_id) {
                            $branchName = getBranchDetail($request['branch'])->name;
                            // $ssb['description'] ='JV Journal Transfer To '.$sAccount->account_no.'- From '.$branchName.'';
                            // $description = 'JV Journal Transfer To '.$sAccount->account_no.'- From '.$branchName.'';
                            $ssb['description'] = $request['description'][$key];
                            $description  = $request['description'][$key];
                        } else {
                            // $ssb['description'] = 'JV Journal Transfer To '.$sAccount->account_no;  
                            // $description = 'JV Journal Transfer To '.$sAccount->account_no; 
                            $ssb['description'] = $request['description'][$key];
                            $description  = $request['description'][$key];
                        }
                        $ssb['reference_no'] = $request['reference'];
                        $ssb['associate_id'] = $sAccount->associate_id;
                        $ssb['branch_id'] = $request['branch'];
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_mode'] = 6;
                        $ssb['is_renewal'] = 0;
                        $ssb['daybook_ref_id'] = $dayBookRef;
                        $ssb['company_id'] = $request['company_id'];
                        $ssb['jv_journal_id'] = $type_id;
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['cre_date'])));
                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                        $ssbAccountTranFromId = $type_transaction_id = $ssbAccountTran->id;
                     updateSavingAccountTransaction($sAccount->id, $sAccount->account_no);
                        if ($sAccount->member_investments_id > 0) {
                            $investmentData = getInvestmentDetails($sAccount->member_investments_id);
                            $lastAmount = Daybook::where('investment_id', $investmentData->id)->where('account_no', $investmentData->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->orderby('id', 'desc')->first();
                            if ($lastAmount) {
                                $lastBalance = $lastAmount->opening_balance + $amount;
                            } else {
                                $lastBalance = $amount;
                            }
                            $satRefId = NULL;
                            $createTransaction = NULL;
                            $amountArraySsb = array('1' => $amount);
                            $createDayBook = $this->createDayBookNew($createTransaction, $satRefId, 2, $investmentData->id, $investmentData->id, $investmentData->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $sAccount->branch_id, getBranchCode($sAccount->branch_id)->branch_code, $amountArraySsb, 6, NULL, $sAccount->member_id, $investmentData->account_number, NULL, NULL, $amount_from_name, $request['cre_date'], NULL, $online_payment_by = NULL, $sAccount->id, $payment_type, $received_cheque_id = NULL, $cheque_deposit_bank_id = NULL, $cheque_deposit_bank_ac_id = NULL, $online_deposit_bank_id = NULL, $online_deposit_bank_ac_id = NULL, $type_id, $companyId);
                        }
                    } elseif ($head == 64 || $head == 65 || $head == 67 || $head == 66) {
                        // Loan Heads
                        if ($head == 66) {
                            $loan = getGroupLoanDetailById($request['contact'][$key]);
                            $sub_type = 512;
                            $jv_sub_type = 13;
                            $transaction_type = 29;
                            $day_transaction_type = 30;
                            $loan_id = $loan->member_loan_id;
                            $group_loan_id = $loan->id;
                        } else {
                            $loan = getLoanDetail($request['contact'][$key]);
                            $sub_type = 511;
                            $jv_sub_type = 11;
                            $transaction_type = 28;
                            $day_transaction_type = 29;
                            $loan_id = $loan->id;
                            $group_loan_id = NULL;
                        }
                        $contectId = $loan_id;
                        $jvtype = 1;
                        $contacttype = 1;
                        $jv_type_id = $loan_id;
                        $type = 5;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArray = array('1' => $amount);
                        $createTran = NULL;
                        $lastAmount = Daybook::where('loan_id', $loan->id)->where('account_no', $loan->account_number)->orderby('id', 'desc')->first();
                        if ($request['debit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance + $amount;
                            $deposit = $amount;
                            $withdrawal = NULL;
                        } elseif ($request['credit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance - $amount;
                            $withdrawal = $amount;
                            $deposit = NULL;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTran, NULL, $day_transaction_type, $loan->id, $loan->associate_member_id, $loan->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $amountArray, 6, NULL, $loan->applicant_id, $loan->account_number, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                        $loanType = $loan->loan_type;
                        $createLoanDayBook = $this->createLoanDayBook($loanType, 4, $loan_id, $group_loan_id, $loan->account_number, $loan->applicant_id, $amount, NULL, NULL, NULL, NULL, $description, $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $payment_type, 'INR', 6, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, 1, 1, NULL, NULL, NULL, NULL, $loan->associate_member_id, $loan->applicant_id, $type_id, $companyId);
                        $type_transaction_id = $createDayBook;
                    } elseif ($head == 33) {
                        // Loan panelty
                        $loan = getLoanDetail($request['contact'][$key]);
                        if ($loan) {
                            $sub_type = 513;
                            $jv_sub_type = 12;
                            $transaction_type = 26;
                            $day_transaction_type = 27;
                            $loan_id = $loan->id;
                            $group_loan_id = NULL;
                        } else {
                            $loan = getGroupLoanDetailById($request['contact'][$key]);
                            $sub_type = 514;
                            $jv_sub_type = 14;
                            $transaction_type = 27;
                            $day_transaction_type = 28;
                            $loan_id = $loan->member_loan_id;
                            $group_loan_id = $loan->id;
                        }
                        $contectId = $loan_id;
                        $jvtype = 1;
                        $contacttype = 1;
                        $jv_type_id = $loan_id;
                        $type = 5;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArray = array('1' => $amount);
                        $createTran = NUll;
                        $lastAmount = Daybook::where('loan_id', $loan->id)->where('account_no', $loan->account_number)->orderby('id', 'desc')->first();
                        if ($request['debit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance - $amount;
                            $deposit = NULL;
                            $withdrawal = $amount;
                        } elseif ($request['credit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance + $amount;
                            $withdrawal = NULL;
                            $deposit = $amount;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTran, NULL, $day_transaction_type, $loan->id, $loan->associate_member_id, $loan->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $amountArray, 6, NULL, $loan->applicant_id, $loan->account_number, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                        // if($head == 64){
                        //     $loanType = 1;
                        // }elseif($head == 65){
                        //     $loanType = 2;
                        // }elseif($head == 67){
                        //     $loanType = 4;
                        // }
                        $loanType = $loan->loan_type;
                        $createLoanDayBook = $this->createLoanDayBook($loanType, 3, $loan_id, $group_loan_id, $loan->account_number, $loan->applicant_id, $amount, NULL, NULL, NULL, NULL, $description, $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $payment_type, 'INR', 6, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, 1, 1, NULL, NULL, NULL, NULL, $loan->associate_member_id, $loan->applicant_id, $type_id, $companyId);
                        $type_transaction_id = $createDayBook;
                    } elseif ($head == 31) {
                        // Loan emi
                        $loan = getLoanDetail($request['contact'][$key]);
                        if ($loan) {
                            $sub_type = 515;
                            $transaction_type = 22;
                            $day_transaction_type = 23;
                            $loan_id = $loan->id;
                            $group_loan_id = NULL;
                        } else {
                            $loan = getGroupLoanDetailById($request['contact'][$key]);
                            $sub_type = 516;
                            $transaction_type = 24;
                            $day_transaction_type = 25;
                            $loan_id = $loan->member_loan_id;
                            $group_loan_id = $loan->id;
                        }
                        $contectId = $loan_id;
                        $jvtype = 1;
                        $jv_sub_type = 11;
                        $contacttype = 1;
                        $jv_type_id = $loan_id;
                        $type = 5;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArray = array('1' => $amount);
                        /*$createTran = $this->createTransaction(NULL,$transaction_type,$loan->id,$loan->applicant_id,$loan->branch_id,getBranchCode($loan->branch_id)->branch_code,$amountArray,6,NULL,$loan->applicant_id,$loan->account_number,NULL,NULL,getBranchDetail($loan->branch_id)->name,date("Y-m-d", strtotime( str_replace('/','-',$request['cre_date'] ) ) ),NULL,NULL,NULL,$payment_type,$type_id);
                        $lastAmount = Daybook::where('loan_id',$loan->id)->where('account_no',$loan->account_number)->orderby('id','desc')->first();
                        if($request['debit'][$key]){
                            $lastBalance = $lastAmount->opening_balance-$amount;
                            $deposit = NULL;
                            $withdrawal = $amount;
                        }elseif($request['credit'][$key]){
                            $lastBalance = $lastAmount->opening_balance+$amount;
                            $withdrawal = NULL;
                            $deposit = $amount;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTran,NULL,$day_transaction_type,$loan->id,$loan->associate_member_id,$loan->member_id,$lastBalance,$deposit,$withdrawal,$description,$request['reference'],$loan->branch_id,getBranchCode($loan->branch_id)->branch_code,$amountArray,6,NULL,$loan->applicant_id,$loan->account_number,NULL,NULL,getBranchDetail($loan->branch_id)->name,date("Y-m-d", strtotime( str_replace('/','-',$request['cre_date'] ) ) ),NULL,NULL,NULL,$payment_type, NULL,NULL,NULL,NULL,NULL,$type_id);
                        $loanType = $loan->loan_type;
                        $createLoanDayBook = $this->createLoanDayBook($loanType,2,$loan_id,$group_loan_id,$loan->account_number,$loan->applicant_id,$amount,NULL,NULL,NULL,NULL,$description,$loan->branch_id,getBranchCode($loan->branch_id)->branch_code,$payment_type,'INR',6,NULL,NULL,getBranchDetail($loan->branch_id)->name,date("Y-m-d", strtotime( str_replace('/','-',$request['cre_date'] ) ) ),NULL,1,1,NULL,NULL,NULL,NULL,$loan->associate_member_id,$loan->applicant_id,$type_id);
                        $type_transaction_id = $createDayBook;*/
                        $type_transaction_id = NULL;
                    } elseif ($head == 234 || $parentId->parent_id == 236 || $head == 233) {
                        // Loan emi
                        $data = \App\Models\CompanyBound::where('id', $request['contact'][$key])->first();
                        if ($data) {
                            $sub_type = 302;
                            $transaction_type = 22;
                            $day_transaction_type = 23;
                            $loan_id = $data->id;
                            $group_loan_id = NULL;
                        }
                        $contectId = $data->id;
                        $jvtype = 19;
                        $jv_sub_type = 19;
                        $contacttype = 19;
                        $jv_type_id = $data->id;
                        $bound = $data->id;
                        $type = 30;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = NULL;
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = NULL;
                        $amountArray = array('1' => $amount);
                        $type_transaction_id = NULL;
                    } elseif ($head == 90) {
                        // File Charage
                        $loan = getLoanDetail($request['contact'][$key]);
                        if ($loan) {
                            $sub_type = 519;
                            $transaction_type = 30;
                            $day_transaction_type = 31;
                            $loan_id = $loan->id;
                            $group_loan_id = NULL;
                            $jv_sub_type = 17;
                        } else {
                            $loan = getGroupLoanDetailById($request['contact'][$key]);
                            $sub_type = 520;
                            $transaction_type = 31;
                            $day_transaction_type = 32;
                            $loan_id = $loan->member_loan_id;
                            $group_loan_id = $loan->id;
                            $jv_sub_type = 18;
                        }
                        $contectId = $loan_id;
                        $jvtype = 1;
                        $contacttype = 1;
                        $jv_type_id = $loan_id;
                        $type = 5;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArray = array('1' => $amount);
                        $createTran = NULL;
                        $lastAmount = Daybook::where('loan_id', $loan->id)->where('account_no', $loan->account_number)->orderby('id', 'desc')->first();
                        if ($request['debit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance - $amount;
                            $deposit = NULL;
                            $withdrawal = $amount;
                        } elseif ($request['credit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance + $amount;
                            $withdrawal = NULL;
                            $deposit = $amount;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTran, NULL, $day_transaction_type, $loan->id, $loan->associate_member_id, $loan->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $amountArray, 6, NULL, $loan->applicant_id, $loan->account_number, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                        $loanType = $loan->loan_type;
                        $createLoanDayBook = $this->createLoanDayBook($loanType, 5, $loan_id, $group_loan_id, $loan->account_number, $loan->applicant_id, $amount, NULL, NULL, NULL, NULL, $description, $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $payment_type, 'INR', 6, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, 1, 1, NULL, NULL, NULL, NULL, $loan->associate_member_id, $loan->applicant_id, $type_id, $companyId);
                        $type_transaction_id = $createDayBook;
                    } elseif ($head == 97) {
                        // INTEREST ON LOAN TAKEN
                        $bankData = LoanFromBank::where('id', $request['contact'][$key])->first();
                        $jvtype = 12;
                        $jv_sub_type = NULL;
                        $contacttype = 12;
                        $contectId = $bankData->id;
                        $jv_type_id = $bankData->id;
                        $type = 17;
                        $sub_type = 174;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $bankData->id;
                        $amount_to_name = $bankData->bank_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                        /*
                        $loan = getLoanDetail($request['contact'][$key]);
                        if($loan){
                            $sub_type = 517;
                            $jv_sub_type = 15;
                        }else{
                            $loan = getGroupLoanDetail($request['contact'][$key]);
                            $sub_type = 518;
                            $jv_sub_type = 16;
                        }
                        $contectId = $loan->id;
                        $jvtype = 1;
                        $contacttype = 1;
                        $jv_type_id = $loan->id;
                        $type = 5;
                        $type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;*/
                    } elseif ($head == 58 || $head == 77 || $head == 78 || $head == 79 || $head == 80 || $head == 81 || $head == 82 || $head == 83 || $head == 84 || $head == 85 || $head == 89 || $head == 139 || $head == 62) {
                        // Investment Heads
                        $investmentData = getInvestmentDetails($request['contact'][$key]);
                        if ($investmentData->plan_id == 1) {
                            $sAccount = getSavingAccountMemberId($request['contact'][$key]);
                            $contectId = $sAccount->id;
                            $jvtype = 2;
                            $jv_sub_type = NULL;
                            $contacttype = 2;
                            $jv_type_id = $sAccount->id;
                            $type = 3;
                            $sub_type = 38;
                            $branch_id = $request['branch'];
                            //$memberData = getMemberData($loan->associate_member_id);
                            $associate_id = $sAccount->associate_id;
                            $member_id = $sAccount->member_id;
                            $branch_id_to = $sAccount->branch_id;
                            $branch_id_from = $request['branch'];
                            $toBranch = getBranchDetail($sAccount->branch_id);
                            $fromBranch = getBranchDetail($request['branch']);
                            $amount_to_id = $sAccount->branch_id;
                            $amount_to_name = $toBranch->name;
                            $amount_from_id = $request['branch'];
                            $amount_from_name = $fromBranch->name;
                            if ($request['debit'][$key]) {
                                $ssbAccountAmount = $sAccount->balance - $amount;
                            } elseif ($request['credit'][$key]) {
                                $ssbAccountAmount = $sAccount->balance + $amount;
                            }
                            $ssb_id = $collectionSSBId = $sAccount->id;
                            $sResult = SavingAccount::find($ssb_id);
                            $sData['balance'] = $ssbAccountAmount;
                            $sResult->update($sData);
                            $ssb['saving_account_id'] = $ssb_id;
                            $ssb['account_no'] = $sAccount->account_no;
                            $record1 = SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<', date("Y-m-d", strtotime(convertDate($request['cre_date']))))->orderby('created_at', 'desc')->first();
                            if ($record1) {
                                if ($request['debit'][$key]) {
                                    $ssb['opening_balance'] = $record1->opening_balance - $amount;
                                } elseif ($request['credit'][$key]) {
                                    $ssb['opening_balance'] = $record1->opening_balance + $amount;
                                }
                            } else {
                                $ssb['opening_balance'] = $request['amount'][$key];
                            }
                            if ($request['debit'][$key]) {
                                $ssb['withdrawal'] = $amount;
                                $ssb['payment_type'] = 'DR';
                                $ssb['type'] = 12;
                                $ssb['deposit'] = NULL;
                                $deposit = $amount;
                                $withdrawal = NULL;
                            } elseif ($request['credit'][$key]) {
                                $ssb['deposit'] = $amount;
                                $ssb['payment_type'] = 'CR';
                                $ssb['type'] = 13;
                                $ssb['withdrawal'] = NULL;
                                $withdrawal = $amount;
                                $deposit = NULL;
                            }
                            if ($request['branch'] != $sAccount->branch_id) {
                                $branchName = getBranchDetail($request['branch'])->name;
                                // $ssb['description'] ='JV Journal Transfer To '.$sAccount->account_no.'- From '.$branchName.'';
                                $ssb['description'] = $request['description'][$key];
                                //$description = 'JV Journal Transfer To '.$sAccount->account_no.'- From '.$branchName.'';
                                $description = $request['description'][$key];
                            } else {
                                // $ssb['description'] = 'JV Journal Transfer To '.$sAccount->account_no;  
                                $ssb['description'] = $request['description'][$key];
                                //$description = 'JV Journal Transfer To '.$sAccount->account_no; 
                                $description = $request['description'][$key];
                            }
                            $ssb['reference_no'] = $request['reference'];
                            $ssb['associate_id'] = $sAccount->associate_id;
                            $ssb['branch_id'] = $request['branch'];
                            $ssb['currency_code'] = 'INR';
                            $ssb['payment_mode'] = 6;
                            $ssb['is_renewal'] = 0;
                            $ssb['company_id'] = $companyId;
                            $ssb['jv_journal_id'] = $type_id;
                            $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['cre_date'])));
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $ssbAccountTranFromId = $type_transaction_id = $ssbAccountTran->id;
                            updateSavingAccountTransaction($sAccount->id, $sAccount->account_no);
                            if ($sAccount->member_investments_id > 0) {
                                $investmentData = getInvestmentDetails($sAccount->member_investments_id);
                                $lastAmount = Daybook::where('investment_id', $investmentData->id)->where('account_no', $investmentData->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->orderby('id', 'desc')->first();
                                if ($lastAmount) {
                                    $lastBalance = $lastAmount->opening_balance + $amount;
                                } else {
                                    $lastBalance = $amount;
                                }
                                $satRefId = null;
                                $createTransaction = NULL;
                                $amountArraySsb = array('1' => $amount);
                                $createDayBook = $this->createDayBookNew($createTransaction, $satRefId, 1, $investmentData->id, $investmentData->id, $investmentData->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $sAccount->branch_id, getBranchCode($sAccount->branch_id)->branch_code, $amountArraySsb, 6, NULL, $sAccount->member_id, $investmentData->account_number, NULL, NULL, $amount_from_name, $request['cre_date'], NULL, $online_payment_by = NULL, $sAccount->id, $payment_type, $received_cheque_id = NULL, $cheque_deposit_bank_id = NULL, $cheque_deposit_bank_ac_id = NULL, $online_deposit_bank_id = NULL, $online_deposit_bank_ac_id = NULL, $type_id, $companyId);
                            }
                        } else {
                            $contectId = $investmentData->id;
                            $jvtype = 2;
                            $jv_sub_type = NULL;
                            $contacttype = 2;
                            $jv_type_id = $investmentData->id;
                            if ($head == 89) {
                                $type = 3;
                                $sub_type = 313;
                            } else {
                                $type = 3;
                                $sub_type = 38;
                            }
                            $branch_id = $request['branch'];
                            //$memberData = getMemberData($loan->associate_member_id);
                            $associate_id = $investmentData->associate_id;
                            $member_id = $investmentData->member_id;
                            $branch_id_to = $investmentData->branch_id;
                            $branch_id_from = $request['branch'];
                            $toBranch = getBranchDetail($investmentData->branch_id);
                            $fromBranch = getBranchDetail($request['branch']);
                            $amount_to_id = $investmentData->branch_id;
                            $amount_to_name = $toBranch->name;
                            $amount_from_id = $request['branch'];
                            $amount_from_name = $fromBranch->name;
                            $amountArraySsb = array('1' => $amount);
                            $createTransaction = null;
                            $lastAmount = Daybook::where('investment_id', $investmentData->id)->where('account_no', $investmentData->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->orderby('id', 'desc')->first();
                            if ($head == 89) {
                                if ($request['debit'][$key]) {
                                    $lastBalance = $lastAmount->opening_balance + $amount;
                                    $deposit = $amount;
                                    $withdrawal = NULL;
                                } elseif ($request['credit'][$key]) {
                                    $lastBalance = $lastAmount->opening_balance - $amount;
                                    $withdrawal = $amount;
                                    $deposit = NULL;
                                }
                            } else {
                                if ($request['debit'][$key]) {
                                    $lastBalance = $lastAmount->opening_balance - $amount;
                                    $deposit = NULL;
                                    $withdrawal = $amount;
                                } elseif ($request['credit'][$key]) {
                                    $lastBalance = $lastAmount->opening_balance + $amount;
                                    $withdrawal = NULL;
                                    $deposit = $amount;
                                }
                            }
                            $description = $request['description'][$key];
                            $createDayBook = $this->createDayBookNew($createTransaction, NULL, 20, $investmentData->id, $investmentData->associate_id, $investmentData->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $investmentData->branch_id, getBranchCode($investmentData->branch_id)->branch_code, $amountArraySsb, 6, NULL, $investmentData->member_id, $investmentData->account_number, NULL, NULL, NULL, $request['cre_date'], NULL, $online_payment_by = NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                            $type_transaction_id = $createDayBook;
                            updateRenewalTransaction($investmentData->account_number);
                        }
                    } elseif ($head == 36) {
                        //INTEREST ON DEPOSITS Heads
                        $investmentData = getInvestmentDetails($request['contact'][$key]);
                        $contectId = $investmentData->id;
                        $jvtype = 2;
                        $jv_sub_type = 21;
                        $contacttype = 2;
                        $jv_type_id = $investmentData->id;
                        $type = 3;
                        $sub_type = 314;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $investmentData->associate_id;
                        $member_id = $investmentData->member_id;
                        $branch_id_to = $investmentData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($investmentData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $investmentData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArraySsb = array('1' => $amount);
                        /*$createTransaction = $this->createTransaction(NULL,20,$investmentData->id,$investmentData->member_id,$investmentData->branch_id,getBranchCode($investmentData->branch_id)->branch_code,$amountArraySsb,6,NULL,$investmentData->member_id,$investmentData->account_number,NULL,NULL,getBranchDetail($investmentData->branch_id)->name,date("Y-m-d", strtotime( str_replace('/','-',$request['cre_date'] ) ) ),NULL,$online_payment_by=NULL,NULL,$payment_type,$type_id); 
                        $lastAmount = Daybook::where('investment_id',$investmentData->id)->where('account_no',$investmentData->account_number)->whereNotIn('transaction_type', [3,5,6,7,8,9,10,11,12,13,14,15,19])->orderby('id','desc')->first();
                        if($request['debit'][$key]){
                            $lastBalance = $lastAmount->opening_balance+$amount;
                            $withdrawal = NULL;  
                            $deposit = $amount;  
                        }elseif($request['credit'][$key]){
                            $lastBalance = $lastAmount->opening_balance-$amount;
                            $deposit = NULL;
                            $withdrawal = $amount;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTransaction,NULL,21,$investmentData->id,$investmentData->associate_id,$investmentData->member_id,$lastBalance,$deposit,$withdrawal,$description,$request['reference'],$investmentData->branch_id,getBranchCode($investmentData->branch_id)->branch_code,$amountArraySsb,6,NULL,$investmentData->member_id,$investmentData->account_number,NULL,NULL,NULL,$request['cre_date'],NULL,$online_payment_by=NULL,NULL,$payment_type,NULL,NULL,NULL,NULL,NULL,$type_id);
                        $type_transaction_id = $createDayBook;
                        updateRenewalTransaction($investmentData->account_number);*/
                        $type_transaction_id = NULL;
                    } elseif ($head == 122) {
                        //Investment plan stationery charge
                        $investmentData = getInvestmentDetails($request['contact'][$key]);
                        $contectId = $investmentData->id;
                        $jvtype = 2;
                        $jv_sub_type = 22;
                        $contacttype = 2;
                        $jv_type_id = $investmentData->id;
                        $type = 3;
                        $sub_type = 39;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $investmentData->associate_id;
                        $member_id = $investmentData->member_id;
                        $branch_id_to = $investmentData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($investmentData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $investmentData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArraySsb = array('1' => $amount);
                        $createTransaction = null;
                        $lastAmount = Daybook::where('investment_id', $investmentData->id)->where('account_no', $investmentData->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->orderby('id', 'desc')->first();
                        if ($request['debit'][$key]) {
                            $withdrawal = $amount;
                            $deposit = NULL;
                        } elseif ($request['credit'][$key]) {
                            $deposit = $amount;
                            $withdrawal = NULL;
                        }
                        $lastBalance = $amount;
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTransaction, NULL, 21, $investmentData->id, $investmentData->associate_id, $investmentData->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $investmentData->branch_id, getBranchCode($investmentData->branch_id)->branch_code, $amountArraySsb, 6, NULL, $investmentData->member_id, $investmentData->account_number, NULL, NULL, NULL, $request['cre_date'], NULL, $online_payment_by = NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                        $type_transaction_id = $createDayBook;
                        //updateRenewalTransaction($investmentData->account_number);
                    } elseif ($head == 34) {
                        // STATIONERY CHARGE
                        $memberData = getMemberData($request['contact'][$key]);
                        $jvtype = 4;
                        $jv_sub_type = 41;
                        $contacttype = 3;
                        $contectId = $memberData->id;
                        $jv_type_id = $memberData->id;
                        $type = 1;
                        $sub_type = 13;
                        $branch_id = $request['branch'];
                        $associate_id = $memberData->associate_id;
                        $member_id = $memberData->id;
                        $branch_id_to = $memberData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($memberData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $memberData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 35) {
                        // DUPLICATE PASSBOOK CHARGE
                        $record = SavingAccount::where('account_no', $request['contact_account'][$key])->first();
                        if ($record) {
                            $type = 4;
                            $sub_type = 413;
                            $jvtype = 2;
                            $jv_sub_type = 23;
                            $contacttype = 4;
                        } else {
                            $record = Memberinvestments::where('account_number', $request['contact_account'][$key])->first();
                            $type = 3;
                            $sub_type = 311;
                            $jvtype = 3;
                            $jv_sub_type = 31;
                            $contacttype = 2;
                        }
                        $contectId = $record->id;
                        $jv_type_id = $record->id;
                        $branch_id = $request['branch'];
                        $associate_id = $record->associate_id;
                        $member_id = $record->member_id;
                        $branch_id_to = $record->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($record->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $record->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    }/*elseif($head == 62){
                        // TDS ON INTEREST ON DEPOSIT
                        $memberData = getMemberData($request['contact'][$key]);
                        $jvtype = 4;
                        $jv_sub_type = 41;
                        $contacttype = 3;
                        $contectId = $memberData->id;
                        $jv_type_id = $memberData->id;
                        $type = 1;
                        $sub_type = 14;
                        $branch_id = $request['branch'];
                        $associate_id = $memberData->associate_id;
                        $member_id = $memberData->id;
                        $branch_id_to = $memberData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($memberData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $memberData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    }*/ elseif ($head == 68 || $head == 69 || $head == 70 || $head == 91 || $head == 27 || $parentId->parent_id == 27) {
                        // Bank Heads
                        $bankAccountData = getSamraddhBankAccountId($request['contact'][$key],$companyId);
                        $bankData = getSamraddhBank($bankAccountData->bank_id);
                        $jvtype = 5;
                        $jv_sub_type = NULL;
                        $contacttype = 5;
                        $contectId = $bankData->id;
                        $jv_type_id = $bankData->id;
                        $type = 22;
                        $sub_type = 222;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $bankData->id;
                        $amount_to_name = $bankData->bank_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 28 || $head == 71) {
                        // TDS ON INTEREST ON DEPOSIT
                        $branchData = getBranchDetail($request['contact'][$key]);
                        $jvtype = 6;
                        $jv_sub_type = NULL;
                        $contacttype = 6;
                        $contectId = $branchData->id;
                        $jv_type_id = $branchData->id;
                        $type = 23;
                        $sub_type = 232;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $branchData->id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $branchData->id;
                        $amount_to_name = $branchData->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 61) {
                        // Salary Crediotrs
                        $employeeData = Employee::where('id', $request['contact'][$key])->first();
                        $jvtype = 7;
                        $jv_sub_type = NULL;
                        $contacttype = 7;
                        $contectId = $employeeData->id;
                        $jv_type_id = $employeeData->id;
                        $type = 6;
                        $sub_type = 62;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $employeeData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $employeeData->id;
                        $amount_to_name = $employeeData->employee_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 140) {
                        // Vendor Creditior
                        $vendorData = Vendor::where('type', 0)->where('id', $request['contact'][$key])->first();
                        $jvtype = 16;
                        $jv_sub_type = 161;
                        $contacttype = 161;
                        $contectId = $vendorData->id;
                        $jv_type_id = $vendorData->id;
                        $type = 27;
                        $sub_type = 273;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $type_transaction_id = NULL;
                    } elseif ($head == 142) {
                        // Vendor Creditior
                        $customerData = Vendor::where('type', 1)->where('id', $request['contact'][$key])->first();
                        $jvtype = 17;
                        $jv_sub_type = 171;
                        $contacttype = 171;
                        $contectId = $customerData->id;
                        $jv_type_id = $customerData->id;
                        $type = 27;
                        $sub_type = 274;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $type_transaction_id = NULL;
                    } elseif ($head == 141) {
                        // Associate Creditior
                        $AssociateData = Member::where('id', $request['contact'][$key])->first();
                        $jvtype = 9;
                        $jv_sub_type = 91;
                        $contacttype = 2;
                        $contectId = $AssociateData->id;
                        $jv_type_id = $AssociateData->id;
                        $type = 2;
                        $sub_type = 24;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $type_transaction_id = NULL;
                    } elseif ($parentId->parent_id == 167) {
                        // Associate Creditior
                        $creditCard = CreditCard::where('id', $request['contact'][$key])->first();
                        $jvtype = 18;
                        $jv_sub_type = 18;
                        $contacttype = 18;
                        $contectId = $creditCard->id;
                        $jv_type_id = $creditCard->id;
                        $type = 28;
                        $sub_type = 281;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $type_transaction_id = NULL;
                    } elseif ($head == 32) {
                        // Panel Interest
                        $employeeData = Employee::where('id', $request['contact'][$key])->first();
                        $jvtype = 7;
                        $jv_sub_type = NULL;
                        $contacttype = 7;
                        $contectId = $employeeData->id;
                        $jv_type_id = $employeeData->id;
                        $type = 6;
                        $sub_type = 63;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $employeeData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $employeeData->id;
                        $amount_to_name = $employeeData->employee_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 60 || $head == 74 || $head == 75 || $head == 144) {
                        // Rent Crediotrs
                        $rentLiabilityData = RentLiability::where('id', $request['contact'][$key])->first();
                        $jvtype = 8;
                        $jv_sub_type = NULL;
                        $contacttype = 8;
                        $contectId = $rentLiabilityData->id;
                        $jv_type_id = $rentLiabilityData->id;
                        $type = 10;
                        $sub_type = 105;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $rentLiabilityData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $rentLiabilityData->id;
                        $amount_to_name = $rentLiabilityData->owner_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 72 || $head == 73 || $head == 76 || $head == 143) {
                        // TA Advanced
                        $employeeData = Employee::where('id', $request['contact'][$key])->first();
                        $jvtype = 7;
                        $jv_sub_type = NULL;
                        $contacttype = 7;
                        $contectId = $employeeData->id;
                        $jv_type_id = $employeeData->id;
                        if ($head == 143) {
                            $type = 6;
                            $sub_type = 64;
                        } else {
                            $type = 13;
                            $sub_type = 138;
                        }
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $employeeData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $employeeData->id;
                        $amount_to_name = $employeeData->employee_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 75) {
                        // Rent Crediotrs
                        $rentLiabilityData = RentLiability::where('id', $request['contact'][$key])->first();
                        $jvtype = 8;
                        $jv_sub_type = NULL;
                        $contacttype = 8;
                        $contectId = $rentLiabilityData->id;
                        $jv_type_id = $rentLiabilityData->id;
                        $type = 10;
                        $sub_type = 106;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $rentLiabilityData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $rentLiabilityData->id;
                        $amount_to_name = $rentLiabilityData->owner_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 87) {
                        // Commission
                        $associateData = getMemberData($request['contact'][$key]);
                        $jvtype = 9;
                        $jv_sub_type = NULL;
                        $contacttype = 9;
                        $contectId = $associateData->id;
                        $jv_type_id = $associateData->id;
                        $type = 2;
                        $sub_type = 22;
                        $branch_id = $request['branch'];
                        $associate_id = $associateData->id;
                        $member_id = $associateData->id;
                        $branch_id_to = $associateData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = $associateData->branch_id;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $associateData->id;
                        $amount_to_name = $associateData->first_name . ' ' . $associateData->last_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 88) {
                        // Fuel Charge
                        $associateData = getMemberData($request['contact'][$key]);
                        $jvtype = 9;
                        $jv_sub_type = NULL;
                        $contacttype = 9;
                        $contectId = $associateData->id;
                        $jv_type_id = $associateData->id;
                        $type = 2;
                        $sub_type = 23;
                        $branch_id = $request['branch'];
                        $associate_id = $associateData->id;
                        $member_id = $associateData->id;
                        $branch_id_to = $associateData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = $associateData->branch_id;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $associateData->id;
                        $amount_to_name = $associateData->first_name . ' ' . $associateData->last_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 63) {
                        // Fuel Charge
                        $associateData = getMemberData($request['contact'][$key]);
                        $jvtype = 9;
                        $jv_sub_type = NULL;
                        $contacttype = 9;
                        $contectId = $associateData->id;
                        $jv_type_id = $associateData->id;
                        $type = 9;
                        $sub_type = 91;
                        $branch_id = $request['branch'];
                        $associate_id = $associateData->id;
                        $member_id = $associateData->id;
                        $branch_id_to = $associateData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = $associateData->branch_id;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $associateData->id;
                        $amount_to_name = $associateData->first_name . ' ' . $associateData->last_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 55) {
                        // MEMBERSHIP FEES
                        $memberData = getMemberData($request['contact'][$key]);
                        $jvtype = 4;
                        $jv_sub_type = 43;
                        $contacttype = 3;
                        $contectId = $memberData->id;
                        $jv_type_id = $memberData->id;
                        $type = 1;
                        $sub_type = 15;
                        $branch_id = $request['branch'];
                        $associate_id = $memberData->associate_id;
                        $member_id = $memberData->id;
                        $branch_id_to = $memberData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($memberData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $memberData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($parentId->parent_id == 15) {
                        // Share Holder
                        $shareHolderData = ShareHolder::where('id', $request['contact'][$key])->first();
                        $jvtype = 10;
                        $jv_sub_type = NULL;
                        $contacttype = 10;
                        $contectId = $shareHolderData->id;
                        $jv_type_id = $shareHolderData->id;
                        $type = 16;
                        $sub_type = 163;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = $shareHolderData->member_id;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $shareHolderData->member_id;
                        $amount_to_name = $shareHolderData->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($parentId->parent_id == 19) {
                        // Director
                        $shareHolderData = ShareHolder::where('id', $request['contact'][$key])->first();
                        $jvtype = 11;
                        $jv_sub_type = NULL;
                        $contacttype = 11;
                        $contectId = $shareHolderData->id;
                        $jv_type_id = $shareHolderData->id;
                        $type = 15;
                        $sub_type = 153;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = $shareHolderData->member_id;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $shareHolderData->member_id;
                        $amount_to_name = $shareHolderData->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($parentId->parent_id == 18) {
                        // Loan From Banks
                        $bankData = LoanFromBank::where('id', $request['contact'][$key])->first();
                        $jvtype = 12;
                        $jv_sub_type = NULL;
                        $contacttype = 12;
                        $contectId = $bankData->id;
                        $jv_type_id = $bankData->id;
                        $type = 17;
                        $sub_type = 173;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $bankData->id;
                        $amount_to_name = $bankData->bank_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } else {
                        // Loan From Banks
                        if ($request['sub_head1'][$key] == 9) {
                            $type = 19;
                            $sub_type = 193;
                            $jvtype = 14;
                            $contacttype = 14;
                            $jv_sub_type = NULL;
                        } elseif ($head == 30) {
                            $type = 24;
                            $sub_type = 241;
                            $jvtype = 13;
                            $contacttype = 13;
                            $jv_sub_type = NULL;
                        } elseif ($head == 49) {
                            $type = 25;
                            $sub_type = 252;
                            $jvtype = 15;
                            $jv_sub_type = NULL;
                            $contacttype = 15;
                        } else {
                            $type = 25;
                            $sub_type = 251;
                            $jvtype = 15;
                            $jv_sub_type = NULL;
                            $contacttype = 15;
                        }
                        $contectId = NULL;
                        $jv_type_id = NULL;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    }
                    if ($head == 37) {
                        $contectId = $request['contact'][$key]; 
                    }
                    $payment_mode = 6;
                    $currency_code = 'INR';
                    $description = $request['description'][$key];
                    $account_id = NULL;
                    $created_by = 1;
                    $created_by_id = 1;
                    $jvheaddata['jv_journal_id'] = $type_id;
                    $jvheaddata['daybook_ref_id'] = $dayBookRef;
                    $jvheaddata['type'] = $jvtype;
                    $jvheaddata['company_id'] = $companyId;
                    $jvheaddata['type_id'] = $jv_type_id;
                    $jvheaddata['sub_type'] = $jv_sub_type;
                    $jvheaddata['head_id'] = $head;
                    $jvheaddata['contact_type'] = $contacttype;
                    $jvheaddata['contact_id'] = $contectId;
                    $jvheaddata['description'] = $request['description'][$key];
                    $jvheaddata['debits_amount'] = $dr_amount;
                    $jvheaddata['credits_amount'] = $cr_amount;
                    $jvheaddata['created_at'] = date("Y-m-d", strtotime(convertDate($request['cre_date'])));
                    $jvheadres = JvJournalHeads::create($jvheaddata);
                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    $jv_unique_id = "";
                    for ($i = 0; $i < 10; $i++) {
                        $jv_unique_id .= $chars[mt_rand(0, strlen($chars) - 1)];
                    }
                    if ($head == 32 || $head == 61 || $head == 72 || $head == 143 || $head == 73 || $head == 76) {
                        // employee ledger
                        $employeeLeaser = $this->employeeLeaser($employeeData->id, $branch_id, 5, NULL, $amount, $amount, $amount, $description, $currency_code, $payment_type, 4, 1, $created_at, $created_at, $jv_unique_id, $request['reference'], $v_date = NULL, $ssb_account_id_to = NULL, $ssb_account_id_from = NULL, $to_bank_name = NULL, $to_bank_branch = NULL, $to_bank_ac_no = NULL, $to_bank_ifsc = NULL, $to_bank_id = NULL, $to_bank_account_id = NULL, $from_bank_name = NULL, $from_bank_branch = NULL, $from_bank_ac_no = NULL, $from_bank_ifsc = NULL, $from_bank_id = NULL, $from_bank_ac_id = NULL, $cheque_id = NULL, $cheque_no = NULL, $cheque_date = NULL, $transaction_no = NULL, $transaction_date, $transaction_charge = NULL, $type_id, $companyId);
                        $type_transaction_id = $employeeLeaser;
                    } elseif ($head == 60 || $head == 74 || $head == 75 || $head == 144) {
                        // Rent ledger
                        $rentLeaser = $this->RentLiabilityLedger($rentLiabilityData->id, 4, NULL, $amount, $amount, $amount, $description, $currency_code, $payment_type, 4, 1, $created_at, $created_at, $request['reference'], $v_no = NULL, $v_date = NULL, $ssb_account_id_to = NULL, $ssb_account_id_from = NULL, $to_bank_name = NULL, $to_bank_branch = NULL, $to_bank_ac_no = NULL, $to_bank_ifsc = NULL, $to_bank_id = NULL, $to_bank_account_id = NULL, $from_bank_name = NULL, $from_bank_branch = NULL, $from_bank_ac_no = NULL, $from_bank_ifsc = NULL, $from_bank_id = NULL, $from_bank_ac_id = NULL, $cheque_id = NULL, $cheque_no = NULL, $cheque_date = NULL, $transaction_no = NULL, $transaction_date, $transaction_charge = NULL, $type_id, $companyId);
                        $type_transaction_id = $rentLeaser;
                    } elseif ($head == 140) {
                        // Vendor Transaction
                        $vendorTrans = $this->VendorTransaction(4, NULL, $type_id, NULL, $vendorData->id, $branch_id, NULL, NULL, $amount, $description, $payment_type, 6, 'INR', $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $companyId);
                        $type_transaction_id = $vendorTrans;
                    } elseif ($head == 142) {
                        // Customer Transaction
                        $customerTrans = $this->CustomerTransaction(3, NULL, $type_id, NULL, $customerData->id, $branch_id, NULL, NULL, $amount, $description, $payment_type, 6, 'INR', $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $companyId);
                        $type_transaction_id = $customerTrans;
                    } elseif ($parentId->parent_id == 167) {
                        // Credit Card Transaction
                        $associateTrans = $this->creditCard(2, NULL, $type_id, NULL, $creditCard->id, $branch_id, NULL, NULL, $amount, $description, $payment_type, 6, 'INR', $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $companyId);
                        $type_transaction_id = $associateTrans;
                    } elseif ($head == 234) {
                        // Credit Card Transaction
                        $associateTrans = $this->companyBond($dayBookRef, $bound, $entry_date, $amount, 0, Null, NULL, NULL, NULL, $payment_type, $type_id, 6, $description, $head, $companyId);
                    }
                    // elseif($head == 233  ){
                    //     // Credit Card Transaction
                    //     $associateTrans = $this->companyBond($dayBookRef,$bound,$entry_date,$amount,1,Null,NULL,NULL,NULL,$payment_type,$type_id,6,$description,$head);    
                    // }
                    elseif ($parentId->parent_id == 236) {
                        // Credit Card Transaction
                        $associateTrans = $this->companyBond($dayBookRef, $bound, $entry_date, 0, 0, $amount, NULL, NULL, $head, $payment_type, $type_id, 6, $description, $head, $companyId);
                    }
                    if ($head == 68 || $head == 69 || $head == 70 || $head == 91 || $head == 27 || $parentId->parent_id == 27) {
                        // Head Transacion
                        $allTranRDcheque = CommanController::createAllHeadTransactionModify(
                            $dayBookRef,
                            $branch_id,
                            $bank_id = NULL,
                            $bank_ac_id = NULL,
                            $head,
                            $type,
                            $sub_type,
                            $type_id,
                            $type_transaction_id,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $amount,
                            $description,
                            $payment_type,
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id,
                            $v_no = NULL,
                            $ssb_account_id_from = NULL,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no = NULL,
                            $transction_no = NULL,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );
                    } else {
                        // Head Transacion
                        $allTranRDcheque = CommanController::createAllHeadTransactionModify($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $payment_type, $payment_mode, $currency_code, $jv_unique_id, $v_no = NULL, $ssb_account_id_from = NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, $created_by, $created_by_id, $companyId);
                    }
                    // Branch Day Book
                    if ($head == 28) {
                        if ($request['debit'][$key] > 0) {
                            $payment_type = 'CR';
                        } elseif ($request['credit'][$key] > 0) {
                            $payment_type = 'DR';
                        }
                    }
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id, $cheque_type = NULL, $cheque_id = NULL, $companyId);
                    if ($head == 68 || $head == 69 || $head == 70 || $head == 91) {
                        if ($request['debit'][$key] > 0) {
                            $payment_type = 'CR';
                        } elseif ($request['credit'][$key] > 0) {
                            $payment_type = 'DR';
                        }
                        $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef, $amount_to_id, $account_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $amount, $amount, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transaction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Journal Entry Created Successfully!');
    }
    public static function createDayBookNew($transaction_id, $satRefId, $transaction_type, $account_id, $associateId, $memberId, $openingBalance, $deposite, $withdrawal, $description, $referenceno, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type, $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $jv_type_id, $company_id)
    {
        $globaldate = Session::get('created_at');
        $entryTime = date("H:i:s");
        foreach ($amountArray as $key => $option) {
            $data_log['transaction_type'] = $transaction_type;
            $data_log['transaction_id'] = $transaction_id;
            $data_log['saving_account_transaction_reference_id'] = $satRefId;
            $data_log['investment_id'] = $account_id;
            $data_log['account_no'] = $account_no;
            $data_log['associate_id'] = $associateId;
            $data_log['member_id'] = $memberId;
            $data_log['opening_balance'] = $openingBalance;
            $data_log['deposit'] = $deposite;
            $data_log['withdrawal'] = $withdrawal;
            $data_log['description'] = $description;
            $data_log['reference_no'] = $referenceno;
            $data_log['branch_id'] = $branch_id;
            $data_log['branch_code'] = $branchCode;
            $data_log['amount'] = $option;
            $data_log['currency_code'] = 'INR';
            $data_log['payment_mode'] = $payment_mode;
            $data_log['payment_type'] = $payment_type;
            $data_log['company_id'] = $company_id;
            if ($payment_mode == 1 || $payment_mode == 2) {
                $data_log['cheque_dd_no'] = $cheque_dd_no;
                $data_log['bank_name'] = $bank_name;
                $data_log['branch_name'] = $branch_name;
                $data_log['received_cheque_id'] = $received_cheque_id;
                $data_log['cheque_deposit_bank_id'] = $cheque_deposit_bank_id;
                $data_log['cheque_deposit_bank_ac_id'] = $cheque_deposit_bank_ac_id;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }
            }
            if ($payment_mode == 3) {
                $data_log['online_payment_id'] = $online_payment_id;
                $data_log['online_payment_by'] = $online_payment_by;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }
                $data_log['online_deposit_bank_id'] = $online_deposit_bank_id;
                $data_log['online_deposit_bank_ac_id'] = $online_deposit_bank_ac_id;
            }
            if ($payment_mode == 4) {
                $data_log['saving_account_id'] = $saving_account_id;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }
            }
            $data_log['amount_deposit_by_name'] = $deposit_by_name;
            if ($deposit_by_id) {
                $data_log['amount_deposit_by_id'] = $deposit_by_id;
            }
            $data_log['created_by_id'] = Auth::user()->id;
            $data_log['created_by'] = 2;
            //$data_log['created_at']=date("Y-m-d h:i:s", strtotime($payment_date));
            //$data_log['created_at']=$globaldate;
            $data_log['jv_journal_id'] = $jv_type_id;
            if ($transaction_type == 16) {
                $data_log['created_at'] = date("Y-m-d", strtotime(convertDate($globaldate)));
            } else {
                $data_log['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            }
            $transcation = Daybook::create($data_log);
            $tran_id = $transcation->id;
        }
        return $tran_id;
    }
    public static function createLoanDayBook($loan_type, $loan_sub_type, $loan_id, $group_loan_id, $account_number, $applicant_id, $jv_amount, $roi_amount, $principal_amount, $opening_balance, $deposit, $description, $branch_id, $branch_code, $payment_type, $currency_code, $payment_mode, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $created_by, $status, $cheque_date, $bank_account_number, $online_payment_by, $amount_deposit_by_name, $associate_id, $amount_deposit_by_id, $jv_type_id, $company_id)
    {
        $globaldate = Session::get('created_at');
        $data['loan_type'] = $loan_type;
        $data['company_id'] = $company_id;
        $data['loan_sub_type'] = $loan_sub_type;
        $data['loan_id'] = $loan_id;
        $data['group_loan_id'] = $group_loan_id;
        $data['account_number'] = $account_number;
        $data['applicant_id'] = $applicant_id;
        $data['associate_id'] = $associate_id;
        $data['jv_journal_amount'] = $jv_amount;
        $data['roi_amount'] = $roi_amount;
        $data['principal_amount'] = $principal_amount;
        $data['opening_balance'] = $opening_balance;
        $data['deposit'] = $deposit;
        $data['description'] = $description;
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branch_code;
        $data['payment_type'] = $payment_type;
        $data['currency_code'] = $currency_code;
        $data['payment_mode'] = $payment_mode;
        $data['payment_date'] = date("Y-m-d", strtotime(convertDate($payment_date)));
        $data['created_by'] = $created_by;
        $data['status'] = $status;
        $data['created_at'] = $globaldate;
        if ($payment_mode == 1 || $payment_mode == 2) {
            $data['cheque_dd_id'] = $cheque_dd_no;
            $data['cheque_date'] = date("Y-m-d", strtotime(convertDate($cheque_date)));
            $data['bank_id'] = $bank_name;
            $data['bank_account_number'] = $bank_account_number;
            $data['branch_name'] = $branch_name;
        }
        if ($payment_mode == 3) {
            $data['online_payment_id'] = $online_payment_id;
        }
        $data['online_payment_by'] = $online_payment_by;
        $data['amount_deposit_by_name'] = $amount_deposit_by_name;
        $data['amount_deposit_by_id'] = $amount_deposit_by_id;
        $data['jv_journal_id'] = $jv_type_id;
        $loadDayBook = LoanDayBooks::create($data);
        $loaddaybook_id = $loadDayBook->id;
        return $loaddaybook_id;
    }
    public static function employeeLeaser($employee_id, $branch_id, $type, $type_id, $opening_balance, $deposit, $withdrawal, $description, $currency_code, $payment_type, $payment_mode, $status, $created_at, $updated_at, $v_no, $jv_unique_id, $v_date, $ssb_account_id_to, $ssb_account_id_from, $to_bank_name, $to_bank_branch, $to_bank_ac_no, $to_bank_ifsc, $to_bank_id, $to_bank_account_id, $from_bank_name, $from_bank_branch, $from_bank_ac_no, $from_bank_ifsc, $from_bank_id, $from_bank_ac_id, $cheque_id, $cheque_no, $cheque_date, $transaction_no, $transaction_date, $transaction_charge, $jv_type_id, $company_id = null)
    {
        $data['employee_id'] = $employee_id;
        $data['branch_id'] = $branch_id;
        $data['type'] = $type;
        $data['type_id'] = $type_id;
        $data['opening_balance'] = $opening_balance;
        $data['deposit'] = $deposit;
        $data['withdrawal'] = $withdrawal;
        $data['description'] = $description;
        $data['currency_code'] = $currency_code;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['status'] = $status;
        $data['created_at'] = $created_at;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['to_bank_name'] = $to_bank_name;
        $data['to_bank_branch'] = $to_bank_branch;
        $data['to_bank_ac_no'] = $to_bank_ac_no;
        $data['to_bank_ifsc'] = $to_bank_ifsc;
        $data['to_bank_id'] = $to_bank_id;
        $data['to_bank_account_id'] = $to_bank_account_id;
        $data['from_bank_name'] = $from_bank_name;
        $data['from_bank_branch'] = $from_bank_branch;
        $data['from_bank_ac_no'] = $from_bank_ac_no;
        $data['from_bank_ifsc'] = $from_bank_ifsc;
        $data['from_bank_id'] = $from_bank_id;
        $data['from_bank_ac_id'] = $from_bank_ac_id;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['transaction_no'] = $transaction_no;
        $data['transaction_date'] = $transaction_date;
        $data['transaction_charge'] = $transaction_charge;
        $data['company_id'] = $company_id;
        $data['jv_journal_id'] = $jv_type_id;
        $transcation = EmployeeLedger::create($data);
        return $transcation->id;
    }
    public static function RentLiabilityLedger($rent_liability_id, $type, $type_id, $opening_balance, $deposit, $withdrawal, $description, $currency_code, $payment_type, $payment_mode, $status, $created_at, $updated_at, $jv_unique_id, $v_no, $v_date, $ssb_account_id_to, $ssb_account_id_from, $to_bank_name, $to_bank_branch, $to_bank_ac_no, $to_bank_ifsc, $to_bank_id, $to_bank_account_id, $from_bank_name, $from_bank_branch, $from_bank_ac_no, $from_bank_ifsc, $from_bank_id, $from_bank_ac_id, $cheque_id, $cheque_no, $cheque_date, $transaction_no, $transaction_date, $transaction_charge, $jv_type_id, $company_id)
    {
        $data['rent_liability_id'] = $rent_liability_id;
        $data['type'] = $type;
        $data['type_id'] = $type_id;
        $data['opening_balance'] = $opening_balance;
        $data['deposit'] = $deposit;
        $data['withdrawal'] = $withdrawal;
        $data['description'] = $description;
        $data['currency_code'] = $currency_code;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['status'] = $status;
        $data['created_at'] = $created_at;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['to_bank_name'] = $to_bank_name;
        $data['to_bank_branch'] = $to_bank_branch;
        $data['to_bank_ac_no'] = $to_bank_ac_no;
        $data['to_bank_ifsc'] = $to_bank_ifsc;
        $data['to_bank_id'] = $to_bank_id;
        $data['to_bank_account_id'] = $to_bank_account_id;
        $data['from_bank_name'] = $from_bank_name;
        $data['from_bank_branch'] = $from_bank_branch;
        $data['from_bank_ac_no'] = $from_bank_ac_no;
        $data['from_bank_ifsc'] = $from_bank_ifsc;
        $data['from_bank_id'] = $from_bank_id;
        $data['from_bank_ac_id'] = $from_bank_ac_id;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['transaction_no'] = $transaction_no;
        $data['transaction_date'] = $transaction_date;
        $data['company_id'] = $company_id;
        $data['transaction_charge'] = $transaction_charge;
        $data['jv_journal_id'] = $jv_type_id;
        $transcation = RentLiabilityLedger::create($data);
        return $transcation->id;
    }
    public function designationListing(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            /******* fillter query start ****/
            $data  = JvJournals::has('company')->select('id', 'jv_auto_id', 'reference', 'status', 'notes', 'created_at', 'branch_id', 'company_id')
                ->with([
                    'Branch:id,name',
                    'company:id,name',
                    'jvJournalHeads:id,credits_amount,debits_amount,jv_journal_id'
                ])
                ->where('status', 1);
                if ($arrFormData['is_search'] == 'yes') {
                  if ($arrFormData['company_id'] > 0) {
                    $data = $data->where('company_id',$arrFormData['company_id']);
                  }
                  if ($arrFormData['branch_id'] > 0) {
                    $data = $data->where('branch_id',$arrFormData['branch_id']);
                  }
                }
            $count = $data->count("id");
            $data = $data->orderby('id', 'DESC')
                ->offset($_POST['start'])
                ->limit($_POST['length'])
                ->get();
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['journal'] = $row->jv_auto_id;
                $val['company'] = isset($row['company']->name) ? $row['company']->name : "";
                $val['branch'] = $row['Branch']->name;
                $val['reference_number'] = $row->reference;
                if ($row->status == 1) {
                    $val['status'] = 'Active';
                } else {
                    $val['status'] = 'Disable';
                }
                $val['status'] = $sno;
                $val['notes'] = $row->notes;
                $totaldebit = $row['jvJournalHeads']->sum('debits_amount'); //JvJournalHeads::where('jv_journal_id',$row->id)->sum('debits_amount');
                $val['debit'] = number_format($totaldebit, 2);
                $totalcredit = $row['jvJournalHeads']->sum('credits_amount'); // JvJournalHeads::where('jv_journal_id',$row->id)->sum('credits_amount');
                $val['credit'] = number_format($totalcredit, 2);
                $val['created'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                //$url = URL::to("admin/hr/designation/delete/".$row->id."");  
                $url1 = URL::to("admin/jv/edit/" . $row->id . "");
                $ur2 = URL::to("admin/jv/delete/" . $row->id . "");
                if (check_my_permission(Auth::user()->id, "151") == "1") {
                    $btn .= '<a class="dropdown-item" href="' . $url1 . '" title="JV Edit"><i class="icon-pencil7  mr-2"></i> Edit</a>  ';
                }
                if (check_my_permission(Auth::user()->id, "152") == "1") {
                    $btn .= '<a class="dropdown-item delete-jv-journal" href="' . $ur2 . '" title="JV Delete" ><i class="icon-trash-alt  mr-2"></i> Delete</a>  ';
                }
                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
        }
    }
    public function edit($id)
    {
        if (check_my_permission(Auth::user()->id, "151") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Edit Journal Entry';
        $data['branches'] = Branch::where('status', 1)->get();
        $data['account_heads'] = AccountHeads::where('labels', 1)->where('status', 0)->get();
        $data['jv'] = JvJournals::with('jvJournalHeads')->where('id', $id)->first();
        $data['debitAmount'] = JvJournalHeads::where('jv_journal_id', $data['jv']->id)->sum('debits_amount');
        $data['creditAmount'] = JvJournalHeads::where('jv_journal_id', $data['jv']->id)->sum('credits_amount');
        return view('templates.admin.jv_management.edit', $data);
    }
    public function updateJV(Request $request)
    {
        DB::beginTransaction();
        try {
            $companyId = $request['company_id'];
            $jvData = JvJournals::where('id', $request['id'])->where('is_deleted', 0)->first();
            $jvHeadData = JvJournalHeads::where('jv_journal_id', $request['id'])->get();
            foreach ($jvHeadData as $key => $value) {
                if (isset($value->daybook_ref_id)  && $value->type == 5 && $value->type == 19) {
                    $deleteAllHeadTransaction = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->delete();
                    $deleteBranchDaybook = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->delete();
                    if ($value->type == 5) {
                        $deleteSamDaybook = SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->delete();
                    }
                    $deleteCompanyBond = CompanyBoundTransaction::where('daybook_ref_id', $value->daybook_ref_id)->delete();
                } else {
                    // saving account transaction delete
                    $savingAccountTranscation = SavingAccountTranscation::whereIN('type', [12, 13])->where('jv_journal_id', $request['id'])->delete();
                    // day book transaction delete
                    $dayBookTranscation = Daybook::whereIN('transaction_type', [20, 21, 22])->where('jv_journal_id', $request['id'])->delete();
                    // Loan day book transaction delete
                    $loanDayBookTranscation = LoanDayBooks::whereIN('loan_sub_type', [2, 3])->where('jv_journal_id', $request['id'])->delete();
                    // // transaction delete
                    // $ranscation = Transcation::whereIN('transaction_type', [19, 20, 21])->where('jv_journal_id', $request['id'])->delete();
                    // // transaction log delete
                    // $ranscationLog = TranscationLog::whereIN('transaction_type', [19, 20, 21])->where('jv_journal_id', $request['id'])->delete();
                    // employee ledger delete
                    $employeeLeaser = EmployeeLedger::where('type', 5)->where('jv_journal_id', $request['id'])->delete();
                    //Rent Ledger delete    
                    $rentLeaser = RentLiabilityLedger::where('type', 4)->where('jv_journal_id', $request['id'])->delete();
                    //Vendor Transaction delete
                    $vendorTransaction  = VendorTransaction::where('type', 4)->where('type_id', $request['id'])->delete();
                    // Customer Transaction delete
                    $CustomerTransaction = CustomerTransaction::where('type', 3)->where('type_id', $request['id'])->delete();
                    // Credit card Transaction Delete
                    $creditcard = CreditCradTransaction::where('type', 2)->where('type_id', $request['id'])->delete();
                    //All head transaction
                    $headData = AllHeadTransaction::whereIN('sub_type', [13, 15, 22, 23, 38, 39, 311, 312, 313, 412, 413, 414, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 62, 63, 64, 91, 105, 106, 137, 138, 173, 174, 193, 153, 163, 222, 232, 241, 251, 252, 314, 24, 273, 274, 281])->where('type_id', $request['id'])->delete();
                    // Branch Day Book delete
                    $branchDayBook = BranchDaybook::whereIN('sub_type', [13, 15, 22, 23, 38, 39, 311, 312, 313, 412, 413, 414, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 62, 63, 64, 91, 105, 106, 137, 138, 173, 174, 193, 153, 163, 222, 232, 241, 251, 252, 314, 24, 273, 274, 281])->where('type_id', $request['id'])->delete();
                    // Samradhh Bank Day Book delete
                    $samraddhBankDaybook = SamraddhBankDaybook::whereIN('sub_type', [13, 15, 22, 23, 38, 39, 311, 312, 313, 412, 413, 414, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 62, 63, 64, 91, 105, 106, 137, 138, 173, 174, 193, 153, 163, 222, 232, 241, 251, 252, 314])->where('type_id', $request['id'])->delete();
                }
                //Jv journal head
                $jvheadres = JvJournalHeads::where('jv_journal_id', $request['id'])->delete();
            }
            $jvResult = JvJournals::find($request['id']);
            $jvFieldData['branch_id'] = $request['branch'];
            $jvFieldData['date'] = date("Y-m-d", strtotime(convertDate($request['cre_date'])));
            $jvFieldData['reference'] = $request['reference'];
            $jvFieldData['notes'] = $request['notes'];
            $jvFieldData['company_id'] = $request['company_id'];
            $jvResult->update($jvFieldData);
            $type_id = $request['id'];
            $amount = Collection::make($request['credit'])->filter(function ($value) { return $value !== null;})->sum();
            $dayBookRef = CommanController::createBranchDayBookReference($amount);
            foreach ($request['account_head'] as $key => $value) {
                if ($request['account_head'][$key]) {
                    $entryTime = $entry_time = date("H:i:s");
                    Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['cre_date']))));
                    if ($request['debit'][$key] > 0) {
                        $payment_type = 'DR';
                        $amount = $request['debit'][$key];
                        $dr_amount = $request['debit'][$key];
                        $cr_amount = NULL;
                        $description_dr = 'JV Journal Transfer DR ' . $amount;
                        $description_cr = NULL;
                    } elseif ($request['credit'][$key] > 0) {
                        $payment_type = 'CR';
                        $amount = $request['credit'][$key];
                        $dr_amount = NULL;
                        $cr_amount = $request['credit'][$key];
                        $description_cr = 'JV Amount Transfer CR ' . $amount;
                        $description_dr = NULL;
                    }
                    $transaction_date = $created_at = date("Y-m-d", strtotime(convertDate($request['cre_date'])));
                    $entry_date = date("Y-m-d", strtotime(convertDate($request['cre_date'])));
                    if ($request['sub_head4'][$key]) {
                        $head = $request['sub_head4'][$key];
                    } elseif ($request['sub_head3'][$key]) {
                        $head = $request['sub_head3'][$key];
                    } elseif ($request['sub_head2'][$key]) {
                        $head = $request['sub_head2'][$key];
                    } elseif ($request['sub_head1'][$key]) {
                        $head = $request['sub_head1'][$key];
                    } elseif ($request['account_head'][$key]) {
                        $head = $request['account_head'][$key];
                    }
                    $acoountHeadId = $request['account_head'][$key];
                    $parentId = AccountHeads::where('head_id', $head)->first('parent_id');
                    // Saving Heads
                    if ($head == 56 || $parentId->parent_id == 406 || $parentId->parent_id == 403) {
                        $sAccount = getSavingAccountMemberId($request['contact'][$key]);
                        $contectId = $sAccount->id;
                        $jvtype = 3;
                        $jv_sub_type = NULL;
                        $contacttype = 4;
                        $jv_type_id = $sAccount->id;
                        $type = 4;
                        $sub_type = 412;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $sAccount->associate_id;
                        $member_id = $sAccount->member_id;
                        $branch_id_to = $sAccount->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($sAccount->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $sAccount->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        if ($request['debit'][$key]) {
                            $ssbAccountAmount = $sAccount->balance - $amount;
                        } elseif ($request['credit'][$key]) {
                            $ssbAccountAmount = $sAccount->balance + $amount;
                        }
                        $ssb_id = $collectionSSBId = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        // $record1 = SavingAccountTransactionView::where('account_no', $sAccount->account_no)->where('opening_date', '<', date("Y-m-d", strtotime(convertDate($request['cre_date']))))->orderby('opening_date', 'desc')->first();
                        // if ($record1) {
                        //     if ($request['debit'][$key]) {
                        //         if ($record1->opening_balance > $amount) {
                        //             $ssb['opening_balance'] = number_format( ($record1->opening_balance - $amount),2);
                        //         } else {
                        //             throw new \Exception("Insufficient balance in account no".$sAccount->account_no);
                        //         }
                        //     } elseif ($request['credit'][$key]) {
                        //         $ssb['opening_balance'] = $record1->opening_balance + $amount;
                        //     }
                        // } else {
                        //     if ($request['debit'][$key]) {
                        //         if ($sAccount->balance > $amount) {
                        //             $ssb['opening_balance']  = $ssbAccountAmount;
                        //         } else {
                        //             throw new \Exception("Insufficient balance in account no".$sAccount->account_no);
                        //         }
                        //     } elseif ($request['credit'][$key]) {
                        //         $ssb['opening_balance']  = $sAccount->balance + $amount;
                        //     }
                        // }
                        $record1 = SavingAccountTransactionView::where('account_no', $sAccount->account_no)->whereDate('opening_date', '<=', date("Y-m-d", strtotime(convertDate($request['cre_date']))))->orderby('opening_date', 'desc')->first();
                        if ($record1) {
                            if ($request['debit'][$key]) {
                                if ($record1->opening_balance > $amount) {
                                    $ssb['opening_balance'] = $record1->opening_balance - $amount;
                                } else {
                                    throw new \Exception("Insufficient balance in account no".$sAccount->account_no);
                                }
                            } elseif ($request['credit'][$key]) {
                                $ssb['opening_balance'] = $record1->opening_balance + $amount;
                            }
                        } else {
                            if ($request['debit'][$key]) {
                                if ($sAccount->balance > $amount) {
                                    $ssb['opening_balance']  = $ssbAccountAmount;
                                } else {
                                    throw new \Exception("Insufficient balance in account no".$sAccount->account_no);
                                }
                            } elseif ($request['credit'][$key]) {
                                $ssb['opening_balance']  = $sAccount->balance + $amount;
                            }
                        }
                        if ($request['debit'][$key]) {
                            $ssb['withdrawal'] = $amount;
                            $ssb['payment_type'] = 'DR';
                            $ssb['type'] = 12;
                            $ssb['deposit'] = NULL;
                            $deposit = $amount;
                            $withdrawal = NULL;
                        } elseif ($request['credit'][$key]) {
                            $ssb['deposit'] = $amount;
                            $ssb['payment_type'] = 'CR';
                            $ssb['type'] = 13;
                            $ssb['withdrawal'] = NULL;
                            $withdrawal = $amount;
                            $deposit = NULL;
                        }
                        if ($request['branch'] != $sAccount->branch_id) {
                            $branchName = getBranchDetail($request['branch'])->name;
                            // $ssb['description'] ='JV Journal Transfer To '.$sAccount->account_no.'- From '.$branchName.'';
                            // $description = 'JV Journal Transfer To '.$sAccount->account_no.'- From '.$branchName.'';
                            $ssb['description'] = $request['description'][$key];
                            $description  = $request['description'][$key];
                        } else {
                            // $ssb['description'] = 'JV Journal Transfer To '.$sAccount->account_no;  
                            // $description = 'JV Journal Transfer To '.$sAccount->account_no; 
                            $ssb['description'] = $request['description'][$key];
                            $description  = $request['description'][$key];
                        }
                        $ssb['reference_no'] = $request['reference'];
                        $ssb['associate_id'] = $sAccount->associate_id;
                        $ssb['branch_id'] = $request['branch'];
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_mode'] = 6;
                        $ssb['is_renewal'] = 0;
                        $ssb['jv_journal_id'] = $type_id;
                        $ssb['company_id'] = $companyId;
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['cre_date'])));
                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                        $ssbAccountTranFromId = $type_transaction_id = $ssbAccountTran->id;
                        updateSavingAccountTransaction($sAccount->id, $sAccount->account_no);
                        if ($sAccount->member_investments_id > 0) {
                            $investmentData = getInvestmentDetails($sAccount->member_investments_id);
                            $lastAmount = Daybook::where('investment_id', $investmentData->id)->where('account_no', $investmentData->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->orderby('id', 'desc')->first();
                            if ($lastAmount) {
                                $lastBalance = $lastAmount->opening_balance + $amount;
                            } else {
                                $lastBalance = $amount;
                            }
                            $satRefId = null;
                            $createTransaction = NULL;
                            $amountArraySsb = array('1' => $amount);
                            $createDayBook = $this->createDayBookNew($createTransaction, $satRefId, 2, $investmentData->id, $investmentData->id, $investmentData->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $sAccount->branch_id, getBranchCode($sAccount->branch_id)->branch_code, $amountArraySsb, 6, NULL, $sAccount->member_id, $investmentData->account_number, NULL, NULL, $amount_from_name, $request['cre_date'], NULL, $online_payment_by = NULL, $sAccount->id, $payment_type, $received_cheque_id = NULL, $cheque_deposit_bank_id = NULL, $cheque_deposit_bank_ac_id = NULL, $online_deposit_bank_id = NULL, $online_deposit_bank_ac_id = NULL, $type_id,$companyId);
                        }
                    } elseif ($head == 64 || $head == 65 || $head == 67 || $head == 66) {
                        // Loan Heads
                        if ($head == 66) {
                            $loan = getGroupLoanDetailById($request['contact'][$key]);
                            $sub_type = 512;
                            $jv_sub_type = 13;
                            $transaction_type = 29;
                            $day_transaction_type = 30;
                            $loan_id = $loan->member_loan_id;
                            $group_loan_id = $loan->id;
                        } else {
                            $loan = getLoanDetail($request['contact'][$key]);
                            $sub_type = 511;
                            $jv_sub_type = 11;
                            $transaction_type = 28;
                            $day_transaction_type = 29;
                            $loan_id = $loan->id;
                            $group_loan_id = NULL;
                        }
                        $contectId = $loan_id;
                        $jvtype = 1;
                        $contacttype = 1;
                        $jv_type_id = $loan_id;
                        $type = 5;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArray = array('1' => $amount);
                        $createTran = null;
                        $lastAmount = Daybook::where('loan_id', $loan->id)->where('account_no', $loan->account_number)->orderby('id', 'desc')->first();
                        if ($request['debit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance + $amount;
                            $deposit = $amount;
                            $withdrawal = NULL;
                        } elseif ($request['credit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance - $amount;
                            $withdrawal = $amount;
                            $deposit = NULL;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTran, NULL, $day_transaction_type, $loan->id, $loan->associate_member_id, $loan->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $amountArray, 6, NULL, $loan->applicant_id, $loan->account_number, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                        $loanType = $loan->loan_type;
                        $createLoanDayBook = $this->createLoanDayBook($loanType, 4, $loan_id, $group_loan_id, $loan->account_number, $loan->applicant_id, $amount, NULL, NULL, NULL, NULL, $description, $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $payment_type, 'INR', 6, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, 1, 1, NULL, NULL, NULL, NULL, $loan->associate_member_id, $loan->applicant_id, $type_id, $companyId);
                        $type_transaction_id = $createDayBook;
                    } elseif ($head == 33) {
                        // Loan panelty
                        $loan = getLoanDetail($request['contact'][$key]);
                        if ($loan) {
                            $sub_type = 513;
                            $jv_sub_type = 12;
                            $transaction_type = 26;
                            $day_transaction_type = 27;
                            $loan_id = $loan->id;
                            $group_loan_id = NULL;
                        } else {
                            $loan = getGroupLoanDetailById($request['contact'][$key]);
                            $sub_type = 514;
                            $jv_sub_type = 14;
                            $transaction_type = 27;
                            $day_transaction_type = 28;
                            $loan_id = $loan->member_loan_id;
                            $group_loan_id = $loan->id;
                        }
                        $contectId = $loan_id;
                        $jvtype = 1;
                        $contacttype = 1;
                        $jv_type_id = $loan_id;
                        $type = 5;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArray = array('1' => $amount);
                        $createTran = null;
                        $lastAmount = Daybook::where('loan_id', $loan->id)->where('account_no', $loan->account_number)->orderby('id', 'desc')->first();
                        if ($request['debit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance - $amount;
                            $deposit = NULL;
                            $withdrawal = $amount;
                        } elseif ($request['credit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance + $amount;
                            $withdrawal = NULL;
                            $deposit = $amount;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTran, NULL, $day_transaction_type, $loan->id, $loan->associate_member_id, $loan->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $amountArray, 6, NULL, $loan->applicant_id, $loan->account_number, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                        $loanType = $loan->loan_type;
                        $createLoanDayBook = $this->createLoanDayBook($loanType, 3, $loan_id, $group_loan_id, $loan->account_number, $loan->applicant_id, $amount, NULL, NULL, NULL, NULL, $description, $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $payment_type, 'INR', 6, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, 1, 1, NULL, NULL, NULL, NULL, $loan->associate_member_id, $loan->applicant_id, $type_id, $companyId);
                        $type_transaction_id = $createDayBook;
                    } elseif ($head == 234 || $head == 233 || $parentId->parent_id == 236) {
                        // Loan emi
                        $data = \App\Models\CompanyBound::where('id', $request['contact'][$key])->first();
                        if ($data) {
                            $sub_type = 302;
                            $transaction_type = 22;
                            $day_transaction_type = 23;
                            $loan_id = $data->id;
                            $group_loan_id = NULL;
                        }
                        $contectId = $data->id;
                        $jvtype = 19;
                        $jv_sub_type = 19;
                        $contacttype = 19;
                        $jv_type_id = $data->id;
                        $bound = $data->id;
                        $type = 30;
                        //$type_transaction_id = $data->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = NULL;
                        $toBranch = NULL;
                        $fromBranch = NULL;
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $amountArray = array('1' => $amount);
                        $type_transaction_id = NULL;
                    } elseif ($head == 31) {
                        // Loan emi
                        $loan = getLoanDetail($request['contact'][$key]);
                        if ($loan) {
                            $sub_type = 515;
                            $transaction_type = 22;
                            $day_transaction_type = 23;
                            $loan_id = $loan->id;
                            $group_loan_id = NULL;
                        } else {
                            $loan = getGroupLoanDetailById($request['contact'][$key]);
                            $sub_type = 516;
                            $transaction_type = 24;
                            $day_transaction_type = 25;
                            $loan_id = $loan->member_loan_id;
                            $group_loan_id = $loan->id;
                        }
                        $contectId = $loan_id;
                        $jvtype = 1;
                        $jv_sub_type = 11;
                        $contacttype = 1;
                        $jv_type_id = $loan_id;
                        $type = 5;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArray = array('1' => $amount);
                        /*$createTran = $this->createTransaction(NULL,$transaction_type,$loan->id,$loan->applicant_id,$loan->branch_id,getBranchCode($loan->branch_id)->branch_code,$amountArray,6,NULL,$loan->applicant_id,$loan->account_number,NULL,NULL,getBranchDetail($loan->branch_id)->name,date("Y-m-d", strtotime( str_replace('/','-',$request['cre_date'] ) ) ),NULL,NULL,NULL,$payment_type,$type_id);
                        $lastAmount = Daybook::where('loan_id',$loan->id)->where('account_no',$loan->account_number)->orderby('id','desc')->first();
                        if($request['debit'][$key]){
                            $lastBalance = $lastAmount->opening_balance-$amount;
                            $deposit = NULL;
                            $withdrawal = $amount;
                        }elseif($request['credit'][$key]){
                            $lastBalance = $lastAmount->opening_balance+$amount;
                            $withdrawal = NULL;
                            $deposit = $amount;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTran,NULL,$day_transaction_type,$loan->id,$loan->associate_member_id,$loan->member_id,$lastBalance,$deposit,$withdrawal,$description,$request['reference'],$loan->branch_id,getBranchCode($loan->branch_id)->branch_code,$amountArray,6,NULL,$loan->applicant_id,$loan->account_number,NULL,NULL,getBranchDetail($loan->branch_id)->name,date("Y-m-d", strtotime( str_replace('/','-',$request['cre_date'] ) ) ),NULL,NULL,NULL,$payment_type, NULL,NULL,NULL,NULL,NULL,$type_id);
                        $loanType = $loan->loan_type;
                        $createLoanDayBook = $this->createLoanDayBook($loanType,2,$loan_id,$group_loan_id,$loan->account_number,$loan->applicant_id,$amount,NULL,NULL,NULL,NULL,$description,$loan->branch_id,getBranchCode($loan->branch_id)->branch_code,$payment_type,'INR',6,NULL,NULL,getBranchDetail($loan->branch_id)->name,date("Y-m-d", strtotime( str_replace('/','-',$request['cre_date'] ) ) ),NULL,1,1,NULL,NULL,NULL,NULL,$loan->associate_member_id,$loan->applicant_id,$type_id);
                        $type_transaction_id = $createDayBook;*/
                        $type_transaction_id = NULL;
                    } elseif ($head == 90) {
                        // File Charage
                        $loan = getLoanDetail($request['contact'][$key]);
                        if ($loan) {
                            $sub_type = 519;
                            $transaction_type = 30;
                            $day_transaction_type = 31;
                            $loan_id = $loan->id;
                            $group_loan_id = NULL;
                            $jv_sub_type = 17;
                        } else {
                            $loan = getGroupLoanDetailById($request['contact'][$key]);
                            $sub_type = 520;
                            $transaction_type = 31;
                            $day_transaction_type = 32;
                            $loan_id = $loan->member_loan_id;
                            $group_loan_id = $loan->id;
                            $jv_sub_type = 18;
                        }
                        $contectId = $loan_id;
                        $jvtype = 1;
                        $contacttype = 1;
                        $jv_type_id = $loan_id;
                        $type = 5;
                        //$type_transaction_id = $loan->id;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $loan->associate_member_id;
                        $member_id = $loan->applicant_id;
                        $branch_id_to = $loan->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($loan->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $loan->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArray = array('1' => $amount);
                        $createTran = null;
                        $lastAmount = Daybook::where('loan_id', $loan->id)->where('account_no', $loan->account_number)->orderby('id', 'desc')->first();
                        if ($request['debit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance - $amount;
                            $deposit = NULL;
                            $withdrawal = $amount;
                        } elseif ($request['credit'][$key]) {
                            $lastBalance = $lastAmount->opening_balance + $amount;
                            $withdrawal = NULL;
                            $deposit = $amount;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTran, NULL, $day_transaction_type, $loan->id, $loan->associate_member_id, $loan->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $amountArray, 6, NULL, $loan->applicant_id, $loan->account_number, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                        $loanType = $loan->loan_type;
                        $createLoanDayBook = $this->createLoanDayBook($loanType, 5, $loan_id, $group_loan_id, $loan->account_number, $loan->applicant_id, $amount, NULL, NULL, NULL, NULL, $description, $loan->branch_id, getBranchCode($loan->branch_id)->branch_code, $payment_type, 'INR', 6, NULL, NULL, getBranchDetail($loan->branch_id)->name, date("Y-m-d", strtotime(str_replace('/', '-', $request['cre_date']))), NULL, 1, 1, NULL, NULL, NULL, NULL, $loan->associate_member_id, $loan->applicant_id, $type_id, $companyId);
                        $type_transaction_id = $createDayBook;
                    } elseif ($head == 97) {
                        // INTEREST ON LOAN TAKEN
                        $bankData = LoanFromBank::where('id', $request['contact'][$key])->first();
                        $jvtype = 12;
                        $jv_sub_type = NULL;
                        $contacttype = 12;
                        $contectId = $bankData->id;
                        $jv_type_id = $bankData->id;
                        $type = 17;
                        $sub_type = 174;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $bankData->id;
                        $amount_to_name = $bankData->bank_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 58 || $head == 77 || $head == 78 || $head == 79 || $head == 80 || $head == 81 || $head == 82 || $head == 83 || $head == 84 || $head == 85 || $head == 89 || $head == 139 || $head == 62) {
                        // Investment Heads
                        $investmentData = getInvestmentDetails($request['contact'][$key]);
                        if ($investmentData->plan_id == 1) {
                            $sAccount = getSavingAccountMemberId($request['contact'][$key]);
                            $contectId = $sAccount->id;
                            $jvtype = 2;
                            $jv_sub_type = NULL;
                            $contacttype = 2;
                            $jv_type_id = $sAccount->id;
                            $type = 3;
                            $sub_type = 38;
                            $branch_id = $request['branch'];
                            //$memberData = getMemberData($loan->associate_member_id);
                            $associate_id = $sAccount->associate_id;
                            $member_id = $sAccount->member_id;
                            $branch_id_to = $sAccount->branch_id;
                            $branch_id_from = $request['branch'];
                            $toBranch = getBranchDetail($sAccount->branch_id);
                            $fromBranch = getBranchDetail($request['branch']);
                            $amount_to_id = $sAccount->branch_id;
                            $amount_to_name = $toBranch->name;
                            $amount_from_id = $request['branch'];
                            $amount_from_name = $fromBranch->name;
                            if ($request['debit'][$key]) {
                                $ssbAccountAmount = $sAccount->balance - $amount;
                            } elseif ($request['credit'][$key]) {
                                $ssbAccountAmount = $sAccount->balance + $amount;
                            }
                            $ssb_id = $collectionSSBId = $sAccount->id;
                            $sResult = SavingAccount::find($ssb_id);
                            $sData['balance'] = $ssbAccountAmount;
                            $sResult->update($sData);
                            $ssb['saving_account_id'] = $ssb_id;
                            $ssb['account_no'] = $sAccount->account_no;
                            $record1 = SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<', date("Y-m-d", strtotime(convertDate($request['cre_date']))))->orderby('created_at', 'desc')->first();
                            if ($record1) {
                                if ($request['debit'][$key]) {
                                    $ssb['opening_balance'] = $record1->opening_balance - $amount;
                                } elseif ($request['credit'][$key]) {
                                    $ssb['opening_balance'] = $record1->opening_balance + $amount;
                                }
                            } else {
                                $ssb['opening_balance'] = $request['amount'][$key];
                            }
                            if ($request['debit'][$key]) {
                                $ssb['withdrawal'] = $amount;
                                $ssb['payment_type'] = 'DR';
                                $ssb['type'] = 12;
                                $ssb['deposit'] = NULL;
                                $deposit = $amount;
                                $withdrawal = NULL;
                            } elseif ($request['credit'][$key]) {
                                $ssb['deposit'] = $amount;
                                $ssb['payment_type'] = 'CR';
                                $ssb['type'] = 13;
                                $ssb['withdrawal'] = NULL;
                                $withdrawal = $amount;
                                $deposit = NULL;
                            }
                            if ($request['branch'] != $sAccount->branch_id) {
                                $branchName = getBranchDetail($request['branch'])->name;
                                // $ssb['description'] ='JV Journal Transfer To '.$sAccount->account_no.'- From '.$branchName.'';
                                $ssb['description'] = $request['description'][$key];
                                //$description = 'JV Journal Transfer To '.$sAccount->account_no.'- From '.$branchName.'';
                                $description = $request['description'][$key];
                            } else {
                                // $ssb['description'] = 'JV Journal Transfer To '.$sAccount->account_no;  
                                $ssb['description'] = $request['description'][$key];
                                //$description = 'JV Journal Transfer To '.$sAccount->account_no; 
                                $description = $request['description'][$key];
                            }
                            $ssb['reference_no'] = $request['reference'];
                            $ssb['associate_id'] = $sAccount->associate_id;
                            $ssb['branch_id'] = $request['branch'];
                            $ssb['currency_code'] = 'INR';
                            $ssb['payment_mode'] = 6;
                            $ssb['is_renewal'] = 0;
                            $ssb['jv_journal_id'] = $type_id;
                            $ssb['company_id'] = $companyId;
                            $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['cre_date'])));
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $ssbAccountTranFromId = $type_transaction_id = $ssbAccountTran->id;
                            updateSavingAccountTransaction($sAccount->id, $sAccount->account_no);
                            if ($sAccount->member_investments_id > 0) {
                                $investmentData = getInvestmentDetails($sAccount->member_investments_id);
                                $lastAmount = Daybook::where('investment_id', $investmentData->id)->where('account_no', $investmentData->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->orderby('id', 'desc')->first();
                                if ($lastAmount) {
                                    $lastBalance = $lastAmount->opening_balance + $amount;
                                } else {
                                    $lastBalance = $amount;
                                }
                                $satRefId = null;
                                $createTransaction = NULL;
                                $amountArraySsb = array('1' => $amount);
                                $createDayBook = $this->createDayBookNew($createTransaction, $satRefId, 1, $investmentData->id, $investmentData->id, $investmentData->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $sAccount->branch_id, getBranchCode($sAccount->branch_id)->branch_code, $amountArraySsb, 6, NULL, $sAccount->member_id, $investmentData->account_number, NULL, NULL, $amount_from_name, $request['cre_date'], NULL, $online_payment_by = NULL, $sAccount->id, $payment_type, $received_cheque_id = NULL, $cheque_deposit_bank_id = NULL, $cheque_deposit_bank_ac_id = NULL, $online_deposit_bank_id = NULL, $online_deposit_bank_ac_id = NULL, $type_id, $companyId);
                            }
                        } else {
                            $contectId = $investmentData->id;
                            $jvtype = 2;
                            $jv_sub_type = NULL;
                            $contacttype = 2;
                            $jv_type_id = $investmentData->id;
                            if ($head == 89) {
                                $type = 3;
                                $sub_type = 313;
                            } else {
                                $type = 3;
                                $sub_type = 38;
                            }
                            $branch_id = $request['branch'];
                            //$memberData = getMemberData($loan->associate_member_id);
                            $associate_id = $investmentData->associate_id;
                            $member_id = $investmentData->member_id;
                            $branch_id_to = $investmentData->branch_id;
                            $branch_id_from = $request['branch'];
                            $toBranch = getBranchDetail($investmentData->branch_id);
                            $fromBranch = getBranchDetail($request['branch']);
                            $amount_to_id = $investmentData->branch_id;
                            $amount_to_name = $toBranch->name;
                            $amount_from_id = $request['branch'];
                            $amount_from_name = $fromBranch->name;
                            $amountArraySsb = array('1' => $amount);
                            $createTransaction = null;
                            $lastAmount = Daybook::where('investment_id', $investmentData->id)->where('account_no', $investmentData->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->orderby('id', 'desc')->first();
                            if ($head == 89) {
                                if ($request['debit'][$key]) {
                                    $lastBalance = $lastAmount->opening_balance + $amount;
                                    $deposit = $amount;
                                    $withdrawal = NULL;
                                } elseif ($request['credit'][$key]) {
                                    $lastBalance = $lastAmount->opening_balance - $amount;
                                    $withdrawal = $amount;
                                    $deposit = NULL;
                                }
                            } else {
                                if ($request['debit'][$key]) {
                                    $lastBalance = $lastAmount->opening_balance - $amount;
                                    $deposit = NULL;
                                    $withdrawal = $amount;
                                } elseif ($request['credit'][$key]) {
                                    $lastBalance = $lastAmount->opening_balance + $amount;
                                    $withdrawal = NULL;
                                    $deposit = $amount;
                                }
                            }
                            $description = $request['description'][$key];
                            $createDayBook = $this->createDayBookNew($createTransaction, NULL, 20, $investmentData->id, $investmentData->associate_id, $investmentData->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $investmentData->branch_id, getBranchCode($investmentData->branch_id)->branch_code, $amountArraySsb, 6, NULL, $investmentData->member_id, $investmentData->account_number, NULL, NULL, NULL, $request['cre_date'], NULL, $online_payment_by = NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                            $type_transaction_id = $createDayBook;
                            updateRenewalTransaction($investmentData->account_number);
                        }
                    } elseif ($head == 36) {
                        //INTEREST ON DEPOSITS Heads
                        $investmentData = getInvestmentDetails($request['contact'][$key]);
                        $contectId = $investmentData->id;
                        $jvtype = 2;
                        $jv_sub_type = 21;
                        $contacttype = 2;
                        $jv_type_id = $investmentData->id;
                        $type = 3;
                        $sub_type = 314;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $investmentData->associate_id;
                        $member_id = $investmentData->member_id;
                        $branch_id_to = $investmentData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($investmentData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $investmentData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArraySsb = array('1' => $amount);
                        /*$createTransaction = $this->createTransaction(NULL,20,$investmentData->id,$investmentData->member_id,$investmentData->branch_id,getBranchCode($investmentData->branch_id)->branch_code,$amountArraySsb,6,NULL,$investmentData->member_id,$investmentData->account_number,NULL,NULL,getBranchDetail($investmentData->branch_id)->name,date("Y-m-d", strtotime( str_replace('/','-',$request['cre_date'] ) ) ),NULL,$online_payment_by=NULL,NULL,$payment_type,$type_id); 
                        $lastAmount = Daybook::where('investment_id',$investmentData->id)->where('account_no',$investmentData->account_number)->whereNotIn('transaction_type', [3,5,6,7,8,9,10,11,12,13,14,15,19])->orderby('id','desc')->first();
                        if($request['debit'][$key]){
                            $lastBalance = $lastAmount->opening_balance+$amount;
                            $withdrawal = NULL;  
                            $deposit = $amount;  
                        }elseif($request['credit'][$key]){
                            $lastBalance = $lastAmount->opening_balance-$amount;
                            $deposit = NULL;
                            $withdrawal = $amount;
                        }
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTransaction,NULL,21,$investmentData->id,$investmentData->associate_id,$investmentData->member_id,$lastBalance,$deposit,$withdrawal,$description,$request['reference'],$investmentData->branch_id,getBranchCode($investmentData->branch_id)->branch_code,$amountArraySsb,6,NULL,$investmentData->member_id,$investmentData->account_number,NULL,NULL,NULL,$request['cre_date'],NULL,$online_payment_by=NULL,NULL,$payment_type,NULL,NULL,NULL,NULL,NULL,$type_id);
                        $type_transaction_id = $createDayBook;
                        updateRenewalTransaction($investmentData->account_number);*/
                        $type_transaction_id = NULL;
                    } elseif ($head == 122) {
                        //Investment plan stationery charge
                        $investmentData = getInvestmentDetails($request['contact'][$key]);
                        $contectId = $investmentData->id;
                        $jvtype = 2;
                        $jv_sub_type = 22;
                        $contacttype = 2;
                        $jv_type_id = $investmentData->id;
                        $type = 3;
                        $sub_type = 39;
                        $branch_id = $request['branch'];
                        //$memberData = getMemberData($loan->associate_member_id);
                        $associate_id = $investmentData->associate_id;
                        $member_id = $investmentData->member_id;
                        $branch_id_to = $investmentData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($investmentData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $investmentData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $amountArraySsb = array('1' => $amount);
                        $createTransaction = null;
                        $lastAmount = Daybook::where('investment_id', $investmentData->id)->where('account_no', $investmentData->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->orderby('id', 'desc')->first();
                        if ($request['debit'][$key]) {
                            $withdrawal = $amount;
                            $deposit = NULL;
                        } elseif ($request['credit'][$key]) {
                            $deposit = $amount;
                            $withdrawal = NULL;
                        }
                        $lastBalance = $amount;
                        $description = $request['description'][$key];
                        $createDayBook = $this->createDayBookNew($createTransaction, NULL, 21, $investmentData->id, $investmentData->associate_id, $investmentData->member_id, $lastBalance, $deposit, $withdrawal, $description, $request['reference'], $investmentData->branch_id, getBranchCode($investmentData->branch_id)->branch_code, $amountArraySsb, 6, NULL, $investmentData->member_id, $investmentData->account_number, NULL, NULL, NULL, $request['cre_date'], NULL, $online_payment_by = NULL, NULL, $payment_type, NULL, NULL, NULL, NULL, NULL, $type_id, $companyId);
                        $type_transaction_id = $createDayBook;
                        //updateRenewalTransaction($investmentData->account_number);
                    } elseif ($head == 34) {
                        // STATIONERY CHARGE
                        $memberData = getMemberData($request['contact'][$key]);
                        $jvtype = 4;
                        $jv_sub_type = 41;
                        $contacttype = 3;
                        $contectId = $memberData->id;
                        $jv_type_id = $memberData->id;
                        $type = 1;
                        $sub_type = 13;
                        $branch_id = $request['branch'];
                        $associate_id = $memberData->associate_id;
                        $member_id = $memberData->id;
                        $branch_id_to = $memberData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($memberData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $memberData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 35) {
                        // DUPLICATE PASSBOOK CHARGE
                        $record = SavingAccount::where('account_no', $request['contact_account'][$key])->first();
                        if ($record) {
                            $type = 4;
                            $sub_type = 413;
                            $jvtype = 2;
                            $jv_sub_type = 23;
                            $contacttype = 4;
                        } else {
                            $record = Memberinvestments::where('account_number', $request['contact_account'][$key])->first();
                            $type = 3;
                            $sub_type = 311;
                            $jvtype = 3;
                            $jv_sub_type = 31;
                            $contacttype = 2;
                        }
                        $contectId = $record->id;
                        $jv_type_id = $record->id;
                        $branch_id = $request['branch'];
                        $associate_id = $record->associate_id;
                        $member_id = $record->member_id;
                        $branch_id_to = $record->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($record->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $record->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    }/*elseif($head == 62){
                        // TDS ON INTEREST ON DEPOSIT
                        $memberData = getMemberData($request['contact'][$key]);
                        $jvtype = 4;
                        $jv_sub_type = 41;
                        $contacttype = 3;
                        $contectId = $memberData->id;
                        $jv_type_id = $memberData->id;
                        $type = 1;
                        $sub_type = 14;
                        $branch_id = $request['branch'];
                        $associate_id = $memberData->associate_id;
                        $member_id = $memberData->id;
                        $branch_id_to = $memberData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($memberData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $memberData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    }*/ elseif ($head == 68 || $head == 69 || $head == 70 || $head == 91 || $head == 27 || $parentId->parent_id == 27) {
                        // Bank Heads
                        $bankAccountData = getSamraddhBankAccountId($request['contact'][$key],$companyId);
                        $bankData = getSamraddhBank($bankAccountData->bank_id);
                        $jvtype = 5;
                        $jv_sub_type = NULL;
                        $contacttype = 5;
                        $contectId = $bankData->id;
                        $jv_type_id = $bankData->id;
                        $type = 22;
                        $sub_type = 222;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $bankData->id;
                        $amount_to_name = $bankData->bank_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 28 || $head == 71) {
                        // TDS ON INTEREST ON DEPOSIT
                        $branchData = getBranchDetail($request['contact'][$key]);
                        $jvtype = 6;
                        $jv_sub_type = NULL;
                        $contacttype = 6;
                        $contectId = $branchData->id;
                        $jv_type_id = $branchData->id;
                        $type = 23;
                        $sub_type = 232;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $branchData->id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $branchData->id;
                        $amount_to_name = $branchData->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 61) {
                        // Salary Crediotrs
                        $employeeData = Employee::where('id', $request['contact'][$key])->first();
                        $jvtype = 7;
                        $jv_sub_type = NULL;
                        $contacttype = 7;
                        $contectId = $employeeData->id;
                        $jv_type_id = $employeeData->id;
                        $type = 6;
                        $sub_type = 62;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $employeeData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $employeeData->id;
                        $amount_to_name = $employeeData->employee_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 140) {
                        // Vendor Creditior
                        $vendorData = Vendor::where('type', 0)->where('id', $request['contact'][$key])->first();
                        $jvtype = 16;
                        $jv_sub_type = 161;
                        $contacttype = 161;
                        $contectId = $vendorData->id;
                        $jv_type_id = $vendorData->id;
                        $type = 27;
                        $sub_type = 273;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $type_transaction_id = NULL;
                    } elseif ($head == 142) {
                        // Vendor Creditior
                        $customerData = Vendor::where('type', 1)->where('id', $request['contact'][$key])->first();
                        $jvtype = 17;
                        $jv_sub_type = 171;
                        $contacttype = 171;
                        $contectId = $customerData->id;
                        $jv_type_id = $customerData->id;
                        $type = 27;
                        $sub_type = 274;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $type_transaction_id = NULL;
                    } elseif ($head == 141) {
                        // Associate Creditior
                        $AssociateData = Member::where('id', $request['contact'][$key])->first();
                        $jvtype = 9;
                        $jv_sub_type = 91;
                        $contacttype = 2;
                        $contectId = $AssociateData->id;
                        $jv_type_id = $AssociateData->id;
                        $type = 2;
                        $sub_type = 24;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $type_transaction_id = NULL;
                    } elseif ($parentId->parent_id == 167) {
                        // Associate Creditior
                        $creditCard = CreditCard::where('id', $request['contact'][$key])->first();
                        $jvtype = 18;
                        $jv_sub_type = 18;
                        $contacttype = 18;
                        $contectId = $creditCard->id;
                        $jv_type_id = $creditCard->id;
                        $type = 28;
                        $sub_type = 281;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $type_transaction_id = NULL;
                    } elseif ($head == 32) {
                        // Panel Interest
                        $employeeData = Employee::where('id', $request['contact'][$key])->first();
                        $jvtype = 7;
                        $jv_sub_type = NULL;
                        $contacttype = 7;
                        $contectId = $employeeData->id;
                        $jv_type_id = $employeeData->id;
                        $type = 6;
                        $sub_type = 63;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $employeeData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $employeeData->id;
                        $amount_to_name = $employeeData->employee_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 60 || $head == 74 || $head == 75 || $head == 144) {
                        // Rent Crediotrs
                        $rentLiabilityData = RentLiability::where('id', $request['contact'][$key])->first();
                        $jvtype = 8;
                        $jv_sub_type = NULL;
                        $contacttype = 8;
                        $contectId = $rentLiabilityData->id;
                        $jv_type_id = $rentLiabilityData->id;
                        $type = 10;
                        $sub_type = 105;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $rentLiabilityData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $rentLiabilityData->id;
                        $amount_to_name = $rentLiabilityData->owner_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 72 || $head == 73 || $head == 76 || $head == 143) {
                        // TA Advanced
                        $employeeData = Employee::where('id', $request['contact'][$key])->first();
                        $jvtype = 7;
                        $jv_sub_type = NULL;
                        $contacttype = 7;
                        $contectId = $employeeData->id;
                        $jv_type_id = $employeeData->id;
                        if ($head == 143) {
                            $type = 6;
                            $sub_type = 64;
                        } else {
                            $type = 13;
                            $sub_type = 138;
                        }
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $employeeData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $employeeData->id;
                        $amount_to_name = $employeeData->employee_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 75) {
                        // Rent Crediotrs
                        $rentLiabilityData = RentLiability::where('id', $request['contact'][$key])->first();
                        $jvtype = 8;
                        $jv_sub_type = NULL;
                        $contacttype = 8;
                        $contectId = $rentLiabilityData->id;
                        $jv_type_id = $rentLiabilityData->id;
                        $type = 10;
                        $sub_type = 106;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = $rentLiabilityData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $rentLiabilityData->id;
                        $amount_to_name = $rentLiabilityData->owner_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 87) {
                        // Commission
                        $associateData = getMemberData($request['contact'][$key]);
                        $jvtype = 9;
                        $jv_sub_type = NULL;
                        $contacttype = 9;
                        $contectId = $associateData->id;
                        $jv_type_id = $associateData->id;
                        $type = 2;
                        $sub_type = 22;
                        $branch_id = $request['branch'];
                        $associate_id = $associateData->id;
                        $member_id = $associateData->id;
                        $branch_id_to = $associateData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = $associateData->branch_id;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $associateData->id;
                        $amount_to_name = $associateData->first_name . ' ' . $associateData->last_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 88) {
                        // Fuel Charge
                        $associateData = getMemberData($request['contact'][$key]);
                        $jvtype = 9;
                        $jv_sub_type = NULL;
                        $contacttype = 9;
                        $contectId = $associateData->id;
                        $jv_type_id = $associateData->id;
                        $type = 2;
                        $sub_type = 23;
                        $branch_id = $request['branch'];
                        $associate_id = $associateData->id;
                        $member_id = $associateData->id;
                        $branch_id_to = $associateData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = $associateData->branch_id;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $associateData->id;
                        $amount_to_name = $associateData->first_name . ' ' . $associateData->last_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 63) {
                        // Fuel Charge
                        $associateData = getMemberData($request['contact'][$key]);
                        $jvtype = 9;
                        $jv_sub_type = NULL;
                        $contacttype = 9;
                        $contectId = $associateData->id;
                        $jv_type_id = $associateData->id;
                        $type = 9;
                        $sub_type = 91;
                        $branch_id = $request['branch'];
                        $associate_id = $associateData->id;
                        $member_id = $associateData->id;
                        $branch_id_to = $associateData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = $associateData->branch_id;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $associateData->id;
                        $amount_to_name = $associateData->first_name . ' ' . $associateData->last_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($head == 55) {
                        // MEMBERSHIP FEES
                        $memberData = getMemberData($request['contact'][$key]);
                        $jvtype = 4;
                        $jv_sub_type = 43;
                        $contacttype = 3;
                        $contectId = $memberData->id;
                        $jv_type_id = $memberData->id;
                        $type = 1;
                        $sub_type = 15;
                        $branch_id = $request['branch'];
                        $associate_id = $memberData->associate_id;
                        $member_id = $memberData->id;
                        $branch_id_to = $memberData->branch_id;
                        $branch_id_from = $request['branch'];
                        $toBranch = getBranchDetail($memberData->branch_id);
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $memberData->branch_id;
                        $amount_to_name = $toBranch->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($parentId->parent_id == 15) {
                        // Share Holder
                        $shareHolderData = ShareHolder::where('id', $request['contact'][$key])->first();
                        $jvtype = 10;
                        $jv_sub_type = NULL;
                        $contacttype = 10;
                        $contectId = $shareHolderData->id;
                        $jv_type_id = $shareHolderData->id;
                        $type = 16;
                        $sub_type = 163;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = $shareHolderData->member_id;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $shareHolderData->member_id;
                        $amount_to_name = $shareHolderData->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($parentId->parent_id == 19) {
                        // Director
                        $shareHolderData = ShareHolder::where('id', $request['contact'][$key])->first();
                        $jvtype = 11;
                        $jv_sub_type = NULL;
                        $contacttype = 11;
                        $contectId = $shareHolderData->id;
                        $jv_type_id = $shareHolderData->id;
                        $type = 15;
                        $sub_type = 153;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = $shareHolderData->member_id;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $shareHolderData->member_id;
                        $amount_to_name = $shareHolderData->name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } elseif ($parentId->parent_id == 18) {
                        // Loan From Banks
                        $bankData = LoanFromBank::where('id', $request['contact'][$key])->first();
                        $jvtype = 12;
                        $jv_sub_type = NULL;
                        $contacttype = 12;
                        $contectId = $bankData->id;
                        $jv_type_id = $bankData->id;
                        $type = 17;
                        $sub_type = 173;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = $bankData->id;
                        $amount_to_name = $bankData->bank_name;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    } else {
                        // Loan From Banks
                        if ($request['sub_head1'][$key] == 9) {
                            $type = 19;
                            $sub_type = 193;
                            $jvtype = 14;
                            $contacttype = 14;
                            $jv_sub_type = NULL;
                        } elseif ($head == 30) {
                            $type = 24;
                            $sub_type = 241;
                            $jvtype = 13;
                            $contacttype = 13;
                            $jv_sub_type = NULL;
                        } elseif ($head == 49) {
                            $type = 25;
                            $sub_type = 252;
                            $jvtype = 15;
                            $jv_sub_type = NULL;
                            $contacttype = 15;
                        } else {
                            $type = 25;
                            $sub_type = 251;
                            $jvtype = 15;
                            $jv_sub_type = NULL;
                            $contacttype = 15;
                        }
                        $contectId = NULL;
                        $jv_type_id = NULL;
                        $branch_id = $request['branch'];
                        $associate_id = NULL;
                        $member_id = NULL;
                        $branch_id_to = NULL;
                        $branch_id_from = $request['branch'];
                        $toBranch = NULL;
                        $fromBranch = getBranchDetail($request['branch']);
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = $request['branch'];
                        $amount_from_name = $fromBranch->name;
                        $type_transaction_id = NULL;
                    }
                    if ($head == 37) {
                        $contectId = $request['contact'][$key]; 
                    }
                    $payment_mode = 6;
                    $currency_code = 'INR';
                    $description = $request['description'][$key];
                    $account_id = NULL;
                    $created_by = 1;
                    $created_by_id = 1;
                    $jvheaddata['jv_journal_id'] = $type_id;
                    $jvheaddata['type'] = $jvtype;
                    $jvheaddata['type_id'] = $jv_type_id;
                    $jvheaddata['sub_type'] = $jv_sub_type;
                    $jvheaddata['head_id'] = $head;
                    $jvheaddata['contact_type'] = $contacttype;
                    $jvheaddata['contact_id'] = $contectId;
                    $jvheaddata['description'] = $request['description'][$key];
                    $jvheaddata['debits_amount'] = $dr_amount;
                    $jvheaddata['credits_amount'] = $cr_amount;
                    $jvheaddata['company_id'] = $companyId;
                    $jvheaddata['created_at'] = date("Y-m-d", strtotime(convertDate($request['cre_date'])));
                    $jvheadres = JvJournalHeads::create($jvheaddata);
                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    $jv_unique_id = "";
                    for ($i = 0; $i < 10; $i++) {
                        $jv_unique_id .= $chars[mt_rand(0, strlen($chars) - 1)];
                    }
                    if ($head == 32 || $head == 61 || $head == 72 || $head == 143 || $head == 73 || $head == 76) {
                        // employee ledger
                        $employeeLeaser = $this->employeeLeaser($employeeData->id, $branch_id, 5, NULL, $amount, $amount, $amount, $description, $currency_code, $payment_type, 4, 1, $created_at, $created_at, $jv_unique_id, $request['reference'], $v_date = NULL, $ssb_account_id_to = NULL, $ssb_account_id_from = NULL, $to_bank_name = NULL, $to_bank_branch = NULL, $to_bank_ac_no = NULL, $to_bank_ifsc = NULL, $to_bank_id = NULL, $to_bank_account_id = NULL, $from_bank_name = NULL, $from_bank_branch = NULL, $from_bank_ac_no = NULL, $from_bank_ifsc = NULL, $from_bank_id = NULL, $from_bank_ac_id = NULL, $cheque_id = NULL, $cheque_no = NULL, $cheque_date = NULL, $transaction_no = NULL, $transaction_date, $transaction_charge = NULL, $type_id, $companyId);
                        $type_transaction_id = $employeeLeaser;
                    } elseif ($head == 60 || $head == 74 || $head == 75 || $head == 144) {
                        // Rent ledger
                        $rentLeaser = $this->RentLiabilityLedger($rentLiabilityData->id, 4, NULL, $amount, $amount, $amount, $description, $currency_code, $payment_type, 4, 1, $created_at, $created_at, $request['reference'], $v_no = NULL, $v_date = NULL, $ssb_account_id_to = NULL, $ssb_account_id_from = NULL, $to_bank_name = NULL, $to_bank_branch = NULL, $to_bank_ac_no = NULL, $to_bank_ifsc = NULL, $to_bank_id = NULL, $to_bank_account_id = NULL, $from_bank_name = NULL, $from_bank_branch = NULL, $from_bank_ac_no = NULL, $from_bank_ifsc = NULL, $from_bank_id = NULL, $from_bank_ac_id = NULL, $cheque_id = NULL, $cheque_no = NULL, $cheque_date = NULL, $transaction_no = NULL, $transaction_date, $transaction_charge = NULL, $type_id, $companyId);
                        $type_transaction_id = $rentLeaser;
                    } elseif ($head == 140) {
                        // Vendor Transaction
                        $vendorTrans = $this->VendorTransaction(4, NULL, $type_id, NULL, $vendorData->id, $branch_id, NULL, NULL, $amount, $description, $payment_type, 6, 'INR', $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $companyId);
                        $type_transaction_id = $vendorTrans;
                    } elseif ($head == 142) {
                        // Customer Transaction
                        $customerTrans = $this->CustomerTransaction(3, NULL, $type_id, NULL, $customerData->id, $branch_id, NULL, NULL, $amount, $description, $payment_type, 6, 'INR', $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $companyId);
                        $type_transaction_id = $customerTrans;
                    } elseif ($parentId->parent_id == 167) {
                        // Credit Card Transaction
                        $associateTrans = $this->creditCard(2, NULL, $type_id, NULL, $creditCard->id, $branch_id, NULL, NULL, $amount, $description, $payment_type, 6, 'INR', $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $companyId);
                        $type_transaction_id = $associateTrans;
                    } elseif ($head == 234) {
                        // Credit Card Transaction
                        $associateTrans = $this->companyBond($dayBookRef, $bound, $entry_date, $amount, 0, Null, NULL, NULL, NULL, $payment_type, $type_id, 6, $description, $companyId);
                    } elseif ($parentId->parent_id == 236) {
                        // Credit Card Transaction
                        $associateTrans = $this->companyBond($dayBookRef, $bound, $entry_date, 0, 0, $amount, NULL, NULL, $head, $payment_type, $type_id, 6, $description, $companyId);
                    }
                    if ($head == 68 || $head == 69 || $head == 70 || $head == 91 || $head == 27 || $parentId->parent_id == 27) {
                        // Head Transacion
                        $allTranRDcheque = CommanController::createAllHeadTransaction(
                            $dayBookRef,
                            $branch_id,
                            $bank_id = NULL,
                            $bank_ac_id = NULL,
                            $head,
                            $type,
                            $sub_type,
                            $type_id,
                            $type_transaction_id,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $amount,
                            $description,
                            $payment_type,
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id,
                            $v_no = NULL,
                            $ssb_account_id_from = NULL,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no = NULL,
                            $transction_no = NULL,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );
                    } else {
                        // Head Transacion
                        $allTranRDcheque = CommanController::createAllHeadTransaction(
                            $dayBookRef,
                            $branch_id,
                            $bank_id = NULL,
                            $bank_ac_id = NULL,
                            $head,
                            $type,
                            $sub_type,
                            $type_id,
                            $type_transaction_id,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $amount,
                            $description,
                            $payment_type,
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id,
                            $v_no = NULL,
                            $ssb_account_id_from = NULL,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no = NULL,
                            $transction_no = NULL,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );
                    }
                    // Branch Day Book
                    if ($head == 28) {
                        if ($request['debit'][$key] > 0) {
                            $payment_type = 'CR';
                        } elseif ($request['credit'][$key] > 0) {
                            $payment_type = 'DR';
                        }
                    }
                    $branchDayBook = CommanController::branchDayBookNew(
                        $dayBookRef,
                        $branch_id,
                        $type,
                        $sub_type,
                        $type_id,
                        $type_transaction_id,
                        $associate_id,
                        $member_id,
                        $branch_id_to,
                        $branch_id_from,
                        $amount,
                        $description,
                        $description_dr,
                        $description_cr,
                        $payment_type,
                        $payment_mode,
                        $currency_code,
                        $v_no = NULL,
                        $ssb_account_id_from = NULL,
                        $cheque_no = NULL,
                        $transction_no = NULL,
                        $entry_date,
                        $entry_time,
                        $created_by,
                        $created_by_id,
                        $created_at,
                        $ssb_account_tran_id_to = NULL,
                        $ssb_account_tran_id_from = NULL,
                        $jv_unique_id,
                        $cheque_type = NULL,
                        $cheque_id = NULL,
                        $companyId
                    );
                    if ($head == 68 || $head == 69 || $head == 70 || $head == 91) {
                        // Samradhh Bank Day Book
                        if ($request['debit'][$key] > 0) {
                            $payment_type = 'CR';
                        } elseif ($request['credit'][$key] > 0) {
                            $payment_type = 'DR';
                        }
                        $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef, $amount_to_id, $account_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $amount, $amount, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transaction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Journal Entry Updated Successfully!');
    }
    public function delete($id)
    {
        if (check_my_permission(Auth::user()->id, "152") != "1") {
            return redirect()->route('admin.dashboard');
        }
        DB::beginTransaction();
        try {
            $jvHeadData = JvJournalHeads::where('jv_journal_id', $id)->get();
            foreach ($jvHeadData as $key => $value) {
                if (isset($value->daybook_ref_id)  && ($value->type == 5 || $value->type == 19)) {
                    $deleteAllHeadTransaction = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->delete();
                    $deleteBranchDaybook = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->delete();
                    if ($value->type == 5) {
                        $deleteSamDaybook = SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->delete();
                    }
                    $deleteCompanyBond = \App\Models\CompanyBoundTransaction::where('daybook_ref_id', $value->daybook_ref_id)->delete();
                } else {
                    // saving account transaction delete
                    $savingAccountTranscation = SavingAccountTranscation::whereIN('type', [12, 13])->where('jv_journal_id', $id)->delete();
                    // day book transaction delete
                    $dayBookTranscation = Daybook::where('jv_journal_id', $id)->delete();
                    // Loan day book transaction delete
                    $loanDayBookTranscation = LoanDayBooks::where('jv_journal_id', $id)->delete();
                    // employee ledger delete
                    $employeeLeaser = EmployeeLedger::where('type', 5)->where('jv_journal_id', $id)->delete();
                    //Rent Ledger delete    
                    $rentLeaser = RentLiabilityLedger::where('type', 4)->where('jv_journal_id', $id)->delete();
                    $vendorTransaction  = VendorTransaction::where('type', 4)->where('type_id', $id)->delete();
                    // Customer Transaction delete
                    $CustomerTransaction = CustomerTransaction::where('type', 3)->where('type_id', $id)->delete();
                    // Credit card Transaction Delete
                    $creditcard = CreditCradTransaction::where('type', 2)->where('type_id', $id)->delete();
                    //All head transaction
                    $headData = AllHeadTransaction::whereIN('sub_type', [13, 15, 22, 23, 38, 39, 311, 312, 313, 412, 413, 414, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 62, 63, 64, 91, 105, 106, 137, 138, 173, 174, 193, 153, 163, 222, 232, 241, 251, 252, 314, 24, 273, 274, 281])->where('type_id', $id)->delete();
                    // Branch Day Book delete
                    $branchDayBook = BranchDaybook::whereIN('sub_type', [13, 15, 22, 23, 38, 39, 311, 312, 313, 412, 413, 414, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 62, 63, 64, 91, 105, 106, 137, 138, 173, 174, 193, 153, 163, 222, 232, 241, 251, 252, 314, 24, 273, 274, 281])->where('type_id', $id)->delete();
                    // // Member Transaction delete
                    // $memberTransaction = MemberTransaction::whereIN('sub_type',[13,15,22,23,38,39,311,312,313,412,413,414,511,512,513,514,515,516,517,518,519,520,62,63,64,91,105,106,137,138,173,174,193,153,163,222,232,241,251,252,314])->where('type_id',$id)->delete();
                    // Samradhh Bank Day Book delete
                    $samraddhBankDaybook = SamraddhBankDaybook::whereIN('sub_type', [13, 15, 22, 23, 38, 39, 311, 312, 313, 412, 413, 414, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 62, 63, 64, 91, 105, 106, 137, 138, 173, 174, 193, 153, 163, 222, 232, 241, 251, 252, 314])->where('type_id', $id)->delete();
                }
                //Jv journal head
                $jvheadres = JvJournalHeads::where('jv_journal_id', $id)->delete();
            }
            //Jv journal
            $jvData = JvJournals::where('id', $id)->delete();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Journal Entry Deleted Successfully!');
    }
    public function jv_detail($id)
    {
        $data['title'] = 'Journal Detail';
        return view('templates.admin.jv_management.detail', $data);
    }
    public function getHead(Request $request)
    {
        $accountHead = AccountHeads::where('labels', 1)->whereIn('head_id', [1, 4])->get();
        return response()->json($accountHead);
    }
    public function getSavingAccounts(Request $request)
    {
        $input = $request->all();
        $branchId = $request->branchId;
        $company_id = $request->company_id??0;
        $result = SavingAccount::select('id', 'account_no')
            ->when($company_id > 0 , function ($q) use ($company_id) { $q->where('company_id',$company_id); } )
            ->where('branch_id',$branchId)
            ->where('account_no', 'LIKE', '%' . $input['query'] . '%')
            ->get();
        $saving = [];
        if (count($result) > 0) {
            foreach ($result as $s) {
                $saving[] = array(
                    "id" => $s->id,
                    "text" => $s->account_no,
                );
            }
        }
        return response()->json($saving);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getSavingAccountsdetails(Request $request)
    {
        $input = $request->all();
        $resValue = $request->resValue;
        $result = SavingAccount::with('customerSSB')->where('id', $resValue)->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getbankAccountsdetails(Request $request)
    {
        $input = $request->all();
        $resValue = $request->resValue;
        $result = SamraddhBankAccount::select('id', 'account_no', 'bank_id', 'ifsc_code')->where('id', $resValue)->first();
        $name = getSamraddhBank($result->bank_id)->bank_name;
        $return_array = compact('result', 'name');
        return json_encode($return_array);
    }
    public function getMembers(Request $request)
    {
        $input = $request->all();
        $branchId = $request->branchId;
        $company_id = $input['company_id']??0;
        $result = Member::select('id', 'member_id')
        ->when($company_id > 0 , function ($q) use ($company_id) { $q->where('company_id',$company_id); } )
        ->where('member_id', 'LIKE', '%' . $input['query'] . '%')
            /*->where('branch_id',$branchId)*/->get();
        /**********************/
        $members = [];
        if (count($result) > 0) {
            foreach ($result as $member) {
                $members[] = array(
                    "id" => $member->id,
                    "text" => $member->member_id,
                );
            }
        }
        return response()->json($members);
        /**********************/
        //$return_array = compact('members');
        //return json_encode($return_array);
    }
    public function getMemberDetails(Request $request)
    {
        $resValue = $request->resValue;
        $result = Member::select('id', 'member_id', 'first_name', 'last_name')->where('id', $resValue)
            /*->where('branch_id',$branchId)*/->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getAssociateDetails(Request $request)
    {
        $resValue = $request->resValue;
        $result = Member::select('id', 'member_id', 'first_name', 'last_name', 'associate_no')->where('id', $resValue)
            /*->where('branch_id',$branchId)*/->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getAssociates(Request $request)
    {
        $input = $request->all();
        $branchId = $request->branchId;
        $company_id = $input['company_id']??0; 
        $result = Member::select('id', 'associate_no', 'first_name', 'last_name')
        ->where('associate_no', 'LIKE', '%' . $input['query'] . '%')
        ->when($company_id > 0 , function ($q) use ($company_id){
            $q->where('company_id', $company_id);
        })
        /*->where('branch_id',$branchId)*/
        ->limit(10)
        ->get();
        $associate = [];
        if (count($result) > 0) {
            foreach ($result as $a) {
                $associate[] = array(
                    "id" => $a->id,
                    "text" => $a->associate_no
                );
            }
        }
        return response()->json($associate);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getLoanAccounts(Request $request)
    {
        $headid = $request->headId;
        $input = $request->all();
        $branchId = $request->branchId;
        $company_id = $input['company_id']??0; 
        if ($headid == 64 || $headid == 65 || $headid == 67) {
            if ($headid == 64) {
                $loanType = 1;
            } elseif ($headid == 65) {
                $loanType = 2;
            } elseif ($headid == 67) {
                $loanType = 4;
            }
            $result = Memberloans::select('id', 'account_number', 'loan_type')->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })->where('loan_type', $loanType)->where('account_number', 'LIKE', '%' . $input['query'] . '%')/*->where('branch_id',$branchId)->limit(10)*/->get();
        } elseif ($headid == 66) {
            $result = Grouploans::select('id', 'account_number', 'loan_type')->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })->where('account_number', 'LIKE', '%' . $input['query'] . '%')/*->where('branch_id',$branchId)->limit(10)*/->get();
        } elseif ($headid == 97 || $headid == 31 || $headid == 33 || $headid == 90) {
            $result = Memberloans::select('id', 'account_number', 'loan_type')->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })->where('account_number', 'LIKE', '%' . $input['query'] . '%')->get();
        }
        $loan = [];
        if (count($result) > 0) {
            foreach ($result as $l) {
                $loan[] = array(
                    "id" => $l->id,
                    "text" => $l->account_number, "attr1" => $l->loan_type
                );
            }
        }
        return response()->json($loan);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getCustomer(Request $request)
    {
        $headid = $request->headId;
        $input = $request->all();
        $pId = $request->pId;
        $company_id = $input['company_id']??0;
        $result = Vendor::select('id', 'name')->where('type', $request->userType)->when($company_id > 0 , function ($q) use ($company_id) { $q->where('company_id',$company_id); } )->where('name', 'LIKE', '%' . $input['query'] . '%')->get();
        //$result=ShareHolder::with('member')->select('id','name','member_id')->where('type',$headid)->where('name', 'LIKE', '%'.$input['query'].'%')->get();
        $shareHolder = [];
        if (count($result) > 0) {
            foreach ($result as $s) {
                $shareHolder[] = array(
                    "id" => $s->id,
                    "text" => $s->name,
                );
            }
        }
        return response()->json($shareHolder);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getLoanAccountsDetails(Request $request)
    {
        $headid = $request->headId;
        $input = $request->all();
        $resValue = $request->resValue;
        if ($headid == 64 || $headid == 65 || $headid == 67) {
            if ($headid == 64) {
                $loanType = 1;
            } elseif ($headid == 65) {
                $loanType = 2;
            } elseif ($headid == 67) {
                $loanType = 4;
            }
            $result = Memberloans::with('loanMember')->where('loan_type', $loanType)->where('id', $resValue)->first();
        } elseif ($headid == 66) {
            $result = Grouploans::with('loanMember')->where('id', $resValue)->first();
        } elseif ($headid == 97 || $headid == 31 || $headid == 33) {
            $result = Memberloans::with('loanMember')->where('id', $resValue)->first();
        }
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getInvestmentAccounts(Request $request)
    {
        $headid = $request->headId;
        $branchId = $request->branchId;
        $company_id = $request['company_id']??0; 
        $input = $request->all();
        if ($headid == 58) {
            $planType = 7;
        } elseif ($headid == 77 || $headid == 57) {
            $planType = 9;
        } elseif ($headid == 78) {
            $planType = 8;
        } elseif ($headid == 79) {
            $planType = 4;
        } elseif ($headid == 80) {
            $planType = 2;
        } elseif ($headid == 81) {
            $planType = 5;
        } elseif ($headid == 82) {
            $planType = 11;
        } elseif ($headid == 83) {
            $planType = 10;
        } elseif ($headid == 84) {
            $planType = 6;
        } elseif ($headid == 85) {
            $planType = 3;
        } else {
            $planType = '';
        }
        if ($headid == 58 || $headid == 77 || $headid == 57 || $headid == 78 || $headid == 79 || $headid == 80 || $headid == 81 || $headid == 82 || $headid == 83 || $headid == 84 || $headid == 85) {
            $result = Memberinvestments::select('id', 'account_number')->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })->where('plan_id', $planType)->where('account_number', 'LIKE', '%' . $input['query'] . '%')/*->where('branch_id',$branchId)->limit(10)*/->get();
        } elseif ($headid == 36) {
            $result = Memberinvestments::select('id', 'account_number')->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })->where('account_number', 'LIKE', '%' . $input['query'] . '%')->where('plan_id', '!=', 1)->get();
        } else {
            $result = Memberinvestments::select('id', 'account_number')->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })->where('account_number', 'LIKE', '%' . $input['query'] . '%')->get();
        }
        $investment = [];
        if (count($result) > 0) {
            foreach ($result as $i) {
                $investment[] = array(
                    "id" => $i->id,
                    "text" => $i->account_number,
                );
            }
        }
        return response()->json($investment);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getinvestmentsaccountsdetails(Request $request)
    {
        $headid = $request->headId;
        $resValue = $request->resValue;
        $input = $request->all();
        if ($headid == 58) {
            $planType = 7;
        } elseif ($headid == 77 || $headid == 57) {
            $planType = 9;
        } elseif ($headid == 78) {
            $planType = 8;
        } elseif ($headid == 79) {
            $planType = 4;
        } elseif ($headid == 80) {
            $planType = 2;
        } elseif ($headid == 81) {
            $planType = 5;
        } elseif ($headid == 82) {
            $planType = 11;
        } elseif ($headid == 83) {
            $planType = 10;
        } elseif ($headid == 84) {
            $planType = 6;
        } elseif ($headid == 85) {
            $planType = 3;
        } else {
            $planType = '';
        }
        if ($headid == 58 || $headid == 77 || $headid == 57 || $headid == 78 || $headid == 79 || $headid == 80 || $headid == 81 || $headid == 82 || $headid == 83 || $headid == 84 || $headid == 85) {
            $result = Memberinvestments::with('member', 'plan')->where('plan_id', $planType)->where('id', $resValue)->first();
        } else {
            $result = Memberinvestments::with('member', 'plan')->where('id', $resValue)->first();
        }
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getInvestmentDetails(Request $request)
    {
        $resValue = $request->resValue;
        $result = Memberinvestments::select('id', 'account_number')->where('id', $resValue)
            /*->where('branch_id',$branchId)*/->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getReInvestmentAccounts(Request $request)
    {
        $input = $request->all();
        $company_id = $input['company_id']??0; 
        $result = Memberinvestments::select('id', 'account_number')->when($company_id > 0 , function ($q) use ($company_id){
            $q->where('company_id', $company_id);
        })->where('account_number', 'like', '%' . $input['query'] . '%')->get();
        $reinvest = [];
        if (count($result) > 0) {
            foreach ($result as $ri) {
                $reinvest[] = array(
                    "id" => $ri->id,
                    "text" => $ri->account_number,
                );
            }
        }
        return response()->json($reinvest);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getReInvestmentAccountsDetails(Request $request)
    {
        $input = $request->all();
        $resValue = $request->resValue;
        $result = Memberinvestments::with('member', 'plan')->where('id', $resValue)->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getEmployees(Request $request)
    {
        $input = $request->all();
        $branchId = $input['branchId'];
        $company_id = $input['company_id']??0;
        $result = Employee::select('id', 'employee_name', 'employee_code')
            ->where('employee_code', 'LIKE', '%' . $input['query'] . '%')
             ->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })
            ->where('branch_id',$branchId)
            ->get();
        /**********************/
        $employee = [];
        if (count($result) > 0) {
            foreach ($result as $e) {
                $employee[] = array(
                    "id" => $e->id,
                    "text" => $e->employee_code,
                );
            }
        }
        return response()->json($employee);
        //$return_array = compact('result');
        //return json_encode($return_array);
    }
    public function getEmployeeDetails(Request $request)
    {
        $resValue = $request->resValue;
        $result = Employee::select('id', 'employee_name', 'employee_code')->where('id', $resValue)
            /*->where('branch_id',$branchId)*/->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getShareholders(Request $request)
    {
        $headid = $request->headId;
        $input = $request->all();
        $pId = $request->pId;
        $company_id = $input['company_id']??0; 
        $result = ShareHolder::with('member')
        ->select('id', 'name', 'member_id')
        ->when($company_id > 0 , function ($q) use ($company_id){
            $q->where('company_id', $company_id);
        })
        ->where('head_id', $headid)
        ->where('type', $pId)
        ->where('name', 'LIKE', '%' . $input['query'] . '%')
        ->get();
        //$result=ShareHolder::with('member')->select('id','name','member_id')->where('type',$headid)->where('name', 'LIKE', '%'.$input['query'].'%')->get();
        $shareHolder = [];
        if (count($result) > 0) {
            foreach ($result as $s) {
                $shareHolder[] = array(
                    "id" => $s->id,
                    "text" => $s->name,
                );
            }
        }
        return response()->json($shareHolder);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getshareholdersdetails(Request $request)
    {
        $headid = $request->headId;
        $input = $request->all();
        $result = ShareHolder::with('member')->select('id', 'name', 'member_id')->where('id', $headid)->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getLoanFromBankDetail(Request $request)
    {
        $headid = $request->headId;
        $input = $request->all();
        $resValue = $request->resValue;
        $result = LoanFromBank::select('bank_name', 'loan_account_number')->where('id', $resValue)->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getLoanFromBank(Request $request)
    {
        $headid = $request->headId;
        $input = $request->all();
        $company_id = $input['company_id']??0; 
        if ($headid == 97) {
            $result = LoanFromBank::select('id', 'loan_account_number')->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })->where('loan_account_number', 'LIKE', '%' . $input['query'] . '%')->get();
        } else {
            $result = LoanFromBank::select('id', 'loan_account_number')->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })->where('account_head_id', $headid)->where('loan_account_number', 'LIKE', '%' . $input['query'] . '%')->get();
        }
        $bank = [];
        if (count($result) > 0) {
            foreach ($result as $b) {
                $bank[] = array(
                    "id" => $b->id,
                    "text" => $b->loan_account_number,
                );
            }
        }
        return response()->json($bank);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getBank(Request $request)
    {
        $headid = $request->headId;
        $input = $request->all();
        $company_id = $input['company_id']??0; 
        $result = SamraddhBankAccount::select('id', 'account_no')->when($company_id > 0 , function ($q) use ($company_id){
            $q->where('company_id', $company_id);
        })->where('account_head_id', $headid)->where('account_no', 'LIKE', '%' . $input['query'] . '%')->get();
        /**********************/
        $bank = [];
        if (count($result) > 0) {
            foreach ($result as $b) {
                $bank[] = array(
                    "id" => $b->id,
                    "text" => $b->account_no,
                );
            }
        }
        return response()->json($bank);
        /*$return_array = compact('result');
        return json_encode($return_array);*/
    }
    public function getBranch(Request $request)
    {
        $input = $request->all();
        $branchId = $request->branchId;
        $company_id = $input['company_id']??0;
        $result = Branch::select('id', 'name')
        ->where('name', 'LIKE', '%' . $input['query'] . '%')
            /*->where('branch_id',$branchId)*/
            ->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })
            ->get();
        /**********************/ 
        $branch = [];
        if (count($result) > 0) {
            foreach ($result as $b) {
                $branch[] = array(
                    "id" => $b->id,
                    "text" => $b->name,
                );
            }
        }
        return response()->json($branch);
        //$return_array = compact('result');
        //return json_encode($return_array);
    }
    public function getRentLiability(Request $request)
    {
        $input = $request->all();
        $company_id = $input['company_id']??0;
        $result = RentLiability::select('id', 'owner_name')
            ->where('owner_name', 'LIKE', '%' . $input['query'] . '%')
            /*->where('branch_id',$branchId)*/
            ->when($company_id > 0 , function ($q) use ($company_id){
                $q->where('company_id', $company_id);
            })
            ->get();
        /**********************/
        $rent = [];
        if (count($result) > 0) {
            foreach ($result as $r) {
                $rent[] = array(
                    "id" => $r->id,
                    "text" => $r->owner_name,
                );
            }
        }
        return response()->json($rent);
    }
    public function getRentDetails(Request $request)
    {
        $resValue = $request->resValue;
        $result = RentLiability::select('id', 'owner_name')->where('id', $resValue)
            /*->where('branch_id',$branchId)*/
            ->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function getCustomerDetail(Request $request)
    {
        $resValue = $request->resValue;
        $vendorDetail = Vendor::where('type', $request->type)->where('id', $resValue)->first();
        return response()->json($vendorDetail);
    }
    public function VendorTransaction($type, $sub_type, $type_id, $type_transaction_id, $vendorid, $branch_id, $bankId, $accountId, $amount, $description, $payment_type, $payment_mode, $curr, $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $company_id)
    {
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['vendor_id'] = $vendorid;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bankId;
        $data['account_id'] = $accountId;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $curr;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['company_id'] = $company_id;
        $transcation = VendorTransaction::create($data);
        return $transcation->id;
    }
    public function CustomerTransaction($type, $sub_type, $type_id, $type_transaction_id, $vendorid, $branch_id, $bankId, $accountId, $amount, $description, $payment_type, $payment_mode, $curr, $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $company_id)
    {
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['customer_id'] = $vendorid;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bankId;
        $data['account_id'] = $accountId;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $curr;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['company_id'] = $company_id;
        $data['created_by_id'] = $created_by_id;
        $transcation = CustomerTransaction::create($data);
        return $transcation->id;
    }
    public function AssociateTransaction($type, $sub_type, $type_id, $type_transaction_id, $associateid, $branch_id, $bankId, $accountId, $amount, $description, $payment_type, $payment_mode, $curr, $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $payment_status)
    {
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associateid;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bankId;
        $data['account_id'] = $accountId;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $curr;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['payment_status'] = $payment_status;
        $transcation = AssociateTransaction::create($data);
        return $transcation->id;
    }
    public function getcreditcarddetail(Request $request)
    {
        $resValue = $request->resValue;
        $result = CreditCard::select('id', 'credit_card_account_number', 'credit_card_bank')->where('id', $resValue)
            /*->where('branch_id',$branchId)*/->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function CreditCard($type, $sub_type, $type_id, $type_transaction_id, $creditCardId, $branch_id, $bankId, $accountId, $amount, $description, $payment_type, $payment_mode, $curr, $entry_date, $entry_time, $created_by, $created_by_id, $jv_unique_id, $companyId)
    {
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['credit_card_id'] = $creditCardId;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bankId;
        $data['account_id'] = $accountId;
        $data['amount'] = $amount;
        $data['total_amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $curr;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['company_id'] = $companyId;
        $transcation = CreditCradTransaction::create($data);
        return $transcation->id;
    }
    // Company Bond Details
    public function getCompanyBondDetails(Request $request)
    {
        $resValue = $request->resValue;
        $input = $request->all();
        $result = \App\Models\CompanyBound::select('id', 'fd_no', 'bank_name')->where('status', 0)->where('fd_no', 'LIKE', '%' . $input['query'] . '%')->get();
        $fdBond = [];
        if (count($result) > 0) {
            foreach ($result as $r) {
                $fdBond[] = array(
                    "id" => $r->id,
                    "text" => $r->fd_no,
                );
            }
        }
        return response()->json($fdBond);
    }
    public function getCompleteCompanyBondDetails(Request $request)
    {
        $resValue = $request->resValue;
        $result = \App\Models\CompanyBound::select('id', 'fd_no', 'bank_name')->where('id', $resValue)->first();
        $return_array = compact('result');
        return json_encode($return_array);
    }
    public function companyBond($DayBookRef, $id, $date, $interest_amount, $interest_type, $tds_amount, $received_bank_name, $received_bank_account, $tds_receive_year, $payment_type, $jv_unique_id, $payment_mode, $des, $companyId)
    {
        //$companyBound = CompanyBound::where('fd_no',$request->fd_no)->first(); 
        $data = [
            'daybook_ref_id' => $DayBookRef,
            'bound_id' => $id,
            'date' => date('Y-m-d', strtotime(convertDate($date))),
            'tds_amount' => $tds_amount,
            'interest_amount' => $interest_amount,
            'remark' => $des,
            'rec_bank' => $received_bank_name,
            'rec_bank_account' => $received_bank_account,
            'interest_type' => $interest_type,
            'tds_receivable' => $tds_receive_year,
            'payment_type' => $payment_type,
            'payment_mode' => $payment_mode,
            'jv_unique_id' => $jv_unique_id,
            'company_id' => $companyId,
        ];
        $transaction = \App\Models\CompanyBoundTransaction::create($data);
        return $transaction->id;
    }
}