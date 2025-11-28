<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Employee;
use App\Services\Sms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;
use DB;

class SendNewYearWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:new-year-wishes:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send New Year wishes through SMS to customers and employees';

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
    private $counter = 0;

    public function handle()
    {
        $cronChannel = 'newyear';

        Log::channel('newyear')->info('start time - ' . Carbon::now());
        $ldate = Carbon::now()->format('d-m-Y H:i:s');
        $logName = 'newyear/newyear-' . date('Y-m-d', strtotime(now())) . '.log';
        $this->cronService->startCron($this->signature, $logName);

        try {
            $this->info('Sending New Year wishes...');
            // $this->sendWishesToGroup(Member::class, 'member');
            // $this->sendWishesToGroup(Employee::class, 'employee');
            $this->sendWishesToGroup();
            $this->info('New Year wishes sent successfully!');

            $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
            $sms_text = "Software Cron - new-year-wishes completed with status - success on $ldate Samraddh Bestwin";
            $templateId = 1207170141602955251;
            $contactNumber = $processedNumbers;
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);

            $this->cronService->completed();
            Log::channel('newyear')->info('end time - ' . Carbon::now());
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            $this->info($e->getMessage());
            $this->info($e->getLine());
            $this->sendErrorMessage();
            Log::channel('newyear')->info('error-resion - ' . $e->getMessage());
            $this->cronService->errorLogs(4, $e->getMessage() . ' - Line No ' . $e->getLine() . ' - File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
            return 1;
        }

        return 0; // Return success code
    }

    private function sendErrorMessage()
    {
        $ldate = Carbon::now()->format('Y-m-d H:i:s');
        $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
        $sms_text = "Software Cron - new-year-wishes completed with status - error on $ldate  Samraddh Bestwin";
        $templateId = 1207170141602955251;
        $contactNumber = $processedNumbers;
        $sendToMember = new Sms();
        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
    }

    private function sendWishesToGroup()
    {
        $processedNumbers = [];
  

            $results = DB::table(DB::raw('(SELECT TRIM(CONCAT(a.first_name, " ", COALESCE(a.last_name, ""))) AS name, a.mobile_no FROM members a WHERE status = 1 
                UNION ALL
                SELECT e.employee_name AS name, e.mobile_no FROM employees e WHERE status = 1  ) t'))
        ->select('t.name', 't.mobile_no')
        
        ->distinct()
        

        ->get();


$countsend = 0;
$countsendsame = 0;
        // Assuming $result is a collection of rows retrieved from the query
        foreach ($results as $item) {


            $mobileNumber = $item->mobile_no;
        
        

           
            if (in_array($mobileNumber, $processedNumbers)) {
                $countsendsame ++;
                Log::channel('newyear')->info('end time - ' . Carbon::now() . ', sms alredy sent  -  ' . $mobileNumber );
                continue; // Skip this number
            }
            $processedNumbers[] = $mobileNumber;

            $sms_text = "Dear " . $item->name . ", Wishing you a Happy New Year. Samraddh Bestwin Microfinance";
            $this->cronService->inProgress();
            $templateId = 1207170359772043186;
            $contactNumber = [$mobileNumber];
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            $countsend++;
            Log::channel('newyear')->info('end time - ' . Carbon::now() . ', reason - sms sent on ' . $mobileNumber . ' number on  - ' . $item->name);
            // if ($this->counter >= 1) {
            //     break;
            // }
        }
        Log::channel('newyear')->info('countsend - ' .$countsend . ', countsendsame -  ' . $countsendsame );
    }
}
