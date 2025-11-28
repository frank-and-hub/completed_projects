<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use DB;




use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Artisan;
use App\Services\Sms;


class MonthlyEcsTransactionLoan extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:MonthlyEcsTransactionLoan';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Monthly ECS Transaction Loan cron run sucessfully';

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
	 *
	 * @return int
	 */
	public function handle()
	{
		// dd('asas');
		$t = true;
		$logName = 'loan/monthly_ESC_transaction_for_loan' . '-' . date('Y-m-d', strtotime(now())) . '.log';
		print_r($logName);
		$this->cronService->startCron($this->signature, $logName);
		$cronName = "Monthly ECS Transaction " . ($t ? 'Loan' : 'Group Loan');
		$currentDate = date('d/m/Y');

		$stateId = 33; // system date come from rajasthan state set date only.
		Log::channel('ssbEcsCronLoan')->info('Transaction  Start');
		// try {
		$branches = \App\Models\Branch::select(['id', 'state_id'])->get();
		// dd($branches);
		foreach ($branches as $branch) {

			$systemDate = headerMonthAvailability(date('d'), date('m'), date('Y'), $branch->state_id);
			$sysDate = date('Y-m-d', strtotime(convertdate($systemDate)));

			$loanModel = $t ? \App\Models\Memberloans::query() : \App\Models\Grouploans::query();

			$loans = $loanModel
				->with('newLoanSSB')
				->select('id', 'company_id', 'branch_id', 'emi_due_date', 'account_number', 'status', 'ssb_id', 'due_amount', 'associate_member_id', 'emi_amount', 'closing_date', 'emi_option', 'approve_date', 'loan_type', 'ROI')
				->where('branch_id', $branch->id)
				->where('status', 4)
				// ->where('emi_due_date', '=' ,'2024-03-17')
				// ->where('emi_due_date','>' ,'2024-03-16')
				// ->whereIn('emi_due_date', ['2024-04-10','2024-04-11'])
				->where('emi_due_date', $sysDate)
				->where('ecs_type', 2)
				->get();
			// p($loans);
			Log::channel('ssbEcsCronLoan')->info('total account =' . $loans->count());
			foreach ($loans as $loan) {
				$amount = $loan->due_amount;
				p($loan->account_number);
				// Run cron or calculate intrest 
				$stateId = $loan['loanBranch']->state_id;
				$currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
				$currentDate = date('Y-m-d', strtotime($currentDate));
				$dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $loan->account_number, 0]);
				Log::channel('ssbEcsCronLoan')->info('interestCalculate  a/c no =' . $loan->account_number . ', date =' . $currentDate);
				// Run cron or calculate intrest End


				// calculate closer amount
				$outstandingAmountData = \App\Models\LoanEmisNew::where("loan_id", $loan->id)
					->where("loan_type", $loan->loan_type)
					->where("is_deleted", "0")
					->orderBy("id", "desc")
					->first(["emi_date", "out_standing_amount"]);

				$outstandingAmount = isset(
					$outstandingAmountData->out_standing_amount
				)
					? ($outstandingAmountData->out_standing_amount > 0
						? $outstandingAmountData->out_standing_amount
						: 0)
					: $loan->amount;
				$lastEmidate = isset($outstandingAmountData->emi_date)
					? date("d/m/Y", strtotime($outstandingAmountData->emi_date))
					: date("d/m/Y", strtotime($loan->approve_date));

				$closerAmount = calculateCloserAmount(
					$outstandingAmount,
					$lastEmidate,
					$loan->ROI,
					$loan['loanBranch']->state_id
				);


				// calculate closer amount end
				// dd($closerAmount);

				$this->cronService->inProgress();

				if ($loan->newLoanSSB && $amount > 0 || $loan->newLoanSSB && $closerAmount > 0) {

					$msg = $this->callCronController($loan, $sysDate, $closerAmount);
					Log::channel('ssbEcsCronLoan')->info('  amount >0   a/c no =' . $loan->account_number);
				} else {
					Log::channel('ssbEcsCronLoan')->info('no due amount   a/c no =' . $loan->account_number . ', date =' . $currentDate);
					// Change next emi due date
					$mlData = \App\Models\Memberloans::find($loan->id);
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
					// End update next emi due date

					Log::channel('ssbEcsCronLoan')->info('no due amount   a/c no =' . $loan->account_number . ', next emi due =' . $emiDueDate);

					// // Run cron or calculate intrest 
					// $stateId = $mlData['loanBranch']->state_id;
					// $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
					// $currentDate = date('Y-m-d', strtotime($currentDate));
					// $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $mlData['account_number'], 0]);
					// // Run cron or calculate intrest End

					$msg = "For $cronName Cron A/C {$loan->account_number} due amount is 0.";
				}

				$this->updatecron($msg);
				
			}
		}
		$ldate = Carbon::now()->format('Y-m-d H:i:s');
		$processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
		$sms_text = "Software Cron - MonthlyEcsTransactionLoan completed with status - success on $ldate Samraddh Bestwin";
		$templateId = 1207170141602955251;
		$contactNumber = $processedNumbers;
		$sendToMember = new Sms();
		$sendToMember->sendSms($contactNumber, $sms_text, $templateId);


	}
	public function updatecron($msg)
	{

		$this->cronService->upToDateProgress($msg);
		$this->cronService->completed();
		
	}
	public function callCronController($value, $systemDate, $closerAmount)
	{
		// p($value->toArray());
		// dd('hoooooo');
		$req = new Request();
		$req['penalty_amount'] = 0;
		$req['account_number'] = $value->account_number;
		$req['state_id'] = $value['loanBranch']->state_id;
		$req['approve_date'] = $value->approve_date;
		$req['closing_date'] = $value->closing_date;
		$req['emi_option'] = $value->emi_option;
		$req['application_date'] = date('Y-m-d', strtotime(convertdate($systemDate))); // system date
		$req['loan_emi_payment_mode'] = 0; // for ssb payment only
		$req['ssb_id'] = $value['newLoanSSB']['id']; // user ssb account id
		$req['due_amount'] = $value->due_amount; // deposite_amount for due amount
		$req['closerAmount'] = $closerAmount; // Closure Amount
		$req['loan_id'] = $value->id; // loan id
		$req['emi_amount'] = $value->emi_amount; // emi amount
		$req['branch'] = $value->branch_id; // loan branch id
		$req['company_id'] = $value->company_id; // loan branch id
		$req['created_date'] = date('Y-m-d'); // current date
		$req['closing_date'] = $value->closing_date; // current date
		$req['ssb_account'] = $value['newLoanSSB']['account_no']; // ssb account number
		$req['loan_associate_id'] = $req['associate_member_id'] = $value->associate_member_id;
		$req['created_at'] = date('Y-m-d', strtotime(convertdate($systemDate))); // system date
		$req['loan_associate_name'] = getMemberFullName($value->associate_member_id);
		$req['recovery_module'] = 1;
		$res = [];
		// dd($req->all());
		$response = \App\Http\Controllers\Admin\Cron\CronController::cronDepositeLoanEmi($req);
		array_push($res, $response['message']);
		return $res;
	}
}
