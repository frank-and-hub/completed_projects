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

class CommissionFuleCalculation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:fuleCalculation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'calculate fule amount.';

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
       
        $monthEnd=CommisionMonthEnd::where('month',$monthForLedger)->where('year',$yearForLedger)->where('fule_calculation',0)->orderBy('id','DESC')->first(); 
        try
        {
            if($monthEnd )
            {
                $logName = 'commission/fule_calculation-'.date('Y-m-d',strtotime(now())).'.log';
                    $this->cronService->startCron($this->signature,$logName);
                    Log::channel('fulecalculation')->info('Fule Calculation   Start'); 

                if($monthEnd->investment_commission==1 && $monthEnd->investment_collection==1 && $monthEnd->loan==1 && $monthEnd->sum==1)
                {
                    $leadgerMonth=$monthEnd->month;
                    $leadgerYear=$monthEnd->year; 
                    //  pd($leadgerMonth);                  


                    $getData= \App\Models\AssociateCommissionTotalMonthly::select(DB::raw('associate_commissions_total_monthly.member_id as member_id')) 
                    ->where('month',$leadgerMonth)
                    ->where('year',$leadgerYear) 
                    ->groupBy(DB::raw('associate_commissions_total_monthly.member_id'))    
                    ->get(); 

                    $totalAssociateCount=count($getData);
                    $finalCount=0;
                    if(count($getData) > 0)
                    {    
                        
                        \Log::channel('fulecalculation')->info('Calculation  >>> total associate count = '.$totalAssociateCount);  
                        $this->cronService->inProgress();   

                        foreach ($getData->chunk(100) as  $val) 
                        {   
                                    
                            foreach ($val as $v) 
                            {
                                

                                $getCollection=\App\Models\CommissionFuleCollection::where('month', $leadgerMonth)->where('year', $leadgerYear)->where('associate_id', '=', $v->member_id)->sum('total_amount');

                                $reCollection =round($getCollection);
                                $date=$leadgerYear.'-'.$leadgerMonth.'-1';
                                $start_date = date("Y-m-d", strtotime(convertDate($date)));
                                //echo $start_date;die;

                                if($reCollection >= 50000 && $reCollection <= 99999)
                                {
                                    $fule=1000; 
                                    \Log::channel('fulecalculation')->info("Associate's collection amount should be greater than or equal to 50,000 and less than or equal to 99,999. >>> assocaite id  = ".$v->member_id);
                                }
                                else if($reCollection > 99999)
                                {
                                    $fule=2000;
                                    \Log::channel('fulecalculation')->info("Associate's collection amount should exceed 99,999. >>> assocaite id  = ".$v->member_id);
                                }
                                else
                                {
                                    $fule=0;
                                    \Log::channel('fulecalculation')->info("Associate's collection amount should be less than 50,000.  >>> assocaite id  = ".$v->member_id);
                                } 
                                if(getAssociateOneYear($start_date,$v->member_id)==1)
                                {
                                    $fule = $fule;
                                }
                                else
                                {
                                    $fule = 0;
                                    \Log::channel('fulecalculation')->info('One year has passed for the associate.  >>> assocaite id  = '.$v->member_id);
                                }

                                $assocaiteCompany = \App\Models\CompanyAssociate::where('status','1')->first();

                                $commission=\App\Models\AssociateCommissionTotalMonthly::where('member_id',$v->member_id)
                                ->where('month',$leadgerMonth)
                                ->where('year',$leadgerYear)
                                ->where('company_id',$assocaiteCompany->company_id)
                                ->first(); 
                                if($commission)
                                {
                                    $comDataUpdate = \App\Models\AssociateCommissionTotalMonthly::where('id',$commission->id)->update([ 'collection_amount' => $reCollection,'fule_amount' => $fule ]);
                                }
                                else{

                                    if($fule>0)
                                    {
                                        $leaser1['member_id'] = $v->member_id;
                                        $leaser1['total_amount'] = 0.00; 
                                        $leaser1['commission_amount'] =0.00;              
                                        $leaser1['month'] = $leadgerMonth; 
                                        $leaser1['year'] = $leadgerYear; 
                                        $leaser1['status'] = 2; 
                                        $leaser1['company_id'] =$assocaiteCompany->company_id; 
                                        $leaser1['collection_amount'] = $reCollection; 
                                        $leaser1['fule_amount'] = $fule; 
                                        $leaserCreate1 = \App\Models\AssociateCommissionTotalMonthly::create($leaser1);
                                    }
                                        
                                } 
                                        
                                        
                                $finalCount++;

                            }
                            
                        }
                        \Log::channel('fulecalculation')->info('Calculation  end  >>> final count = '.$finalCount);
                        $end = CommisionMonthEnd::where('month',$leadgerMonth)->where('year',$leadgerYear)->update([ 'fule_calculation' => 1]); 
                        $this->cronService->completed();
                            $ldate = Carbon::now()->format('Y-m-d H:i:s');
                            $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                            $sms_text = "Software Cron - FuleCalculation completed with status - success on $ldate Samraddh Bestwin";
                            $templateId = 1207170141602955251;
                            $contactNumber = $processedNumbers;
                            $sendToMember = new Sms();
                            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
                        // Artisan::call('commission:associateExceptionCron');
                    }
                }
                else
                {
                    \Log::channel('fulecalculation')->info('The commission cron jobs did not execute correctly. Please perform a thorough verification.');
                }

                
            
            

            }
            DB::commit();
        }
        catch(\Exception $e)
        {
            /** error log in cron is use to store error message on cron_log table to get why the cron has not complete the task. with file name , line number and message. */
                           $ldate = Carbon::now()->format('Y-m-d H:i:s');
                            $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                            $sms_text = "Software Cron - FuleCalculation completed with status - error on $ldate Samraddh Bestwin";
                            $templateId = 1207170141602955251;
                            $contactNumber = $processedNumbers;
                            $sendToMember = new Sms();
                            $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            $this->cronService->errorLogs(2, $e->getMessage() . ' -Line No ' . $e->getLine().'-File Name - '.$e->getFile().'',$this->signature);
        }
    }
}
