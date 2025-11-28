<?php
namespace App\Console\Commands;
use App\Http\Controllers\Admin\CommanController;
use Illuminate\Console\Command;
namespace App\Console\Commands;
use App\Http\Controllers\Admin\CommanController;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use App\Models\InvestmentMonthlyYearlyInterestDeposits;
use App\Models\PlanTenures;
use App\Models\Plans;
use App\Models\MemberInvestmentInterest;
use DB;
use Session;
use App\Services\Sms;
use App\Services\CronStoreInfo;
class MonthlyIncomeSchemeInterestTransfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthlyincomeschemeinteresttransfer:transfer';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer Monthly Income Scheme Interest Transfer';/**
        * Create a new command instance.
        *
        * @return void
        */
    public function __construct(CronStoreInfo $CronStoreInfo)
    {
        parent::__construct();
        $this->cronService = $CronStoreInfo;
    }
    /**
     * Execute the console command.
     * This command processed  mis for investment account on current date  (MIS plan)
     * @return mixed
     */
    public function handle()
    {
        // Begin database transactions
        try {
            // Retrive current Financial Year
            $finacialYear = getFinacialYear();
            $fenddate = date("Y", strtotime(convertDate($finacialYear['dateEnd'])));
            $fstrtdate = date("Y", strtotime(convertDate($finacialYear['dateStart'])));
            $logName = 'mis/mis-' . date('Y-m-d', strtotime(now())) . '.log';
            // Retrieve all not matured (is_mature ,1)  accounts under the Monthly Income Scheme Plan .
            // If you want to execute the command for a specific account number, uncomment the 'account_number' condition.
            $sjInvestment = Memberinvestments::whereHas('plan', function ($q) {
                $q->where('plan_sub_category_code', 'I');
            })->with(['branch'])
                // ->whereIn('account_number', ['706790600001'])  // Uncomment this line to filter by account number
                ->where('is_mature', 1)
                ->get();
            //Intialize variables to store transaction data
            $entryTime = date("H:i:s");
            $cDate = Carbon::now()->format('Y-m-d');
            $cYear = Carbon::now()->format('Y');
            //  $cDate = date('2023-10-16');  // It is used for execute manual cron on the particular date
            // Call a service to store command start process
            $this->cronService->startCron($this->signature, $logName);
            // Iterate through each investment account
            foreach ($sjInvestment as $key => $val) {
                // Call a service to store command inProgress process
                $this->cronService->inProgress();
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
                                    /************* Head Implement************/
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
                                    // Create a saving account record in a daybooks table  (as per new information this code in commented by Alpana ma'am)
                                    // $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->associate_id,$ssbAccountDetails->member_id,$totalCalculate-$tdsAmountonInterest+$ssbAccountDetails->balance,$totalCalculate-$tdsAmountonInterest,$withdrawal=0,$desssb,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($createDate))),NULL,$online_payment_by=NULL,NULL,'CR',$ssbAccountDetails->company_id);
                                    // Create a mis plan record in a  daybooks table
                                    $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance, $totalCalculate, $withdrawal = 0, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($createDate))), NULL, $online_payment_by = NULL, NULL, 'CR', $val->company_id);
                                    // If tdsAmountonInterest greater than 0
                                    if ($tdsAmountonInterest > 0) {
                                        // Retrive latest record from member_investment_interest_tds
                                        $getLastRecord = \App\Models\MemberInvestmentInterestTds::where('member_id', $val->member_id)->where('investment_id', $val->id)->orderby('id', 'desc')->first();
                                        // Create a record in member_investment_interest_tds
                                        \App\Models\MemberInvestmentInterestTds::create([
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
                                    /************* Head Implement************/
                                }
                            }
                            // Check last_deposit_to_ssb_date is not null
                            elseif ($val->last_deposit_to_ssb_date != '') {
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
                                    /************* Head Implement************/
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
                                    // Create a ssb  CR record in daybooks table  (as per new information this code in commented by Alpana ma'am)
                                    $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->associate_id, $ssbAccountDetails->member_id, $totalCalculate - $tdsAmountonInterest + $ssbAccountDetails->balance, $totalCalculate - $tdsAmountonInterest, $withdrawal = 0, $desssb, $ssbAccountDetails->account_no, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($addOneMonth))), NULL, $online_payment_by = NULL, NULL, 'CR', $ssbAccountDetails->company_id);
                                    // Create a mis CR record in daybooks table
                                    $createDayBookCR = CommanController::createDayBook($ssbCreateTran, $satRefId, 29, $val->id, $val->associate_id, $val->member_id, $totalCalculate + $val->balance, $totalCalculate, $withdrawal = 0, $description, $val->account_number, $val->branch_id, $val['branch']->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $val->member_id, $val->account_number, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($addOneMonth))), NULL, $online_payment_by = NULL, NULL, 'CR', $val->company_id);
                                    if ($tdsAmountonInterest > 0) {
                                        // Retrive latest record from member_investment_interest_tds
                                        \App\Models\MemberInvestmentInterestTds::create([
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
                                    /*Tds Entries in Head and Member Transqaction  */
                                    /************* Head Implement************/
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
                        }
                    }
                }
                updateRenewalTransaction($val->account_number);
                \Log::channel('mis')->info('MemberId- ' . $val->member_id . 'Account Number - ' . $val->account_number . ', InvestmentId -' . $val->id . ', Current Balance- ' . $val->current_balance . ' , Deno Amount - ' . $depositeAmount . ', Monthly Interest - ' . $monthlyInterest . ', Tds Amount - ' . $tdsAmountonInterest . ', misSend' . $misSend);
            }
            // DB::commit();
            $this->cronService->completed();
        } catch (\Exception $e) {
            //DB::rollback();
            $this->cronService->errorLogs(4, $e->getMessage() . ' -Line No ' . $e->getLine() . '-File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
        } finally {
            if (isset($e) && $e instanceof \Exception) {
                $this->cronService->sentCronSms(false);
            } else {
                $this->cronService->sentCronSms(true);
            }
        }
    }
}
