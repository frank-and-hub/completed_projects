<?php

namespace App\Console\Commands;


use App\Models\HolidayNotificationSetting;
use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Employee;
use App\Services\Sms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;
use DB;

class HolisWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:Holi-wishes:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $cronChannel = 'Holi Wishes';

        Log::channel('Holi Wishes')->info('start time - ' . Carbon::now());
        $ldate = Carbon::now()->format('d-m-Y H:i:s');
        $logName = 'Holi/Wishes-' . date('Y-m-d', strtotime(now())) . '.log';
        $this->cronService->startCron($this->signature, $logName);
        $today = Carbon::now()->format('Y-m-d');


        
        try {
            $this->info('Sending Holi Wishes ...');
            // $this->sendWishesToGroup(Member::class, 'member');
            // $this->sendWishesToGroup(Employee::class, 'employee');
            $this->sendHolidayMessage();
            $this->info('Holi wishes sent successfully!');

            $templateId = 1207170141602955251;
            $contactNumber = [8619793301, 9694690124,8946984812,9415085891]; // make changes by sourab on 22-01-24 for move glogabl sms numbers (check env file)
            $smsStatus = smsStatus();

        
           
                if('2024-03-25' == $today){

            $sms_text = "Software Cron - Holi Wishes completed with status - success on $ldate Samraddh Bestwin";

            
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            
        }

            $this->cronService->completed();
            Log::channel('Holi')->info('end time - ' . Carbon::now());
        } catch (\Exception $e) {
            
            $this->error('An error occurred: ' . $e->getMessage());
            $this->info($e->getMessage());
            $this->info($e->getLine());
            $this->sendErrorMessage();
            Log::channel('Holi')->info('error-resion - ' . $e->getMessage());
            $this->cronService->errorLogs(4, $e->getMessage() . ' - Line No ' . $e->getLine() . ' - File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
            return 1;
        }

        return 0; // Return success code
    }

    private function sendErrorMessage()
    {
        $ldate = Carbon::now()->format('Y-m-d H:i:s');
        $processedNumbers = [8619793301, 9694690124,8946984812,9415085891];
        

        // $sms_text = "Software Cron - Holiday-wishes completed with status - error on $ldate  Samraddh Bestwin";
        $templateId = 1207170141602955251;
        $contactNumber = $processedNumbers;
        $smsStatus = smsStatus();
        $today = Carbon::now()->format('Y-m-d');


   
      
            if('2024-03-25' == $today){

            $sms_text = "Software Cron - Holi Cron completed with status - error on $ldate  Samraddh Bestwin";

   
        $sendToMember = new Sms();
        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
        
    }
    }
    private function sendHolidayMessage()
    {
        $processedNumbers = [];
  

        $results = DB::table(DB::raw('(SELECT TRIM(CONCAT(a.first_name, " ", COALESCE(a.last_name, ""))) AS name, a.mobile_no FROM members a WHERE status = 1 
            UNION ALL
            SELECT e.employee_name AS name, e.mobile_no FROM employees e WHERE status = 1  ) t'))
        ->select('t.name', 't.mobile_no')
        ->distinct()
        ->get();

      
       
        // $results = DB::table(DB::raw('(
        //     SELECT 
        //         TRIM(CONCAT(a.first_name, " ", COALESCE(a.last_name, ""))) AS name, 
        //         a.mobile_no,
        //         a.id AS member_id,
        //         NULL AS employee_id
        //     FROM 
        //         members a 
        //     WHERE 
        //         a.status = 1 
        //     UNION ALL
        //     SELECT 
        //         e.employee_name AS name, 
        //         e.mobile_no,
        //         NULL AS member_id,
        //         e.id AS employee_id
        //     FROM 
        //         employees e 
        //     WHERE 
        //         e.status = 1  
        // ) t'))
        // ->select('t.name', 't.mobile_no', 't.member_id', 't.employee_id')
        // ->where(function ($query) {
        //     $query->whereIn('member_id', [42570, 42566, 42563])
        //           ->orWhereIn('employee_id', [518, 516]);
        // })
        // ->distinct()
        // ->take(8) // Limit to first 3 records
        // ->get();
        
        
        


        
       

        $today = Carbon::now()->format('Y-m-d');

  
       


        $countsend = 0;
        $countsendsame = 0;
        
        foreach ($results as $item) {


            $mobileNumber = $item->mobile_no;
        
            if (in_array($mobileNumber, $processedNumbers)) {
                $countsendsame ++;
                Log::channel('Holi')->info('end time - ' . Carbon::now() . ', sms alredy sent  -  ' . $mobileNumber );
                continue; // Skip this number
            }
            $processedNumbers[] = $mobileNumber;
       
                if('2024-03-25' == $today){
              
       

               


                    $sms_text = "Dear " . $item->name . ", May God fulfil your home with happiness, health and prosperity. Happy Holi. Samraddh Bestwin Microfinance";
                   
                    
                    $templateid = '1207170359166737922';
                    $templateId = $templateid;
          
                

            }
           

            $this->cronService->inProgress();
            $contactNumber = [$mobileNumber];
            
            
     
         
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);

 
        
            $countsend++;
            Log::channel('Holi')->info('end time - ' . Carbon::now() . ', reason - sms sent on ' . $mobileNumber . ' number on  - ' . $item->name);
         
 
        }
        Log::channel('Holi')->info('countsend - ' .$countsend . ', countsendsame -  ' . $countsendsame );
    }
}
