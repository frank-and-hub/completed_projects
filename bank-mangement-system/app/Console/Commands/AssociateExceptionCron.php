<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;
use App\Models\CommisionMonthEnd;
use App\Models\CommissionLeaserMonthly;
use App\Models\Memberinvestments;
use App\Models\Member;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\AssociateException;
use App\Models\AssociateExceptionLog;
use App\Services\Sms;
use Carbon\Carbon;

class AssociateExceptionCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:associateExceptionCron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the quarterly review of the associate exception listing';

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
        die("on 03-05-2024 by alpana");
        try
        {   
            $getMonth = date('m');
            $getYear = date('Y');

            // Calculate the quarter
            $quarter = ceil($getMonth / 3);

            // Define the start and end dates based on the quarter
            switch ($quarter) {
                case 1:
                    $fromDate = ($getYear - 1) . '-10-01';
                    $toDate = ($getYear - 1) . '-12-31';
                    break;
                case 2:
                    $fromDate = $getYear . '-01-01';
                    $toDate = $getYear . '-03-31';
                    break;
                case 3:
                    $fromDate = $getYear . '-04-01';
                    $toDate = $getYear . '-06-30';
                    break;
                case 4:
                    $fromDate = $getYear . '-07-01';
                    $toDate = $getYear . '-09-30';
                    break;
                default:
                    break;
            }
            // Now, $fromDate and $toDate contain the desired values based on the quarter

            if($getMonth==01 || $getMonth==04 || $getMonth==07 || $getMonth==10 )
            {
                $logName = 'commission/associate_exception-'.date('Y-m-d H:i:s',strtotime(now())).'.log';
                
                $this->cronService->startCron($this->signature,$logName); 
                Log::channel('associateexception')->info('Assocaite Exception Start');

                $results = Member::select([
                    'id',
                    'associate_no',
                    DB::raw("TRIM(CONCAT(first_name, ' ', COALESCE(last_name, ''))) as full_name"),
                    DB::raw('IFNULL(dncc, 0) as dncc'),
                    DB::raw('IFNULL(mncc, 0) as mncc')
                ])
                ->leftJoin(DB::raw('(
                    SELECT
                        mi.associate_id,
                        SUM(CASE WHEN p.plan_category_code = "D" THEN mi.deposite_amount ELSE 0 END) AS dncc,
                        SUM(CASE WHEN p.plan_category_code = "M" THEN mi.deposite_amount ELSE 0 END) AS mncc
                    FROM
                        member_investments AS mi
                    JOIN
                        plans AS p ON p.id = mi.plan_id
                    WHERE
                        p.plan_category_code IN ("D", "M")
                        AND mi.is_deleted = 0
                        AND DATE(mi.created_at) BETWEEN "'.$fromDate.'" AND "'.$toDate.'"
                    GROUP BY mi.associate_id
                ) z'), 'z.associate_id', '=', 'members.id')
                ->where('is_associate', 1)
                ->where('members.id', '!=',1)
                ->whereDate('members.associate_join_date', '<=',$toDate)
                ->get();
            
                
                
                // Memberinvestments::join('plans as p', 'p.id', '=', 'member_investments.plan_id')
                // ->join('members as m', 'm.id', '=', 'member_investments.associate_id')
                // ->select(
                //     'm.associate_no',
                //     'member_investments.associate_id',
                //     DB::raw('SUM(CASE WHEN p.plan_category_code = "D" THEN member_investments.deposite_amount ELSE 0 END) AS dncc'),
                //     DB::raw('SUM(CASE WHEN p.plan_category_code = "M" THEN member_investments.deposite_amount ELSE 0 END) AS mncc')
                // ) 
                // ->whereIn('p.plan_category_code', ['D', 'M'])
                // ->where('member_investments.is_deleted', 0)
                // ->whereBetween('member_investments.created_at', [$fromDate, $toDate])
                // ->groupBy('m.associate_no', 'member_investments.associate_id')
                // ->get();
    
               // pd(count($results) );
                
                        if(count($results) > 0)
                        {   
                            Log::channel('associateexception')->info('Total   count = '.count($results));  
                            
                            $a=0;
                            $this->cronService->inProgress();
                            foreach ($results->chunk(10) as  $val) 
                            {   
                                            
                                foreach ($val as $v) 
                                {
                                   // pd($v);

                                    $dncc=$v->dncc;
                                    $mncc=$v->mncc;
                                    if($dncc >=500   || $mncc>=3000)
                                    {
                                        $msg = "Associate's commission & fuel released by Cron Job, [Achieved their business target]." ; 
                                        $exist = AssociateException::where('associate_id', $v->id)->first();
                                        if($exist)
                                        {
                                            AssociateException::where('id', $exist->id)->update([ 'fuel_status' =>0,'commission_status'=>0]);

                                            $data1['created_by'] = 0;
                                            $data1['description'] =  $msg;
                                            $data1['associate_exception_id'] =  $exist->id;
                                            $transcation1 = AssociateExceptionLog::create($data1); 
                                        }
                                       
                                       Log::channel('associateexception')->info('Associate - '.$v->associate_no.' [ID = '.$v->id.'] has successfully achieved their business target. DNCC = '.$dncc.' , MNCC = '.$mncc);  
                                    }
                                    else
                                    {
                                       //exception    
                                        $dataexp['fuel_status'] =  1;
                                        $dataexp['commission_status'] =  1;
                                        $msg = "Associate's commission & fuel stopped by Cron Job, [Not met their business target]" ; 
                                        $dataexp['is_cron'] = 1; 
                                        $dataexp['reason'] =  'The business target has not been achieved.';
                                        $dataexp['associate_id'] = $v->id;
                                        //  pd($dataexp);
                                        $exist = AssociateException::where('associate_id', $v->id)->first();
                                        if (!$exist) {
                                            $transcation = AssociateException::create($dataexp);
                                            
                                            $data1['created_by'] = 0;
                                            $data1['description'] =  $msg;
                                            $data1['associate_exception_id'] =  $transcation->id;
                                            $transcation1 = AssociateExceptionLog::create($data1);                                             
                                            
                                        }
                                        else
                                        {
                                            AssociateException::where('id', $exist->id)->update([ 'fuel_status' =>1,'commission_status'=>1]);

                                            $data1['created_by'] = 0;
                                            $data1['description'] =  $msg;
                                            $data1['associate_exception_id'] =  $exist->id;
                                            $transcation1 = AssociateExceptionLog::create($data1);
                                        }
                                        Log::channel('associateexception')->info('Exception >> Associate - '.$v->associate_no.' [ID = '.$v->id.'] has not met their business target. DNCC = '.$dncc.' , MNCC = '.$mncc); 
                                        $a++;
                                    }
                                }
                            }
                            Log::channel('associateexception')->info('Total Exception  count = '.$a); 
                            $this->cronService->completed();                            
                            
                            $end = CommisionMonthEnd::where('month',date("m", strtotime($toDate)))->where('year',date("Y", strtotime($toDate)))->update([ 'exception' => 1]); 
                                $ldate = Carbon::now()->format('Y-m-d H:i:s');
                                $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                                $sms_text = "Software Cron - AssociateExceptionCron completed with status - success on $ldate Samraddh Bestwin";
                                $templateId = 1207170141602955251;
                                $contactNumber = $processedNumbers;
                                $sendToMember = new Sms();
                                $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
                        }

                Log::channel('associateexception')->info('Assocaite Exception Start');      
            }  
            DB::commit();
        }
        catch(\Exception $e)
        {
            /** error log in cron is use to store error message on cron_log table to get why the cron has not complete the task. with file name , line number and message. */
                            $ldate = Carbon::now()->format('Y-m-d H:i:s');
                            $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                            $sms_text = "Software Cron - AssociateExceptionCron completed with status - error on $ldate Samraddh Bestwin";
                            $templateId = 1207170141602955251;
                            $contactNumber = $processedNumbers;
                            $sendToMember = new Sms();
                            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            $this->cronService->errorLogs(2, $e->getMessage() . ' -Line No ' . $e->getLine().'-File Name - '.$e->getFile().'',$this->signature);
        }

    }
}
