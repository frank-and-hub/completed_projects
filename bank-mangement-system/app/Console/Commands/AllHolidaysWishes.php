<?php

namespace App\Console\Commands;
use App\Models\HolidayNotificationSetting;
use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Employee;
use App\Models\HolidaySettingLogs;

use App\Services\Sms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;
use DB;

class AllHolidaysWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:allHolidays-wishes:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This cron will send all holidays wises to users and employees';

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
        $cronChannel = 'Holiday Wishes';

        Log::channel('Holiday Wishes')->info('start time - ' . Carbon::now());
        $ldate = Carbon::now()->format('d-m-Y H:i:s');
        $logName = 'HolidayWishes/Wishes-' . date('Y-m-d', strtotime(now())) . '.log';
        $this->cronService->startCron($this->signature, $logName);
        $today = Carbon::now()->format('Y-m-d');


        $crons = HolidayNotificationSetting::where('cron_date',$today)->where('status',1)->get();
        try {
            $this->info('Sending Holiday Wishes ...');
          
            foreach($crons as $value){
                $this->sendHolidayMessage($value->templateId, $value->message);

           
            $this->info('Holidays wishes sent successfully!');

            $templateId = 1207170141602955251;
            $contactNumber = [8619793301, 9694690124, 8946984812]; 
            

        
            if(isset($crons) && $value->cron_date == $today && $value->status == 1){
            $sms_text = "Software Cron - $value->title completed with status - success on $ldate Samraddh Bestwin";

      
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);

          
        
        $title = $value->title .' cron ended sucessfully !!';
        $description = $value->title .' cron completed sucessfully on ' . date('d/m/Y');
        $user_id = 0;
        $holiday_table_id = $value->id;
        
        $saveLogs = new HolidaySettingLogs();
        $saveLogs->title = $title;
        $saveLogs->description = $description;
        $saveLogs->user_id = $user_id;
        $saveLogs->holiday_id = $holiday_table_id;
        $saveLogs->save();


         
        }
        $crons = HolidayNotificationSetting::where('cron_date', $today)
       
        ->update(['status' => 0]);
        }

            $this->cronService->completed();
            Log::channel('newyear')->info('end time - ' . Carbon::now());
        } catch (\Exception $e) {
            
            $this->error('An error occurred: ' . $e->getMessage());
            $this->info($e->getMessage());
            $this->info($e->getLine());
            $this->sendErrorMessage();
            Log::channel('Holiday')->info('error-resion - ' . $e->getMessage());
            $this->cronService->errorLogs(4, $e->getMessage() . ' - Line No ' . $e->getLine() . ' - File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
            return 1;
        }

        return 0; // Return success code
    }

    private function sendErrorMessage()
    {
        $ldate = Carbon::now()->format('Y-m-d H:i:s');
        $processedNumbers = [8619793301, 9694690124,8946984812];
        // $sms_text = "Software Cron - Holiday-wishes completed with status - error on $ldate  Samraddh Bestwin";
        $templateId = 1207170141602955251;
        $contactNumber = $processedNumbers;
      
        $today = Carbon::now()->format('Y-m-d');
 

        $crons = HolidayNotificationSetting::where('cron_date',$today)->first();
        if(isset($crons) && $crons->cron_date == $today && $crons->status == 1){
            $sms_text = "Software Cron - $crons->title completed with status - error on $ldate  Samraddh Bestwin";

        $sendToMember = new Sms();
        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
        
    }
    }

    private function sendHolidayMessage($templateID, $message)
    {
        $processedNumbers = [];
        $today = Carbon::now()->format('Y-m-d');
        $crons = HolidayNotificationSetting::where('cron_date', $today)->where('status',1)->get();
        
        
            $countsend = 0;
            $countsendsame = 0;
    
            $results = DB::table(DB::raw('(
                SELECT TRIM(CONCAT(a.first_name, " ", COALESCE(a.last_name, ""))) AS name, a.mobile_no
                FROM members a 
                WHERE a.status = 1 
                UNION ALL
                SELECT e.employee_name AS name, e.mobile_no
                FROM employees e 
                WHERE e.status = 1  
            ) t'))
            ->select('t.name', 't.mobile_no')
            ->distinct()
            
            ->get();

            // $results = DB::table(DB::raw('(
            //     SELECT a.id, TRIM(CONCAT(a.first_name, " ", COALESCE(a.last_name, ""))) AS name, a.mobile_no
            //     FROM members a 
            //     WHERE a.status = 1 
            //     UNION ALL
            //     SELECT e.id, e.employee_name AS name, e.mobile_no
            //     FROM employees e 
            //     WHERE e.status = 1  
            // ) t'))
            // ->select('t.id', 't.name', 't.mobile_no')
            // ->whereIn('id',[203,591])
            
            // ->distinct()
            // ->take(5) 
            // ->get();
        
            
          
        
            foreach ($results as $item) {
                $mobileNumber = $item->mobile_no;
                
                if (in_array($mobileNumber, $processedNumbers)) {
                    $countsendsame++;
                    Log::channel('Holiday')->info('end time - ' . Carbon::now() . ', sms already sent - ' . $mobileNumber );
                    continue; // Skip this number
                }
                
                $processedNumbers[] = $mobileNumber;
                
                $templateid = $templateID;
                $sms_text = str_replace('{#var#}', $item->name, $message);
                $this->cronService->inProgress();
                $contactNumber = [$mobileNumber];
              

          
                
                $sendToMember = new Sms();
                $sendToMember->sendSms($contactNumber, $sms_text, $templateid);
               
                $countsend++;
    
                Log::channel('Holiday')->info('end time - ' . Carbon::now() . ', reason - sms sent on ' . $mobileNumber . ' number on - ' . $item->name);
           
                
            }
        
    
        Log::channel('Holiday')->info('countsend - ' .$countsend . ', countsendsame -  ' . $countsendsame );
    }
    
    
    

}
