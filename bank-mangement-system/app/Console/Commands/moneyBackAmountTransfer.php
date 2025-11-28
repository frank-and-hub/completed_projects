<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Http\Traits\MoneyBackCalculation;
use App\Http\Controllers\Admin\CommanController;
use Session;
use DB;
use App\Services\CronStoreInfo;

class moneyBackAmountTransfer extends Command
{
    use MoneyBackCalculation;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:transferMoneyback';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer Money Back in Money back Investment Account and Saving Account';
    /**
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
     * This command processed money back for investment account on current date  (Money back Plan)
     * @return mixed
     */
    public function handle()
    {
        // Begin database transactions
        // DB::beginTransaction();
        try {
            // set log File variable  to store logs of the command
            $logName = 'moneyBack/money_back-' . date('Y-m-d', strtotime(now())) . '.log';
            $moneyBackSend = 'Not Sended';
            $cronChannel = 'moneyBack';
            //get all money back investment account
            $investmentAccount = \App\Models\Memberinvestments::whereHas('plan', function ($q) {
                $q->where('plan_category_code', 'M')->where('plan_sub_category_code', 'B');
            })
                // ->WhereIn('account_number',['R-086523001418'])
                ->where('is_mature', 1)
                ->chunk(20, function ($accountNumbers) use ($moneyBackSend, $cronChannel, $logName) {
                    // the code process invetsment accounts in chunks of 20
                    //Intialize varaibles to store transaction data
                    $entryTime = date("h:i:s");
                    $daybookRecord = array ();
                    $ssb = array ();
                    $amount = 0;
                    $carryForwardAmount = 0;
                    // Call a service to store command start process
                    $this->cronService->startCron($this->signature, $logName);
                    // Iterate through each investment account
                    foreach ($accountNumbers as $key => $accountNumber) {
                        //set Varioud variables based on account data
                        $branchId = $accountNumber->branch_id;
                        $BranchDetail = getBranchDetail($branchId);
                        $BranchManagerId = getBranchDetailManagerId($BranchDetail->manager_id);
                        $stateId = $BranchManagerId->state_id;
                        // intialize current date based on state
                        $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                        $currentDate = date('Y-m-d', strtotime($currentDate));
                        // $currentDate = '2024-03-17'; // For hit manual cron
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
                        $this->cronService->inProgress();
                        // Check  if the endYearDate of dateInfo matches the current date
                        if ($dateInfo['endYearDate'] == $currentDate) {
                            // Calculate the total count of records with investment_id and plan_type_id  equal to certain values
                            $countTotalYear = \App\Models\InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $accountNumber->id)->where('plan_type_id', 3)->count();
                            // Check if a record with specific investment_id,plan_type_id and date exists
                            $checkExist = \App\Models\InvestmentMonthlyYearlyInterestDeposits::where('investment_id', $accountNumber->id)->where('plan_type_id', 3)->where('date', $dateInfo['endYearDate'])->exists();
                            // Store a formatted created_at value in the session
                            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($dateInfo['endYearDate']))));
                            // Calculate no of transactions based on tenure
                            $noOfTransaction = $accountNumber->tenure / 12;
                            // Check if the countTotalYear less than 7($noOfTransaction) and  matches the checkExist
                            if ($countTotalYear < 7 && !$checkExist) {
                                // Calculate moneyBack Amount based on provided accountNumber,dateInfo and months
                                $transferedData = $this->calculate($accountNumber, $dateInfo);
                                // Match MoneyBackAmount greater than 0 then execute
                                if ($transferedData['moneyBackAmount'] > 0) {
                                    // Intialize variable based on provided transferData
                                    $amount = $transferedData['moneyBackAmount'];
                                    $carryForwardAmount = $transferedData['carryForwardAmount'];
                                    $amountArraySsb = array ('1' => ($amount));
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
                                    $record3 = \App\Models\SavingAccountTransactionView::where('account_no', $accountNumber->ssb->account_no)->where('opening_date', '<=', date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate']))))->orderby('id', 'desc')->first();
                                    $balance_update = $amount + $accountNumber->ssb->balance;
                                    // Retrive ssb detail based on  money back account Number
                                    $ssbBalance = \App\Models\SavingAccount::find($accountNumber->ssb->id);
                                    $ssbBalance->balance = $balance_update;
                                    $ssbBalance->save();
                                    $ssb[] = [
                                        'saving_account_id' => $accountNumber->ssb->id,
                                        'account_no' => $accountNumber->ssb->account_no,
                                        'opening_balance' => (isset ($record3->opening_balance)) ? $amount + $record3->opening_balance : $amount,
                                        'deposit' => $amount,
                                        'branch_id' => $branchId,
                                        'type' => 10,
                                        'withdrawal' => 0,
                                        'description' => $description,
                                        'currency_code' => 'INR',
                                        'payment_type' => 'CR',
                                        'payment_mode' => 3,
                                        'daybook_ref_id' => $dayBookRef,
                                        'created_at' => date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate']))),
                                    ];
                                    // Retrive latest transaction of saving account based on account number and endYearDate  (SavingAccountTransactionView It is mysql view)
                                    $record4 = \App\Models\SavingAccountTransactionView::where('account_no', $accountNumber->ssb->account_no)->where('opening_date', '>', date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate']))))->get();
                                    foreach ($record4 as $key => $value) {
                                        $sResult = \App\Models\SavingAccountTranscation::find($value->transaction_id);
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

                                    // Store ssb transaction in daybook  (as per new information this code in commented by Alpana ma'am)

                                    // $createDayBook = CommanController::createDayBookNew($ssbCreateTran, $satRefId, 1, $accountNumber->ssb->id, NULL, $accountNumber->ssb->member_id, $amount + $accountNumber->ssb->balance, $amount, $withdrawal = 0, $description, $accountNumber->ssb->account_no, $accountNumber->ssb->branch_id, $accountNumber->ssb->branch_code, $amountArraySsb, $paymentMode, NULL, $accountNumber->ssb->member_id, $accountNumber->ssb->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($dateInfo['endYearDate']))), NULL, $online_payment_by = NULL, $accountNumber->ssb->id, 'CR', NULL, NULL, NULL, NULL, NULL, $companyId);

                                    // Retrive saving account head id based on company id
                                    $getPlan = \App\Models\Plans::where('company_id', $accountNumber->company_id)->where('plan_category_code', 'S')->first('deposit_head_id');

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
                                    \App\Models\InvestmentMonthlyYearlyInterestDeposits::create([
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
                                    /************** Head Implement************/
                                }
                                $cBalance = ($accountNumber->current_balance - $amount);
                                // update current_balance,last_deposit_to_ssb_amount,last_deposit_to_ssb_date,carry_forward_amount based on provided transaction and account Number
                                \App\Models\Memberinvestments::where('id', $investementId)->update([
                                    'current_balance' => $cBalance,
                                    'last_deposit_to_ssb_amount' => ($amount),
                                    'last_deposit_to_ssb_date' => $dateInfo['endYearDate'],
                                    'carry_forward_amount' => $carryForwardAmount,
                                ]);
                                $moneyBackSend = 'Sended';
                                // Store Logs
                                \Log::info('Money Back of --' . $accountNumber->account_number . ' on ' . $dateInfo['endYearDate'] . ' and cron running date is ' . $currentDate);
                            }
                        }
                        // Store Logs in moneyBackj Channel
                        \Log::channel('moneyBack')->info('MemberId- ' . $accountNumber->member_id . 'Account Number - ' . $accountNumber->account_number . ', InvestmentId -' . $accountNumber->id . ', Current Balance- ' . $accountNumber->current_balance . ' , Current Amount - ' . $amount . ', Carry Forward  - ' . $carryForwardAmount . ', moneyBackSend - ' . $moneyBackSend);
                        // \Log::channel('moneyBack')->info('MemberId- ' . $accountNumber->member_id . 'Account Number - ' . $accountNumber->account_number . ', InvestmentId -' . $accountNumber->id . ', Current Balance- ' . $accountNumber->current_balance . ' , Current Amount - ' . $amount . ', Carry Forward  - ' . $carryForwardAmount . ', moneyBackSend' . $moneyBackSend);
                    }
                    // Check daybookRecord array is empty or not
                    if (count($daybookRecord) > 0) {
                        $transcation = \App\Models\Daybook::insert($daybookRecord);
                    }
                    // Check ssb array is empty or not
                    if (count($ssb) > 0) {
                        $ssbAccountTran = \App\Models\SavingAccountTranscation::insert($ssb);
                    }
                    // DB::Commit();
                    $this->cronService->completed();
                });
        } catch (\Exception $e) {
            // DB::rollback();
            $this->cronService->errorLogs(4, $e->getMessage() . ' -Line No ' . $e->getLine() . '-File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
        } finally {
            if (isset($e) && $e instanceof \Exception) {
                $this->cronService->sentCronSms(false);
            } else {
                $this->cronService->sentCronSms(true);
            }
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
}
