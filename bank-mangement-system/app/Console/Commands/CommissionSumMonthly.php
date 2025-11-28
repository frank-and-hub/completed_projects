<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;  
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\CronStoreInfo;
use Illuminate\Support\Facades\Artisan;
use App\Models\CommisionMonthEnd;
use App\Models\CommissionLeaserMonthly;
use Illuminate\Support\Facades\Log;
use App\Services\Sms;
use Carbon\Carbon;

class CommissionSumMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:summonthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'calculate sum of commission';

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
     * @return mixed
     */
    public function handle()
    {

       $lastLedger=CommissionLeaserMonthly::orderBy('id','DESC')->first();
       $month=$lastLedger->month;
       $year=$lastLedger->year;

       if($month==12)
       {
               $monthForLedger=1;
               $yearForLedger=$year+1;
       }
       else{
           $monthForLedger=$month+1;
           $yearForLedger=$year;
       }
      
       $monthEnd=CommisionMonthEnd::where('month',$monthForLedger)->where('year',$yearForLedger)->where('sum',0)->orderBy('id','DESC')->first(); 
       try
       {
            if($monthEnd )
            {
                $logName = 'commission/commission_sum_monthly-'.date('Y-m-d',strtotime(now())).'.log';
                $this->cronService->startCron($this->signature,$logName);
                Log::channel('commissionsum')->info('Commission sum  Start'); 

                if($monthEnd->investment_commission==1 && $monthEnd->investment_collection==1 && $monthEnd->loan==1 )
                {
                    $leadgerMonth=$monthEnd->month;
                    $leadgerYear=$monthEnd->year; 

                        $getData= \App\Models\AssociateMonthlyCommission::select(DB::raw('sum(associate_monthly_commission.commission_amount) as commission_amount'),DB::raw('sum(associate_monthly_commission.total_amount) as total_amount'), DB::raw('associate_monthly_commission.assocaite_id as member_id'),DB::raw('associate_monthly_commission.company_id as company_id')) 
                        ->where('commission_for_month',$leadgerMonth)
                        ->where('commission_for_year',$leadgerYear)
                        ->where('associate_monthly_commission.assocaite_id','!=',1)
                        ->where('associate_monthly_commission.is_distribute',0)
                        ->groupBy(DB::raw('associate_monthly_commission.assocaite_id,associate_monthly_commission.company_id'))    
                        ->get(); 
                        if(count($getData) > 0)
                        {   
                            Log::channel('commissionsum')->info('Commission sum  count = '.count($getData));  
                            
                            $a=0;
                            $this->cronService->inProgress();
                            foreach ($getData->chunk(10) as  $val) 
                            {   
                                            
                                foreach ($val as $v) 
                                {
                                    

                                    $commission=\App\Models\AssociateMonthlyCommission::where('assocaite_id',$v->member_id)->where('commission_for_month',$leadgerMonth)
                                    ->where('commission_for_year',$leadgerYear)
                                    ->where('company_id',$v->company_id)
                                    ->pluck('id')->toArray(); 
                                    
                                        $a=implode( ',',$commission); 
                                            
                                            $leaser1['member_id'] = $v->member_id;
                                            $leaser1['total_amount'] = $v->total_amount; 
                                            $leaser1['commission_amount'] = $v->commission_amount;              
                                            $leaser1['month'] = $leadgerMonth; 
                                            $leaser1['year'] = $leadgerYear; 
                                            $leaser1['total_row'] = count($commission); 
                                            $leaser1['commission_id'] = $a;
                                            $leaser1['status'] = 2; 
                                            $leaser1['company_id'] = $v->company_id; 
                                            $leaserCreate1 = \App\Models\AssociateCommissionTotalMonthly::create($leaser1);
                                            $a++;

                                }
                                \Log::channel('commissionsum')->info('sum -- Generate end');
                            }
                        }

                        Log::channel('commissionsum')->info('completed >>>  count  = '.$a);
                        $end = CommisionMonthEnd::where('month',$leadgerMonth)->where('year',$leadgerYear)->update([ 'sum' => 1,'commission_process' =>1]); 
                        $this->cronService->completed();

                            $ldate = Carbon::now()->format('Y-m-d H:i:s');
                            $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                            $sms_text = "Software Cron - CommissionSum completed with status - success on $ldate Samraddh Bestwin";
                            $templateId = 1207170141602955251;
                            $contactNumber = $processedNumbers;
                            $sendToMember = new Sms();
                            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);

                        Artisan::call('commission:fuleCalculation');

                }
                else
                {
                    \Log::channel('commissionsum')->info('The commission cron jobs did not execute correctly. Please perform a thorough verification.');
                }
            }
            DB::commit();
        }
        catch(\Exception $e)
        {
            /** error log in cron is use to store error message on cron_log table to get why the cron has not complete the task. with file name , line number and message. */
                            $ldate = Carbon::now()->format('Y-m-d H:i:s');
                            $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                            $sms_text = "Software Cron - CommissionSum completed with status - error on $ldate Samraddh Bestwin";
                            $templateId = 1207170141602955251;
                            $contactNumber = $processedNumbers;
                            $sendToMember = new Sms();
                            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            $this->cronService->errorLogs(2, $e->getMessage() . ' -Line No ' . $e->getLine().'-File Name - '.$e->getFile().'',$this->signature);
        }

      

    }
}
