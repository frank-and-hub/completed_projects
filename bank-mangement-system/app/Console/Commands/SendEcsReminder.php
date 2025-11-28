<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;
use Illuminate\Support\Facades\Date;
use App\Models\{Loans, Memberloans, Grouploans};
use App\Services\Sms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;

class SendEcsReminder extends Command
{
    protected $signature = 'command:SendEcsReminder'; // Command signature

    protected $description = 'Send Ecs Reminder'; // Command description

    public function __construct(CronStoreInfo $CronStoreInfo)
    {
        parent::__construct();
        $this->cronService = $CronStoreInfo; // Injecting CronStoreInfo service
    }
    public function handle()
    {
        $cronChannel = 'SendEcsReminder'; // Log channel name
        Log::channel('SendEcsReminder')->info('start time - ' . Carbon::now()); // Logging start time
        $logName = 'sendEcsSms/SendEcsReminder-' . date('Y-m-d', strtotime(now())) . '.log'; // Generating log file name

        // Starting cron service and logging operation
        $this->cronService->startCron($this->signature, $logName);
        try {
            // Calculating date after 2 days
            $currentDate = Date::now();
            $dateAfterDays = $currentDate->addDays(2);
            $dateFormatted = $dateAfterDays->format('Y-m-d');

            // Fetching member loans due in 2 days
            $memberloans = Memberloans::whereIn('ecs_type', [1, 2]) // Selects ecs_type 1 and 2
                ->where('emi_due_date', $dateFormatted)
                ->whereHas('loan', function ($query) {
                    $query->where('loan_type', 'L');
                })
                ->with(['member:id,mobile_no'])
                ->get();

            // ->dd();


            // Fetching group loans due in 2 days
            $grouploans = Grouploans::whereIn('ecs_type', [1, 2])
                ->where('emi_due_date', '=', $dateFormatted)
                ->whereHas('loan', function ($query) {
                    $query->where('loan_type', 'G');
                })
                ->with(['member:id,mobile_no'])
                ->get();
            if (!empty($memberloans)) {
                foreach ($memberloans as $memberloans) {

                    p($memberloans->account_number);
                    p($memberloans->ecs_type);
                    // Sending SMS reminder to member
                    $ldate = Carbon::now()->format('d-m-Y H:i:s');
                    $ecsRreminderDate = Carbon::now()->format('Y-m-d');
                    $this->info('Sending ECS reminder...');
                    $this->info('ECS reminder sent successfully!');
                    $d = date('d/m/Y', strtotime(convertdate($memberloans->emi_due_date)));
                    $processedNumbers = [$memberloans->member->mobile_no];
                    if ($memberloans->ecs_type == 1) {
                        $sms_text = "Loan EMI of Rs. $memberloans->emi_amount for a/c $memberloans->account_number with ECS is due on $d. Please ensure sufficient balance in Bank A/c. Samraddh Bestwin Microfinance";
                        $templateId = 1207170557804898764;
                    }
                    if ($memberloans->ecs_type == 2) {
                        $sms_text = "Loan EMI of Rs. $memberloans->emi_amount for a/c $memberloans->account_number with ECS is due on $d. Please ensure sufficient balance in SSB A/c. Samraddh Bestwin Microfinanc ";
                        $templateId = 1207171221423691698;
                    }

                    $contactNumber = $processedNumbers;

                    // Logging SMS sending operation
                    Log::channel('SendEcsReminder')->info('loan a/c - ' . $memberloans->account_number . ' - Loan EMI of Rs. ' . $memberloans->emi_amount);
                    // $smsStatus = smsStatus();
                    $smsStatus = 1;

                    if ($smsStatus === 1) {
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
                    }
                }
            }
            $this->cronService->inProgress();
            if (!empty($grouploans)) {
                foreach ($grouploans as $grouploans) {

                    p($grouploans->account_number);
                    p($grouploans->ecs_type);
                    // Sending SMS reminder to group
                    $ldate = Carbon::now()->format('d-m-Y H:i:s');
                    $ecsRreminderDate = Carbon::now()->format('Y-m-d');
                    $this->info('Sending ECS reminder...');
                    $this->info('ECS reminder sent successfully!');
                    $processedNumbers = [$grouploans->member->mobile_no];
                    $d = date('d/m/Y', strtotime(convertdate($grouploans->emi_due_date)));
                    if ($grouploans->ecs_type == 1) {
                        $sms_text = "Loan EMI of Rs. $grouploans->emi_amount for a/c $grouploans->account_number with ECS is due on $d. Please ensure sufficient balance in Bank A/c. Samraddh Bestwin Microfinance";
                        $templateId = 1207170557804898764;
                    }
                    if ($grouploans->ecs_type == 2) {
                        $sms_text = "Loan EMI of Rs. $grouploans->emi_amount for a/c $grouploans->account_number with ECS is due on $d. Please ensure sufficient balance in SSB A/c. Samraddh Bestwin Microfinanc ";
                        $templateId = 1207171221423691698;
                    }


                    // Logging SMS sending operation
                    Log::channel('SendEcsReminder')->info('group loan - a/c - ' . $grouploans->account_number . ' - Loan EMI of Rs. ' . $grouploans->emi_amount);
                    $contactNumber = $processedNumbers;
                    // $smsStatus = smsStatus();
                    $smsStatus =1;
                    if ($smsStatus === 1) {
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
                    }
                }
            }


            // Logging end time
            Log::channel('SendEcsReminder')->info('end time - ' . Carbon::now());
            // Marking cron as completed
            $this->cronService->completed();
        } catch (\Exception $e) {
            // Logging error if any exception occurs
            Log::channel('SendEcsReminder')->info('error-resion - ' . $e->getMessage());
            // Handling error and logging it
            $this->cronService->errorLogs(4, $e->getMessage() . ' - Line No ' . $e->getLine() . ' - File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
        } 
        finally {
            // Handling cron status after execution (success or failure)
            if (isset($e) && $e instanceof \Exception) {
                $this->cronService->sentCronSms(false);
            } else {
                $this->cronService->sentCronSms(true);
            }
        }
    }
}
