<?php
namespace App\Http\Controllers\Admin\Cron;

use App\Http\Controllers\Admin\CommanController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\{CronLog, Log, Memberinvestments, InvestmentMonthlyYearlyInterestDeposits, SavingAccountTransactionView, Plans, SavingAccountTranscation, SavingAccount, Daybook, PlanTenures, Event, State, MemberInvestmentInterestTds, ECSTransaction, Branch};
use App\Http\Traits\MoneyBackCalculation;
use App\Services\CronStoreInfo;
use URL;
use DB;
use Session;
use App\Services\Sms;
use Carbon\Carbon;
use Artisan;
use App\Http\Traits\EmiDatesTraits;

/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Demand Advice DemandAdviceController
    |--------------------------------------------------------------------------
    |
    | This controller handles demand advice all functionlity.
*/
class CronController extends Controller
{
    use MoneyBackCalculation;
    use EmiDatesTraits;

    protected $signature = 'command:transferMoneyback';
    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct(CronStoreInfo $CronStoreInfo)
    {
        // check user login or not
        $this->middleware('auth');
        // $this->cronService = $CronStoreInfo;
    }
    //display the UI page of cron log management module
    public function index()
    {
        if (check_my_permission(auth()->user()->id, "337") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Cron |  Cron Log Listing';
        return view('templates.admin.cron.cron_listing', $data);
    }
    //Fetch the data from cron_logs table
    public function listing(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = CronLog::select('id', 'cron_name', 'start_date_time', 'end_date_time', 'status', 'log_file');
                if ($arrFormData['is_search'] == 'yes') {
                    $date = date('Y-m-d', strtotime(convertdate($arrFormData['date'])));
                    if ($date != '') {
                        $data = $data->whereDate(DB::raw('DATE(created_at)'), $date);
                    }
                }
                if ($arrFormData['status'] != '0') {
                    $data = $data->whereStatus($arrFormData['status']);
                }
                if ($arrFormData['name'] != '') {
                    $data = $data->where('cron_name', 'like', '%' . $arrFormData['name'] . '%');
                }
                $count = $data->count('id');
                $cache_data = $data->orderby('created_at', 'ASC')->get();
                $token = session()->get('_token');
                Cache::put('cronLogData' . $token, $cache_data);
                Cache::put('cronLogData_count' . $token, $count);
                $data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                $status = [
                    1 => 'Start',
                    2 => 'InProgress',
                    3 => 'Success',
                    4 => 'Failed',
                ];
                foreach ($data as $row) {
                    $action = '<a href="' . url("core/storage/logs/{$row->log_file}") . '" title="Download" download><i class="fas fa-download"></i></a>';
                    $val = [
                        'id' => $sno++,
                        'cron_name' => (isset($row->cron_name) && !empty($row->cron_name)) ? $row->cron_name : 'N/A',
                        'start_date' => (isset($row->start_date_time)) ? date('d/m/Y h:i:s A', strtotime($row->start_date_time)) : 'N/A',
                        'end_date' => (isset($row->end_date_time)) ? date('d/m/Y h:i:s A', strtotime($row->end_date_time)) : 'N/A',
                        'status' => $status[$row->status] ?? 'N/A',
                        'action' => $action,
                    ];
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
            }
            return json_encode($output);
        }
    }
    //delete the cron selected data
    //table cron_logs
    public function delete(Request $request)
    {
        //
    }
    //Export the listing
    //table name cron_logs
    public function export(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('cronLogData' . $token);
        $count = Cache::get('cronLogData_count' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/cron_logs.csv";
        $fileName = env('APP_EXPORTURL') . "/asset/cron_logs.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        if ($request['cron_export'] == 0) {
            $totalResults = $count;
            $result = 'next';
            if (($start + $limit) >= $totalResults) {
                $result = 'finished';
            }
            // if its a fist run truncate the file. else append the file
            if ($start == 0) {
                $handle = fopen($fileName, 'w');
            } else {
                $handle = fopen($fileName, 'a');
            }
            if ($start == 0) {
                $headerDisplayed = false;
            } else {
                $headerDisplayed = true;
            }
            $sno = $_POST['start'];
            $status = [
                1 => 'Start',
                2 => 'InProgress',
                3 => 'Success',
                4 => 'Failed',
            ];
            foreach ($data as $row) {
                $sno++;
                $val['S. No.'] = $sno;
                $val['CRON NAME'] = isset($row->cron_name) ? $row->cron_name : "N/A";
                $val['START DATE/TIME'] = date('d/m/Y H:i:s', strtotime($row->start_date_time));
                $val['END DATE/TIME'] = $row->end_date_time ? date('d/m/Y H:i:s', strtotime($row->end_date_time)) : 'N/A';
                $val['STATUS'] = $status[$row->status] ?? 'N/A';
                if (!$headerDisplayed) {
                    // Use the keys from $data as the titles
                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                // Put the data into the stream
                fputcsv($handle, $val);
            }
            // Close the file
            fclose($handle);
            if ($totalResults == 0) {
                $percentage = 100;
            } else {
                $percentage = ($start + $limit) * 100 / $totalResults;
                $percentage = number_format((float) $percentage, 1, '.', '');
            }
            // Output some stuff for jquery to use
            $response = array(
                'result' => $result,
                'start' => $start,
                'limit' => $limit,
                'totalResults' => $totalResults,
                'fileName' => $returnURL,
                'percentage' => $percentage
            );
            echo json_encode($response);
            // Make sure nothing else is sent, our file is done
            exit;
        }
    }
    public function setDate($accountNumber)
    {
        $endYearDate = is_null($accountNumber->last_deposit_to_ssb_date) || !isset($accountNumber->last_deposit_to_ssb_date)
            ?
            date('Y-m-d', strtotime($accountNumber->created_at . ' + ' . 1 . ' years'))
            :
            date('Y-m-d', strtotime($accountNumber->last_deposit_to_ssb_date . ' + ' . 1 . ' years'));
        $startYearDate = is_null($accountNumber->last_deposit_to_ssb_date) || !isset($accountNumber->last_deposit_to_ssb_date)
            ?
            date('Y-m-d', strtotime($accountNumber->created_at))
            :
            date('Y-m-d', strtotime($accountNumber->last_deposit_to_ssb_date));
        return ['startYearDate' => $startYearDate, 'endYearDate' => $endYearDate];
    }

    /** money back cron for custom account number on given date created by sourab on 03-11-2023 */
    /*
    public function money_back_amount_transfer_cron_run(Request $request)
    {
        $array = [];
        foreach ($request->formData as $k => $v) {
            $array[$v['name']] = $v['value'];
        }
        $msg = '';
        $accountNo = $array['account_no'];
        $accountmoneybackdate = $array['date'];
        $lastDepositToSsbDate = date('Y-m-d', strtotime($accountmoneybackdate));
        $todayDate = date('Y-m-d');
        if ($todayDate < $lastDepositToSsbDate) {
            $msg = "Can't run the process for future dates";
            $type = 'alert';
        } else {
            //get all money back investment account
            $accountNumbers = Memberinvestments::whereHas('plan', function ($q) {
                $q->where('plan_category_code', 'M')->where('plan_sub_category_code', 'B');
            })
                ->WhereIn('account_number', [$accountNo])
                ->where('is_mature', 1)
                ->get();
            //  ->chunk(20, function ($accountNumbers) use($accountmoneybackdate,$accountNo) {
            // the code process invetsment accounts in chunks of 20
            //Intialize varaibles to store transaction data
            $entryTime = date("h:i:s");
            $daybookRecord = array();
            $ssb = array();
            $amount = 0;
            $carryForwardAmount = 0;
            // Begin database transactions
            DB::beginTransaction();
            try {
                // set log File variable to store logs of the command
                $logName = 'moneyBack/money_back-' . date('Y-m-d', strtotime(now())) . '.log';
                $moneyBackSend = 'Not Sended';
                $cronChannel = 'moneyBack';
                // Call a service to store command start process
                // Iterate through each investment account
                foreach ($accountNumbers as $key => $accountNumber) {
                    //set Varioud variables based on account data
                    $branchId = $accountNumber->branch_id;
                    $BranchDetail = getBranchDetail($branchId);
                    $BranchManagerId = getBranchDetailManagerId($BranchDetail->manager_id);
                    $stateId = $BranchManagerId->state_id;
                    // intialize current date based on state
                    $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                    $currentDate = date('Y-m-d', strtotime(convertdate($accountmoneybackdate)));
                    // $currentDate = '2023-10-29'; // For hit manual cron
                    $inveDate = date('Y-m-d', strtotime($accountNumber['created_at']));
                    $investmentDate = \Carbon\Carbon::createFromFormat('Y-m-d', $inveDate);
                    $currentDateCheck = \Carbon\Carbon::createFromFormat('Y-m-d', $currentDate);
                    // Calculate number of  months between the investment start date and the current date for dynamic money back setting
                    $months = $currentDateCheck->diffInMonths($investmentDate);
                    $investementId = $accountNumber->id;
                    $memberId = $accountNumber->member_id;
                    $acNumber = $accountNumber->account_number;
                    $companyId = $accountNumber->company_id;
                    // Retrive the maturity start and enddate based on provided account Number
                    $dateInfo = $this->setDate($accountNumber);
                    // Call a service to store command inProgress process
                    // Check  if the endYearDate of dateInfo matches the current date
                    if ($dateInfo['endYearDate'] == $currentDate) {
                        // Calculate the total count of records with investment_id and plan_type_id  equal to certain values
                        $countTotalYear = InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $accountNumber->id)->where('plan_type_id', 3)->count();
                        // Check if a record with specific investment_id,plan_type_id and date exists
                        $checkExist = InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $accountNumber->id)->where('plan_type_id', 3)->where('date', $dateInfo['endYearDate'])->exists();
                        // Store a formatted created_at value in the session
                        Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($dateInfo['endYearDate']))));
                        // Calculate no of transactions based on tenure
                        $noOfTransaction = $accountNumber->tenure / 12;
                        // Check if the countTotalYear less than 7($noOfTransaction) and  matches the checkExist
                        if ($countTotalYear < 7 && !$checkExist) {
                            // Calculate moneyBack Amount based on provided accountNumber,dateInfo and months
                            $transferedData = $this->calculate($accountNumber, $dateInfo, $months);
                            // Match MoneyBackAmount greater than 0 then execute
                            if ($transferedData['moneyBackAmount'] > 0) {
                                // Intialize variable based on provided transferData
                                $amount = $transferedData['moneyBackAmount'];
                                $carryForwardAmount = $transferedData['carryForwardAmount'];
                                $amountArraySsb = array('1' => ($amount));
                                $trdata['saving_account_transaction_id'] = NULL;
                                $trdata['investment_id'] = $investementId;
                                $trdata['created_at'] = date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate'])));
                                // Generate daybook_ref_id (branch_daybook_reference)
                                $dayBookRef = CommanController::createBranchDayBookReference($amount);
                                $satRef = $dayBookRef;
                                $satRefId = $dayBookRef;
                                $createTransaction = $dayBookRef;
                                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                $vno = "";
                                // generate v_no
                                for ($k = 0; $k < 10; $k++) {
                                    $vno .= $chars[mt_rand(0, strlen($chars) - 1)];
                                }
                                $branch_id = $branchId;
                                $type = 3;
                                $sub_type = 34;
                                $type_id = $investementId;
                                $type_transaction_id = $investementId;
                                $associate_id = NULL;
                                $member_id = $memberId;
                                $branch_id_to = NULL;
                                $branch_id_from = NULL;
                                $opening_balance = $transferedData['moneyBackAmount'];
                                $closing_balance = $transferedData['moneyBackAmount'];
                                // Set description based on provided account Number
                                $description = ($acNumber) . ' Money Back amount ' . number_format((float) $amount, 2, '.', '');
                                // Set description_dr based on provided account Number  and get customer first name and last name (members table)
                                $description_dr = getMemberCustomData($memberId)->first_name . ' ' . getMemberCustomData($memberId)->last_name . ' Dr ' . number_format((float) $amount, 2, '.', '');
                                // Set description_cr based on provided account Number
                                $description_cr = 'To Monthly Income scheme A/C Cr ' . number_format((float) $amount, 2, '.', '');
                                $payment_type = 'CR';
                                $payment_mode = 3;
                                $currency_code = 'INR';
                                $amount_to_id = $memberId;
                                // Get customer first name and last name (members table)
                                $amount_to_name = getMemberCustomData($memberId)->first_name . ' ' . getMemberCustomData($memberId)->last_name;
                                $amount_from_id = NULL;
                                $amount_from_name = NULL;
                                $v_no = $vno;
                                $v_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($dateInfo['endYearDate'])));
                                $ssb_account_id_from = NULL;
                                $cheque_no = NULL;
                                $cheque_date = NULL;
                                $cheque_bank_from = NULL;
                                $cheque_bank_ac_from = NULL;
                                $cheque_bank_ifsc_from = NULL;
                                $cheque_bank_branch_from = NULL;
                                $cheque_bank_to = NULL;
                                $cheque_bank_ac_to = NULL;
                                $transction_no = NULL;
                                $transction_bank_from = NULL;
                                $transction_bank_ac_from = NULL;
                                $transction_bank_ifsc_from = NULL;
                                $transction_bank_branch_from = NULL;
                                $transction_bank_to = NULL;
                                $transction_bank_ac_to = NULL;
                                $transction_date = NULL;
                                $entry_date = NULL;
                                $entry_time = NULL;
                                $created_by = 1;
                                $created_by_id = 1;
                                $is_contra = NULL;
                                $contra_id = NULL;
                                $created_at = NULL;
                                $bank_id = NULL;
                                $bank_ac_id = NULL;
                                $transction_bank_to_name = NULL;
                                $transction_bank_to_ac_no = NULL;
                                $transction_bank_to_branch = NULL;
                                $transction_bank_to_ifsc = NULL;
                                $jv_unique_id = NULL;
                                $ssb_account_tran_id_from = NULL;
                                $cheque_type = NULL;
                                $cheque_id = NULL;
                                $cheque_bank_from_id = NULL;
                                $cheque_bank_ac_from_id = NULL;
                                $cheque_bank_to_name = NULL;
                                $cheque_bank_to_branch = NULL;
                                $cheque_bank_to_ac_no = NULL;
                                $cheque_bank_to_ifsc = NULL;
                                $transction_bank_from_id = NULL;
                                $transction_bank_from_ac_id = NULL;
                                // Retrive latest transaction of saving account based on account number (SavingAccountTransactionView It is mysql view)
                                $record3 = SavingAccountTransactionView::where('account_no', $accountNumber->ssb->account_no)->where('opening_date', '<=', date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate']))))->orderby('id', 'desc')->first();
                                $balance_update = $amount + $accountNumber->ssb->balance;
                                // Retrive ssb detail based on  money back account Number
                                $ssbBalance = SavingAccount::find($accountNumber->ssb->id);
                                $ssbBalance->balance = $balance_update;
                                $ssbBalance->save();
                                $ssb[] = [
                                    'saving_account_id' => $accountNumber->ssb->id,
                                    'account_no' => $accountNumber->ssb->account_no,
                                    'opening_balance' => (isset($record3->opening_balance)) ? $amount + $record3->opening_balance : $amount,
                                    'deposit' => $amount,
                                    'branch_id' => $branchId,
                                    'type' => 10,
                                    'withdrawal' => 0,
                                    'description' => $description,
                                    'currency_code' => 'INR',
                                    'payment_type' => 'CR',
                                    'payment_mode' => 3,
                                    'created_at' => $dateInfo['endYearDate'],
                                    'daybook_ref_id' => $dayBookRef,
                                    'created_at' => date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate']))),
                                ];
                                // Retrive latest transaction of saving account based on account number and endYearDate  (SavingAccountTransactionView It is mysql view)
                                $record4 = SavingAccountTransactionView::where('account_no', $accountNumber->ssb->account_no)->where('opening_date', '>', date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate']))))->get();
                                foreach ($record4 as $key => $value) {
                                    $sResult = SavingAccountTranscation::find($value->transaction_id);
                                    $nsResult['opening_balance'] = $value->opening_balance + $amount;
                                    $nsResult['updated_at'] = $dateInfo['endYearDate'];
                                    $sResult->update($nsResult);
                                }
                                $paymentMode = 4;
                                $amount_deposit_by_name = $accountNumber->ssb->ssbMember->first_name . ' ' . $accountNumber->ssb->ssbMember->last_name;
                                $trdata['saving_account_transaction_id'] = NULL;
                                $trdata['investment_id'] = $investementId;
                                $trdata['created_at'] = date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate'])));
                                $satRef = $dayBookRef;
                                $satRefId = $dayBookRef;
                                // Create a array to store daybook transaction based on transaction details and at last insert in dauybook table
                                $daybookRecord[] = [
                                    'transaction_type' => 18,
                                    'transaction_id' => $createTransaction,
                                    'saving_account_transaction_reference_id' => $satRefId,
                                    'investment_id' => $investementId,
                                    'account_no' => $acNumber,
                                    'member_id' => $memberId,
                                    'opening_balance' => $transferedData['openingBalance'] - $transferedData['moneyBackAmount'],
                                    'withdrawal' => $transferedData['moneyBackAmount'],
                                    'description' => 'Money Back amount transfer ' . $accountNumber->ssb->account_no,
                                    'branch_id' => $branchId,
                                    'branch_code' => getBranchCode($branchId)->branch_code,
                                    'amount' => $transferedData['moneyBackAmount'],
                                    'currency_code' => 'INR',
                                    'payment_mode' => 4,
                                    'payment_type' => 'DR',
                                    'payment_date' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($dateInfo['endYearDate']))),
                                    'amount_deposit_by_name' => getBranchName($branchId),
                                    'amount_deposit_by_id' => $branchId,
                                    'created_by_id' => 1,
                                    'created_by' => 2,
                                    'daybook_ref_id' => $dayBookRef,
                                    'created_at' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($dateInfo['endYearDate']))),
                                ];
                                $ssbCreateTran = $dayBookRef;
                                $description = $description;
                                // Store ssb transaction in daybook
                                $createDayBook = CommanController::createDayBookNew($ssbCreateTran, $satRefId, 1, $accountNumber->ssb->id, NULL, $accountNumber->ssb->member_id, $amount + $accountNumber->ssb->balance, $amount, $withdrawal = 0, $description, $accountNumber->ssb->account_no, $accountNumber->ssb->branch_id, $accountNumber->ssb->branch_code, $amountArraySsb, $paymentMode, NULL, $accountNumber->ssb->member_id, $accountNumber->ssb->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate']))), NULL, $online_payment_by = NULL, $accountNumber->ssb->id, 'CR', NULL, NULL, NULL, NULL, NULL, $companyId);
                                // Retrive saving account head id based on company id
                                $getPlan = Plans::where('company_id', $accountNumber->company_id)->where('plan_category_code', 'S')->first('deposit_head_id');
                                $head411 = $getPlan->deposit_head_id;
                                // Create  all_head_transactions  based on transaction details and at last insert in all_head_transactions table (CR)
                                $allTranRDonline = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head411, $type, $sub_type, $accountNumber->ssb->id, $accountNumber->ssb->id, $accountNumber->ssb->associate_id, $accountNumber->ssb->member_id, $branch_id_to, $branch_id_from, $amount, $accountNumber->ssb->account_no . ' Money Back amount' . $amount, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, NULL, NULL, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                                // Create a  branch_daybook transaction  based on transaction details and at last insert in branch_daybook table (DR)
                                $daybookInvest = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $accountNumber->id, $accountNumber->id, $accountNumber->associate_id, $accountNumber->member_id, $branch_id, $branch_id, $amount, $description, $description, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $dateInfo['endYearDate'], $entry_time, $created_by, $created_by_id, $dateInfo['endYearDate'], NULL, NULL, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                                // Create a  branch_daybook transaction  based on transaction details and at last insert in branch_daybook table (CR)
                                $daybookInvest = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $accountNumber->ssb->id, $accountNumber->ssb->id, $accountNumber->associate_id, $accountNumber->member_id, $branch_id, $branch_id, $amount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $dateInfo['endYearDate'], $entry_time, $created_by, $created_by_id, $dateInfo['endYearDate'], NULL, NULL, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                                $head5 = $accountNumber->plan->deposit_head_id;
                                // $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head5, $type, $sub_type, $accountNumber->id, $accountNumber->id, $accountNumber->associate_id, $accountNumber->member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                                // Create a  all_head_transactions  based on transaction details and at last insert in all_head_transactions table (DR)
                                $allTranRDonline = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head5, $type, $sub_type, $accountNumber->id, $accountNumber->id, $accountNumber->associate_id, $accountNumber->member_id, $branch_id_to, $branch_id_from, $amount, $description, 'DR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, NULL, NULL, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                                // Create InvestmentMonthlyYearlyInterestDeposits transaction in (investment_monthly_yearly_interest_deposits)
                                InvestmentMonthlyYearlyInterestDeposits::create([
                                    'investment_id' => $investementId,
                                    'plan_type_id' => 3,
                                    'fd_amount' => 0,
                                    'yearly_deposit_amount' => $amount,
                                    'available_amount' => 0,
                                    'date' => $dateInfo['endYearDate'],
                                    'carry_forward_amount' => $carryForwardAmount,
                                    'fd_amount_with_interest' => 0,
                                    'interest_amount' => 0,
                                ]);
                                // Head Implement 
                            }
                            $cBalance = ($accountNumber->current_balance - $amount);
                            // update current_balance,last_deposit_to_ssb_amount,last_deposit_to_ssb_date,carry_forward_amount based on provided transaction and account Number
                            Memberinvestments::where('id', $investementId)->update([
                                'current_balance' => $cBalance,
                                'last_deposit_to_ssb_amount' => ($amount),
                                'last_deposit_to_ssb_date' => $dateInfo['endYearDate'],
                                'carry_forward_amount' => $carryForwardAmount,
                            ]);
                            $moneyBackSend = 'Sended';
                            // Store Logs
                            // \Log::info('Money Back of --'.$accountNumber->account_number.' on '.$dateInfo['endYearDate']. ' and cron running date is '.$currentDate);
                        }
                        $type = 'success';
                        $msg = "Money back transferred successfully for account no  $accountNo";
                    } else {
                        $type = 'alert';
                        $endYearDate = $dateInfo['endYearDate'];
                        // $msg = "Money back account no $accountNo for money back date is $endYearDate and not sent on $currentDate";
                        $msg = "Money back Date has value $currentDate it should be $endYearDate";
                    }
                    // Store Logs in moneyBackj Channel
                    // \Log::channel('moneyBack')->info('MemberId- '.$accountNumber->member_id.'Account Number - '.$accountNumber->account_number.', InvestmentId -'.$accountNumber->id. ', Current Balance- '.$accountNumber->current_balance.' , Current Amount - ' . $amount. ', Carry Forward  - ' .$carryForwardAmount.', moneyBackSend - ' .$moneyBackSend);
                    // \Log::channel('moneyBack')->info('MemberId- '.$accountNumber->member_id.'Account Number - '.$accountNumber->account_number.', InvestmentId -'.$accountNumber->id. ', Current Balance- '.$accountNumber->current_balance.' , Current Amount - ' . $amount. ', Carry Forward  - ' .$carryForwardAmount.', moneyBackSend' .$moneyBackSend);
                }
                // Check daybookRecord array is empty or not
                if (count($daybookRecord) > 0) {
                    $transcation = Daybook::insert($daybookRecord);
                }
                // Check ssb array is empty or not
                if (count($ssb) > 0) {
                    $ssbAccountTran = SavingAccountTranscation::insert($ssb);
                }
                DB::Commit();
            } catch (\Exception $e) {
                DB::rollback();
                $type = 'error';
                $msg = 'money back cron run successfully , having error !! ' . $e->getLine();
            }
        }
        return (['msg' => $msg, 'type' => $type]);
    }
    */

    public function money_back_amount_transfer_cron_run(Request $request)
    {
        $array = [];
        foreach ($request->formData as $k => $v) {
            $array[$v['name']] = $v['value'];
        }
        $msg = '';
        $type = 'alert';
        $accountNo = $array['account_no'];
        $accountmoneybackdate = $array['date'];
        $lastDepositToSsbDate = date('Y-m-d', strtotime($accountmoneybackdate));
        $todayDate = date('Y-m-d');
        if ($todayDate < $lastDepositToSsbDate) {
            $msg = "Can't run the process for future dates";
            $type = 'alert';
        } else {
            //get all money back investment account
            $accountNumbers = Memberinvestments::whereHas('plan', function ($q) {
                $q->where('plan_category_code', 'M')->where('plan_sub_category_code', 'B');
            })
                ->WhereIn('account_number', [$accountNo])
                ->where('is_mature', 1)
                ->get();
            //  ->chunk(20, function ($accountNumbers) use($accountmoneybackdate,$accountNo) {
            // the code process invetsment accounts in chunks of 20
            //Intialize varaibles to store transaction data
            $entryTime = date("h:i:s");
            $daybookRecord = array();
            $ssb = array();
            $amount = 0;
            $carryForwardAmount = 0;
            // Begin database transactions
            DB::beginTransaction();
            try {
                // set log File variable to store logs of the command
                $logName = 'moneyBack/money_back-' . date('Y-m-d', strtotime(now())) . '.log';
                $moneyBackSend = 'Not Sended';
                $cronChannel = 'moneyBack';
                // Call a service to store command start process
                // Iterate through each investment account
                foreach ($accountNumbers as $key => $accountNumber) {
                    //set Varioud variables based on account data

                    // $edate = date('Y-m-d',strtotime(convertdate($dateInfo['endYearDate'])));
                    $edate = date('Y-m-d', strtotime(convertdate($array['created_at'])));

                    $branchId = $accountNumber->branch_id;
                    $BranchDetail = getBranchDetail($branchId);
                    $BranchManagerId = getBranchDetailManagerId($BranchDetail->manager_id);
                    $stateId = $BranchManagerId->state_id;
                    // intialize current date based on state
                    $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                    $currentDate = date('Y-m-d', strtotime(convertdate($accountmoneybackdate)));

                    $currentDate = date('Y-m-d', strtotime(convertdate($array['view_date']))); // For hit manual cron

                    $inveDate = date('Y-m-d', strtotime($accountNumber['created_at']));
                    $investmentDate = \Carbon\Carbon::createFromFormat('Y-m-d', $inveDate);
                    $currentDateCheck = \Carbon\Carbon::createFromFormat('Y-m-d', $currentDate);
                    // Calculate number of  months between the investment start date and the current date for dynamic money back setting
                    $months = $currentDateCheck->diffInMonths($investmentDate);
                    $investementId = $accountNumber->id;
                    $memberId = $accountNumber->member_id;
                    $acNumber = $accountNumber->account_number;
                    $companyId = $accountNumber->company_id;
                    // Retrive the maturity start and enddate based on provided account Number
                    $dateInfo = $this->setDate($accountNumber);
                    // Call a service to store command inProgress process
                    $endYearDateFormate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($currentDate)));
                    // Check  if the endYearDate of dateInfo matches the current date
                    if ($edate <= $currentDate) {
                        // Calculate the total count of records with investment_id and plan_type_id  equal to certain values
                        $countTotalYear = InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $accountNumber->id)->where('plan_type_id', 3)->count();
                        // Check if a record with specific investment_id,plan_type_id and date exists
                        $checkExist = InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $accountNumber->id)->where('plan_type_id', 3)->where('date', $currentDate)->exists();
                        // Store a formatted created_at value in the session
                        Session::put('created_at', $endYearDateFormate);
                        // Calculate no of transactions based on tenure
                        $noOfTransaction = $accountNumber->tenure / 12;
                        // Check if the countTotalYear less than 7($noOfTransaction) and  matches the checkExist
                        if ($countTotalYear < 7 && !$checkExist) {
                            // Calculate moneyBack Amount based on provided accountNumber,dateInfo and months
                            $transferedData = $this->calculate($accountNumber, $dateInfo, $months);
                            // Match MoneyBackAmount greater than 0 then execute
                            if ($transferedData['moneyBackAmount'] > 0) {
                                // Intialize variable based on provided transferData
                                $amount = $transferedData['moneyBackAmount'];
                                $carryForwardAmount = $transferedData['carryForwardAmount'];
                                $amountArraySsb = array('1' => ($amount));
                                $trdata['saving_account_transaction_id'] = NULL;
                                $trdata['investment_id'] = $investementId;
                                $trdata['created_at'] = $currentDate;
                                // Generate daybook_ref_id (branch_daybook_reference)
                                $dayBookRef = CommanController::createBranchDayBookReference($amount);
                                $satRef = $dayBookRef;
                                $satRefId = $dayBookRef;
                                $createTransaction = $dayBookRef;
                                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                $vno = "";
                                // generate v_no
                                for ($k = 0; $k < 10; $k++) {
                                    $vno .= $chars[mt_rand(0, strlen($chars) - 1)];
                                }
                                $branch_id = $branchId;
                                $type = 3;
                                $sub_type = 34;
                                $type_id = $investementId;
                                $type_transaction_id = $investementId;
                                $associate_id = NULL;
                                $member_id = $memberId;
                                $branch_id_to = NULL;
                                $branch_id_from = NULL;
                                $opening_balance = $transferedData['moneyBackAmount'];
                                $closing_balance = $transferedData['moneyBackAmount'];
                                // Set description based on provided account Number
                                $description = ($acNumber) . ' Money Back amount ' . number_format((float) $amount, 2, '.', '');
                                // Set description_dr based on provided account Number  and get customer first name and last name (members table)
                                $description_dr = getMemberCustomData($memberId)->first_name . ' ' . getMemberCustomData($memberId)->last_name . ' Dr ' . number_format((float) $amount, 2, '.', '');
                                // Set description_cr based on provided account Number
                                $description_cr = 'To Monthly Income scheme A/C Cr ' . number_format((float) $amount, 2, '.', '');
                                $payment_type = 'CR';
                                $payment_mode = 3;
                                $currency_code = 'INR';
                                $amount_to_id = $memberId;
                                // Get customer first name and last name (members table)
                                $amount_to_name = getMemberCustomData($memberId)->first_name . ' ' . getMemberCustomData($memberId)->last_name;
                                $amount_from_id = NULL;
                                $amount_from_name = NULL;
                                $v_no = $vno;
                                $v_date = $endYearDateFormate;
                                $ssb_account_id_from = NULL;
                                $cheque_no = NULL;
                                $cheque_date = NULL;
                                $cheque_bank_from = NULL;
                                $cheque_bank_ac_from = NULL;
                                $cheque_bank_ifsc_from = NULL;
                                $cheque_bank_branch_from = NULL;
                                $cheque_bank_to = NULL;
                                $cheque_bank_ac_to = NULL;
                                $transction_no = NULL;
                                $transction_bank_from = NULL;
                                $transction_bank_ac_from = NULL;
                                $transction_bank_ifsc_from = NULL;
                                $transction_bank_branch_from = NULL;
                                $transction_bank_to = NULL;
                                $transction_bank_ac_to = NULL;
                                $transction_date = NULL;
                                $entry_date = NULL;
                                $entry_time = NULL;
                                $created_by = Auth::user()->role_id == 3 ? '2' : '1';
                                $created_by_id = Auth::user()->id;
                                $is_contra = NULL;
                                $contra_id = NULL;
                                $created_at = NULL;
                                $bank_id = NULL;
                                $bank_ac_id = NULL;
                                $transction_bank_to_name = NULL;
                                $transction_bank_to_ac_no = NULL;
                                $transction_bank_to_branch = NULL;
                                $transction_bank_to_ifsc = NULL;
                                $jv_unique_id = NULL;
                                $ssb_account_tran_id_from = NULL;
                                $cheque_type = NULL;
                                $cheque_id = NULL;
                                $cheque_bank_from_id = NULL;
                                $cheque_bank_ac_from_id = NULL;
                                $cheque_bank_to_name = NULL;
                                $cheque_bank_to_branch = NULL;
                                $cheque_bank_to_ac_no = NULL;
                                $cheque_bank_to_ifsc = NULL;
                                $transction_bank_from_id = NULL;
                                $transction_bank_from_ac_id = NULL;
                                // Retrive latest transaction of saving account based on account number (SavingAccountTransactionView It is mysql view)
                                $record3 = SavingAccountTransactionView::whereAccountNo($accountNumber->ssb->account_no)
                                    ->where('opening_date', '<=', $edate)
                                    ->orderby('id', 'desc')
                                    ->first();
                                $balance_update = $amount + $accountNumber->ssb->balance;
                                // Retrive ssb detail based on  money back account Number
                                $ssbBalance = SavingAccount::find($accountNumber->ssb->id);
                                $ssbBalance->balance = $balance_update;
                                $ssbBalance->save();
                                $ssb[] = [
                                    'saving_account_id' => $accountNumber->ssb->id,
                                    'account_no' => $accountNumber->ssb->account_no,
                                    'opening_balance' => (isset($record3->opening_balance)) ? $amount + $record3->opening_balance : $amount,
                                    'deposit' => $amount,
                                    'branch_id' => $branchId,
                                    'type' => 10,
                                    'withdrawal' => 0,
                                    'description' => $description,
                                    'currency_code' => 'INR',
                                    'payment_type' => 'CR',
                                    'payment_mode' => 3,
                                    'daybook_ref_id' => $dayBookRef,
                                    'created_at' => $currentDate,
                                ];
                                // Retrive latest transaction of saving account based on account number and endYearDate  (SavingAccountTransactionView It is mysql view)
                                $record4 = SavingAccountTransactionView::whereAccountNo($accountNumber->ssb->account_no)->where('opening_date', '>', $edate)->get();
                                foreach ($record4 as $key => $value) {
                                    $sResult = SavingAccountTranscation::find($value->transaction_id);
                                    $nsResult['opening_balance'] = $value->opening_balance + $amount;
                                    $nsResult['updated_at'] = $edate;
                                    $sResult->update($nsResult);
                                }
                                $paymentMode = 4;
                                $amount_deposit_by_name = $accountNumber->ssb->ssbMember->first_name . ' ' . $accountNumber->ssb->ssbMember->last_name;
                                $trdata['saving_account_transaction_id'] = NULL;
                                $trdata['investment_id'] = $investementId;
                                $trdata['created_at'] = $currentDate;
                                $satRef = $dayBookRef;
                                $satRefId = $dayBookRef;
                                // Create a array to store daybook transaction based on transaction details and at last insert in dauybook table
                                $daybookRecord[] = [
                                    'transaction_type' => 18,
                                    'transaction_id' => $createTransaction,
                                    'saving_account_transaction_reference_id' => $satRefId,
                                    'investment_id' => $investementId,
                                    'account_no' => $acNumber,
                                    'member_id' => $memberId,
                                    'opening_balance' => $transferedData['openingBalance'] - $transferedData['moneyBackAmount'],
                                    'withdrawal' => $transferedData['moneyBackAmount'],
                                    'description' => 'Money Back amount transfer ' . $accountNumber->ssb->account_no,
                                    'branch_id' => $branchId,
                                    'branch_code' => getBranchCode($branchId)->branch_code,
                                    'amount' => $transferedData['moneyBackAmount'],
                                    'currency_code' => 'INR',
                                    'payment_mode' => 4,
                                    'payment_type' => 'DR',
                                    'payment_date' => $endYearDateFormate,
                                    'amount_deposit_by_name' => getBranchName($branchId),
                                    'amount_deposit_by_id' => $branchId,
                                    'created_by_id' => 1,
                                    'created_by' => 2,
                                    'daybook_ref_id' => $dayBookRef,
                                    'created_at' => $endYearDateFormate,
                                ];
                                $ssbCreateTran = $dayBookRef;
                                $description = $description;
                                // Store ssb transaction in daybook
                                $createDayBook = CommanController::createDayBookNew($ssbCreateTran, $satRefId, 1, $accountNumber->ssb->id, NULL, $accountNumber->ssb->member_id, $amount + $accountNumber->ssb->balance, $amount, $withdrawal = 0, $description, $accountNumber->ssb->account_no, $accountNumber->ssb->branch_id, $accountNumber->ssb->branch_code, $amountArraySsb, $paymentMode, NULL, $accountNumber->ssb->member_id, $accountNumber->ssb->account_no, 0, NULL, NULL, $edate, NULL, $online_payment_by = NULL, $accountNumber->ssb->id, 'CR', NULL, NULL, NULL, NULL, NULL, $companyId);
                                // Retrive saving account head id based on company id
                                $getPlan = Plans::where('company_id', $accountNumber->company_id)->where('plan_category_code', 'S')->first('deposit_head_id');
                                $head411 = $getPlan->deposit_head_id;
                                // Create  all_head_transactions  based on transaction details and at last insert in all_head_transactions table (CR)
                                $allTranRDonline = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head411, $type, $sub_type, $accountNumber->ssb->id, $accountNumber->ssb->id, $accountNumber->ssb->associate_id, $accountNumber->ssb->member_id, $branch_id_to, $branch_id_from, $amount, $accountNumber->ssb->account_no . ' Money Back amount' . $amount, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, NULL, NULL, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                                // Create a  branch_daybook transaction  based on transaction details and at last insert in branch_daybook table (DR)
                                $daybookInvest = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $accountNumber->id, $accountNumber->id, $accountNumber->associate_id, $accountNumber->member_id, $branch_id, $branch_id, $amount, $description, $description, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $currentDate, $entry_time, $created_by, $created_by_id, $currentDate, NULL, NULL, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                                // Create a  branch_daybook transaction  based on transaction details and at last insert in branch_daybook table (CR)
                                $daybookInvest = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $accountNumber->ssb->id, $accountNumber->ssb->id, $accountNumber->associate_id, $accountNumber->member_id, $branch_id, $branch_id, $amount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $currentDate, $entry_time, $created_by, $created_by_id, $currentDate, NULL, NULL, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                                $head5 = $accountNumber->plan->deposit_head_id;
                                // $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head5, $type, $sub_type, $accountNumber->id, $accountNumber->id, $accountNumber->associate_id, $accountNumber->member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                                // Create a  all_head_transactions  based on transaction details and at last insert in all_head_transactions table (DR)
                                $allTranRDonline = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head5, $type, $sub_type, $accountNumber->id, $accountNumber->id, $accountNumber->associate_id, $accountNumber->member_id, $branch_id_to, $branch_id_from, $amount, $description, 'DR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, NULL, NULL, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                                // Create InvestmentMonthlyYearlyInterestDeposits transaction in (investment_monthly_yearly_interest_deposits)
                                InvestmentMonthlyYearlyInterestDeposits::create([
                                    'investment_id' => $investementId,
                                    'plan_type_id' => 3,
                                    'fd_amount' => 0,
                                    'yearly_deposit_amount' => $amount,
                                    'available_amount' => 0,
                                    'date' => $currentDate,
                                    'carry_forward_amount' => $carryForwardAmount,
                                    'fd_amount_with_interest' => 0,
                                    'interest_amount' => 0,
                                ]);
                                // Head Implement
                            }
                            $cBalance = ($accountNumber->current_balance - $amount);
                            // update current_balance,last_deposit_to_ssb_amount,last_deposit_to_ssb_date,carry_forward_amount based on provided transaction and account Number
                            Memberinvestments::where('id', $investementId)->update([
                                'current_balance' => $cBalance,
                                'last_deposit_to_ssb_amount' => ($amount),
                                'last_deposit_to_ssb_date' => $dateInfo['endYearDate'],
                                'carry_forward_amount' => $carryForwardAmount,
                            ]);
                            $moneyBackSend = 'Sended';
                            // Store Logs
                            // \Log::info('Money Back of --'.$accountNumber->account_number.' on '.$edate. ' and cron running date is '.$currentDate);
                        }
                        $type = 'success';
                        $msg = "Money back transferred successfully for account no  $accountNo";
                    } else {
                        $type = 'alert';
                        $endYearDate = $edate;
                        // $msg = "Money back account no $accountNo for money back date is $endYearDate and not sent on $currentDate";
                        $msg = "Money back Date has value $currentDate it should be $endYearDate";
                    }
                    // Store Logs in moneyBackj Channel
                    // \Log::channel('moneyBack')->info('MemberId- '.$accountNumber->member_id.'Account Number - '.$accountNumber->account_number.', InvestmentId -'.$accountNumber->id. ', Current Balance- '.$accountNumber->current_balance.' , Current Amount - ' . $amount. ', Carry Forward  - ' .$carryForwardAmount.', moneyBackSend - ' .$moneyBackSend);
                    // \Log::channel('moneyBack')->info('MemberId- '.$accountNumber->member_id.'Account Number - '.$accountNumber->account_number.', InvestmentId -'.$accountNumber->id. ', Current Balance- '.$accountNumber->current_balance.' , Current Amount - ' . $amount. ', Carry Forward  - ' .$carryForwardAmount.', moneyBackSend' .$moneyBackSend);
                }
                // Check daybookRecord array is empty or not
                if (count($daybookRecord) > 0) {
                    $transcation = Daybook::insert($daybookRecord);
                }
                // Check ssb array is empty or not
                if (count($ssb) > 0) {
                    $ssbAccountTran = SavingAccountTranscation::insert($ssb);
                }
                DB::Commit();
            } catch (\Exception $e) {
                DB::rollback();
                $type = 'error';
                $msg = 'money back cron run successfully , having error !! ' . $e->getLine();
            }
        }
        return (['msg' => $msg, 'type' => $type]);
    }
    public function money_back_amount_transfer_cron()
    {
        if (check_my_permission(auth()->user()->id, "339") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Cron | Money Back Amount Transfer Cron';
        $data['cron_type'] = 'mbat';
        return view('templates.admin.cron.mbat_cron', $data);
    }

    public function monthly_income_scheme_interest_transfer_cron()
    {
        if (check_my_permission(auth()->user()->id, "338") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Cron | Monthly Income Scheme Interest Transfer Cron';
        $data['cron_type'] = 'misit';
        return view('templates.admin.cron.miscit_cron', $data);
    }
    // code is commented and replaced by sourab on 09-04-24
    /*
    public function monthly_income_scheme_interest_transfer_cron_run(Request $request)
    {
        $array = [];
        foreach ($request->formData as $k => $v) {
            $array[$v['name']] = $v['value'];
        }
        $msg = '';
        $accountNo = $array['account_no'];
        $accountmoneybackdate = $array['date'];
        $lastDepositToSsbDate = date('Y-m-d', strtotime($accountmoneybackdate));
        $todayDate = date('Y-m-d');
        if ($todayDate < $lastDepositToSsbDate) {
            $msg = "Can't run the process for future dates";
            $type = 'alert';
        } else {
            // Retrieve all not matured (is_mature ,1)  accounts under the Monthly Income Scheme Plan .
            // If you want to execute the command for a specific account number, uncomment the 'account_number' condition.
            $sjInvestment = Memberinvestments::whereHas('plan', function ($q) {
                $q->where('plan_sub_category_code', 'I');
            })->with(['branch'])
                ->whereIn('account_number', [$accountNo])  // Uncomment this line to filter by account number
                ->where('is_mature', 1)
                ->get();
            //Intialize variables to store transaction data
            $entryTime = date("H:i:s");
            //  $cDate = Carbon::now()->format('Y-m-d');
            $cYear = Carbon::now()->format('Y');
            $cDate = date('Y-m-d', strtotime($accountmoneybackdate));  // It is used for execute manual cron on the particular date
            // Retrive current Financial Year
            $finacialYear = getFinacialYear();
            $fenddate = date("Y", strtotime(convertDate($finacialYear['dateEnd'])));
            $fstrtdate = date("Y", strtotime(convertDate($finacialYear['dateStart'])));
            $logName = 'mis/mis-' . date('Y-m-d', strtotime(now())) . '.log';
            // Begin database transactions
            DB::beginTransaction();
            try {
                // Call a service to store command start process
                // Iterate through each investment account
                foreach ($sjInvestment as $key => $val) {
                    // Call a service to store command inProgress process
                    $misSend = 'Not Sended';
                    $tdsAmountonInterest = 0;
                    // Check if the account number is not an Eli Account (Eli Accounts start with 'R-').
                    if (strpos($val->account_number, 'R-') === false) {
                        // Convert Year Tenure into months
                        $tenureMonths = $val->tenure * 12;
                        // Retrive roi from plan_tenures table based on plan and tenure
                        $interestRoi = PlanTenures::select('roi', 'id')->where('plan_id', $val->plan_id)->where('tenure', $tenureMonths)->first();
                        $financialyear = Carbon::parse($finacialYear['dateEnd']);
                        // Calculate total count of record with investment_id   equal to certain values
                        $countDepositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $val->id)->count();
                        // Calculate monthly Interest based on deposite_amount (deno) and roi
                        $monthlyInterest = round($val->deposite_amount * $interestRoi->roi / 1200);
                        $depositeAmount = $val->deposite_amount;
                        // Check last_deposit_to_ssb_amount,last_deposit_to_ssb_date is empty or not
                        // If last_deposit_to_ssb_amount,last_deposit_to_ssb_date is null then investmentOpeningdate is investment created_at date
                        if ($val->last_deposit_to_ssb_amount == '' && $val->last_deposit_to_ssb_date == '') {
                            $investmentOpeningDate = Carbon::parse($val->created_at);
                        }
                        // In this case investmentOpeningdate is the current financial Year start Date
                        else {
                            $investmentOpeningDate = Carbon::parse($finacialYear['dateStart']);
                            ;
                        }
                        $addMonth = 1;
                        // Check if last_deposit_to_ssb_date is empty then add one month in investment account created_at date
                        if ($val->last_deposit_to_ssb_date == '') {
                            $addOneMonth = date('Y-m-d', strtotime($val->created_at . ' + ' . $addMonth . ' months'));
                            $addOneDate = date('d/m/Y', strtotime($val->created_at . ' + ' . $addMonth . ' months'));
                        }
                        // Check if last_deposit_to_ssb_date is not  empty then add one month in last_deposit_to_ssb_date  date
                        else {
                            $addOneMonth = date('Y-m-d', strtotime($val->last_deposit_to_ssb_date . ' + ' . $addMonth . ' months'));
                            $addOneDate = date('d/m/Y', strtotime($val->last_deposit_to_ssb_date . ' + ' . $addMonth . ' months'));
                        }
                        $fenddate = date("Y", strtotime(convertDate($finacialYear['dateEnd'])));
                        $fstrtdate = date("Y", strtotime(convertDate($finacialYear['dateStart'])));
                        // Calculate number of month between investment created date and current Financial EndYear
                        $diffMonth = round($investmentOpeningDate->floatDiffInMonths($financialyear));
                        // Calculate the total interest amount based on the number of months and the monthly interest rate.
                        $totalAmount = $diffMonth * round($monthlyInterest);
                        //Check  if the pancard is exists for the customer
                        $penCard = get_member_id_proof($val->customer_id, 5);
                        $checkYear = date("Y", strtotime(convertDate($val->created_at)));
                        // Retrive latest transaction of member_investment_interest_tds table based on member_id and investment_id
                        $getLastRecord = \App\Models\MemberInvestmentInterestTds::where('member_id', $val->member_id)->where('investment_id', $val->id)->orderby('id', 'desc')->first();
                        // Calculate tds Amount on the totalAmount based on investment opening date,startdate,enddate
                        $tdsData = tdsCalculate($totalAmount, $val, $investmentOpeningDate, NULL, $fstrtdate, $fenddate);
                        // Check if the TDS (Tax Deducted at Source) amount in tdsData is not equal to 0.
                        if ($tdsData['tdsAmount'] != 0) {
                            // Calculate the TDS amount on interest based on the monthly interest and TDS percentage.
                            $tdsAmountonInterest = $tdsData['tdsPercentage'] * $monthlyInterest / 100;
                            $investmentTds = $tdsAmountonInterest;
                        } else {
                            $tdsAmountonInterest = 0;
                        }
                        // Retrive SavingAccount latest transaction with get customer detail  based in member_id
                        $ssbAccountDetails = SavingAccount::with('ssbMemberCustomer')->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no', 'company_id', 'associate_id', 'customer_id')->where('member_id', $val->member_id)->first();
                        // Check  countDepositInterest count is less than to  $val->tenure*12
                        if ($countDepositInterest < ($val->tenure * 12)) {
                            // Check mis execute date is equal to current date
                            if ($addOneMonth == $cDate) {
                                // If last_deposit_to_ssb_amount ,last_deposit_to_ssb_date is empty
                                if ($val->last_deposit_to_ssb_amount == '' && $val->last_deposit_to_ssb_date == '') {
                                    // Intialise variable based on provided account Number details
                                    $m1 = strtotime($val->created_at);
                                    $m2 = strtotime($cDate);
                                    $y1 = date('Y', $m1);
                                    $y2 = date('Y', $m2);
                                    $n1 = date('m', $m1);
                                    $n2 = date('m', $m2);
                                    // Calculate number of month between investment created at date and current date
                                    $mDiff = (($y2 - $y1) * 12) + ($n2 - $n1);
                                    $totalCalculate = round($monthlyInterest);
                                    // Iterate through each month diff (if monthDiff is  6 then iteration start form 1 and end on 6)
                                    for ($i = 1; $i <= $mDiff; $i++) {
                                        $createDate = date('Y-m-d', strtotime($val->created_at . ' + ' . $i . ' months'));
                                        $cMonth = date('M-Y', strtotime($val->created_at . ' + ' . $i . ' months'));
                                        // Update MemberInvestment  table
                                        Memberinvestments::where('id', $val->id)->update(['last_deposit_to_ssb_amount' => $totalCalculate, 'last_deposit_to_ssb_date' => $createDate, 'investment_interest_date' => $createDate]);
                                        // Create a record in a investment_monthly_yearly_interest_deposits	 table
                                        InvestmentMonthlyYearlyInterestDeposits::create([
                                            'investment_id' => $val->id,
                                            'plan_type_id' => 6,
                                            'monthly_deposit_amount' => $totalCalculate,
                                            'date' => $createDate,
                                        ]);
                                        // Update MemberInvestment  table - You can remove it
                                        Memberinvestments::where('id', $val->id)->update(['investment_interest_date' => $createDate]);
                                        // Head Implement
                                        // Store a formatted created_at value in the session
                                        Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($createDate))));
                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                        $vno = "";
                                        // Generate a v_no
                                        for ($i = 0; $i < 10; $i++) {
                                            $vno .= $chars[mt_rand(0, strlen($chars) - 1)];
                                        }
                                        $branch_id = $val->branch_id;
                                        $type = 3;
                                        $sub_type = 34;
                                        $type_id = $val->id;
                                        $type_transaction_id = $val->id;
                                        $associate_id = NULL;
                                        $member_id = $val->member_id;
                                        $branch_id_to = NULL;
                                        $branch_id_from = NULL;
                                        $opening_balance = $totalCalculate;
                                        $amount = $totalCalculate;
                                        $closing_balance = $totalCalculate;
                                        // Set description based on provided account Number for month
                                        $description = " Monthly Interest Payable (" . $cMonth . ")";
                                        // Set description_dr based on provided account Number  and get customer first name and last name (members table)
                                        $description_dr = getMemberCustom($val->customer_id)->first_name . ' ' . getMemberCustom($val->customer_id)->last_name . ' Dr ' . number_format((float) $totalCalculate, 2, '.', '');
                                        // Set description_cr based on provided account Number
                                        $description_cr = 'To Monthly Income scheme A/C Cr ' . number_format((float) $totalCalculate, 2, '.', '');
                                        $payment_type = 'CR';
                                        $payment_mode = 3;
                                        $currency_code = 'INR';
                                        $amount_to_id = $val->member_id;
                                        // Retrive Customer Details from members table
                                        $amount_to_name = getMemberCustom($val->customer_id)->first_name . ' ' . getMemberCustom($val->customer_id)->last_name;
                                        $amount_from_id = NULL;
                                        $amount_from_name = NULL;
                                        $v_no = $vno;
                                        $v_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($createDate)));
                                        $ssb_account_id_from = NULL;
                                        $cheque_no = NULL;
                                        $cheque_date = NULL;
                                        $cheque_bank_from = NULL;
                                        $cheque_bank_ac_from = NULL;
                                        $cheque_bank_ifsc_from = NULL;
                                        $cheque_bank_branch_from = NULL;
                                        $cheque_bank_to = NULL;
                                        $cheque_bank_ac_to = NULL;
                                        $transction_no = NULL;
                                        $transction_bank_from = NULL;
                                        $transction_bank_ac_from = NULL;
                                        $transction_bank_ifsc_from = NULL;
                                        $transction_bank_branch_from = NULL;
                                        $transction_bank_to = NULL;
                                        $transction_bank_ac_to = NULL;
                                        $transction_date = NULL;
                                        $entry_date = NULL;
                                        $entry_time = NULL;
                                        $created_by = 1;
                                        $created_by_id = 1;
                                        $is_contra = NULL;
                                        $contra_id = NULL;
                                        $created_at = NULL;
                                        $bank_id = NULL;
                                        $bank_ac_id = NULL;
                                        $transction_bank_to_name = NULL;
                                        $transction_bank_to_ac_no = NULL;
                                        $transction_bank_to_branch = NULL;
                                        $transction_bank_to_ifsc = NULL;
                                        $jv_unique_id = NULL;
                                        $ssb_account_tran_id_from = NULL;
                                        $cheque_type = NULL;
                                        $cheque_id = NULL;
                                        $cheque_bank_from_id = NULL;
                                        $cheque_bank_ac_from_id = NULL;
                                        $cheque_bank_to_name = NULL;
                                        $cheque_bank_to_branch = NULL;
                                        $cheque_bank_to_ac_no = NULL;
                                        $cheque_bank_to_ifsc = NULL;
                                        $transction_bank_from_id = NULL;
                                        $transction_bank_from_ac_id = NULL;
                                        // Retrive latest transaction saving_account_transaction  (Change it to SavingAccountTransactionView)
                                        $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($createDate))))->first();
                                        $balance_update = $totalCalculate + $ssbAccountDetails->balance;
                                        // Retrive Saving Account detail based on saving account id
                                        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                        $ssbBalance->balance = $balance_update;
                                        $ssbBalance->save();
                                        // Generate daybook_ref_id (branch_daybook_reference)
                                        $dayBookRef = CommanController::createBranchDayBookReference($totalCalculate);
                                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                                        if ($record1) {
                                            $ssb['opening_balance'] = $totalCalculate - $tdsAmountonInterest + $record1->opening_balance;
                                        } else {
                                            $ssb['opening_balance'] = $record1->opening_balance ?? 0;
                                        }
                                        $ssb['deposit'] = $totalCalculate - $tdsAmountonInterest;
                                        $ssb['branch_id'] = $val->branch_id;
                                        $ssb['type'] = 10;
                                        $ssb['withdrawal'] = 0;
                                        $ssb['description'] = "Received Monthly Interest(" . $cMonth . ") " . ($val->account_number);
                                        $ssb['currency_code'] = 'INR';
                                        $ssb['payment_type'] = 'CR';
                                        $ssb['payment_mode'] = 3;
                                        $ssb['company_id'] = $ssbAccountDetails->company_id;
                                        $ssb['daybook_ref_id'] = $dayBookRef;
                                        $ssb['created_at'] = date("Y-m-d", strtotime(convertDate($createDate)));
                                        // Create a record in saving_account_transactions table
                                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                        $saTranctionId = $ssbAccountTran->id;
                                        $ssb_account_id_to = $ssbAccountDetails->id;
                                        $ssb_account_tran_id_to = $ssbAccountTran->id;
                                        // Retrive latest transaction of saving account based on account number and endYearDate  (Convert it to SavingAccountTransactionView)
                                        $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($createDate))))->get();
                                        foreach ($record2 as $key => $value) {
                                            $nsResult = SavingAccountTranscation::find($value->id);
                                            $sResult['opening_balance'] = $value->opening_balance + $totalCalculate;
                                            $sResult['updated_at'] = $createDate;
                                            $nsResult->update($sResult);
                                        }
                                        $paymentMode = 4;
                                        $amount_deposit_by_name = $ssbAccountDetails['ssbMemberCustomer']->member->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']->member->last_name;
                                        $data['saving_account_transaction_id'] = $saTranctionId;
                                        $data['investment_id'] = $val->id;
                                        $data['created_at'] = date("Y-m-d", strtotime(convertDate($createDate)));
                                        $satRef = $dayBookRef;
                                        $satRefId = $dayBookRef;
                                        $amountArraySsb = array('1' => $totalCalculate);
                                        $ssbCreateTran = $dayBookRef;
                                        $desssb = "Tranferred to Saving A/C " . ($ssbAccountDetails->account_no);
                                        // Create a saving account  record in a daybooks table
                                        $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->associate_id, $ssbAccountDetails->member_id, $totalCalculate - $tdsAmountonInterest + $ssbAccountDetails->balance, $totalCalculate - $tdsAmountonInterest, $withdrawal = 0, $desssb, $ssbAccountDetails->account_no, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($createDate))), NULL, $online_payment_by = NULL, NULL, 'CR', $ssbAccountDetails->company_id);
                                        // Create a mis plan  record in a  daybooks table
                                        $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance, $totalCalculate, $withdrawal = 0, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($createDate))), NULL, $online_payment_by = NULL, NULL, 'CR', $val->company_id);
                                        // If tdsAmountonInterest greater than 0
                                        if ($tdsAmountonInterest > 0) {
                                            // Retrive latest record from member_investment_interest_tds
                                            $getLastRecord = MemberInvestmentInterestTds::where('member_id', $val->member_id)->where('investment_id', $val->id)->orderby('id', 'desc')->first();
                                            // Create a record in member_investment_interest_tds
                                            MemberInvestmentInterestTds::create([
                                                'member_id' => $val->member_id,
                                                'investment_id' => $val->id,
                                                'plan_type' => $val->plan_id,
                                                'branch_id' => $val->branch_id,
                                                'interest_amount' => $totalCalculate,
                                                'date_from' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($createDate))),
                                                'date_to' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($createDate))),
                                                'tdsamount_on_interest' => $tdsAmountonInterest,
                                                'tds_amount' => $tdsData['tdsAmount'],
                                                'tds_percentage' => $tdsData['tdsPercentage'],
                                                'created_at' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($createDate))),
                                            ]);
                                            $description = "TDS on Interest (" . $cMonth . ") @ " . number_format((float) $tdsData['tdsPercentage'], 0, '.', '') . '%';
                                            // Create a tds CR transaction  in all_head_transactions table
                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 62, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $tdsAmountonInterest, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $val->company_id);
                                            // Create a tds DR transaction  in branch_day_books table
                                            $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $tdsAmountonInterest, $description, $description, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $val->company_id);
                                            // Create a tds DR transaction  in daybooks table
                                            $createDayBookDR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $tdsAmountonInterest - $val->balance, 0, $tdsAmountonInterest, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($createDate))), NULL, $online_payment_by = NULL, NULL, 'DR', $val->company_id);
                                        }
                                        $description = " Monthly Interest Payable (" . $cMonth . ")";
                                        // Create a DR transaction  in daybooks table
                                        $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance - $tdsAmountonInterest, 0, $totalCalculate - $tdsAmountonInterest, $desssb, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($createDate))), NULL, $online_payment_by = NULL, NULL, 'DR', $val->company_id);
                                        $ssbHead = Plans::where('plan_category_code', 'S')->where('company_id', $ssbAccountDetails->company_id)->first();
                                        // Create a CR transaction  in all_head_transactions table
                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $ssbHead->deposit_head_id, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $amount - $tdsAmountonInterest, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $ssbAccountDetails->company_id);
                                        $head1 = 4;
                                        $head2 = 14;
                                        $head3 = 36;
                                        $head4 = NULL;
                                        $head5 = NULL;
                                        // Create a DR transaction  in all_head_transactions table
                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head3, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, 'DR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $val->company_id);
                                        // Create a CR transaction  in branch_day_books table
                                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $val->company_id);
                                        $amount = $totalCalculate - $tdsAmountonInterest;
                                        // Head Implement
                                    }
                                } elseif ($val->last_deposit_to_ssb_date != '') { // Check last_deposit_to_ssb_date is not null
                                    $cMonth = date('M-Y', strtotime($cDate));
                                    $m1 = strtotime($val->last_deposit_to_ssb_date);
                                    $m2 = strtotime($cDate);
                                    $y1 = date('Y', $m1);
                                    $y2 = date('Y', $m2);
                                    $n1 = date('m', $m1);
                                    $n2 = date('m', $m2);
                                    // Calculate number of month between investment created at date and current date
                                    $mDiff = (($y2 - $y1) * 12) + ($n2 - $n1);
                                    // Check mDiff greater than 0
                                    if ($mDiff > 0) {
                                        $totalCalculate = round($monthlyInterest);
                                        // Update MemberInvestment
                                        Memberinvestments::where('id', $val->id)->update(['last_deposit_to_ssb_amount' => $totalCalculate, 'last_deposit_to_ssb_date' => $addOneMonth, 'investment_interest_date' => $addOneMonth]);
                                        // Create a record in a investment_monthly_yearly_interest_deposits	 table
                                        InvestmentMonthlyYearlyInterestDeposits::create([
                                            'investment_id' => $val->id,
                                            'plan_type_id' => $val->plan->id,
                                            'monthly_deposit_amount' => $totalCalculate,
                                            'date' => $addOneMonth,
                                        ]);
                                        // Head Implement
                                        // Store a formatted created_at value in the session
                                        Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($cDate))));
                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                        $vno = "";
                                        // Generate v_no
                                        for ($i = 0; $i < 10; $i++) {
                                            $vno .= $chars[mt_rand(0, strlen($chars) - 1)];
                                        }
                                        $branch_id = $val->branch_id;
                                        $type = 3;
                                        $sub_type = 34;
                                        $type_id = $val->id;
                                        $type_transaction_id = $val->id;
                                        $associate_id = $val->associate_id;
                                        $member_id = $val->member_id;
                                        $branch_id_to = NULL;
                                        $branch_id_from = NULL;
                                        $opening_balance = $totalCalculate;
                                        $amount = $totalCalculate;
                                        $closing_balance = $totalCalculate;
                                        $description = " Monthly Interest Payable (" . $cMonth . ")";
                                        $payment_type = 'CR';
                                        $payment_mode = 3;
                                        $currency_code = 'INR';
                                        $amount_to_id = $val->member_id;
                                        // Retrive customer first name and last name from members table
                                        $amount_to_name = getMemberCustom($val->customer_id)->first_name . ' ' . getMemberCustom($val->customer_id)->last_name;
                                        $amount_from_id = NULL;
                                        $amount_from_name = NULL;
                                        $v_no = $vno;
                                        $v_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($cDate)));
                                        $ssb_account_id_from = NULL;
                                        $cheque_no = NULL;
                                        $cheque_date = NULL;
                                        $cheque_bank_from = NULL;
                                        $cheque_bank_ac_from = NULL;
                                        $cheque_bank_ifsc_from = NULL;
                                        $cheque_bank_branch_from = NULL;
                                        $cheque_bank_to = NULL;
                                        $cheque_bank_ac_to = NULL;
                                        $transction_no = NULL;
                                        $transction_bank_from = NULL;
                                        $transction_bank_ac_from = NULL;
                                        $transction_bank_ifsc_from = NULL;
                                        $transction_bank_branch_from = NULL;
                                        $transction_bank_to = NULL;
                                        $transction_bank_ac_to = NULL;
                                        $transction_date = NULL;
                                        $entry_date = NULL;
                                        $entry_time = NULL;
                                        $created_by = 1;
                                        $created_by_id = 1;
                                        $is_contra = NULL;
                                        $contra_id = NULL;
                                        $created_at = NULL;
                                        $bank_id = NULL;
                                        $bank_ac_id = NULL;
                                        $transction_bank_to_name = NULL;
                                        $transction_bank_to_ac_no = NULL;
                                        $transction_bank_to_branch = NULL;
                                        $transction_bank_to_ifsc = NULL;
                                        $jv_unique_id = NULL;
                                        $ssb_account_tran_id_from = NULL;
                                        $cheque_type = NULL;
                                        $cheque_id = NULL;
                                        $cheque_bank_from_id = NULL;
                                        $cheque_bank_ac_from_id = NULL;
                                        $cheque_bank_to_name = NULL;
                                        $cheque_bank_to_branch = NULL;
                                        $cheque_bank_to_ac_no = NULL;
                                        $cheque_bank_to_ifsc = NULL;
                                        $transction_bank_from_id = NULL;
                                        $transction_bank_from_ac_id = NULL;
                                        // Retrive saving Account details based on member id and company id
                                        $ssbAccountDetails = SavingAccount::with('ssbMemberCustomer')->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no', 'company_id', 'customer_id')->where('member_id', $val->member_id)->where('company_id', $val->company_id)->first();
                                        // Retrive record from saving_account_transaction based on provided date - You can update it with SavingAccountTransactionView
                                        $record3 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($addOneMonth))))->first();
                                        $balance_update = $totalCalculate + $ssbAccountDetails->balance;
                                        // Retrive saving account detail based on ssb id
                                        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                        // Generate daybook_ref_id (branch_daybook_reference)
                                        $dayBookRef = CommanController::createBranchDayBookReference($totalCalculate);
                                        $ssbBalance->balance = $balance_update;
                                        $ssbBalance->save();
                                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                                        if ($record3) {
                                            $ssb['opening_balance'] = $totalCalculate - $tdsAmountonInterest + $record3->opening_balance;
                                        } else {
                                            $ssb['opening_balance'] = $totalCalculate;
                                        }
                                        $ssb['deposit'] = $totalCalculate - $tdsAmountonInterest;
                                        $ssb['branch_id'] = $val->branch_id;
                                        $ssb['type'] = 10;
                                        $ssb['withdrawal'] = 0;
                                        $ssb['description'] = "Received Monthly Interest(" . $cMonth . ")" . ($val->account_number);
                                        ;
                                        $ssb['currency_code'] = 'INR';
                                        $ssb['payment_type'] = 'CR';
                                        $ssb['payment_mode'] = 3;
                                        $ssb['created_at'] = $addOneMonth;
                                        $ssb['company_id'] = $ssbAccountDetails->company_id;
                                        $ssb['daybook_ref_id'] = $dayBookRef;
                                        // Create transaction in saving_account_transactions table
                                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                        $saTranctionId = $ssbAccountTran->id;
                                        $ssb_account_id_to = $ssbAccountDetails->id;
                                        $ssb_account_tran_id_to = $ssbAccountTran->id;
                                        // Retrive record from saving_account_transaction based on provided date - You can update it with SavingAccountTransactionView
                                        $record4 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($cDate))))->get();
                                        foreach ($record4 as $key => $value) {
                                            $nsResult = SavingAccountTranscation::find($value->id);
                                            $sResult['opening_balance'] = $value->opening_balance + $totalCalculate;
                                            $sResult['updated_at'] = $addOneMonth;
                                            $nsResult->update($sResult);
                                        }
                                        $paymentMode = 4;
                                        $amount_deposit_by_name = $ssbAccountDetails['ssbMemberCustomer']->member->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']->member->last_name;
                                        $data['saving_account_transaction_id'] = $saTranctionId;
                                        $data['investment_id'] = $val->id;
                                        $data['created_at'] = date("Y-m-d", strtotime(convertDate($addOneMonth)));
                                        $satRef = $dayBookRef;
                                        $satRefId = $dayBookRef;
                                        $amountArraySsb = array('1' => $totalCalculate);
                                        $ssbCreateTran = $dayBookRef;
                                        $description = $description;
                                        // Set description_dr based on provided account Number  and get customer first name and last name (members table)
                                        $description_dr = getMemberCustom($val->customer_id)->first_name . ' ' . getMemberCustom($val->customer_id)->last_name . ' Dr ' . number_format((float) $totalCalculate, 2, '.', '');
                                        // Set description_cr based on provided account Number
                                        $description_cr = 'To Monthly Income scheme A/C Cr ' . number_format((float) $totalCalculate, 2, '.', '');
                                        // Set description based on provided account Number
                                        $desssb = "Tranferred to Saving A/C" . ($ssbAccountDetails->account_no);
                                        // Create a ssb  CR record in daybooks table
                                        $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->associate_id, $ssbAccountDetails->member_id, $totalCalculate - $tdsAmountonInterest + $ssbAccountDetails->balance, $totalCalculate - $tdsAmountonInterest, $withdrawal = 0, $desssb, $ssbAccountDetails->account_no, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($addOneMonth))), NULL, $online_payment_by = NULL, NULL, 'CR', $ssbAccountDetails->company_id);
                                        // Create a mis CR record in daybooks table
                                        $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance, $totalCalculate, $withdrawal = 0, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($addOneMonth))), NULL, $online_payment_by = NULL, NULL, 'CR', $val->company_id);
                                        if ($tdsAmountonInterest > 0) {
                                            // Retrive latest record from member_investment_interest_tds
                                            MemberInvestmentInterestTds::create([
                                                'member_id' => $val->member_id,
                                                'investment_id' => $val->id,
                                                'plan_type' => $val->plan_id,
                                                'branch_id' => $val->branch_id,
                                                'interest_amount' => $totalCalculate,
                                                'date_from' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($cDate))),
                                                'date_to' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($cDate))),
                                                'tdsamount_on_interest' => $tdsAmountonInterest,
                                                'tds_amount' => $tdsData['tdsAmount'],
                                                'tds_percentage' => $tdsData['tdsPercentage'],
                                                'created_at' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($cDate))),
                                            ]);
                                            $description = " Monthly Interest Payable (" . $cMonth . ")";
                                            // Create a CR record for tds amount in all_head_transactions
                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 62, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $tdsAmountonInterest, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $val->company_id);
                                            // Create a DR record for tds amount in branch_daybooks
                                            $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $tdsAmountonInterest, $description, $description, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $val->company_id);
                                            // Create a DR record for tds amount in day_books
                                            $createDayBookDR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $tdsAmountonInterest - $val->balance, 0, $tdsAmountonInterest, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($addOneMonth))), NULL, $online_payment_by = NULL, NULL, 'DR', $val->company_id);
                                        }
                                        $description = " Monthly Interest Payable (" . $cMonth . ")";
                                        // Create a DR record  in day_books
                                        $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance - $tdsAmountonInterest, 0, $totalCalculate - $tdsAmountonInterest, $desssb, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($addOneMonth))), NULL, $online_payment_by = NULL, NULL, 'DR', $val->company_id);
                                        // Retrive  a saving account head_id from plan_tenures table
                                        $ssbHead = Plans::where('plan_category_code', 'S')->where('company_id', $ssbAccountDetails->company_id)->first();
                                        // Create a CR record all_head_transactions
                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $ssbHead->deposit_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount - $tdsAmountonInterest, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $ssbAccountDetails->company_id);
                                        $head1 = 4;
                                        $head2 = 14;
                                        $head3 = 36;
                                        $head4 = NULL;
                                        $head5 = NULL;
                                        // Create a DR record all_head_transactions
                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head3, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, 'DR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $val->company_id);
                                        // Create a CR record branch_daybook
                                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $val->company_id);
                                        $amount = $totalCalculate - $tdsAmountonInterest;
                                        // Tds Entries in Head and Member Transqaction  
                                        // Head Implement
                                    }
                                }
                                $misSend = 'Sended';
                                $text = 'Dear Member, MIS Rs.' . $amount . ' of A/C' . $ssbAccountDetails->account_no . ' credited in your Saving A/C on ' . $addOneDate . 'TDS deducted as per Govt Rules. Samraddh Bestwin Micro Finance';
                                $temaplteId = 1207166634409628392;
                                $contactNumber = array();
                                $memberDetail = \App\Models\Member::find($val->customer_id);
                                $contactNumber[] = $memberDetail->mobile_no;
                                $sendToMember = new Sms();
                                $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                                $type = 'success';
                                $msg = "MIS transferred successfully for account no $accountNo";
                            } else {
                                $type = 'alert';
                                // $msg = "Monthly income scheme interest transfer account no $accountNo for monthly income transfer date is $addOneMonth and not sent on $cDate ";
                                $_cDate = date('d-m-Y', strtotime($cDate));
                                $_addOneMonth = date('d-m-Y', strtotime($addOneMonth));
                                $msg = "Monthly Income Scheme Interest Date has value $_cDate it should be $_addOneMonth";
                            }
                        }
                    } else {
                        $type = 'alert';
                        $msg = "Monthly income scheme interest transfer is not allowed for account number $accountNo because it starts with 1R-1'";
                    }
                    updateRenewalTransaction($val->account_number);
                    // Log::channel('mis')->info('MemberId- '.$val->member_id.'Account Number - '.$val->account_number.', InvestmentId -'.$val->id. ', Current Balance- '.$val->current_balance.' , Deno Amount - ' . $depositeAmount. ', Monthly Interest - ' .$monthlyInterest.', Tds Amount - ' .$tdsAmountonInterest.', misSend' .$misSend);
                }
                DB::Commit();
            } catch (\Exception $e) {
                DB::rollback();
                $msg = 'monthly income scheme interest transfer cron run successfully , having error !! om line - ' . $e->getLine();
                $type = 'error';
            }
        }
        return (['msg' => $msg, 'type' => $type]);
    }
    */
    public function monthly_income_scheme_interest_transfer_cron_run(Request $request)
    {
        $array = [];
        foreach ($request->formData as $k => $v) {
            $array[$v['name']] = $v['value'];
        }
        //Intialize variables to store transaction data
        $entryTime = date("H:i:s");
        $created_system_at = date('Y-m-d  ' . $entryTime . "", strtotime($array['view_date']));
        $msg = '';
        $accountNo = $array['account_no'];
        $accountmoneybackdate = $array['date'];
        $lastDepositToSsbDate = date('Y-m-d', strtotime($accountmoneybackdate));
        $todayDate = date('Y-m-d');
        if ($todayDate < $lastDepositToSsbDate) {
            $msg = "Can't run the process for future dates";
            $type = 'alert';
        } else {
            // Retrieve all not matured (is_mature ,1)  accounts under the Monthly Income Scheme Plan .
            // If you want to execute the command for a specific account number, uncomment the 'account_number' condition.
            $sjInvestment = Memberinvestments::whereHas('plan', function ($q) {
                $q->where('plan_sub_category_code', 'I');
            })->with(['branch'])
                ->whereIn('account_number', [$accountNo])  // Uncomment this line to filter by account number
                ->where('is_mature', 1)
                ->get();
            $branchId = $sjInvestment[0]->branch_id;
            $BranchDetail = getBranchDetail($branchId);
            $BranchManagerId = getBranchDetailManagerId($BranchDetail->manager_id);
            $stateId = $BranchManagerId->state_id;

            //  $cDate = Carbon::now()->format('Y-m-d');
            $cYear = Carbon::now()->format('Y');
            $cDate = date('Y-m-d', strtotime($accountmoneybackdate));  // It is used for execute manual cron on the particular date
            // Retrive current Financial Year
            $finacialYear = getFinacialYear();
            $fenddate = date("Y", strtotime(convertDate($finacialYear['dateEnd'])));
            $fstrtdate = date("Y", strtotime(convertDate($finacialYear['dateStart'])));
            $logName = 'mis/mis-' . date('Y-m-d', strtotime(now())) . '.log';
            // Begin database transactions
            DB::beginTransaction();
            try {
                // Call a service to store command start process
                // Iterate through each investment account
                foreach ($sjInvestment as $key => $val) {
                    // Call a service to store command inProgress process
                    $misSend = 'Not Sended';
                    $tdsAmountonInterest = 0;
                    // Check if the account number is not an Eli Account (Eli Accounts start with 'R-').
                    if (strpos($val->account_number, 'R-') === false) {
                        // Convert Year Tenure into months
                        $tenureMonths = $val->tenure * 12;
                        // Retrive roi from plan_tenures table based on plan and tenure
                        $interestRoi = PlanTenures::select('roi', 'id')->where('plan_id', $val->plan_id)->where('tenure', $tenureMonths)->first();
                        $financialyear = Carbon::parse($finacialYear['dateEnd']);
                        // Calculate total count of record with investment_id   equal to certain values
                        $countDepositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $val->id)->count();
                        // Calculate monthly Interest based on deposite_amount (deno) and roi
                        $monthlyInterest = round($val->deposite_amount * $interestRoi->roi / 1200);
                        $depositeAmount = $val->deposite_amount;
                        // Check last_deposit_to_ssb_amount,last_deposit_to_ssb_date is empty or not
                        // If last_deposit_to_ssb_amount,last_deposit_to_ssb_date is null then investmentOpeningdate is investment created_at date
                        if ($val->last_deposit_to_ssb_amount == '' && $val->last_deposit_to_ssb_date == '') {
                            $investmentOpeningDate = Carbon::parse($val->created_at);
                        }
                        // In this case investmentOpeningdate is the current financial Year start Date
                        else {
                            $investmentOpeningDate = Carbon::parse($finacialYear['dateStart']);
                        }
                        $addMonth = 1;
                        // Check if last_deposit_to_ssb_date is empty then add one month in investment account created_at date
                        if ($val->last_deposit_to_ssb_date == '') {
                            $addOneMonth = date('Y-m-d', strtotime($val->created_at . ' + ' . $addMonth . ' months'));
                            $addOneDate = date('d/m/Y', strtotime($val->created_at . ' + ' . $addMonth . ' months'));
                        }
                        // Check if last_deposit_to_ssb_date is not  empty then add one month in last_deposit_to_ssb_date  date
                        else {
                            $addOneMonth = date('Y-m-d', strtotime($val->last_deposit_to_ssb_date . ' + ' . $addMonth . ' months'));
                            $addOneDate = date('d/m/Y', strtotime($val->last_deposit_to_ssb_date . ' + ' . $addMonth . ' months'));
                        }
                        $fenddate = date("Y", strtotime(convertDate($finacialYear['dateEnd'])));
                        $fstrtdate = date("Y", strtotime(convertDate($finacialYear['dateStart'])));
                        // Calculate number of month between investment created date and current Financial EndYear
                        $diffMonth = round($investmentOpeningDate->floatDiffInMonths($financialyear));
                        // Calculate the total interest amount based on the number of months and the monthly interest rate.
                        $totalAmount = $diffMonth * round($monthlyInterest);
                        //Check  if the pancard is exists for the customer
                        $penCard = get_member_id_proof($val->customer_id, 5);
                        $checkYear = date("Y", strtotime(convertDate($val->created_at)));
                        // Retrive latest transaction of member_investment_interest_tds table based on member_id and investment_id
                        $getLastRecord = \App\Models\MemberInvestmentInterestTds::where('member_id', $val->member_id)
                            ->where('investment_id', $val->id)
                            ->orderby('id', 'desc')
                            ->first();
                        // Calculate tds Amount on the totalAmount based on investment opening date,startdate,enddate
                        $tdsData = tdsCalculate($totalAmount, $val, $investmentOpeningDate, NULL, $fstrtdate, $fenddate);
                        // Check if the TDS (Tax Deducted at Source) amount in tdsData is not equal to 0.
                        if ($tdsData['tdsAmount'] != 0) {
                            // Calculate the TDS amount on interest based on the monthly interest and TDS percentage.
                            $tdsAmountonInterest = $tdsData['tdsPercentage'] * $monthlyInterest / 100;
                            $investmentTds = $tdsAmountonInterest;
                        } else {
                            $tdsAmountonInterest = 0;
                        }
                        // Retrive SavingAccount latest transaction with get customer detail  based in member_id
                        $ssbAccountDetails = SavingAccount::with('ssbMemberCustomer')
                            ->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no', 'company_id', 'associate_id', 'customer_id')
                            ->where('member_id', $val->member_id)
                            ->first();
                        // Check  countDepositInterest count is less than to  $val->tenure*12
                        if ($countDepositInterest < ($val->tenure * 12)) {
                            // Check mis execute date is equal to current date
                            if ($addOneMonth == $cDate) {
                                // If last_deposit_to_ssb_amount ,last_deposit_to_ssb_date is empty
                                if ($val->last_deposit_to_ssb_amount == '' && $val->last_deposit_to_ssb_date == '') {
                                    // Intialise variable based on provided account Number details
                                    $m1 = strtotime($val->created_at);
                                    $m2 = strtotime($cDate);
                                    $y1 = date('Y', $m1);
                                    $y2 = date('Y', $m2);
                                    $n1 = date('m', $m1);
                                    $n2 = date('m', $m2);
                                    // Calculate number of month between investment created at date and current date
                                    $mDiff = (($y2 - $y1) * 12) + ($n2 - $n1);
                                    $totalCalculate = round($monthlyInterest);
                                    // Iterate through each month diff (if monthDiff is  6 then iteration start form 1 and end on 6)
                                    for ($i = 1; $i <= $mDiff; $i++) {
                                        $createDate = date('Y-m-d', strtotime($val->created_at . ' + ' . $i . ' months'));
                                        $cMonth = date('M-Y', strtotime($val->created_at . ' + ' . $i . ' months'));
                                        // Update MemberInvestment  table
                                        Memberinvestments::where('id', $val->id)->update([
                                            'last_deposit_to_ssb_amount' => $totalCalculate,
                                            'last_deposit_to_ssb_date' => $createDate,
                                            'investment_interest_date' => $createDate
                                        ]);
                                        // Create a record in a investment_monthly_yearly_interest_deposits	 table
                                        InvestmentMonthlyYearlyInterestDeposits::create([
                                            'investment_id' => $val->id,
                                            'plan_type_id' => 6,
                                            'monthly_deposit_amount' => $totalCalculate,
                                            'date' => $createDate,
                                        ]);
                                        // Update MemberInvestment  table - You can remove it
                                        Memberinvestments::where('id', $val->id)->update(['investment_interest_date' => $createDate]);
                                        // Head Implement
                                        // Store a formatted created_at value in the session

                                        // Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($createDate))));
                                        Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_system_at))));

                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                        $vno = "";
                                        // Generate a v_no
                                        for ($i = 0; $i < 10; $i++) {
                                            $vno .= $chars[mt_rand(0, strlen($chars) - 1)];
                                        }
                                        $branch_id = $val->branch_id;
                                        $type = 3;
                                        $sub_type = 34;
                                        $type_id = $val->id;
                                        $type_transaction_id = $val->id;
                                        $associate_id = NULL;
                                        $member_id = $val->member_id;
                                        $branch_id_to = NULL;
                                        $branch_id_from = NULL;
                                        $opening_balance = $totalCalculate;
                                        $amount = $totalCalculate;
                                        $closing_balance = $totalCalculate;
                                        // Set description based on provided account Number for month
                                        $description = " Monthly Interest Payable (" . $cMonth . ")";
                                        // Set description_dr based on provided account Number  and get customer first name and last name (members table)
                                        $description_dr = getMemberCustom($val->customer_id)->first_name . ' ' . getMemberCustom($val->customer_id)->last_name . ' Dr ' . number_format((float) $totalCalculate, 2, '.', '');
                                        // Set description_cr based on provided account Number
                                        $description_cr = 'To Monthly Income scheme A/C Cr ' . number_format((float) $totalCalculate, 2, '.', '');
                                        $payment_type = 'CR';
                                        $payment_mode = 3;
                                        $currency_code = 'INR';
                                        $amount_to_id = $val->member_id;
                                        // Retrive Customer Details from members table
                                        $amount_to_name = getMemberCustom($val->customer_id)->first_name . ' ' . getMemberCustom($val->customer_id)->last_name;
                                        $amount_from_id = NULL;
                                        $amount_from_name = NULL;
                                        $v_no = $vno;
                                        $v_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($createDate)));
                                        $ssb_account_id_from = NULL;
                                        $cheque_no = NULL;
                                        $cheque_date = NULL;
                                        $cheque_bank_from = NULL;
                                        $cheque_bank_ac_from = NULL;
                                        $cheque_bank_ifsc_from = NULL;
                                        $cheque_bank_branch_from = NULL;
                                        $cheque_bank_to = NULL;
                                        $cheque_bank_ac_to = NULL;
                                        $transction_no = NULL;
                                        $transction_bank_from = NULL;
                                        $transction_bank_ac_from = NULL;
                                        $transction_bank_ifsc_from = NULL;
                                        $transction_bank_branch_from = NULL;
                                        $transction_bank_to = NULL;
                                        $transction_bank_ac_to = NULL;
                                        $transction_date = NULL;
                                        $entry_date = NULL;
                                        $entry_time = NULL;
                                        $created_by = 1;
                                        $created_by_id = 1;
                                        $is_contra = NULL;
                                        $contra_id = NULL;
                                        $created_at = $created_system_at;
                                        $bank_id = NULL;
                                        $bank_ac_id = NULL;
                                        $transction_bank_to_name = NULL;
                                        $transction_bank_to_ac_no = NULL;
                                        $transction_bank_to_branch = NULL;
                                        $transction_bank_to_ifsc = NULL;
                                        $jv_unique_id = NULL;
                                        $ssb_account_tran_id_from = NULL;
                                        $cheque_type = NULL;
                                        $cheque_id = NULL;
                                        $cheque_bank_from_id = NULL;
                                        $cheque_bank_ac_from_id = NULL;
                                        $cheque_bank_to_name = NULL;
                                        $cheque_bank_to_branch = NULL;
                                        $cheque_bank_to_ac_no = NULL;
                                        $cheque_bank_to_ifsc = NULL;
                                        $transction_bank_from_id = NULL;
                                        $transction_bank_from_ac_id = NULL;
                                        // Retrive latest transaction saving_account_transaction  (Change it to SavingAccountTransactionView)
                                        $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($createDate))))->first();
                                        $balance_update = $totalCalculate + $ssbAccountDetails->balance;
                                        // Retrive Saving Account detail based on saving account id
                                        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                        $ssbBalance->balance = $balance_update;
                                        $ssbBalance->save();
                                        // Generate daybook_ref_id (branch_daybook_reference)
                                        $dayBookRef = CommanController::createBranchDayBookReference($totalCalculate);
                                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                                        if ($record1) {
                                            $ssb['opening_balance'] = $totalCalculate - $tdsAmountonInterest + $record1->opening_balance;
                                        } else {
                                            $ssb['opening_balance'] = $record1->opening_balance ?? 0;
                                        }
                                        $ssb['deposit'] = $totalCalculate - $tdsAmountonInterest;
                                        $ssb['branch_id'] = $val->branch_id;
                                        $ssb['type'] = 10;
                                        $ssb['withdrawal'] = 0;
                                        $ssb['description'] = "Received Monthly Interest(" . $cMonth . ") " . ($val->account_number);
                                        $ssb['currency_code'] = 'INR';
                                        $ssb['payment_type'] = 'CR';
                                        $ssb['payment_mode'] = 3;
                                        $ssb['company_id'] = $ssbAccountDetails->company_id;
                                        $ssb['daybook_ref_id'] = $dayBookRef;
                                        $ssb['created_at'] = date("Y-m-d", strtotime(convertDate($created_system_at)));
                                        // Create a record in saving_account_transactions table
                                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                        $saTranctionId = $ssbAccountTran->id;
                                        $ssb_account_id_to = $ssbAccountDetails->id;
                                        $ssb_account_tran_id_to = $ssbAccountTran->id;
                                        // Retrive latest transaction of saving account based on account number and endYearDate  (Convert it to SavingAccountTransactionView)
                                        $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($created_system_at))))->get();
                                        foreach ($record2 as $key => $value) {
                                            $nsResult = SavingAccountTranscation::find($value->id);
                                            $sResult['opening_balance'] = $value->opening_balance + $totalCalculate;
                                            $sResult['updated_at'] = $created_system_at;
                                            $nsResult->update($sResult);
                                        }
                                        $paymentMode = 4;
                                        $amount_deposit_by_name = $ssbAccountDetails['ssbMemberCustomer']->member->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']->member->last_name;
                                        $data['saving_account_transaction_id'] = $saTranctionId;
                                        $data['investment_id'] = $val->id;
                                        $data['created_at'] = date("Y-m-d", strtotime(convertDate($created_system_at)));
                                        $satRef = $dayBookRef;
                                        $satRefId = $dayBookRef;
                                        $amountArraySsb = array('1' => $totalCalculate);
                                        $ssbCreateTran = $dayBookRef;
                                        $desssb = "Tranferred to Saving A/C " . ($ssbAccountDetails->account_no);
                                        // Create a saving account  record in a daybooks table
                                        $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->associate_id, $ssbAccountDetails->member_id, $totalCalculate - $tdsAmountonInterest + $ssbAccountDetails->balance, $totalCalculate - $tdsAmountonInterest, $withdrawal = 0, $desssb, $ssbAccountDetails->account_no, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($created_system_at))), NULL, $online_payment_by = NULL, NULL, 'CR', $ssbAccountDetails->company_id);
                                        // Create a mis plan  record in a  daybooks table
                                        $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance, $totalCalculate, $withdrawal = 0, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($created_system_at))), NULL, $online_payment_by = NULL, NULL, 'CR', $val->company_id);
                                        // If tdsAmountonInterest greater than 0
                                        if ($tdsAmountonInterest > 0) {
                                            // Retrive latest record from member_investment_interest_tds
                                            $getLastRecord = MemberInvestmentInterestTds::where('member_id', $val->member_id)->where('investment_id', $val->id)->orderby('id', 'desc')->first();
                                            // Create a record in member_investment_interest_tds
                                            MemberInvestmentInterestTds::create([
                                                'member_id' => $val->member_id,
                                                'investment_id' => $val->id,
                                                'plan_type' => $val->plan_id,
                                                'branch_id' => $val->branch_id,
                                                'interest_amount' => $totalCalculate,
                                                'date_from' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_system_at))),
                                                'date_to' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_system_at))),
                                                'tdsamount_on_interest' => $tdsAmountonInterest,
                                                'tds_amount' => $tdsData['tdsAmount'],
                                                'tds_percentage' => $tdsData['tdsPercentage'],
                                                'created_at' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_system_at))),
                                            ]);
                                            $description = "TDS on Interest (" . $cMonth . ") @ " . number_format((float) $tdsData['tdsPercentage'], 0, '.', '') . '%';
                                            // Create a tds CR transaction  in all_head_transactions table

                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 62, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $tdsAmountonInterest, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $val->company_id);

                                            // Create a tds DR transaction  in branch_day_books table

                                            $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $tdsAmountonInterest, $description, $description, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $val->company_id);

                                            // Create a tds DR transaction  in daybooks table

                                            $createDayBookDR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $tdsAmountonInterest - $val->balance, 0, $tdsAmountonInterest, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($created_system_at))), NULL, $online_payment_by = NULL, NULL, 'DR', $val->company_id);

                                        }
                                        $description = " Monthly Interest Payable (" . $cMonth . ")";
                                        // Create a DR transaction  in daybooks table
                                        $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance - $tdsAmountonInterest, 0, $totalCalculate - $tdsAmountonInterest, $desssb, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($created_system_at))), NULL, $online_payment_by = NULL, NULL, 'DR', $val->company_id);
                                        $ssbHead = Plans::where('plan_category_code', 'S')->where('company_id', $ssbAccountDetails->company_id)->first();

                                        // Create a CR transaction  in all_head_transactions table
                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $ssbHead->deposit_head_id, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $amount - $tdsAmountonInterest, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $ssbAccountDetails->company_id);

                                        $head1 = 4;
                                        $head2 = 14;
                                        $head3 = 36;
                                        $head4 = NULL;
                                        $head5 = NULL;

                                        // Create a DR transaction  in all_head_transactions table

                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head3, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, 'DR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $val->company_id);

                                        // Create a CR transaction  in branch_day_books table

                                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $val->associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $val->company_id);

                                        $amount = $totalCalculate - $tdsAmountonInterest;
                                        // Head Implement
                                    }
                                } elseif ($val->last_deposit_to_ssb_date != '')  // Check last_deposit_to_ssb_date is not null
                                {
                                    $cMonth = date('M-Y', strtotime($cDate));
                                    $m1 = strtotime($val->last_deposit_to_ssb_date);
                                    $m2 = strtotime($cDate);
                                    $y1 = date('Y', $m1);
                                    $y2 = date('Y', $m2);
                                    $n1 = date('m', $m1);
                                    $n2 = date('m', $m2);
                                    // Calculate number of month between investment created at date and current date
                                    $mDiff = (($y2 - $y1) * 12) + ($n2 - $n1);
                                    // Check mDiff greater than 0
                                    if ($mDiff > 0) {
                                        $totalCalculate = round($monthlyInterest);
                                        // Update MemberInvestment
                                        Memberinvestments::where('id', $val->id)->update(['last_deposit_to_ssb_amount' => $totalCalculate, 'last_deposit_to_ssb_date' => $addOneMonth, 'investment_interest_date' => $addOneMonth]);
                                        // Create a record in a investment_monthly_yearly_interest_deposits	 table
                                        InvestmentMonthlyYearlyInterestDeposits::create([
                                            'investment_id' => $val->id,
                                            'plan_type_id' => $val->plan->id,
                                            'monthly_deposit_amount' => $totalCalculate,
                                            'date' => $addOneMonth,
                                        ]);
                                        // Head Implement
                                        // Store a formatted created_at value in the session

                                        // Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($cDate))));
                                        Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_system_at))));

                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                        $vno = "";
                                        // Generate v_no
                                        for ($i = 0; $i < 10; $i++) {
                                            $vno .= $chars[mt_rand(0, strlen($chars) - 1)];
                                        }
                                        $branch_id = $val->branch_id;
                                        $type = 3;
                                        $sub_type = 34;
                                        $type_id = $val->id;
                                        $type_transaction_id = $val->id;
                                        $associate_id = $val->associate_id;
                                        $member_id = $val->member_id;
                                        $branch_id_to = NULL;
                                        $branch_id_from = NULL;
                                        $opening_balance = $totalCalculate;
                                        $amount = $totalCalculate;
                                        $closing_balance = $totalCalculate;
                                        $description = " Monthly Interest Payable (" . $cMonth . ")";
                                        $payment_type = 'CR';
                                        $payment_mode = 3;
                                        $currency_code = 'INR';
                                        $amount_to_id = $val->member_id;
                                        // Retrive customer first name and last name from members table
                                        $amount_to_name = getMemberCustom($val->customer_id)->first_name . ' ' . getMemberCustom($val->customer_id)->last_name;
                                        $amount_from_id = NULL;
                                        $amount_from_name = NULL;
                                        $v_no = $vno;
                                        $v_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($cDate)));
                                        $ssb_account_id_from = NULL;
                                        $cheque_no = NULL;
                                        $cheque_date = NULL;
                                        $cheque_bank_from = NULL;
                                        $cheque_bank_ac_from = NULL;
                                        $cheque_bank_ifsc_from = NULL;
                                        $cheque_bank_branch_from = NULL;
                                        $cheque_bank_to = NULL;
                                        $cheque_bank_ac_to = NULL;
                                        $transction_no = NULL;
                                        $transction_bank_from = NULL;
                                        $transction_bank_ac_from = NULL;
                                        $transction_bank_ifsc_from = NULL;
                                        $transction_bank_branch_from = NULL;
                                        $transction_bank_to = NULL;
                                        $transction_bank_ac_to = NULL;
                                        $transction_date = NULL;
                                        $entry_date = NULL;
                                        $entry_time = NULL;
                                        $created_by = 1;
                                        $created_by_id = 1;
                                        $is_contra = NULL;
                                        $contra_id = NULL;
                                        $created_at = $created_system_at;
                                        $bank_id = NULL;
                                        $bank_ac_id = NULL;
                                        $transction_bank_to_name = NULL;
                                        $transction_bank_to_ac_no = NULL;
                                        $transction_bank_to_branch = NULL;
                                        $transction_bank_to_ifsc = NULL;
                                        $jv_unique_id = NULL;
                                        $ssb_account_tran_id_from = NULL;
                                        $cheque_type = NULL;
                                        $cheque_id = NULL;
                                        $cheque_bank_from_id = NULL;
                                        $cheque_bank_ac_from_id = NULL;
                                        $cheque_bank_to_name = NULL;
                                        $cheque_bank_to_branch = NULL;
                                        $cheque_bank_to_ac_no = NULL;
                                        $cheque_bank_to_ifsc = NULL;
                                        $transction_bank_from_id = NULL;
                                        $transction_bank_from_ac_id = NULL;
                                        // Retrive saving Account details based on member id and company id
                                        $ssbAccountDetails = SavingAccount::with('ssbMemberCustomer')->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no', 'company_id', 'customer_id')->where('member_id', $val->member_id)->where('company_id', $val->company_id)->first();
                                        // Retrive record from saving_account_transaction based on provided date - You can update it with SavingAccountTransactionView
                                        $record3 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($created_system_at))))->first();
                                        $balance_update = $totalCalculate + $ssbAccountDetails->balance;
                                        // Retrive saving account detail based on ssb id
                                        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                        // Generate daybook_ref_id (branch_daybook_reference)
                                        $dayBookRef = CommanController::createBranchDayBookReference($totalCalculate);
                                        $ssbBalance->balance = $balance_update;
                                        $ssbBalance->save();
                                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                                        if ($record3) {
                                            $ssb['opening_balance'] = $totalCalculate - $tdsAmountonInterest + $record3->opening_balance;
                                        } else {
                                            $ssb['opening_balance'] = $totalCalculate;
                                        }
                                        $ssb['deposit'] = $totalCalculate - $tdsAmountonInterest;
                                        $ssb['branch_id'] = $val->branch_id;
                                        $ssb['type'] = 10;
                                        $ssb['withdrawal'] = 0;
                                        $ssb['description'] = "Received Monthly Interest(" . $cMonth . ")" . ($val->account_number);
                                        $ssb['currency_code'] = 'INR';
                                        $ssb['payment_type'] = 'CR';
                                        $ssb['payment_mode'] = 3;
                                        $ssb['created_at'] = $created_system_at;
                                        $ssb['company_id'] = $ssbAccountDetails->company_id;
                                        $ssb['daybook_ref_id'] = $dayBookRef;
                                        // Create transaction in saving_account_transactions table
                                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                        $saTranctionId = $ssbAccountTran->id;
                                        $ssb_account_id_to = $ssbAccountDetails->id;
                                        $ssb_account_tran_id_to = $ssbAccountTran->id;
                                        // Retrive record from saving_account_transaction based on provided date - You can update it with SavingAccountTransactionView
                                        $record4 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($created_system_at))))->get();
                                        foreach ($record4 as $key => $value) {
                                            $nsResult = SavingAccountTranscation::find($value->id);
                                            $sResult['opening_balance'] = $value->opening_balance + $totalCalculate;
                                            $sResult['updated_at'] = $created_system_at;
                                            $nsResult->update($sResult);
                                        }
                                        $paymentMode = 4;
                                        $amount_deposit_by_name = $ssbAccountDetails['ssbMemberCustomer']->member->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']->member->last_name;
                                        $data['saving_account_transaction_id'] = $saTranctionId;
                                        $data['investment_id'] = $val->id;
                                        $data['created_at'] = date("Y-m-d", strtotime(convertDate($created_system_at)));
                                        $satRef = $dayBookRef;
                                        $satRefId = $dayBookRef;
                                        $amountArraySsb = array('1' => $totalCalculate);
                                        $ssbCreateTran = $dayBookRef;
                                        $description = $description;
                                        // Set description_dr based on provided account Number  and get customer first name and last name (members table)
                                        $description_dr = getMemberCustom($val->customer_id)->first_name . ' ' . getMemberCustom($val->customer_id)->last_name . ' Dr ' . number_format((float) $totalCalculate, 2, '.', '');
                                        // Set description_cr based on provided account Number
                                        $description_cr = 'To Monthly Income scheme A/C Cr ' . number_format((float) $totalCalculate, 2, '.', '');
                                        // Set description based on provided account Number
                                        $desssb = "Tranferred to Saving A/C " . ($ssbAccountDetails->account_no);

                                        // Create a ssb  CR record in daybooks table

                                        $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->associate_id, $ssbAccountDetails->member_id, $totalCalculate - $tdsAmountonInterest + $ssbAccountDetails->balance, $totalCalculate - $tdsAmountonInterest, $withdrawal = 0, $desssb, $ssbAccountDetails->account_no, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($created_system_at))), NULL, $online_payment_by = NULL, NULL, 'CR', $ssbAccountDetails->company_id);

                                        // Create a mis CR record in daybooks table

                                        $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance, $totalCalculate, $withdrawal = 0, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($created_system_at))), NULL, $online_payment_by = NULL, NULL, 'CR', $val->company_id);

                                        if ($tdsAmountonInterest > 0) {
                                            // Retrive latest record from member_investment_interest_tds
                                            MemberInvestmentInterestTds::create([
                                                'member_id' => $val->member_id,
                                                'investment_id' => $val->id,
                                                'plan_type' => $val->plan_id,
                                                'branch_id' => $val->branch_id,
                                                'interest_amount' => $totalCalculate,
                                                'date_from' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_system_at))),
                                                'date_to' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_system_at))),
                                                'tdsamount_on_interest' => $tdsAmountonInterest,
                                                'tds_amount' => $tdsData['tdsAmount'],
                                                'tds_percentage' => $tdsData['tdsPercentage'],
                                                'created_at' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_system_at))),
                                            ]);
                                            $description = " Monthly Interest Payable (" . $cMonth . ")";

                                            // Create a CR record for tds amount in all_head_transactions

                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 62, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $tdsAmountonInterest, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $val->company_id);

                                            // Create a DR record for tds amount in branch_daybooks

                                            $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $tdsAmountonInterest, $description, $description, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $val->company_id);

                                            // Create a DR record for tds amount in day_books

                                            $createDayBookDR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $tdsAmountonInterest - $val->balance, 0, $tdsAmountonInterest, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($created_system_at))), NULL, $online_payment_by = NULL, NULL, 'DR', $val->company_id);

                                        }
                                        $description = " Monthly Interest Payable (" . $cMonth . ")";

                                        // Create a DR record  in day_books
                                        $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance - $tdsAmountonInterest, 0, $totalCalculate - $tdsAmountonInterest, $desssb, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($created_system_at))), NULL, $online_payment_by = NULL, NULL, 'DR', $val->company_id);

                                        // Retrive  a saving account head_id from plan_tenures table
                                        $ssbHead = Plans::where('plan_category_code', 'S')->where('company_id', $ssbAccountDetails->company_id)->first();

                                        // Create a CR record all_head_transactions

                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $ssbHead->deposit_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount - $tdsAmountonInterest, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $ssbAccountDetails->company_id);

                                        $head1 = 4;
                                        $head2 = 14;
                                        $head3 = 36;
                                        $head4 = NULL;
                                        $head5 = NULL;

                                        // Create a DR record all_head_transactions

                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head3, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, 'DR', $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $val->company_id);

                                        // Create a CR record branch_daybook

                                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $val->company_id);

                                        $amount = $totalCalculate - $tdsAmountonInterest;

                                        // Tds Entries in Head and Member Transqaction 
                                        // Head Implement
                                    }
                                }
                                $misSend = 'Sended';
                                $text = 'Dear Member, MIS Rs.' . $amount . ' of A/C' . $ssbAccountDetails->account_no . ' credited in your Saving A/C on ' . $addOneDate . 'TDS deducted as per Govt Rules. Samraddh Bestwin Micro Finance';
                                $temaplteId = 1207166634409628392;
                                $contactNumber = array();
                                $memberDetail = \App\Models\Member::find($val->customer_id);
                                $contactNumber[] = $memberDetail->mobile_no;
                                $sendToMember = new Sms();
                                $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                                $type = 'success';
                                $msg = "MIS transferred successfully for account no $accountNo";
                            } else {
                                $type = 'alert';
                                // $msg = "Monthly income scheme interest transfer account no $accountNo for monthly income transfer date is $addOneMonth and not sent on $cDate ";
                                $_cDate = date('d-m-Y', strtotime($cDate));
                                $_addOneMonth = date('d-m-Y', strtotime($addOneMonth));
                                $msg = "Monthly Income Scheme Interest Date has value $_cDate it should be $_addOneMonth";
                            }
                        }
                    } else {
                        $type = 'alert';
                        $msg = "Monthly income scheme interest transfer is not allowed for account number $accountNo because it starts with 1R-1'";
                    }
                    updateRenewalTransaction($val->account_number);
                    // Log::channel('mis')->info('MemberId- '.$val->member_id.'Account Number - '.$val->account_number.', InvestmentId -'.$val->id. ', Current Balance- '.$val->current_balance.' , Deno Amount - ' . $depositeAmount. ', Monthly Interest - ' .$monthlyInterest.', Tds Amount - ' .$tdsAmountonInterest.', misSend' .$misSend);
                }
                DB::Commit();
            } catch (\Exception $e) {
                DB::rollback();
                $msg = 'monthly income scheme interest transfer cron run successfully , having error !! om line - ' . $e->getLine();
                $type = 'error';
            }
        }
        return (['msg' => $msg, 'type' => $type]);
    }
    public function investmentdetails(Request $request)
    {
        $view = ['view' => '', [], 'msg_type' => 'Incorrect Account Number !'];
        $account_no = $request->accountNo;
        $type = $request->type;
        $investment = Memberinvestments::with([
            'member',
            'associateMember',
            'ssb',
            'demandadvice',
            'InvestmentBalance',
            'ssbBalanceView',
        ])
            ->whereAccountNumber($account_no)
            ->whereInvestmentCorrectionRequest(0)
            ->whereRenewalCorrectionRequest(0)
            ->whereIsMature(1)
            ->first();
        if (isset($investment)) {
            $plan_id = $investment->plan_id;
            $company_id = $investment->company_id;
            if ((get_plan_type_money_back($plan_id, $company_id)) || (get_plan_type_monthly_income($plan_id, $company_id))) {
                if ($type == 'mbat') {
                    if (!get_plan_type_monthly_income($plan_id, $company_id)) {
                        $view = ['view' => view('templates.admin.cron.investment_view', ['data' => $investment, 'type' => $type])->render(), 'msg_type' => ($investment) ? 'success' : 'error'];
                    } else {
                        $view = ['view' => '', [], 'msg_type' => 'Investment Account no is not money back'];
                    }
                } else {
                    if (!get_plan_type_money_back($plan_id, $company_id)) {
                        $view = ['view' => view('templates.admin.cron.investment_view', ['data' => $investment, 'type' => $type])->render(), 'msg_type' => ($investment) ? 'success' : 'error'];
                    } else {
                        $view = ['view' => '', [], 'msg_type' => 'Investment Account no is not Monthly income'];
                    }
                }
            } else {
                $view = ['view' => '', [], 'msg_type' => 'Investment Account not Found'];
            }
        }
        return \Response::json($view);
        // '597470800004','597870800022','597870800023'
    }

    public function amount_transfer_cron(Request $request)
    {
        $array = [];
        foreach ($request->formData as $k => $v) {
            $array[$v['name']] = $v['value'];
        }
        $return['type'] = 'alert';
        $plan_id = $array['plan_id'];
        $company_id = $array['company_id'];
        if ($array['cron_type'] == 'mbat') {
            // check if the plan is money back or not
            if (get_plan_type_money_back($plan_id, $company_id)) {
                $return = $this->money_back_amount_transfer_cron_run($request);
            } else {
                $return['msg'] = 'Account no is not money back';
            }
        } else {
            // check if account is monthaly income or not
            if (get_plan_type_monthly_income($plan_id, $company_id)) {
                $return = $this->monthly_income_scheme_interest_transfer_cron_run($request);
            } else {
                $return['msg'] = 'Account no is not Monthly income';
            }
        }
        $response = [
            'msg' => $return['msg'],
            'type' => $return['type'],
        ];
        return $response;
    }

    public function bank_holidays_cron()
    {
        if (check_my_permission(auth()->user()->id, "311") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Cron | Saturday / Sunday Holiday Cron ";
        return view('templates.admin.cron.holiday', $data);
    }

    /** this code is created by aman sir ,
     * last modify by sourab on 13-11-2023
     */
    /*
    public function getSaturday(Request $request)
    {
        // Arrays to store Saturdays, Sundays, and data for Saturday events
        $array = [];
        $saturdays = array();
        $sundays = array();
        $dataSaturday = array();
        $dataSunday = array();
        $saturdayCount = 0;
        $eventInserted = false;
        foreach ($request->formData as $key => $val) {
            $array[$val['name']] = $val['value'];
        }
        $year = $array['year'];
        $lastinserteddate = Event::orderBy('start_date', 'desc')->value('start_date');
        $lastEntryEvent = date('Y', strtotime($lastinserteddate));
        if ($lastEntryEvent == $year) {
            $msg = ['type' => 'alert', 'msg' => "Event Already created on $year year"];
        } else {
            DB::begintransaction();
            try {

                // Get the first day of the month in a given year
                $firstDayofMonth = Carbon::createFromDate($year, 1, 1);
                // Get the last day of the given year
                $lastDayofMonth = $firstDayofMonth->copy()->endOfYear();
                // Get all states from the states table
                $states = State::get();
                // Loop for all days in a month
                while ($firstDayofMonth->lte($lastDayofMonth)) {
                    // Condition to check if the day is a Saturday and under bank holiday
                    if ($firstDayofMonth->isSaturday()) {
                        // Check if it is the 2nd or 4th Saturday
                        $weekNumber = $firstDayofMonth->week;
                        // getAllSaturdayCount is a global function to get all 2nd and 4th saterday's
                        if (in_array($weekNumber,getAllSaturdayCount($year))) {
                            // $saturdays[] = $firstDayofMonth->copy()->toDateString();
                            // Loop through states to create data for Saturday events
                            foreach ($states as $state) {
                                $dataSaturday[] = [
                                    'state_id' => $state['id'],
                                    'title' => 'Saturday Holiday',
                                    'start_date' => $firstDayofMonth->copy()->toDateString(),
                                    'end_date' => $firstDayofMonth->copy()->toDateString(),
                                    'month' => $firstDayofMonth->month,
                                ];
                                p($state);
                            }
                        }
                    }
                    // Condition to check if the day is a Sunday
                    if ($firstDayofMonth->isSunday()) {
                        // $sundays[] = $firstDayofMonth->copy()->toDateString();
                        // Loop through states to create data for Sunday events
                        foreach ($states as $state) {
                            $dataSaturday[] = [
                                'state_id' => $state['id'],
                                'title' => 'Sunday Holiday',
                                'start_date' => $firstDayofMonth->copy()->toDateString(),
                                'end_date' => $firstDayofMonth->copy()->toDateString(),
                                'month' => $firstDayofMonth->month,
                            ];
                        }
                    }
                    $firstDayofMonth->addDay();
                }
                dd($dataSaturday);
                // Insert all holiday Sundays/Saturdays for the given year into the event table
                Event::insert($dataSaturday);
                // Set success or error message based on the insertion result
                DB::commit();
                $msg = ['type' => 'success', 'msg' => "Events created successfully for year $year"];
            } catch (\Exception $e) {
                DB::rollback();
                dd(
                    $e->getMessage(),$e->getLine(),$e->getFile(),$e->getCode()
                );
                $msg = ['type' => 'error', 'msg' => "Events not created for year $year"];
            }
        }
        return response()->json($msg);
    }
    */
    /*
    public function getSaturday(Request $request)
    {
        // Arrays to store Saturdays, Sundays, and data for Saturday events
        $array = [];


        foreach ($request->formData as $key => $val) {
            $array[$val['name']] = $val['value'];
        }
        $year = $array['year'];
        $lastinserteddate = Event::orderBy('start_date', 'desc')->value('start_date');
        $lastEntryEvent = date('Y', strtotime($lastinserteddate));
        if ($lastEntryEvent == $year) {
            $msg = ['type' => 'alert', 'msg' => "Event Already created on $year year"];
        } else {
            DB::begintransaction();
            try {
                $this->addEvents($year);
                // Set success or error message based on the insertion result
                DB::commit();
                $msg = ['type' => 'success', 'msg' => "Events created successfully for year $year"];
            } catch (\Exception $e) {
                DB::rollback();
                dd($e->getMessage(),$e->getLine(),$e->getFile());
                $msg = ['type' => 'error', 'msg' => "Events not created for year $year"];
            }
        }
        return response()->json($msg);
    }
    */
    public function addEvents($year)
    {
        $dataSaturday = array();
        // Get the first day of the month in a given year
        $firstDayofMonth = Carbon::createFromDate($year, 1, 1);

        // Get the last day of the given year
        $lastDayofMonth = $firstDayofMonth->copy()->endOfYear();

        // Get all states from the states table
        $states = State::get();

        // Loop for all days in a month
        while ($firstDayofMonth->lte($lastDayofMonth)) {
            // Condition to check if the day is a Saturday and under bank holiday
            if ($firstDayofMonth->isSaturday()) {
                // Check if it is the 2nd or 4th Saturday
                $weekNumber = $firstDayofMonth->week;
                if (in_array($weekNumber, getAllSaturdayCount($year))) {
                    $saturdays[] = $firstDayofMonth->copy()->toDateString();
                    // Loop through states to create data for Saturday events
                    foreach ($states as $state) {
                        $dataSaturday[] = [
                            'state_id' => $state['id'],
                            'title' => 'Saturday Holiday',
                            'start_date' => $firstDayofMonth->copy()->toDateString(),
                            'end_date' => $firstDayofMonth->copy()->toDateString(),
                            'month' => $firstDayofMonth->month,
                        ];
                    }
                }
            }
            // Condition to check if the day is a Sunday
            if ($firstDayofMonth->isSunday()) {
                $sundays[] = $firstDayofMonth->copy()->toDateString();
                // Loop through states to create data for Sunday events
                foreach ($states as $state) {
                    $dataSaturday[] = [
                        'state_id' => $state['id'],
                        'title' => 'Sunday Holiday',
                        'start_date' => $firstDayofMonth->copy()->toDateString(),
                        'end_date' => $firstDayofMonth->copy()->toDateString(),
                        'month' => $firstDayofMonth->month,
                    ];
                }
            }
            $firstDayofMonth->addDay();
        }
        // Insert all holiday Sundays/Saturdays for the given year into the event table
        Event::insert($dataSaturday);
    }

    public function getSaturday(Request $request)
    {
        // Arrays to store Saturdays, Sundays, and data for Saturday events
        $array = [];


        foreach ($request->formData as $key => $val) {
            $array[$val['name']] = $val['value'];
        }
        $year = $array['year'];
        $lastinserteddate = Event::orderBy('start_date', 'desc')->value('start_date');
        $lastEntryEvent = date('Y', strtotime($lastinserteddate));
        if ($lastEntryEvent == $year) {
            $msg = ['type' => 'alert', 'msg' => "Event Already created on $year year"];
        } else {
            DB::beginTransaction();
            try {
                $this->setCarbonTranslator(); // Set Carbon translator
                $this->addEvents($year);
                // Set success or error message based on the insertion result
                DB::commit();
                $msg = ['type' => 'success', 'msg' => "Events created successfully for year $year"];
            } catch (\Exception $e) {
                DB::rollback();
                dd($e->getMessage(), $e->getLine(), $e->getFile());
                $msg = ['type' => 'error', 'msg' => "Events not created for year $year"];
            }
        }
        return response()->json($msg);
    }

    protected function setCarbonTranslator()
    {
        $translator = app('translator');
        Carbon::setTranslator(new \Symfony\Component\Translation\Translator($translator->getLocale()));
    }

    public function command($command)
    {
        return Artisan::call("command:$command");
    }

    //Update by shahid on 08-03-2024
    // New SSB payment and bounce charge through cron
    public static function cronDepositeLoanEmi(Request $request)
    {

        DB::beginTransaction();
        try {
            $entryTime = date("H:i:s");
            $ssbAccountDetails = \App\Models\SavingAccount::with('ssbMember')->whereId($request['ssb_id'])->first();
            $penalty = $request['penalty_amount'] > 0 ? $request['penalty_amount'] : 0;
            $application_date = $request['application_date'];
            $createDayBook = $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($penalty);
            $globaldate = date('Y-m-d', strtotime(convertDate($request['application_date'])));
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
            $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
            $deposit = $request['deposite_amount'];
            $loanId = $request['loan_id'];
            $branchId = $request['branch'];
            $emiAmount = $request['emi_amount'];
            $mLoan = \App\Models\Memberloans::with(['loanMember', 'loan'])->where('id', $request['loan_id'])->first();
            $companyId = $mLoan->company_id;
            $stateid = getBranchState($mLoan['loanBranch']->name);
            $getHeadSetting = \App\Models\HeadSetting::where('head_id', 33)->first();
            $getBounceChargeSetting = \App\Models\HeadSetting::where('head_id', 435)->first();

            $loanBounceCharges = \App\Models\LoanCharge::where('min_amount', '<=', $mLoan['amount'])->where('max_amount', '>=', $mLoan['amount'])->where('loan_id', $mLoan['loan_type'])->where('type', 4)->where('status', 1)->where('tenure', $mLoan['emi_period'])->where('emi_option', $mLoan['emi_option'])->where('effective_from', '<=', (string) $globaldate)->first();

            $getGstSetting = \App\Models\GstSetting::where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->where('company_id', $request['company_id'])->exists();

            $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id', 'applicable_date')->where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->first();
            $ssbBalance = \App\Models\SavingAccount::find($ssbAccountDetails->id);


            $gstAmount = 0;
            if ($penalty > 0 && $getGstSetting) {
                if ($mLoan['loanBranch']->state_id == $getGstSettingno->state_id) {
                    $gstAmount = (($penalty * $getHeadSetting->gst_percentage) / 100) / 2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;
                } else {
                    $gstAmount = ($penalty * $getHeadSetting->gst_percentage) / 100;
                    $cgstHead = 170;
                    $IntraState = false;
                }
                $penalty = $penalty;
            } else {
                $penalty = 0;
            }
            $gstAmount = ceil($gstAmount);

            //Bounce charge GST


            $bounceGstAmount = 0;
            if ($getGstSetting) {
                if ($mLoan['loanBranch']->state_id == $getGstSettingno->state_id) {
                    $bounceGstAmount = (($loanBounceCharges->charge * $getBounceChargeSetting->gst_percentage) / 100) / 2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;
                } else {
                    $bounceGstAmount = ($loanBounceCharges->charge * $getBounceChargeSetting->gst_percentage) / 100;
                    $cgstHead = 170;
                    $IntraState = false;
                }
                $penalty = $penalty;
            } else {
                $penalty = 0;
            }
            $bounceGstAmount = ceil($bounceGstAmount);

            // Bounce Charge GST end
            $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
            $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
            $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
            $LoanCreateDate = date('d', strtotime($mLoan->approve_date));
            $currentDate = date('Y-m-d');
            $CurrentDate = date('d');
            $CurrentDateYear = date('Y');
            $CurrentDateMonth = date('m');
            $applicationDate = date('Y-m-d', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDate = date('d', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDateYear = date('Y', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDateMonth = date('m', strtotime(convertDate($request['application_date'])));
            if ($mLoan->emi_option == 1) { //Month
                $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                $daysDiff2 = today()->diffInDays($LoanCreatedDate);
                $nextEmiDates = self::nextEmiDates($daysDiff, $LoanCreatedDate);
                $nextEmiDates2 = self::nextEmiDatesDays($daysDiff2, $LoanCreatedDate);
                $nextEmiDates3 = self::nextEmiDates($mLoan->emi_period, $LoanCreatedDate);
            }
            if ($mLoan->emi_option == 2) { //Week
                $daysDiff = today()->diffInDays($LoanCreatedDate);
                $daysDiff = $daysDiff / 7;
                $nextEmiDates2 = self::nextEmiDatesDays($daysDiff, $LoanCreatedDate);
                $nextEmiDates = self::nextEmiDatesWeek($daysDiff, $LoanCreatedDate);
            }
            if ($mLoan->emi_option == 3) {  //Days
                $daysDiff = today()->diffInDays($LoanCreatedDate);
                $nextEmiDates = self::nextEmiDatesDays($daysDiff, $LoanCreatedDate);
            }



            $roi = 0; //$accruedInterest['accruedInterest'];
            $principal_amount = 0; //$accruedInterest['principal_amount'];
            $totalDayInterest = 0;
            $totalDailyInterest = 0;
            $newApplicationDate = explode('-', $applicationDate);
            $StartnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-01';
            $EndnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-31';
            $dailyoutstandingAmount = 0;
            $lastOutstanding = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();
            $lastOutstandingDate = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->pluck('emi_date')->toArray();
            $newDate = array();
            if ($lastOutstanding != NULL && isset($lastOutstanding->out_standing_amount)) {

                $checkDateMonth = date('m', strtotime($lastOutstanding->emi_date));
                $checkDateYear = date('Y', strtotime($lastOutstanding->emi_date));
                $newstartDate = $checkDateYear . '-' . $checkDateMonth . '-01';
                $newEndDate = $checkDateYear . '-' . $checkDateMonth . '-31';
                $gapDayes = Carbon::parse($lastOutstanding->emi_date)->diff(Carbon::parse($applicationDate))->format('%a');
                $lastOutstanding2 = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->whereBetween('emi_date', [$newstartDate, $newEndDate])->where('is_deleted', '0')->sum('out_standing_amount');
                if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) {
                    if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
                        $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit);
                    } else {
                        $preDate = current($nextEmiDates);
                        $oldDate = $nextEmiDates[$applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear];
                        if ($mLoan->emi_option == 1) {
                            $previousDate = Carbon::parse($oldDate)->subMonth(1);
                        }
                        if ($mLoan->emi_option == 2) {
                            $previousDate = Carbon::parse($oldDate)->subDays(7);
                        }
                        $pDate = date('Y-m-d', strtotime("+1 day", strtotime($previousDate)));
                        if ($preDate == $applicationDate) {
                            $aqmount = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$LoanCreatedDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
                        } else {
                            $aqmount = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$pDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
                        }
                        if ($aqmount > 0) {
                            $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi + $aqmount);
                        } else {
                            $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi);
                        }
                    }
                    $dailyoutstandingAmount = $outstandingAmount + $roi;
                }
                $deposit = $request['deposite_amount'];
            } else {

                // $gapDayes = $gapDays = Carbon::createFromFormat('d/m/Y', $mLoan->approve_date)
                //     ->diff(Carbon::createFromFormat('Y-m-d', $applicationDate))
                //     ->format('%a');
                $gapDayes = Carbon::parse($mLoan->approve_date)->diff(Carbon::parse($applicationDate))->format('%a');
                if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
                {
                    if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
                        $outstandingAmount = ($mLoan->amount - $deposit);
                    } else {
                        $outstandingAmount = ($mLoan->amount - $deposit + $roi);
                    }
                    $dailyoutstandingAmount = $outstandingAmount + $roi;
                } else {
                    $outstandingAmount = ($mLoan->amount - $principal_amount);
                }
                $deposit = $request['deposite_amount'];
                $dailyoutstandingAmount = $mLoan->amount + $roi;
            }
            $amountArraySsb = array(
                '1' => $request['deposite_amount']
            );
            if (isset($ssbAccountDetails['ssbMember'])) {
                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
            } else {
                $amount_deposit_by_name = NULL;
            }
            $dueAmount = $mLoan->due_amount - round($principal_amount);
            $mlResult = \App\Models\Memberloans::find($request['loan_id']);
            $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
            $lData['due_amount'] = $dueAmount;
            $lData['accrued_interest'] = $mLoan->accrued_interest - $roi;
            if ($dueAmount == 0) {
                //$lData['status'] = 3;
                //$lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
            }
            $lData['received_emi_amount'] = $mLoan->received_emi_amount + $request['deposite_amount'];
            $mlResult->update($lData);
            // add log
            $postData = $_POST;
            $enData = array(
                "post_data" => $postData,
                "lData" => $lData
            );
            $encodeDate = json_encode($enData);
            $desType = 'Loan EMI deposit';
            $cheque_dd_no = NULL;
            $online_payment_id = NULL;
            $online_payment_by = NULL;
            $bank_name = NULL;
            $cheque_date = NULL;
            $account_number = NULL;
            $paymentMode = 4;
            $ssbpaymentMode = 3;
            $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['created_date'])));
            $checkSSBBalanceDeposit = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['ssb_id'])->whereDate('opening_date', '<=', $globaldate)->sum('deposit');
            $checkSSBBalanceWithdrawal = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['ssb_id'])->whereDate('opening_date', '<=', $globaldate)->sum('withdrawal');

            // Get ssb ac  head id
            $ssbHead = \App\Models\Plans::where('company_id', $mlResult['company_id'])->where('plan_category_code', 'S')->first();
            $paymentHead = $ssbHead->deposit_head_id;
            // End Get ssb ac  head id

            $closerAmount = $request['closerAmount'];

            $ssbBalanceAmount = $checkSSBBalanceDeposit - $checkSSBBalanceWithdrawal;

            if ($request['loan_emi_payment_mode'] == 0) {
                // dd($request['closing_date'] > $request['application_date'] );
                // for loans whose closing date > current date using due amount
                if ($request['closing_date'] > $request['application_date'] && $request['due_amount'] > 0) {
                    if ($ssbBalanceAmount >= $request['emi_amount'] && $ssbBalanceAmount >= $request['due_amount'] || ($ssbBalanceAmount <= $request['emi_amount'] && $ssbBalanceAmount >= $request['due_amount'])) {
                        $due_amount = $request['due_amount'];
                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $v_no = "";
                        for ($i = 0; $i < 10; $i++) {
                            $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                        }
                        // Get saving account current balance
                        $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($dueAmount);


                        $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                            ->where('is_deleted', 0)
                            ->first();

                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                        $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $due_amount;
                        $ssb['branch_id'] = $request['branch'];
                        $ssb['type'] = 9;
                        $ssb['deposit'] = 0;
                        $ssb['withdrawal'] = $due_amount;
                        $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['company_id'] = $companyId;
                        $ssb['payment_mode'] = 9;
                        $ssb['daybook_ref_id'] = $DayBookref;
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                        $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;

                        $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                        $ssbBalance->balance = $due_amount;
                        $ssbBalance->save();

                        $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;


                        $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))
                            ->where('is_deleted', 0)
                            ->get();

                        foreach ($record2 as $key => $value) {
                            $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                            $nsResult['opening_balance'] = $value->opening_balance;
                            $nsResult['company_id'] = $companyId;
                            $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                            $nsResult->save();
                        }
                        $data['saving_account_transaction_id'] = $ssb_transaction_id;
                        $data['loan_id'] = $request['loan_id'];
                        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                        $satRefId = null;
                        $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);
                        $ssbCreateTran = null;
                        $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                        // ECS transaction table entery

                        $importData = [
                            'loan_type' => 'L',
                            'loan_id' => $request['loan_id'],
                            'account_number' => $request['account_number'],
                            'branch_id' => $request['branch'],
                            'daybook_ref_id' => $DayBookref,
                            'amount' => $due_amount,
                            'bank_acc_no' => $request['account_no'],
                            'associate_id' => $request['associate_member_id'],
                            'transaction_status' => 1,
                            'utr_transaction_number' => 'N/A',
                            'bank_name' => 'test',
                            'company_id' => $request['company_id'],
                            'transaction_type' => 2,
                            'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                        ];

                        $ecs = ECSTransaction::create($importData);

                        // Ecs trasaction table entry End

                        $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'CR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                        $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 4, 48, $ssbAccountDetails->id, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'DR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                        $ssbCreateTran = NULL;
                        $transactionPaymentMode = 0;
                        /************* Head Implement ****************/
                        if ($request['loan_emi_payment_mode'] == 0) {

                            $paymentMode = 4; //saving account transaction
                            $transactionPaymentMode = 3;



                        }

                        /************* Head Implement ****************/
                        /*---------- commission script  start  ---------*/
                        $daybookId = $createDayBook;
                        $total_amount = $request['deposite_amount'];
                        $percentage = 2;
                        $month = NULL;
                        $type_id = $request['loan_id'];
                        $type = 4;
                        $associate_id = $request['associate_member_id'];
                        $branch_id = $request['branch'];
                        $commission_type = 0;
                        $associateDetail = \App\Models\Member::where('id', $associate_id)->first();
                        $carder = $associateDetail->current_carder_id;
                        $associate_exist = 0;
                        $percentInDecimal = $percentage / 100;
                        $commission_amount = round($percentInDecimal * $total_amount, 4);
                        $loan_associate_code = $request->loan_associate_code;
                        $associateCommission['member_id'] = $associate_id;
                        $associateCommission['branch_id'] = $branch_id;
                        $associateCommission['type'] = $type;
                        $associateCommission['type_id'] = $type_id;
                        $associateCommission['day_book_id'] = $daybookId;
                        $associateCommission['total_amount'] = $total_amount;
                        $associateCommission['month'] = $month;
                        $associateCommission['commission_amount'] = $commission_amount;
                        $associateCommission['percentage'] = $percentage;
                        $associateCommission['commission_type'] = $commission_type;
                        $date = \App\Models\Daybook::where('id', $daybookId)->first();
                        $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
                        $associateCommission['pay_type'] = 4;
                        $associateCommission['carder_id'] = $carder;
                        $associateCommission['associate_exist'] = $associate_exist;

                        // Update is_bounce and emi due date
                        $mlData = \App\Models\Memberloans::find($request['loan_id']);
                        $emiDueDate = $mlData['emi_due_date'];

                        if ($mlData['emi_option'] == 1) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 2) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 3) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                        } else {
                            $emiDueDate = $mlData['emi_due_date'];
                        }



                        $loanData['is_bounce'] = 0;
                        $loanData['emi_due_date'] = $emiDueDate;
                        $mlData->update($loanData);
                        // end is_
                        /*---------- commission script  end  ---------*/
                        $createLoanDayBook = \App\Http\Controllers\Admin\CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, 0, $due_amount, $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, null, $bank_name = NULL, $branch_name = NULL, $request['application_date'], $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
                        self::headTransaction($createLoanDayBook, $transactionPaymentMode, 1);
                        $totalDepsoit = \App\Models\LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                        $text = 'Dear Member,Received Rs.' . $due_amount . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                        $temaplteId = 1207166308935249821;
                        $contactNumber = array();
                        $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                        // if( $res['line'] > 0){
                        //     DB::rollback();
                        // }else{
                        // }

                    } elseif (($ssbBalanceAmount >= $request['emi_amount'] && $ssbBalanceAmount <= $request['due_amount'])) {
                        $due_amount = $ssbBalanceAmount;
                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $v_no = "";
                        for ($i = 0; $i < 10; $i++) {
                            $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                        }
                        // Get saving account current balance
                        $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($dueAmount);


                        $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                            ->where('is_deleted', 0)
                            ->first();

                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                        $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $due_amount;
                        $ssb['branch_id'] = $request['branch'];
                        $ssb['type'] = 9;
                        $ssb['deposit'] = 0;
                        $ssb['withdrawal'] = $due_amount;
                        $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['company_id'] = $companyId;
                        $ssb['payment_mode'] = 9;
                        $ssb['daybook_ref_id'] = $DayBookref;
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                        $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;

                        $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                        $ssbBalance->balance = $due_amount;
                        $ssbBalance->save();

                        $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;


                        $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))
                            ->where('is_deleted', 0)
                            ->get();

                        foreach ($record2 as $key => $value) {
                            $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                            $nsResult['opening_balance'] = $value->opening_balance;
                            $nsResult['company_id'] = $companyId;
                            $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                            $nsResult->save();
                        }
                        $data['saving_account_transaction_id'] = $ssb_transaction_id;
                        $data['loan_id'] = $request['loan_id'];
                        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                        $satRefId = null;
                        $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);
                        $ssbCreateTran = null;
                        $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                        // ECS transaction table entery

                        $importData = [
                            'loan_type' => 'L',
                            'loan_id' => $request['loan_id'],
                            'account_number' => $request['account_number'],
                            'branch_id' => $request['branch'],
                            'daybook_ref_id' => $DayBookref,
                            'amount' => $due_amount,
                            'bank_acc_no' => $request['account_no'],
                            'associate_id' => $request['associate_member_id'],
                            'transaction_status' => 1,
                            'utr_transaction_number' => 'N/A',
                            'bank_name' => 'test',
                            'company_id' => $request['company_id'],
                            'transaction_type' => 2,
                            'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                        ];
                        $ecs = ECSTransaction::create($importData);

                        // Ecs trasaction table entry End

                        $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'CR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                        $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 4, 48, $ssbAccountDetails->id, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'DR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                        $ssbCreateTran = NULL;
                        $transactionPaymentMode = 0;
                        /************* Head Implement ****************/
                        if ($request['loan_emi_payment_mode'] == 0) {

                            $paymentMode = 4; //saving account transaction
                            $transactionPaymentMode = 3;



                        }

                        /************* Head Implement ****************/
                        /*---------- commission script  start  ---------*/
                        $daybookId = $createDayBook;
                        $total_amount = $request['deposite_amount'];
                        $percentage = 2;
                        $month = NULL;
                        $type_id = $request['loan_id'];
                        $type = 4;
                        $associate_id = $request['associate_member_id'];
                        $branch_id = $request['branch'];
                        $commission_type = 0;
                        $associateDetail = \App\Models\Member::where('id', $associate_id)->first();
                        $carder = $associateDetail->current_carder_id;
                        $associate_exist = 0;
                        $percentInDecimal = $percentage / 100;
                        $commission_amount = round($percentInDecimal * $total_amount, 4);
                        $loan_associate_code = $request->loan_associate_code;
                        $associateCommission['member_id'] = $associate_id;
                        $associateCommission['branch_id'] = $branch_id;
                        $associateCommission['type'] = $type;
                        $associateCommission['type_id'] = $type_id;
                        $associateCommission['day_book_id'] = $daybookId;
                        $associateCommission['total_amount'] = $total_amount;
                        $associateCommission['month'] = $month;
                        $associateCommission['commission_amount'] = $commission_amount;
                        $associateCommission['percentage'] = $percentage;
                        $associateCommission['commission_type'] = $commission_type;
                        $date = \App\Models\Daybook::where('id', $daybookId)->first();
                        $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
                        $associateCommission['pay_type'] = 4;
                        $associateCommission['carder_id'] = $carder;
                        $associateCommission['associate_exist'] = $associate_exist;

                        // Update is_bounce and emi due date
                        $mlData = \App\Models\Memberloans::find($request['loan_id']);
                        $emiDueDate = $mlData['emi_due_date'];

                        if ($mlData['emi_option'] == 1) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 2) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 3) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                        } else {
                            $emiDueDate = $mlData['emi_due_date'];
                        }



                        $loanData['is_bounce'] = 0;
                        $loanData['emi_due_date'] = $emiDueDate;
                        $mlData->update($loanData);
                        // end is_
                        /*---------- commission script  end  ---------*/
                        $createLoanDayBook = \App\Http\Controllers\Admin\CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, 0, $due_amount, $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, null, $bank_name = NULL, $branch_name = NULL, $request['application_date'], $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
                        self::headTransaction($createLoanDayBook, $transactionPaymentMode, 1);
                        $totalDepsoit = \App\Models\LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                        $text = 'Dear Member,Received Rs.' . $due_amount . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                        p($text);
                        $temaplteId = 1207166308935249821;
                        $contactNumber = array();
                        $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                        // if( $res['line'] > 0){
                        //     DB::rollback();
                        // }else{
                        // }

                    } else {


                        if (isset($loanBounceCharges)) {

                            $deductAmount = $loanBounceCharges['charge'];
                            $totalAmountBounce = $loanBounceCharges['charge'] + $bounceGstAmount + $bounceGstAmount;


                            // DB::beginTransaction();
                            try {
                                $dayBookRefs = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($totalAmountBounce);
                                $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                                    ->whereDate('created_at', '<=', $transDate)->where('is_deleted', 0)->whereCompanyId($companyId)->orderby('id', 'desc')
                                    ->first();


                                $ssb['saving_account_id'] = $ssbAccountDetails->id;
                                $ssb['account_no'] = $ssbAccountDetails->account_no;
                                $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $totalAmountBounce;
                                $ssb['branch_id'] = $request['branch'];
                                $ssb['type'] = 9;
                                $ssb['deposit'] = 0;
                                $ssb['withdrawal'] = $totalAmountBounce;
                                $ssb['description'] = 'Emi Bounce Charge to ' . $mLoan->account_number;
                                $ssb['currency_code'] = 'INR';
                                $ssb['payment_type'] = 'DR';
                                $ssb['company_id'] = $companyId;
                                $ssb['payment_mode'] = 9;
                                $ssb['daybook_ref_id'] = $dayBookRefs;
                                $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                                $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;
                                $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                                $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                                // update saving account current balance
                                $ssbBalance = \App\Models\SavingAccount::find($ssbAccountDetails->id);
                                $ssbBalance->balance = $ssbBalance->balance - $totalAmountBounce;
                                $ssbBalance->save();
                                $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                                    ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))->where('is_deleted', 0)->get();
                                foreach ($record2 as $key => $value) {
                                    $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                                    $nsResult['opening_balance'] = $value->opening_balance;
                                    $nsResult['company_id'] = $companyId;
                                    $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                                    $nsResult->save();
                                }
                                $data['saving_account_transaction_id'] = $ssb_transaction_id;
                                $data['loan_id'] = $request['loan_id'];
                                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                                $satRefId = null;
                                $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);

                                // set next emi due date and is_bounce
                                $mlData = \App\Models\Memberloans::find($request['loan_id']);
                                $emiDueDate = $mlData['emi_due_date'];

                                if ($mlData['emi_option'] == 1) {
                                    $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                                } elseif ($mlData['emi_option'] == 2) {
                                    $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                                } elseif ($mlData['emi_option'] == 3) {
                                    $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                                } else {
                                    $emiDueDate = $mlData['emi_due_date'];
                                }



                                $loanData['is_bounce'] = 1;
                                $loanData['emi_due_date'] = $emiDueDate;
                                $mlData->update($loanData);
                                // End set next emi due date and is_bounce

                                $importData = [
                                    'loan_type' => 'L',
                                    'loan_id' => $request['loan_id'],
                                    'account_number' => $request['account_number'],
                                    'branch_id' => $request['branch'],
                                    'daybook_ref_id' => $dayBookRefs,
                                    'amount' => $request['due_amount'],
                                    'bank_acc_no' => $request['account_no'],
                                    'associate_id' => $request['associate_member_id'],
                                    'transaction_status' => 0,
                                    'utr_transaction_number' => 'N/A',
                                    'bank_name' => 'N/A',
                                    'company_id' => $request['company_id'],
                                    'transaction_type' => 2,
                                    'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                                    'cgst_charge' => $bounceGstAmount,
                                    'sgst_charge' => $bounceGstAmount,
                                    'bounce_charge' => $loanBounceCharges['charge'],
                                ];


                                $ecs = ECSTransaction::create($importData);

                                // Ecs trasaction table entry End

                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, $paymentHead, 4, 551, $request['ssb_id'], $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'DR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, null, $mLoan['company_id']);


                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 435, 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $deductAmount, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $deductAmount . '', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, null, $mLoan['company_id']);

                                $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 4, 551, $request['ssb_id'], $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, null, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                                $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $loanBounceCharges['charge'], 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, null, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);



                                // Bounce charge entry in loan_day_book  commented as per anup sir instruction 04-03-24

                                // $createLoanDayBook = self::createLoanDayBook($dayBookRefs, $dayBookRefs, $mLoan['loan_type'], 3, $loanId, $lId = NULL, $ssbAccountDetails->account_no, $mLoan['applicant_id'], 0, 0, 0, $totalAmountBounce, "Bounce Charge from saving account  $ssbAccountDetails->account_no", $mLoan['branch_id'], getBranchCode($mLoan['branch_id'])->branch_code, 'CR', 'INR', 4, NULL, NULL, $branch_name = NULL, $paymentDate, NULL, 1, 1, NULL, $mLoan->account_number, NULL, NULL, NULL, $request['branch'], 0, 0, 0, $mLoan['company_id'], null, $bounceGstAmount, $bounceGstAmount);

                                // Calculate intrest through cron
                                $stateId = branchName()->state_id;
                                $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                                $currentDate = date('Y-m-d', strtotime($currentDate));
                                // $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $request['account_number'], 0]);
                                // Calculate intrest through cron

                                // Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                                if (isset($bounceGstAmount) && $bounceGstAmount > 0) {


                                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 171, 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge CGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, null, $mLoan['company_id']);

                                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 172, 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge SGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, null, $mLoan['company_id']);



                                    $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, null, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);


                                    $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'Bounce charge CGST  ' . $mLoan['account_number'] . '', 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, null, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                                    $createdGstTransaction = CommanController::gstTransaction($dayBookRefs, $getGstSettingno->gst_no, null, $totalAmountBounce, $getBounceChargeSetting->gst_percentage, ($IntraState == false ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true) ? $bounceGstAmount + $bounceGstAmount : $totalAmountBounce + $bounceGstAmount, 435, $paymentDate, 'BC435', $mLoan['customer_id'], $mLoan['branch_id'], $mLoan['company_id']);

                                }

                                // end Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                                $text = 'Dear Member, Your Loan ECS bounced on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Bounce charge Rs ' . $totalAmountBounce . ' deducted from your SSB ' . $ssbAccountDetails->account_no . ' Samraddh Bestwin Microfinance';
                                $temaplteId = 1207171074323291072;
                                $contactNumber = array();
                                $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                                $contactNumber[] = $memberDetail->mobile_no;
                                $sendToMember = new Sms();
                                $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                                $res = ['line' => 0, 'message' => "Bounce charge has been deducted from your account. !"];
                                DB::commit();
                            } catch (\Exception $ex) {
                                // dd($ex->getLine(), $ex->getMessage());
                                $res['line'] = $ex->getLine();
                                $res['message'] = $ex->getMessage();
                                //  DB::rollback();
                            }
                        } else {

                            $res['message'] = "Bounce charge Not created. !";
                            $mlData = \App\Models\Memberloans::find($request['loan_id']);
                            $emiDueDate = $mlData['emi_due_date'];

                            if ($mlData['emi_option'] == 1) {
                                $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                            } elseif ($mlData['emi_option'] == 2) {
                                $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                            } elseif ($mlData['emi_option'] == 3) {
                                $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                            } else {
                                $emiDueDate = $mlData['emi_due_date'];
                            }
                            $loanData['is_bounce'] = 1;
                            $loanData['emi_due_date'] = $emiDueDate;
                            $mlData->update($loanData);
                        }
                    }
                }
                // For loans whose closing date <= current date using closure amount
                elseif ($request['closing_date'] <= $request['application_date']) {
                    if (($ssbBalanceAmount >= $request['emi_amount'] && $ssbBalanceAmount >= $closerAmount) || ($ssbBalanceAmount <= $request['emi_amount'] && $ssbBalanceAmount >= $closerAmount)) {
                        $due_amount = $closerAmount;
                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $v_no = "";
                        for ($i = 0; $i < 10; $i++) {
                            $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                        }
                        // Get saving account current balance
                        $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($dueAmount);


                        $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                            ->where('is_deleted', 0)
                            ->first();

                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                        $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $due_amount;
                        $ssb['branch_id'] = $request['branch'];
                        $ssb['type'] = 9;
                        $ssb['deposit'] = 0;
                        $ssb['withdrawal'] = $due_amount;
                        $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['company_id'] = $companyId;
                        $ssb['payment_mode'] = 9;
                        $ssb['daybook_ref_id'] = $DayBookref;
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                        $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;

                        $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                        $ssbBalance->balance = $due_amount;
                        $ssbBalance->save();

                        $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;


                        $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))
                            ->where('is_deleted', 0)
                            ->get();

                        foreach ($record2 as $key => $value) {
                            $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                            $nsResult['opening_balance'] = $value->opening_balance;
                            $nsResult['company_id'] = $companyId;
                            $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                            $nsResult->save();
                        }
                        $data['saving_account_transaction_id'] = $ssb_transaction_id;
                        $data['loan_id'] = $request['loan_id'];
                        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                        $satRefId = null;
                        $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);
                        $ssbCreateTran = null;
                        $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                        // ECS transaction table entery

                        $importData = [
                            'loan_type' => 'L',
                            'loan_id' => $request['loan_id'],
                            'account_number' => $request['account_number'],
                            'branch_id' => $request['branch'],
                            'daybook_ref_id' => $DayBookref,
                            'amount' => $due_amount,
                            'bank_acc_no' => $request['account_no'],
                            'associate_id' => $request['associate_member_id'],
                            'transaction_status' => 1,
                            'utr_transaction_number' => 'N/A',
                            'bank_name' => 'test',
                            'company_id' => $request['company_id'],
                            'transaction_type' => 2,
                            'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                        ];

                        $ecs = ECSTransaction::create($importData);

                        // Ecs trasaction table entry End
                        $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'CR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                        $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 4, 48, $ssbAccountDetails->id, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'DR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);


                        $ssbCreateTran = NULL;
                        $transactionPaymentMode = 0;
                        /************* Head Implement ****************/
                        if ($request['loan_emi_payment_mode'] == 0) {

                            $paymentMode = 4; //saving account transaction
                            $transactionPaymentMode = 3;



                        }

                        /************* Head Implement ****************/
                        /*---------- commission script  start  ---------*/
                        $daybookId = $createDayBook;
                        $total_amount = $request['deposite_amount'];
                        $percentage = 2;
                        $month = NULL;
                        $type_id = $request['loan_id'];
                        $type = 4;
                        $associate_id = $request['associate_member_id'];
                        $branch_id = $request['branch'];
                        $commission_type = 0;
                        $associateDetail = \App\Models\Member::where('id', $associate_id)->first();
                        $carder = $associateDetail->current_carder_id;
                        $associate_exist = 0;
                        $percentInDecimal = $percentage / 100;
                        $commission_amount = round($percentInDecimal * $total_amount, 4);
                        $loan_associate_code = $request->loan_associate_code;
                        $associateCommission['member_id'] = $associate_id;
                        $associateCommission['branch_id'] = $branch_id;
                        $associateCommission['type'] = $type;
                        $associateCommission['type_id'] = $type_id;
                        $associateCommission['day_book_id'] = $daybookId;
                        $associateCommission['total_amount'] = $total_amount;
                        $associateCommission['month'] = $month;
                        $associateCommission['commission_amount'] = $commission_amount;
                        $associateCommission['percentage'] = $percentage;
                        $associateCommission['commission_type'] = $commission_type;
                        $date = \App\Models\Daybook::where('id', $daybookId)->first();
                        $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
                        $associateCommission['pay_type'] = 4;
                        $associateCommission['carder_id'] = $carder;
                        $associateCommission['associate_exist'] = $associate_exist;

                        // Update is_bounce and emi due date
                        $mlData = \App\Models\Memberloans::find($request['loan_id']);
                        $emiDueDate = $mlData['emi_due_date'];

                        if ($mlData['emi_option'] == 1) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 2) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 3) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                        } else {
                            $emiDueDate = $mlData['emi_due_date'];
                        }



                        $loanData['is_bounce'] = 0;
                        $loanData['emi_due_date'] = $emiDueDate;
                        $mlData->update($loanData);
                        // end is_
                        /*---------- commission script  end  ---------*/
                        $createLoanDayBook = \App\Http\Controllers\Admin\CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, 0, $due_amount, $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, null, $bank_name = NULL, $branch_name = NULL, $request['application_date'], $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
                        self::headTransaction($createLoanDayBook, $transactionPaymentMode, 1);
                        // dd('test here ss');
                        // dd()
                        $totalDepsoit = \App\Models\LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                        $text = 'Dear Member,Received Rs.' . $due_amount . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                        p($text);
                        $temaplteId = 1207166308935249821;
                        $contactNumber = array();
                        $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                        // if( $res['line'] > 0){
                        //     DB::rollback();
                        // }else{
                        // }

                    } elseif (($ssbBalanceAmount >= $request['emi_amount'] && $ssbBalanceAmount <= $closerAmount)) {
                        $due_amount = $ssbBalanceAmount;
                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $v_no = "";
                        for ($i = 0; $i < 10; $i++) {
                            $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                        }
                        // Get saving account current balance
                        $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($dueAmount);


                        $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                            ->where('is_deleted', 0)
                            ->first();

                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                        $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $due_amount;
                        $ssb['branch_id'] = $request['branch'];
                        $ssb['type'] = 9;
                        $ssb['deposit'] = 0;
                        $ssb['withdrawal'] = $due_amount;
                        $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['company_id'] = $companyId;
                        $ssb['payment_mode'] = 9;
                        $ssb['daybook_ref_id'] = $DayBookref;
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                        $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;

                        $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                        $ssbBalance->balance = $due_amount;
                        $ssbBalance->save();

                        $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;


                        $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))
                            ->where('is_deleted', 0)
                            ->get();

                        foreach ($record2 as $key => $value) {
                            $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                            $nsResult['opening_balance'] = $value->opening_balance;
                            $nsResult['company_id'] = $companyId;
                            $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                            $nsResult->save();
                        }
                        $data['saving_account_transaction_id'] = $ssb_transaction_id;
                        $data['loan_id'] = $request['loan_id'];
                        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                        $satRefId = null;
                        $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);
                        $ssbCreateTran = null;
                        $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                        // ECS transaction table entery

                        $importData = [
                            'loan_type' => 'L',
                            'loan_id' => $request['loan_id'],
                            'account_number' => $request['account_number'],
                            'branch_id' => $request['branch'],
                            'daybook_ref_id' => $DayBookref,
                            'amount' => $due_amount,
                            'bank_acc_no' => $request['account_no'],
                            'associate_id' => $request['associate_member_id'],
                            'transaction_status' => 1,
                            'utr_transaction_number' => 'N/A',
                            'bank_name' => 'test',
                            'company_id' => $request['company_id'],
                            'transaction_type' => 2,
                            'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                        ];
                        $ecs = ECSTransaction::create($importData);

                        // Ecs trasaction table entry End

                        $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'CR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                        $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 4, 48, $ssbAccountDetails->id, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'DR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                        $ssbCreateTran = NULL;
                        $transactionPaymentMode = 0;
                        /************* Head Implement ****************/
                        if ($request['loan_emi_payment_mode'] == 0) {

                            $paymentMode = 4; //saving account transaction
                            $transactionPaymentMode = 3;



                        }

                        /************* Head Implement ****************/
                        /*---------- commission script  start  ---------*/
                        $daybookId = $createDayBook;
                        $total_amount = $request['deposite_amount'];
                        $percentage = 2;
                        $month = NULL;
                        $type_id = $request['loan_id'];
                        $type = 4;
                        $associate_id = $request['associate_member_id'];
                        $branch_id = $request['branch'];
                        $commission_type = 0;
                        $associateDetail = \App\Models\Member::where('id', $associate_id)->first();
                        $carder = $associateDetail->current_carder_id;
                        $associate_exist = 0;
                        $percentInDecimal = $percentage / 100;
                        $commission_amount = round($percentInDecimal * $total_amount, 4);
                        $loan_associate_code = $request->loan_associate_code;
                        $associateCommission['member_id'] = $associate_id;
                        $associateCommission['branch_id'] = $branch_id;
                        $associateCommission['type'] = $type;
                        $associateCommission['type_id'] = $type_id;
                        $associateCommission['day_book_id'] = $daybookId;
                        $associateCommission['total_amount'] = $total_amount;
                        $associateCommission['month'] = $month;
                        $associateCommission['commission_amount'] = $commission_amount;
                        $associateCommission['percentage'] = $percentage;
                        $associateCommission['commission_type'] = $commission_type;
                        $date = \App\Models\Daybook::where('id', $daybookId)->first();
                        $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
                        $associateCommission['pay_type'] = 4;
                        $associateCommission['carder_id'] = $carder;
                        $associateCommission['associate_exist'] = $associate_exist;

                        // Update is_bounce and emi due date
                        $mlData = \App\Models\Memberloans::find($request['loan_id']);
                        $emiDueDate = $mlData['emi_due_date'];

                        if ($mlData['emi_option'] == 1) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 2) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 3) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                        } else {
                            $emiDueDate = $mlData['emi_due_date'];
                        }



                        $loanData['is_bounce'] = 0;
                        $loanData['emi_due_date'] = $emiDueDate;
                        $mlData->update($loanData);
                        // end is_
                        /*---------- commission script  end  ---------*/
                        $createLoanDayBook = \App\Http\Controllers\Admin\CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, 0, $due_amount, $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, null, $bank_name = NULL, $branch_name = NULL, $request['application_date'], $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
                        self::headTransaction($createLoanDayBook, $transactionPaymentMode, 1);
                        $totalDepsoit = \App\Models\LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                        $text = 'Dear Member,Received Rs.' . $due_amount . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                        $temaplteId = 1207166308935249821;
                        $contactNumber = array();
                        $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                    } else {

                        if (isset($loanBounceCharges)) {

                            $deductAmount = $loanBounceCharges['charge'];
                            $totalAmountBounce = $loanBounceCharges['charge'] + $bounceGstAmount + $bounceGstAmount;


                            // DB::beginTransaction();
                            try {
                                $dayBookRefs = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($totalAmountBounce);
                                $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                                    ->whereDate('created_at', '<=', $transDate)->where('is_deleted', 0)->whereCompanyId($companyId)->orderby('id', 'desc')
                                    ->first();


                                $ssb['saving_account_id'] = $ssbAccountDetails->id;
                                $ssb['account_no'] = $ssbAccountDetails->account_no;
                                $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $totalAmountBounce;
                                $ssb['branch_id'] = $request['branch'];
                                $ssb['type'] = 9;
                                $ssb['deposit'] = 0;
                                $ssb['withdrawal'] = $totalAmountBounce;
                                $ssb['description'] = 'Emi Bounce Charge to ' . $mLoan->account_number;
                                $ssb['currency_code'] = 'INR';
                                $ssb['payment_type'] = 'DR';
                                $ssb['company_id'] = $companyId;
                                $ssb['payment_mode'] = 9;
                                $ssb['daybook_ref_id'] = $dayBookRefs;
                                $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                                $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;
                                $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                                $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                                // update saving account current balance
                                $ssbBalance = \App\Models\SavingAccount::find($ssbAccountDetails->id);
                                $ssbBalance->balance = $ssbBalance->balance - $totalAmountBounce;
                                $ssbBalance->save();
                                $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                                    ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))->where('is_deleted', 0)->get();
                                foreach ($record2 as $key => $value) {
                                    $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                                    $nsResult['opening_balance'] = $value->opening_balance;
                                    $nsResult['company_id'] = $companyId;
                                    $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                                    $nsResult->save();
                                }
                                $data['saving_account_transaction_id'] = $ssb_transaction_id;
                                $data['loan_id'] = $request['loan_id'];
                                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                                $satRefId = null;
                                $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);

                                // set next emi due date and is_bounce
                                $mlData = \App\Models\Memberloans::find($request['loan_id']);
                                $emiDueDate = $mlData['emi_due_date'];

                                if ($mlData['emi_option'] == 1) {
                                    $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                                } elseif ($mlData['emi_option'] == 2) {
                                    $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                                } elseif ($mlData['emi_option'] == 3) {
                                    $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                                } else {
                                    $emiDueDate = $mlData['emi_due_date'];
                                }



                                $loanData['is_bounce'] = 1;
                                $loanData['emi_due_date'] = $emiDueDate;
                                $mlData->update($loanData);
                                // End set next emi due date and is_bounce

                                $importData = [
                                    'loan_type' => 'L',
                                    'loan_id' => $request['loan_id'],
                                    'account_number' => $request['account_number'],
                                    'branch_id' => $request['branch'],
                                    'daybook_ref_id' => $dayBookRefs,
                                    'amount' => $closerAmount,
                                    'bank_acc_no' => $request['account_no'],
                                    'associate_id' => $request['associate_member_id'],
                                    'transaction_status' => 0,
                                    'utr_transaction_number' => 'N/A',
                                    'bank_name' => 'N/A',
                                    'company_id' => $request['company_id'],
                                    'transaction_type' => 2,
                                    'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                                    'cgst_charge' => $bounceGstAmount,
                                    'sgst_charge' => $bounceGstAmount,
                                    'bounce_charge' => $loanBounceCharges['charge'],
                                ];


                                $ecs = ECSTransaction::create($importData);

                                // Ecs trasaction table entry End

                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, $paymentHead, 4, 551, $request['ssb_id'], $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'DR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);


                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 435, 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $deductAmount, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $deductAmount . '', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);

                                $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 4, 551, $request['ssb_id'], $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                                $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $loanBounceCharges['charge'], 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);



                                // Bounce charge entry in loan_day_book  commented as per anup sir instruction 04-03-24

                                // $createLoanDayBook = self::createLoanDayBook($dayBookRefs, $dayBookRefs, $mLoan['loan_type'], 3, $loanId, $lId = NULL, $ssbAccountDetails->account_no, $mLoan['applicant_id'], 0, 0, 0, $totalAmountBounce, "Bounce Charge from saving account  $ssbAccountDetails->account_no", $mLoan['branch_id'], getBranchCode($mLoan['branch_id'])->branch_code, 'CR', 'INR', 4, NULL, NULL, $branch_name = NULL, $paymentDate, NULL, 1, 1, NULL, $mLoan->account_number, NULL, NULL, NULL, $request['branch'], 0, 0, 0, $mLoan['company_id'], null, $bounceGstAmount, $bounceGstAmount);

                                // Calculate intrest through cron
                                $stateId = branchName()->state_id;
                                $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                                $currentDate = date('Y-m-d', strtotime($currentDate));
                                // $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $request['account_number'], 0]);
                                // Calculate intrest through cron

                                // Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                                if (isset($bounceGstAmount) && $bounceGstAmount > 0) {


                                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 171, 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge CGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);

                                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 172, 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge SGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);



                                    $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);


                                    $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['applicant_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'Bounce charge CGST  ' . $mLoan['account_number'] . '', 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                                    $createdGstTransaction = CommanController::gstTransaction($dayBookRefs, $getGstSettingno->gst_no, null, $totalAmountBounce, $getBounceChargeSetting->gst_percentage, ($IntraState == false ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true) ? $bounceGstAmount + $bounceGstAmount : $totalAmountBounce + $bounceGstAmount, 435, $paymentDate, 'BC435', $mLoan['customer_id'], $mLoan['branch_id'], $mLoan['company_id']);

                                }
                                // end Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                                // $text = 'Dear Member,Received Rs.' . $request['deposite_amount'] . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                                $text = 'Dear Member, Your Loan ECS bounced on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Bounce charge Rs ' . $totalAmountBounce . ' deducted from your SSB ' . $ssbAccountDetails->account_no . ' Samraddh Bestwin Microfinance';
                                $temaplteId = 1207171074323291072;
                                $contactNumber = array();
                                $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                                $contactNumber[] = $memberDetail->mobile_no;
                                $sendToMember = new Sms();
                                $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                                $res = ['line' => 0, 'message' => "Bounce charge has been deducted from your account. !"];
                                DB::commit();
                            } catch (\Exception $ex) {
                                // dd($ex->getLine(), $ex->getMessage());
                                $res['line'] = $ex->getLine();
                                $res['message'] = $ex->getMessage();
                                //  DB::rollback();
                            }
                        } else {

                            $res['message'] = "Bounce charge Not created. !";
                            $mlData = \App\Models\Memberloans::find($request['loan_id']);
                            $emiDueDate = $mlData['emi_due_date'];

                            if ($mlData['emi_option'] == 1) {
                                $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                            } elseif ($mlData['emi_option'] == 2) {
                                $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                            } elseif ($mlData['emi_option'] == 3) {
                                $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                            } else {
                                $emiDueDate = $mlData['emi_due_date'];
                            }
                            $loanData['is_bounce'] = 1;
                            $loanData['emi_due_date'] = $emiDueDate;
                            $mlData->update($loanData);
                        }
                    }
                } else {
                    // Update is_bounce and emi due date
                    $mlData = \App\Models\Memberloans::find($request['loan_id']);
                    $emiDueDate = $mlData['emi_due_date'];

                    if ($mlData['emi_option'] == 1) {
                        $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                    } elseif ($mlData['emi_option'] == 2) {
                        $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                    } elseif ($mlData['emi_option'] == 3) {
                        $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                    } else {
                        $emiDueDate = $mlData['emi_due_date'];
                    }



                    $loanData['is_bounce'] = 0;
                    $loanData['emi_due_date'] = $emiDueDate;
                    $mlData->update($loanData);
                }
            }

            DB::commit();
            return $res = ['line' => 0, 'message' => "Loan EMI Successfully submitted!"];

            // return $res;
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getLine(), $ex->getMessage(), $ex->getFile());
            return $res = ['line' => $ex->getLine(), 'message' => $ex->getMessage()];
        }
    }
    // Loan day book entry in case of bounce charge is applicable and gst is deducting
    public static function createLoanDayBook($roidayBookRef, $daybookid, $loan_type, $loan_sub_type, $loan_id, $group_loan_id, $account_number, $applicant_id, $roi_amount, $principal_amount, $opening_balance, $deposit, $description, $branch_id, $branch_code, $payment_type, $currency_code, $payment_mode, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $created_by, $status, $cheque_date, $bank_account_number, $online_payment_by, $amount_deposit_by_name, $associate_id, $amount_deposit_by_id, $totalDailyInterest = Null, $totalDayInterest = Null, $penalty = NUll, $companyId, $recoveryModule = null, $bounceSgstAmount, $bounceCgstAmount)
    {
        $globaldate = Session::get('created_at');
        $data['daybook_ref_id'] = $roidayBookRef;
        $data['day_book_id'] = $daybookid;
        $data['loan_type'] = $loan_type;
        $data['loan_sub_type'] = $loan_sub_type;
        $data['loan_id'] = $loan_id;
        $data['group_loan_id'] = $group_loan_id;
        $data['account_number'] = $account_number;
        $data['applicant_id'] = $applicant_id;
        $data['associate_id'] = $associate_id;
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
        $data['emi_late_no_of_days'] = $totalDayInterest;
        $data['daily_wise_interest'] = $totalDailyInterest;
        $data['penalty'] = $penalty;
        $data['company_id'] = $companyId;
        $data['recovery_module'] = $recoveryModule;
        $data['cgst_charge'] = $bounceCgstAmount;
        $data['sgst_charge'] = $bounceSgstAmount;
        $loadDayBook = \App\Models\LoanDayBooks::create($data);
        $loaddaybook_id = $loadDayBook->id;
        return $loaddaybook_id;
    }
    //End  Loan day book entry in case of bounce charge is applicable and gst is deducting
    public static function updateSsbDayBookAmount($amount, $account_number, $date, $companyId)
    {
        $globaldate = $date;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $getCurrentBranchRecord = \App\Models\SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', $entryDate)->whereCompanyId($companyId)->where('is_deleted', 0)->first();
        if (isset($getCurrentBranchRecord->id)) {
            $bResult = \App\Models\SavingAccountTranscation::find($getCurrentBranchRecord->id);
            $bData['opening_balance'] = $getCurrentBranchRecord->opening_balance - $amount;
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
        }
        $getNextBranchRecord = \App\Models\SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', '>', $entryDate)->whereCompanyId($companyId)->where('is_deleted', 0)->orderby('created_at', 'ASC')
            ->get();
        if ($getNextBranchRecord) {
            foreach ($getNextBranchRecord as $key => $value) {
                $sResult = \App\Models\SavingAccountTranscation::find($value->id);
                $sData['opening_balance'] = $value->opening_balance - $amount;
                $sData['company_id'] = $companyId;
                $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $sResult->update($sData);
            }
        }
    }

    public static function nextEmiDatesDays($daysDiff, $LoanCreatedDate)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $daysDiff; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime($LoanCreatedDate . ' + 1 days'));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }

    public static function nextEmiDates($daysDiff, $LoanCreatedDate)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $daysDiff; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime($LoanCreatedDate . ' + 1 months'));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }

    public static function nextEmiDatesWeek($daysDiff, $LoanCreatedDate)
    {
        $nextEmiDates = array();
        for ($i = 0; $i < $daysDiff; $i++) {
            $LoanCreatedDate = date('Y-m-d', strtotime($LoanCreatedDate . ' + 7 days'));
            $LoanCreatedYear = date('Y', strtotime($LoanCreatedDate));
            $LoanCreatedMonth = date('m', strtotime($LoanCreatedDate));
            $LoanCreateDate = date('d', strtotime($LoanCreatedDate));
            $nextEmiDates[$LoanCreateDate . '_' . $LoanCreatedMonth . '_' . $LoanCreatedYear] = $LoanCreatedDate;
        }
        return $nextEmiDates;
    }

    private static function headTransaction($loanDaybookId, $paymentMode, $loanType)
    {


        try {
            $allHeadAccruedEntry = array();
            $allHeadPrincipleEntry = array();
            $allHeadpaymentEntry = array();
            $allHeadpaymentEntry2 = array();
            $calculatedDate = '';
            $value = \App\Models\LoanDayBooks::findorfail($loanDaybookId);
            $loansDetail = \App\Models\Loans::where('id', $value->loan_type)->first();
            if ($loanType == 1) {
                $loansRecord = \App\Models\Memberloans::where('account_number', $value->account_number)->first();
                $subType = 545;
            } else {
                $loansRecord = \App\Models\Grouploans::where('account_number', $value->account_number)->first();
                $subType = 546;
            }


            $calculatedDate = date('Y-m-d', strtotime($value->created_at));
            $date = $value;

            $rr = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->where('id', '<', $value->id)->orderBY('created_at', 'desc')->first();
            $rangeDate = (isset($date->created_at)) ? date('Y-m-d', strtotime($date->created_at)) : $calculatedDate;
            // // $stateId = branchName()->state_id;
            // // p($rr);
            // // p($value);
            // // dd('stop');
            if (isset($rr)) {
                $stateId = (Branch::whereId($rr['branch_id'])->value('state_id'));
                $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                $currentDate = date('Y-m-d', strtotime($currentDate));
                $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $value->account_number, 0]);
            }
            if (isset($rr->created_at)) {
                $strattDate = date('Y-m-d', strtotime($rr->created_at));
                $endDate = date('Y-m-d', strtotime($date->created_at));
            } else {
                $strattDate = date('Y-m-d', strtotime($loansRecord->approve_date));
                $endDate = $calculatedDate;
            }


            $accuredSumCR = \App\Models\AllHeadTransaction::where('type', '5')->where('sub_type', $subType)->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $loansRecord->id)->where('payment_type', 'CR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
            $accuredSumDR = \App\Models\AllHeadTransaction::where('type', '5')->where('sub_type', $subType)->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $loansRecord->id)->where('payment_type', 'DR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
            $emiData = \App\Models\LoanEmisNew::where('emi_date', $rangeDate)->where('loan_type', $value->loan_type)->where('loan_id', $value->loan_id)->where('is_deleted', '0')->first();
            $accuredSum = $accuredSumDR - $accuredSumCR;

            if ($value->deposit <= $accuredSum) {
                $accruedAmount = $value->deposit;
                $principalAmount = 0;
            } else {
                $accruedAmount = $accuredSum;
                $principalAmount = $value->deposit - $accuredSum;
            }
            $paymentHead = '';
            if ($value->payment_mode == 0) {
                $paymentHead = 28;
            }
            if ($value->payment_mode == 4) {
                $ssbHead = \App\Models\Plans::where('company_id', $loansRecord->company_id)->where('plan_category_code', 'S')->first();
                $paymentHead = $ssbHead->deposit_head_id;
            }
            if ($value->payment_mode == 1 || $value->payment_mode == 2 || $value->payment_mode == 3) {
                $getSamraddhData = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->first();
                $getHead = \App\Models\SamraddhBank::where('id', $getSamraddhData->bank_id)->first();
                $paymentHead = $getHead->account_head_id;
                $bankId = $getSamraddhData->bank_id;
                $bankAcId = $getSamraddhData->account_id;
            }
            $allHeadAccruedEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $loansDetail->ac_head_id,
                'type' => 5,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'sub_type' => $subType,
                'type_id' => $emiData->id ?? null,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $accruedAmount,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'CR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',

                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => 0,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL,
                'is_cron' => 1
            ];
            $allHeadPrincipleEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $loansDetail->head_id,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'type' => 5,
                'sub_type' => ($loansDetail->loan_type != 'G') ? 52 : 55,
                'type_id' => $emiData->id ?? null,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $principalAmount,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'CR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',

                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => 0,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL,
                'is_cron' => 1
            ];
            $allHeadpaymentEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $paymentHead,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'type' => 5,
                'sub_type' => ($loansDetail->loan_type != 'G') ? 52 : 55,
                'type_id' => $emiData->id ?? null,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $value->deposit,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'DR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',

                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => 0,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL,
                'is_cron' => 1
            ];

            $dataInsert1 = \App\Models\AllHeadTransaction::insert($allHeadAccruedEntry);
            $dataInsert2 = \App\Models\AllHeadTransaction::insert($allHeadPrincipleEntry);
            $dataInsert3 = \App\Models\AllHeadTransaction::insert($allHeadpaymentEntry);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getMessage(), $ex->getLine());
            return back()->with('alert', $ex->getMessage());

        }
    }

    
 public static function cronDepositeGroupLoanEmi(Request $request)
 {

     DB::beginTransaction();
     try {
         $entryTime = date("H:i:s");
         $ssbAccountDetails = \App\Models\SavingAccount::with('ssbMember')->whereId($request['ssb_id'])->first();
         $penalty = $request['penalty_amount'] > 0 ? $request['penalty_amount'] : 0;
         $application_date = $request['application_date'];
         $createDayBook = $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($penalty);
         $globaldate = date('Y-m-d', strtotime(convertDate($request['application_date'])));
         Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
         $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
         $deposit = $request['deposite_amount'];
         $loanId = $request['loan_id'];
         $branchId = $request['branch'];
         $emiAmount = $request['emi_amount'];
         $mLoan = \App\Models\Grouploans::with(['loanMember', 'loan'])->where('id', $request['loan_id'])->first();
         $companyId = $mLoan->company_id;
         $stateid = getBranchState($mLoan['loanBranch']->name);
         $getHeadSetting = \App\Models\HeadSetting::where('head_id', 33)->first();
         $getBounceChargeSetting = \App\Models\HeadSetting::where('head_id', 435)->first();

         $loanBounceCharges = \App\Models\LoanCharge::where('min_amount', '<=', $mLoan['amount'])->where('max_amount', '>=', $mLoan['amount'])->where('loan_id', $mLoan['loan_type'])->where('type', 4)->where('status', 1)->where('tenure', $mLoan['emi_period'])->where('emi_option', $mLoan['emi_option'])->where('effective_from', '<=', (string) $globaldate)->first();

         $getGstSetting = \App\Models\GstSetting::where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->where('company_id', $request['company_id'])->exists();

         $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id', 'applicable_date')->where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->first();
         $ssbBalance = \App\Models\SavingAccount::find($ssbAccountDetails->id);


         $gstAmount = 0;
         if ($penalty > 0 && $getGstSetting) {
             if ($mLoan['loanBranch']->state_id == $getGstSettingno->state_id) {
                 $gstAmount = (($penalty * $getHeadSetting->gst_percentage) / 100) / 2;
                 $cgstHead = 171;
                 $sgstHead = 172;
                 $IntraState = true;
             } else {
                 $gstAmount = ($penalty * $getHeadSetting->gst_percentage) / 100;
                 $cgstHead = 170;
                 $IntraState = false;
             }
             $penalty = $penalty;
         } else {
             $penalty = 0;
         }
         $gstAmount = ceil($gstAmount);

         //Bounce charge GST


         $bounceGstAmount = 0;
         if ($getGstSetting) {
             if ($mLoan['loanBranch']->state_id == $getGstSettingno->state_id) {
                 $bounceGstAmount = (($loanBounceCharges->charge * $getBounceChargeSetting->gst_percentage) / 100) / 2;
                 $cgstHead = 171;
                 $sgstHead = 172;
                 $IntraState = true;
             } else {
                 $bounceGstAmount = ($loanBounceCharges->charge * $getBounceChargeSetting->gst_percentage) / 100;
                 $cgstHead = 170;
                 $IntraState = false;
             }
             $penalty = $penalty;
         } else {
             $penalty = 0;
         }
         $bounceGstAmount = ceil($bounceGstAmount);

         // Bounce Charge GST end
         $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
         $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
         $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
         $LoanCreateDate = date('d', strtotime($mLoan->approve_date));
         $currentDate = date('Y-m-d');
         $CurrentDate = date('d');
         $CurrentDateYear = date('Y');
         $CurrentDateMonth = date('m');
         $applicationDate = date('Y-m-d', strtotime(convertDate($request['application_date'])));
         $applicationCurrentDate = date('d', strtotime(convertDate($request['application_date'])));
         $applicationCurrentDateYear = date('Y', strtotime(convertDate($request['application_date'])));
         $applicationCurrentDateMonth = date('m', strtotime(convertDate($request['application_date'])));
         if ($mLoan->emi_option == 1) { //Month
             $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
             $daysDiff2 = today()->diffInDays($LoanCreatedDate);
             $nextEmiDates = self::nextEmiDates($daysDiff, $LoanCreatedDate);
             $nextEmiDates2 = self::nextEmiDatesDays($daysDiff2, $LoanCreatedDate);
             $nextEmiDates3 = self::nextEmiDates($mLoan->emi_period, $LoanCreatedDate);
         }
         if ($mLoan->emi_option == 2) { //Week
             $daysDiff = today()->diffInDays($LoanCreatedDate);
             $daysDiff = $daysDiff / 7;
             $nextEmiDates2 = self::nextEmiDatesDays($daysDiff, $LoanCreatedDate);
             $nextEmiDates = self::nextEmiDatesWeek($daysDiff, $LoanCreatedDate);
         }
         if ($mLoan->emi_option == 3) {  //Days
             $daysDiff = today()->diffInDays($LoanCreatedDate);
             $nextEmiDates = self::nextEmiDatesDays($daysDiff, $LoanCreatedDate);
         }



         $roi = 0; //$accruedInterest['accruedInterest'];
         $principal_amount = 0; //$accruedInterest['principal_amount'];
         $totalDayInterest = 0;
         $totalDailyInterest = 0;
         $newApplicationDate = explode('-', $applicationDate);
         $StartnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-01';
         $EndnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-31';
         $dailyoutstandingAmount = 0;
         $lastOutstanding = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();
         $lastOutstandingDate = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->pluck('emi_date')->toArray();
         $newDate = array();
         if ($lastOutstanding != NULL && isset($lastOutstanding->out_standing_amount)) {
             $checkDateMonth = date('m', strtotime($lastOutstanding->emi_date));
             $checkDateYear = date('Y', strtotime($lastOutstanding->emi_date));
             $newstartDate = $checkDateYear . '-' . $checkDateMonth . '-01';
             $newEndDate = $checkDateYear . '-' . $checkDateMonth . '-31';
             $gapDayes = Carbon::parse($lastOutstanding->emi_date)->diff(Carbon::parse($applicationDate))->format('%a');
             $lastOutstanding2 = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->whereBetween('emi_date', [$newstartDate, $newEndDate])->where('is_deleted', '0')->sum('out_standing_amount');
             if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) {
                 if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
                     $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit);
                 } else {
                     $preDate = current($nextEmiDates);
                     $oldDate = $nextEmiDates[$applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear];
                     if ($mLoan->emi_option == 1) {
                         $previousDate = Carbon::parse($oldDate)->subMonth(1);
                     }
                     if ($mLoan->emi_option == 2) {
                         $previousDate = Carbon::parse($oldDate)->subDays(7);
                     }
                     $pDate = date('Y-m-d', strtotime("+1 day", strtotime($previousDate)));
                     if ($preDate == $applicationDate) {
                         $aqmount = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$LoanCreatedDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
                     } else {
                         $aqmount = \App\Models\LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$pDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
                     }
                     if ($aqmount > 0) {
                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi + $aqmount);
                     } else {
                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi);
                     }
                 }
                 $dailyoutstandingAmount = $outstandingAmount + $roi;
             }
             $deposit = $request['deposite_amount'];
         } else {

             $gapDayes = $gapDays = Carbon::createFromFormat('d/m/Y', $mLoan->approve_date)
                 ->diff(Carbon::createFromFormat('Y-m-d', $applicationDate))
                 ->format('%a');
             // $gapDayes = Carbon::parse($mLoan->approve_date)->diff(Carbon::parse($applicationDate))->format('%a');
             if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
             {
                 if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
                     $outstandingAmount = ($mLoan->amount - $deposit);
                 } else {
                     $outstandingAmount = ($mLoan->amount - $deposit + $roi);
                 }
                 $dailyoutstandingAmount = $outstandingAmount + $roi;
             } else {
                 $outstandingAmount = ($mLoan->amount - $principal_amount);
             }
             $deposit = $request['deposite_amount'];
             $dailyoutstandingAmount = $mLoan->amount + $roi;
         }
         $amountArraySsb = array(
             '1' => $request['deposite_amount']
         );
         if (isset($ssbAccountDetails['ssbMember'])) {
             $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
         } else {
             $amount_deposit_by_name = NULL;
         }
         $dueAmount = $mLoan->due_amount - round($principal_amount);
         $mlResult = \App\Models\Grouploans::find($request['loan_id']);
         $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
         $lData['due_amount'] = $dueAmount;
         $lData['accrued_interest'] = $mLoan->accrued_interest - $roi;
         if ($dueAmount == 0) {
             //$lData['status'] = 3;
             //$lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
         }
         $lData['received_emi_amount'] = $mLoan->received_emi_amount + $request['deposite_amount'];
         $mlResult->update($lData);
         // add log
         $postData = $_POST;
         $enData = array(
             "post_data" => $postData,
             "lData" => $lData
         );
         $encodeDate = json_encode($enData);
         $desType = 'Loan EMI deposit';
         $cheque_dd_no = NULL;
         $online_payment_id = NULL;
         $online_payment_by = NULL;
         $bank_name = NULL;
         $cheque_date = NULL;
         $account_number = NULL;
         $paymentMode = 4;
         $ssbpaymentMode = 3;
         $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['created_date'])));
         $checkSSBBalanceDeposit = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['ssb_id'])->whereDate('opening_date', '<=', $globaldate)->sum('deposit');
         $checkSSBBalanceWithdrawal = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['ssb_id'])->whereDate('opening_date', '<=', $globaldate)->sum('withdrawal');

         // Get ssb ac  head id
         $ssbHead = \App\Models\Plans::where('company_id', $mlResult['company_id'])->where('plan_category_code', 'S')->first();
         $paymentHead = $ssbHead->deposit_head_id;
         // End Get ssb ac  head id

         $closerAmount = $request['closerAmount'];

         $ssbBalanceAmount = $checkSSBBalanceDeposit - $checkSSBBalanceWithdrawal;

         if ($request['loan_emi_payment_mode'] == 0) {
             // for loans whose closing date > current date using due amount
             if ($request['closing_date'] > $request['application_date'] && $request['due_amount'] > 0) {
                 if ($ssbBalanceAmount >= $request['emi_amount'] && $ssbBalanceAmount >= $request['due_amount']) {
                     $due_amount = $request['due_amount'];
                     $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                     $v_no = "";
                     for ($i = 0; $i < 10; $i++) {
                         $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                     }
                     // Get saving account current balance
                     $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($dueAmount);


                     $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                         ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                         ->where('is_deleted', 0)
                         ->first();

                     $ssb['saving_account_id'] = $ssbAccountDetails->id;
                     $ssb['account_no'] = $ssbAccountDetails->account_no;
                     $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $due_amount;
                     $ssb['branch_id'] = $request['branch'];
                     $ssb['type'] = 9;
                     $ssb['deposit'] = 0;
                     $ssb['withdrawal'] = $due_amount;
                     $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                     $ssb['currency_code'] = 'INR';
                     $ssb['payment_type'] = 'DR';
                     $ssb['company_id'] = $companyId;
                     $ssb['payment_mode'] = 9;
                     $ssb['daybook_ref_id'] = $DayBookref;
                     $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                     $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;

                     $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                     $ssbBalance->balance = $due_amount;
                     $ssbBalance->save();

                     $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;


                     $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                         ->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))
                         ->where('is_deleted', 0)
                         ->get();

                     foreach ($record2 as $key => $value) {
                         $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                         $nsResult['opening_balance'] = $value->opening_balance;
                         $nsResult['company_id'] = $companyId;
                         $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                         $nsResult->save();
                     }
                     $data['saving_account_transaction_id'] = $ssb_transaction_id;
                     $data['loan_id'] = $request['loan_id'];
                     $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                     $satRefId = null;
                     $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);
                     $ssbCreateTran = null;
                     $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                     // ECS transaction table entery

                     $importData = [
                         'loan_type' => 'L',
                         'loan_id' => $request['loan_id'],
                         'account_number' => $request['account_number'],
                         'branch_id' => $request['branch'],
                         'daybook_ref_id' => $DayBookref,
                         'amount' => $due_amount,
                         'bank_acc_no' => $request['account_no'],
                         'associate_id' => $request['associate_member_id'],
                         'transaction_status' => 1,
                         'utr_transaction_number' => 'N/A',
                         'bank_name' => 'test',
                         'company_id' => $request['company_id'],
                         'transaction_type' => 2,
                         'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                     ];

                     $ecs = ECSTransaction::create($importData);

                     // Ecs trasaction table entry End

                     $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'CR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                     $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 4, 48, $ssbAccountDetails->id, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'DR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                     $ssbCreateTran = NULL;
                     $transactionPaymentMode = 0;
                     /************* Head Implement ****************/
                     if ($request['loan_emi_payment_mode'] == 0) {

                         $paymentMode = 4; //saving account transaction
                         $transactionPaymentMode = 3;



                     }

                     /************* Head Implement ****************/
                     /*---------- commission script  start  ---------*/
                     $daybookId = $createDayBook;
                     $total_amount = $request['deposite_amount'];
                     $percentage = 2;
                     $month = NULL;
                     $type_id = $request['loan_id'];
                     $type = 4;
                     $associate_id = $request['associate_member_id'];
                     $branch_id = $request['branch'];
                     $commission_type = 0;
                     $associateDetail = \App\Models\Member::where('id', $associate_id)->first();
                     $carder = $associateDetail->current_carder_id;
                     $associate_exist = 0;
                     $percentInDecimal = $percentage / 100;
                     $commission_amount = round($percentInDecimal * $total_amount, 4);
                     $loan_associate_code = $request->loan_associate_code;
                     $associateCommission['member_id'] = $associate_id;
                     $associateCommission['branch_id'] = $branch_id;
                     $associateCommission['type'] = $type;
                     $associateCommission['type_id'] = $type_id;
                     $associateCommission['day_book_id'] = $daybookId;
                     $associateCommission['total_amount'] = $total_amount;
                     $associateCommission['month'] = $month;
                     $associateCommission['commission_amount'] = $commission_amount;
                     $associateCommission['percentage'] = $percentage;
                     $associateCommission['commission_type'] = $commission_type;
                     $date = \App\Models\Daybook::where('id', $daybookId)->first();
                     $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
                     $associateCommission['pay_type'] = 4;
                     $associateCommission['carder_id'] = $carder;
                     $associateCommission['associate_exist'] = $associate_exist;

                     // Update is_bounce and emi due date
                     $mlData = \App\Models\Grouploans::find($request['loan_id']);
                     $emiDueDate = $mlData['emi_due_date'];

                     if ($mlData['emi_option'] == 1) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                     } elseif ($mlData['emi_option'] == 2) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                     } elseif ($mlData['emi_option'] == 3) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                     } else {
                         $emiDueDate = $mlData['emi_due_date'];
                     }



                     $loanData['is_bounce'] = 0;
                     $loanData['emi_due_date'] = $emiDueDate;
                     $mlData->update($loanData);
                     // end is_
                     /*---------- commission script  end  ---------*/
                     $createLoanDayBook = \App\Http\Controllers\Admin\CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->member_id, $roi, $principal_amount, 0, $due_amount, $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, null, $bank_name = NULL, $branch_name = NULL, $request['application_date'], $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
                     self::headTransaction($createLoanDayBook, $transactionPaymentMode, 2);
                     $totalDepsoit = \App\Models\LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                     $text = 'Dear Member,Received Rs.' . $due_amount . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                     $temaplteId = 1207166308935249821;
                     $contactNumber = array();
                     $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                     $contactNumber[] = $memberDetail->mobile_no;
                     $sendToMember = new Sms();
                     $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                     // if( $res['line'] > 0){
                     //     DB::rollback();
                     // }else{
                     // }

                 } elseif (($ssbBalanceAmount >= $request['emi_amount'] && $ssbBalanceAmount <= $request['due_amount']) || ($ssbBalanceAmount <= $request['emi_amount'] && $ssbBalanceAmount >= $request['due_amount'])) {
                     $due_amount = $ssbBalanceAmount;
                     $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                     $v_no = "";
                     for ($i = 0; $i < 10; $i++) {
                         $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                     }
                     // Get saving account current balance
                     $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($dueAmount);


                     $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                         ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                         ->where('is_deleted', 0)
                         ->first();

                     $ssb['saving_account_id'] = $ssbAccountDetails->id;
                     $ssb['account_no'] = $ssbAccountDetails->account_no;
                     $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $due_amount;
                     $ssb['branch_id'] = $request['branch'];
                     $ssb['type'] = 9;
                     $ssb['deposit'] = 0;
                     $ssb['withdrawal'] = $due_amount;
                     $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                     $ssb['currency_code'] = 'INR';
                     $ssb['payment_type'] = 'DR';
                     $ssb['company_id'] = $companyId;
                     $ssb['payment_mode'] = 9;
                     $ssb['daybook_ref_id'] = $DayBookref;
                     $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                     $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;

                     $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                     $ssbBalance->balance = $due_amount;
                     $ssbBalance->save();

                     $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;


                     $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                         ->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))
                         ->where('is_deleted', 0)
                         ->get();

                     foreach ($record2 as $key => $value) {
                         $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                         $nsResult['opening_balance'] = $value->opening_balance;
                         $nsResult['company_id'] = $companyId;
                         $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                         $nsResult->save();
                     }
                     $data['saving_account_transaction_id'] = $ssb_transaction_id;
                     $data['loan_id'] = $request['loan_id'];
                     $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                     $satRefId = null;
                     $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);
                     $ssbCreateTran = null;
                     $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                     // ECS transaction table entery

                     $importData = [
                         'loan_type' => 'L',
                         'loan_id' => $request['loan_id'],
                         'account_number' => $request['account_number'],
                         'branch_id' => $request['branch'],
                         'daybook_ref_id' => $DayBookref,
                         'amount' => $due_amount,
                         'bank_acc_no' => $request['account_no'],
                         'associate_id' => $request['associate_member_id'],
                         'transaction_status' => 1,
                         'utr_transaction_number' => 'N/A',
                         'bank_name' => 'test',
                         'company_id' => $request['company_id'],
                         'transaction_type' => 2,
                         'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                     ];
                     $ecs = ECSTransaction::create($importData);

                     // Ecs trasaction table entry End

                     $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'CR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                     $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 4, 48, $ssbAccountDetails->id, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'DR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                     $ssbCreateTran = NULL;
                     $transactionPaymentMode = 0;
                     /************* Head Implement ****************/
                     if ($request['loan_emi_payment_mode'] == 0) {

                         $paymentMode = 4; //saving account transaction
                         $transactionPaymentMode = 3;



                     }

                     /************* Head Implement ****************/
                     /*---------- commission script  start  ---------*/
                     $daybookId = $createDayBook;
                     $total_amount = $request['deposite_amount'];
                     $percentage = 2;
                     $month = NULL;
                     $type_id = $request['loan_id'];
                     $type = 4;
                     $associate_id = $request['associate_member_id'];
                     $branch_id = $request['branch'];
                     $commission_type = 0;
                     $associateDetail = \App\Models\Member::where('id', $associate_id)->first();
                     $carder = $associateDetail->current_carder_id;
                     $associate_exist = 0;
                     $percentInDecimal = $percentage / 100;
                     $commission_amount = round($percentInDecimal * $total_amount, 4);
                     $loan_associate_code = $request->loan_associate_code;
                     $associateCommission['member_id'] = $associate_id;
                     $associateCommission['branch_id'] = $branch_id;
                     $associateCommission['type'] = $type;
                     $associateCommission['type_id'] = $type_id;
                     $associateCommission['day_book_id'] = $daybookId;
                     $associateCommission['total_amount'] = $total_amount;
                     $associateCommission['month'] = $month;
                     $associateCommission['commission_amount'] = $commission_amount;
                     $associateCommission['percentage'] = $percentage;
                     $associateCommission['commission_type'] = $commission_type;
                     $date = \App\Models\Daybook::where('id', $daybookId)->first();
                     $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
                     $associateCommission['pay_type'] = 4;
                     $associateCommission['carder_id'] = $carder;
                     $associateCommission['associate_exist'] = $associate_exist;

                     // Update is_bounce and emi due date
                     $mlData = \App\Models\Grouploans::find($request['loan_id']);
                     $emiDueDate = $mlData['emi_due_date'];

                     if ($mlData['emi_option'] == 1) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                     } elseif ($mlData['emi_option'] == 2) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                     } elseif ($mlData['emi_option'] == 3) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                     } else {
                         $emiDueDate = $mlData['emi_due_date'];
                     }



                     $loanData['is_bounce'] = 0;
                     $loanData['emi_due_date'] = $emiDueDate;
                     $mlData->update($loanData);
                     // end is_
                     /*---------- commission script  end  ---------*/
                     $createLoanDayBook = \App\Http\Controllers\Admin\CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->member_id, $roi, $principal_amount, 0, $due_amount, $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, null, $bank_name = NULL, $branch_name = NULL, $request['application_date'], $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
                     self::headTransaction($createLoanDayBook, $transactionPaymentMode, 2);
                     $totalDepsoit = \App\Models\LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                     $text = 'Dear Member,Received Rs.' . $due_amount . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                     $temaplteId = 1207166308935249821;
                     $contactNumber = array();
                     $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                     $contactNumber[] = $memberDetail->mobile_no;
                     $sendToMember = new Sms();
                     $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                     // if( $res['line'] > 0){
                     //     DB::rollback();
                     // }else{
                     // }

                 } else {


                     if (isset($loanBounceCharges)) {

                         $deductAmount = $loanBounceCharges['charge'];
                         $totalAmountBounce = $loanBounceCharges['charge'] + $bounceGstAmount + $bounceGstAmount;


                         // DB::beginTransaction();
                         try {
                             $dayBookRefs = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($totalAmountBounce);
                             $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                                 ->whereDate('created_at', '<=', $transDate)->where('is_deleted', 0)->whereCompanyId($companyId)->orderby('id', 'desc')
                                 ->first();


                             $ssb['saving_account_id'] = $ssbAccountDetails->id;
                             $ssb['account_no'] = $ssbAccountDetails->account_no;
                             $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $totalAmountBounce;
                             $ssb['branch_id'] = $request['branch'];
                             $ssb['type'] = 9;
                             $ssb['deposit'] = 0;
                             $ssb['withdrawal'] = $totalAmountBounce;
                             $ssb['description'] = 'Emi Bounce Charge to ' . $mLoan->account_number;
                             $ssb['currency_code'] = 'INR';
                             $ssb['payment_type'] = 'DR';
                             $ssb['company_id'] = $companyId;
                             $ssb['payment_mode'] = 9;
                             $ssb['daybook_ref_id'] = $dayBookRefs;
                             $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                             $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;
                             $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                             $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                             // update saving account current balance
                             $ssbBalance = \App\Models\SavingAccount::find($ssbAccountDetails->id);
                             $ssbBalance->balance = $ssbBalance->balance - $totalAmountBounce;
                             $ssbBalance->save();
                             $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                                 ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))->where('is_deleted', 0)->get();
                             foreach ($record2 as $key => $value) {
                                 $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                                 $nsResult['opening_balance'] = $value->opening_balance;
                                 $nsResult['company_id'] = $companyId;
                                 $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                                 $nsResult->save();
                             }
                             $data['saving_account_transaction_id'] = $ssb_transaction_id;
                             $data['loan_id'] = $request['loan_id'];
                             $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                             $satRefId = null;
                             $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);

                             // set next emi due date and is_bounce
                             $mlData = \App\Models\Grouploans::find($request['loan_id']);
                             $emiDueDate = $mlData['emi_due_date'];

                             if ($mlData['emi_option'] == 1) {
                                 $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                             } elseif ($mlData['emi_option'] == 2) {
                                 $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                             } elseif ($mlData['emi_option'] == 3) {
                                 $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                             } else {
                                 $emiDueDate = $mlData['emi_due_date'];
                             }



                             $loanData['is_bounce'] = 1;
                             $loanData['emi_due_date'] = $emiDueDate;
                             $mlData->update($loanData);
                             // End set next emi due date and is_bounce

                             $importData = [
                                 'loan_type' => 'L',
                                 'loan_id' => $request['loan_id'],
                                 'account_number' => $request['account_number'],
                                 'branch_id' => $request['branch'],
                                 'daybook_ref_id' => $dayBookRefs,
                                 'amount' => $totalAmountBounce,
                                 'bank_acc_no' => $request['account_no'],
                                 'associate_id' => $request['associate_member_id'],
                                 'transaction_status' => 0,
                                 'utr_transaction_number' => 'N/A',
                                 'bank_name' => 'N/A',
                                 'company_id' => $request['company_id'],
                                 'transaction_type' => 2,
                                 'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                                 'cgst_charge' => $bounceGstAmount,
                                 'sgst_charge' => $bounceGstAmount,
                                 'bounce_charge' => $loanBounceCharges['charge'],
                             ];


                             $ecs = ECSTransaction::create($importData);

                             // Ecs trasaction table entry End

                             $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, $paymentHead, 4, 551, $request['ssb_id'], $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'DR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);


                             $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 435, 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $deductAmount, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $deductAmount . '', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);

                             $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 4, 551, $request['ssb_id'], $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                             $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $loanBounceCharges['charge'], 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);



                             // Bounce charge entry in loan_day_book  commented as per anup sir instruction 04-03-24

                             // $createLoanDayBook = self::createLoanDayBook($dayBookRefs, $dayBookRefs, $mLoan['loan_type'], 3, $loanId, $lId = NULL, $ssbAccountDetails->account_no, $mLoan['applicant_id'], 0, 0, 0, $totalAmountBounce, "Bounce Charge from saving account  $ssbAccountDetails->account_no", $mLoan['branch_id'], getBranchCode($mLoan['branch_id'])->branch_code, 'CR', 'INR', 4, NULL, NULL, $branch_name = NULL, $paymentDate, NULL, 1, 1, NULL, $mLoan->account_number, NULL, NULL, NULL, $request['branch'], 0, 0, 0, $mLoan['company_id'], null, $bounceGstAmount, $bounceGstAmount);

                             // Calculate intrest through cron
                             $stateId = branchName()->state_id;
                             $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                             $currentDate = date('Y-m-d', strtotime($currentDate));
                             // $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $request['account_number'], 0]);
                             // Calculate intrest through cron

                             // Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                             if (isset($bounceGstAmount) && $bounceGstAmount > 0) {


                                 $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 171, 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge CGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);

                                 $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 172, 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge SGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);



                                 $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);


                                 $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'Bounce charge CGST  ' . $mLoan['account_number'] . '', 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                                 $createdGstTransaction = CommanController::gstTransaction($dayBookRefs, $getGstSettingno->gst_no, null, $totalAmountBounce, $getBounceChargeSetting->gst_percentage, ($IntraState == false ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true) ? $bounceGstAmount + $bounceGstAmount : $totalAmountBounce + $bounceGstAmount, 435, $paymentDate, 'BC435', $mLoan['customer_id'], $mLoan['branch_id'], $mLoan['company_id']);

                             }
                             // end Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                             $text = 'Dear Member, Your Loan ECS bounced on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Bounce charge Rs ' . $totalAmountBounce . ' deducted from your SSB ' . $ssbAccountDetails->account_no . ' Samraddh Bestwin Microfinance';
                             $temaplteId = 1207171074323291072;
                             $contactNumber = array();
                             $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                             $contactNumber[] = $memberDetail->mobile_no;
                             $sendToMember = new Sms();
                             $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                             $res = ['line' => 0, 'message' => "Bounce charge has been deducted from your account. !"];
                             DB::commit();
                         } catch (\Exception $ex) {
                             // dd($ex->getLine(), $ex->getMessage());
                             $res['line'] = $ex->getLine();
                             $res['message'] = $ex->getMessage();
                             //  DB::rollback();
                         }
                     } else {

                         $res['message'] = "Bounce charge Not created. !";
                         $mlData = \App\Models\Grouploans::find($request['loan_id']);
                         $emiDueDate = $mlData['emi_due_date'];

                         if ($mlData['emi_option'] == 1) {
                             $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                         } elseif ($mlData['emi_option'] == 2) {
                             $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                         } elseif ($mlData['emi_option'] == 3) {
                             $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                         } else {
                             $emiDueDate = $mlData['emi_due_date'];
                         }
                         $loanData['is_bounce'] = 1;
                         $loanData['emi_due_date'] = $emiDueDate;
                         $mlData->update($loanData);
                     }
                 }
             }
             // For loans whose closing date <= current date using closure amount
             elseif ($request['closing_date'] <= $request['application_date']) {
                 if (($ssbBalanceAmount >= $request['emi_amount'] && $ssbBalanceAmount >= $closerAmount) || ($ssbBalanceAmount <= $request['emi_amount'] && $ssbBalanceAmount >= $closerAmount)) {
                     $due_amount = $closerAmount;
                     $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                     $v_no = "";
                     for ($i = 0; $i < 10; $i++) {
                         $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                     }
                     // Get saving account current balance
                     $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($dueAmount);


                     $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                         ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                         ->where('is_deleted', 0)
                         ->first();

                     $ssb['saving_account_id'] = $ssbAccountDetails->id;
                     $ssb['account_no'] = $ssbAccountDetails->account_no;
                     $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $due_amount;
                     $ssb['branch_id'] = $request['branch'];
                     $ssb['type'] = 9;
                     $ssb['deposit'] = 0;
                     $ssb['withdrawal'] = $due_amount;
                     $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                     $ssb['currency_code'] = 'INR';
                     $ssb['payment_type'] = 'DR';
                     $ssb['company_id'] = $companyId;
                     $ssb['payment_mode'] = 9;
                     $ssb['daybook_ref_id'] = $DayBookref;
                     $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                     $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;

                     $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                     $ssbBalance->balance = $due_amount;
                     $ssbBalance->save();

                     $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;


                     $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                         ->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))
                         ->where('is_deleted', 0)
                         ->get();

                     foreach ($record2 as $key => $value) {
                         $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                         $nsResult['opening_balance'] = $value->opening_balance;
                         $nsResult['company_id'] = $companyId;
                         $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                         $nsResult->save();
                     }
                     $data['saving_account_transaction_id'] = $ssb_transaction_id;
                     $data['loan_id'] = $request['loan_id'];
                     $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                     $satRefId = null;
                     $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);
                     $ssbCreateTran = null;
                     $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                     // ECS transaction table entery

                     $importData = [
                         'loan_type' => 'L',
                         'loan_id' => $request['loan_id'],
                         'account_number' => $request['account_number'],
                         'branch_id' => $request['branch'],
                         'daybook_ref_id' => $DayBookref,
                         'amount' => $due_amount,
                         'bank_acc_no' => $request['account_no'],
                         'associate_id' => $request['associate_member_id'],
                         'transaction_status' => 1,
                         'utr_transaction_number' => 'N/A',
                         'bank_name' => 'test',
                         'company_id' => $request['company_id'],
                         'transaction_type' => 2,
                         'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                     ];

                     $ecs = ECSTransaction::create($importData);

                     // Ecs trasaction table entry End

                     $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'CR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                     $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 4, 48, $ssbAccountDetails->id, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'DR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                     $ssbCreateTran = NULL;
                     $transactionPaymentMode = 0;
                     /************* Head Implement ****************/
                     if ($request['loan_emi_payment_mode'] == 0) {

                         $paymentMode = 4; //saving account transaction
                         $transactionPaymentMode = 3;

                     }

                     /************* Head Implement ****************/
                     /*---------- commission script  start  ---------*/
                     $daybookId = $createDayBook;
                     $total_amount = $request['deposite_amount'];
                     $percentage = 2;
                     $month = NULL;
                     $type_id = $request['loan_id'];
                     $type = 4;
                     $associate_id = $request['associate_member_id'];
                     $branch_id = $request['branch'];
                     $commission_type = 0;
                     $associateDetail = \App\Models\Member::where('id', $associate_id)->first();
                     $carder = $associateDetail->current_carder_id;
                     $associate_exist = 0;
                     $percentInDecimal = $percentage / 100;
                     $commission_amount = round($percentInDecimal * $total_amount, 4);
                     $loan_associate_code = $request->loan_associate_code;
                     $associateCommission['member_id'] = $associate_id;
                     $associateCommission['branch_id'] = $branch_id;
                     $associateCommission['type'] = $type;
                     $associateCommission['type_id'] = $type_id;
                     $associateCommission['day_book_id'] = $daybookId;
                     $associateCommission['total_amount'] = $total_amount;
                     $associateCommission['month'] = $month;
                     $associateCommission['commission_amount'] = $commission_amount;
                     $associateCommission['percentage'] = $percentage;
                     $associateCommission['commission_type'] = $commission_type;
                     $date = \App\Models\Daybook::where('id', $daybookId)->first();
                     $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
                     $associateCommission['pay_type'] = 4;
                     $associateCommission['carder_id'] = $carder;
                     $associateCommission['associate_exist'] = $associate_exist;

                     // Update is_bounce and emi due date
                     $mlData = \App\Models\Grouploans::find($request['loan_id']);
                     $emiDueDate = $mlData['emi_due_date'];

                     if ($mlData['emi_option'] == 1) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                     } elseif ($mlData['emi_option'] == 2) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                     } elseif ($mlData['emi_option'] == 3) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                     } else {
                         $emiDueDate = $mlData['emi_due_date'];
                     }



                     $loanData['is_bounce'] = 0;
                     $loanData['emi_due_date'] = $emiDueDate;
                     $mlData->update($loanData);
                     // end is_
                     /*---------- commission script  end  ---------*/
                     $createLoanDayBook = \App\Http\Controllers\Admin\CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->member_id, $roi, $principal_amount, 0, $due_amount, $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, null, $bank_name = NULL, $branch_name = NULL, $request['application_date'], $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
                     self::headTransaction($createLoanDayBook, $transactionPaymentMode, 2);
                     $totalDepsoit = \App\Models\LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                     $text = 'Dear Member,Received Rs.' . $due_amount . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                     $temaplteId = 1207166308935249821;
                     $contactNumber = array();
                     $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                     $contactNumber[] = $memberDetail->mobile_no;
                     $sendToMember = new Sms();
                     $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                     // if( $res['line'] > 0){
                     //     DB::rollback();
                     // }else{
                     // }

                 } elseif (($ssbBalanceAmount >= $request['emi_amount'] && $ssbBalanceAmount <= $closerAmount)) {
                     $due_amount = $ssbBalanceAmount;
                     $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                     $v_no = "";
                     for ($i = 0; $i < 10; $i++) {
                         $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                     }
                     // Get saving account current balance
                     $DayBookref = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($dueAmount);


                     $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                         ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                         ->where('is_deleted', 0)
                         ->first();

                     $ssb['saving_account_id'] = $ssbAccountDetails->id;
                     $ssb['account_no'] = $ssbAccountDetails->account_no;
                     $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $due_amount;
                     $ssb['branch_id'] = $request['branch'];
                     $ssb['type'] = 9;
                     $ssb['deposit'] = 0;
                     $ssb['withdrawal'] = $due_amount;
                     $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                     $ssb['currency_code'] = 'INR';
                     $ssb['payment_type'] = 'DR';
                     $ssb['company_id'] = $companyId;
                     $ssb['payment_mode'] = 9;
                     $ssb['daybook_ref_id'] = $DayBookref;
                     $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                     $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;

                     $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                     $ssbBalance->balance = $due_amount;
                     $ssbBalance->save();

                     $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;


                     $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                         ->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))
                         ->where('is_deleted', 0)
                         ->get();

                     foreach ($record2 as $key => $value) {
                         $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                         $nsResult['opening_balance'] = $value->opening_balance;
                         $nsResult['company_id'] = $companyId;
                         $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                         $nsResult->save();
                     }
                     $data['saving_account_transaction_id'] = $ssb_transaction_id;
                     $data['loan_id'] = $request['loan_id'];
                     $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                     $satRefId = null;
                     $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);
                     $ssbCreateTran = null;
                     $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                     // ECS transaction table entery

                     $importData = [
                         'loan_type' => 'L',
                         'loan_id' => $request['loan_id'],
                         'account_number' => $request['account_number'],
                         'branch_id' => $request['branch'],
                         'daybook_ref_id' => $DayBookref,
                         'amount' => $due_amount,
                         'bank_acc_no' => $request['account_no'],
                         'associate_id' => $request['associate_member_id'],
                         'transaction_status' => 1,
                         'utr_transaction_number' => 'N/A',
                         'bank_name' => 'test',
                         'company_id' => $request['company_id'],
                         'transaction_type' => 2,
                         'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                     ];
                     $ecs = ECSTransaction::create($importData);

                     // Ecs trasaction table entry End

                     $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'CR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                     $principalbranchDayBook = \App\Http\Controllers\Admin\CommanController::branchDayBookNew($DayBookref, $branchId, 4, 48, $ssbAccountDetails->id, $ssbAccountTran->id, $request['associate_member_id'], $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $due_amount, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($due_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($due_amount) . '', 'DR', 3, 'INR', $v_no, $ssbAccountDetails->id, $cheque_no = NULL, $transction_no = NULL, $entry_date = $request['application_date'], $entry_time = date("H:i:s"), 1, 1, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                     $ssbCreateTran = NULL;
                     $transactionPaymentMode = 0;
                     /************* Head Implement ****************/
                     if ($request['loan_emi_payment_mode'] == 0) {

                         $paymentMode = 4; //saving account transaction
                         $transactionPaymentMode = 3;



                     }

                     /************* Head Implement ****************/
                     /*---------- commission script  start  ---------*/
                     $daybookId = $createDayBook;
                     $total_amount = $request['deposite_amount'];
                     $percentage = 2;
                     $month = NULL;
                     $type_id = $request['loan_id'];
                     $type = 4;
                     $associate_id = $request['associate_member_id'];
                     $branch_id = $request['branch'];
                     $commission_type = 0;
                     $associateDetail = \App\Models\Member::where('id', $associate_id)->first();
                     $carder = $associateDetail->current_carder_id;
                     $associate_exist = 0;
                     $percentInDecimal = $percentage / 100;
                     $commission_amount = round($percentInDecimal * $total_amount, 4);
                     $loan_associate_code = $request->loan_associate_code;
                     $associateCommission['member_id'] = $associate_id;
                     $associateCommission['branch_id'] = $branch_id;
                     $associateCommission['type'] = $type;
                     $associateCommission['type_id'] = $type_id;
                     $associateCommission['day_book_id'] = $daybookId;
                     $associateCommission['total_amount'] = $total_amount;
                     $associateCommission['month'] = $month;
                     $associateCommission['commission_amount'] = $commission_amount;
                     $associateCommission['percentage'] = $percentage;
                     $associateCommission['commission_type'] = $commission_type;
                     $date = \App\Models\Daybook::where('id', $daybookId)->first();
                     $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
                     $associateCommission['pay_type'] = 4;
                     $associateCommission['carder_id'] = $carder;
                     $associateCommission['associate_exist'] = $associate_exist;

                     // Update is_bounce and emi due date
                     $mlData = \App\Models\Grouploans::find($request['loan_id']);
                     $emiDueDate = $mlData['emi_due_date'];

                     if ($mlData['emi_option'] == 1) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                     } elseif ($mlData['emi_option'] == 2) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                     } elseif ($mlData['emi_option'] == 3) {
                         $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                     } else {
                         $emiDueDate = $mlData['emi_due_date'];
                     }



                     $loanData['is_bounce'] = 0;
                     $loanData['emi_due_date'] = $emiDueDate;
                     $mlData->update($loanData);
                     // end is_
                     /*---------- commission script  end  ---------*/
                     $createLoanDayBook = \App\Http\Controllers\Admin\CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->member_id, $roi, $principal_amount, 0, $due_amount, $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, null, $bank_name = NULL, $branch_name = NULL, $request['application_date'], $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
                     self::headTransaction($createLoanDayBook, $transactionPaymentMode, 2);
                     $totalDepsoit = \App\Models\LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                     $text = 'Dear Member,Received Rs.' . $due_amount . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                     $temaplteId = 1207166308935249821;
                     $contactNumber = array();
                     $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                     $contactNumber[] = $memberDetail->mobile_no;
                     $sendToMember = new Sms();
                     $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                     // if( $res['line'] > 0){
                     //     DB::rollback();
                     // }else{
                     // }

                 } else {


                     if (isset($loanBounceCharges)) {

                         $deductAmount = $loanBounceCharges['charge'];
                         $totalAmountBounce = $loanBounceCharges['charge'] + $bounceGstAmount + $bounceGstAmount;


                         // DB::beginTransaction();
                         try {
                             $dayBookRefs = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($totalAmountBounce);
                             $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                                 ->whereDate('created_at', '<=', $transDate)->where('is_deleted', 0)->whereCompanyId($companyId)->orderby('id', 'desc')
                                 ->first();


                             $ssb['saving_account_id'] = $ssbAccountDetails->id;
                             $ssb['account_no'] = $ssbAccountDetails->account_no;
                             $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $totalAmountBounce;
                             $ssb['branch_id'] = $request['branch'];
                             $ssb['type'] = 9;
                             $ssb['deposit'] = 0;
                             $ssb['withdrawal'] = $totalAmountBounce;
                             $ssb['description'] = 'Emi Bounce Charge to ' . $mLoan->account_number;
                             $ssb['currency_code'] = 'INR';
                             $ssb['payment_type'] = 'DR';
                             $ssb['company_id'] = $companyId;
                             $ssb['payment_mode'] = 9;
                             $ssb['daybook_ref_id'] = $dayBookRefs;
                             $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                             $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;
                             $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                             $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                             // update saving account current balance
                             $ssbBalance = \App\Models\SavingAccount::find($ssbAccountDetails->id);
                             $ssbBalance->balance = $ssbBalance->balance - $totalAmountBounce;
                             $ssbBalance->save();
                             $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                                 ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))->where('is_deleted', 0)->get();
                             foreach ($record2 as $key => $value) {
                                 $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                                 $nsResult['opening_balance'] = $value->opening_balance;
                                 $nsResult['company_id'] = $companyId;
                                 $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                                 $nsResult->save();
                             }
                             $data['saving_account_transaction_id'] = $ssb_transaction_id;
                             $data['loan_id'] = $request['loan_id'];
                             $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                             $satRefId = null;
                             $updateSsbDayBook = self::updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);

                             // set next emi due date and is_bounce
                             $mlData = \App\Models\Grouploans::find($request['loan_id']);
                             $emiDueDate = $mlData['emi_due_date'];

                             if ($mlData['emi_option'] == 1) {
                                 $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                             } elseif ($mlData['emi_option'] == 2) {
                                 $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                             } elseif ($mlData['emi_option'] == 3) {
                                 $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                             } else {
                                 $emiDueDate = $mlData['emi_due_date'];
                             }



                             $loanData['is_bounce'] = 1;
                             $loanData['emi_due_date'] = $emiDueDate;
                             $mlData->update($loanData);
                             // End set next emi due date and is_bounce

                             $importData = [
                                 'loan_type' => 'L',
                                 'loan_id' => $request['loan_id'],
                                 'account_number' => $request['account_number'],
                                 'branch_id' => $request['branch'],
                                 'daybook_ref_id' => $dayBookRefs,
                                 'amount' => $totalAmountBounce,
                                 'bank_acc_no' => $request['account_no'],
                                 'associate_id' => $request['associate_member_id'],
                                 'transaction_status' => 0,
                                 'utr_transaction_number' => 'N/A',
                                 'bank_name' => 'N/A',
                                 'company_id' => $request['company_id'],
                                 'transaction_type' => 2,
                                 'date' => date("Y-m-d", strtotime(convertDate($request['application_date']))),
                                 'cgst_charge' => $bounceGstAmount,
                                 'sgst_charge' => $bounceGstAmount,
                                 'bounce_charge' => $loanBounceCharges['charge'],
                             ];


                             $ecs = ECSTransaction::create($importData);

                             // Ecs trasaction table entry End

                             $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, $paymentHead, 4, 551, $request['ssb_id'], $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'DR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);


                             $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 435, 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $deductAmount, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $deductAmount . '', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);

                             $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 4, 551, $request['ssb_id'], $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                             $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $loanBounceCharges['charge'], 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);



                             // Bounce charge entry in loan_day_book  commented as per anup sir instruction 04-03-24

                             // $createLoanDayBook = self::createLoanDayBook($dayBookRefs, $dayBookRefs, $mLoan['loan_type'], 3, $loanId, $lId = NULL, $ssbAccountDetails->account_no, $mLoan['applicant_id'], 0, 0, 0, $totalAmountBounce, "Bounce Charge from saving account  $ssbAccountDetails->account_no", $mLoan['branch_id'], getBranchCode($mLoan['branch_id'])->branch_code, 'CR', 'INR', 4, NULL, NULL, $branch_name = NULL, $paymentDate, NULL, 1, 1, NULL, $mLoan->account_number, NULL, NULL, NULL, $request['branch'], 0, 0, 0, $mLoan['company_id'], null, $bounceGstAmount, $bounceGstAmount);

                             // Calculate intrest through cron
                             $stateId = branchName()->state_id;
                             $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                             $currentDate = date('Y-m-d', strtotime($currentDate));
                             // $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $request['account_number'], 0]);
                             // Calculate intrest through crons

                             // Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                             if (isset($bounceGstAmount) && $bounceGstAmount > 0) {


                                 $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 171, 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge CGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);

                                 $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 172, 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge SGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id ?? null, $mLoan['company_id']);



                                 $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);


                                 $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $mLoan['member_id'], $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'Bounce charge CGST  ' . $mLoan['account_number'] . '', 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id ?? null, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                                 $createdGstTransaction = CommanController::gstTransaction($dayBookRefs, $getGstSettingno->gst_no, null, $totalAmountBounce, $getBounceChargeSetting->gst_percentage, ($IntraState == false ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true) ? $bounceGstAmount + $bounceGstAmount : $totalAmountBounce + $bounceGstAmount, 435, $paymentDate, 'BC435', $mLoan['customer_id'], $mLoan['branch_id'], $mLoan['company_id']);

                             }
                             // end Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                             $text = 'Dear Member, Your Loan ECS bounced on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Bounce charge Rs ' . $totalAmountBounce . ' deducted from your SSB ' . $ssbAccountDetails->account_no . ' Samraddh Bestwin Microfinance';
                             $temaplteId = 1207171074323291072;
                             $contactNumber = array();
                             $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                             $contactNumber[] = $memberDetail->mobile_no;
                             $sendToMember = new Sms();
                             $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                             $res = ['line' => 0, 'message' => "Bounce charge has been deducted from your account. !"];
                             DB::commit();
                         } catch (\Exception $ex) {
                             // dd($ex->getLine(), $ex->getMessage());
                             $res['line'] = $ex->getLine();
                             $res['message'] = $ex->getMessage();
                             //  DB::rollback();
                         }
                     } else {

                         $res['message'] = "Bounce charge Not created. !";
                         $mlData = \App\Models\Grouploans::find($request['loan_id']);
                         $emiDueDate = $mlData['emi_due_date'];

                         if ($mlData['emi_option'] == 1) {
                             $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                         } elseif ($mlData['emi_option'] == 2) {
                             $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                         } elseif ($mlData['emi_option'] == 3) {
                             $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                         } else {
                             $emiDueDate = $mlData['emi_due_date'];
                         }
                         $loanData['is_bounce'] = 1;
                         $loanData['emi_due_date'] = $emiDueDate;
                         $mlData->update($loanData);
                     }
                 }
             } else {
                 // Update is_bounce and emi due date
                 $mlData = \App\Models\Grouploans::find($request['loan_id']);
                 $emiDueDate = $mlData['emi_due_date'];

                 if ($mlData['emi_option'] == 1) {
                     $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                 } elseif ($mlData['emi_option'] == 2) {
                     $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                 } elseif ($mlData['emi_option'] == 3) {
                     $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                 } else {
                     $emiDueDate = $mlData['emi_due_date'];
                 }



                 $loanData['is_bounce'] = 0;
                 $loanData['emi_due_date'] = $emiDueDate;
                 $mlData->update($loanData);
             }
         }

         DB::commit();
         return $res = ['line' => 0, 'message' => "Loan EMI Successfully submitted!"];

         // return $res;
     } catch (\Exception $ex) {
         DB::rollback();
         dd($ex->getLine(), $ex->getMessage());
         return $res = ['line' => $ex->getLine(), 'message' => $ex->getMessage()];
     }
 }
    //End New SSB payment and bounce charge through cron
}
