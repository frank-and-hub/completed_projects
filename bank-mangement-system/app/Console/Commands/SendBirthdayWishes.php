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
class SendBirthdayWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:birthday-wishes:generate';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Birthday  wishes through SMS to customers and employees';
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

  

        if (Carbon::now()->format('Y-m-d') < '2023-12-31') {
            $this->info('The cron job should only run on January 1st. Exiting...');
            return 0; // Return success code
        }
        $cronChannel = 'birthday';
        Log::channel('birthday')->info('start time - ' . Carbon::now());
        $logName = 'birthday/birthday-' . date('Y-m-d', strtotime(now())) . '.log';
        $this->cronService->startCron($this->signature, $logName);
        $ldate = Carbon::now()->format('d-m-Y H:i:s');
        $birthdayDate = Carbon::now()->format('Y-m-d');
        try {
            $this->info('Sending Birthday wishes...');
            $this->sendWishesToGroup();
            
            $this->info('Birthday wishes sent successfully!');
            $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
            $sms_text = "Software Cron - Birthday wishes completed with status - success on $ldate Samraddh Bestwin";
            $templateId = 1207170141602955251;
            $contactNumber = $processedNumbers;
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            Log::channel('birthday')->info('end time - ' . Carbon::now());
            $this->cronService->completed();
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            $this->info($e->getMessage());
            Log::channel('birthday')->info('error-resion - ' . $e->getMessage());
            $this->sendErrorMessage();
            $this->cronService->errorLogs(4, $e->getMessage() . ' - Line No ' . $e->getLine() . ' - File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
            return 1;
        }
        return 0; // Return success code
    }
    private function sendErrorMessage()
    {
        $ldate = Carbon::now()->format('Y-m-d H:i:s');
        $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
        $sms_text = "Software Cron - Birthday completed with status - error on $ldate Samraddh Bestwin";
        $templateId = 1207170141602955251;
        $contactNumber = $processedNumbers;
        $sendToMember = new Sms();
        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
    }
    private function sendWishesToGroup()
    {
        $birthdayDate = Carbon::now()->format('Y-m-d');
        $processedNumbers = [];
      
        // $results = DB::table(DB::raw('(SELECT TRIM(CONCAT(a.first_name, " ", COALESCE(a.last_name, ""))) AS name, a.mobile_no, a.dob FROM members a WHERE status = 1 and id in (14,15,16,17)
        // UNION ALL
        // SELECT e.employee_name AS name, e.mobile_no, e.dob FROM employees e WHERE status = 1 and id in (14) ) t'))
        // ->select('t.name', 't.mobile_no')
        // ->whereRaw("MONTH(t.dob) = MONTH('$birthdayDate')")
        // ->whereRaw("DAY(t.dob) = DAY('$birthdayDate')")
        // ->distinct()
        // ->get();
    


$results = DB::table(DB::raw('(SELECT TRIM(CONCAT(a.first_name, " ", COALESCE(a.last_name, ""))) AS name, a.mobile_no, a.dob FROM members a WHERE status = 1 
                UNION ALL
                SELECT e.employee_name AS name, e.mobile_no, e.dob FROM employees e WHERE status = 1  ) t'))
        ->select('t.name', 't.mobile_no')
        
        ->whereRaw("MONTH(t.dob) = MONTH('$birthdayDate')")
        ->whereRaw("DAY(t.dob) = DAY('$birthdayDate')")
        ->distinct()
        ->get();

        $countsendsame = 0;
        $countsend = 0;
     foreach ($results as $item) {
      
        $mobileNumber = $item->mobile_no;
    
       


       
        if (in_array($mobileNumber, $processedNumbers)) {
            $countsendsame ++;
            Log::channel('birthday')->info('end time - ' . Carbon::now() . ', sms alredy sent  -  ' . $mobileNumber );
            continue; // Skip this number
        }
        $processedNumbers[] = $mobileNumber;

        $this->cronService->inProgress();
                    Log::channel('birthday')->info('end time - ' . Carbon::now() . ', resion - sms sent on ' . $mobileNumber . ' number on '  . ' - ' . $item->name);
                    $sms_text = "Dear " . $item->name . ", wishing you a very Happy Birthday. Samraddh Bestwin Microfinance";
                    $templateId = 1207170323858763687;
                    $contactNumber = [$mobileNumber];
                    $sendToMember = new Sms();
                    $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
                    $this->counter++;
    }




   
        
    }
}