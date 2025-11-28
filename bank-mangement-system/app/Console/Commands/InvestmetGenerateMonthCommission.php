<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Http\Controllers\Admin\CommanCommissionController;
use DB;
use App\Models\Daybook;
use App\Models\CommisionMonthEnd;
use App\Models\CommissionLeaserMonthly;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;
use Illuminate\Support\Facades\Artisan;
use App\Services\Sms;
use Carbon\Carbon;

class InvestmetGenerateMonthCommission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:investmentgeneratemonthlycommission';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Commission Monthly Invetment ';
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
       
        
       // $getData= DB::select('call getLoanAllDataForCommission(?,?)',['9','2022']);
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
       
       $monthEnd=CommisionMonthEnd::where('month',$monthForLedger)->where('year',$yearForLedger)->where('investment_commission',0)->orderBy('id','DESC')->first();
      // echo $monthEnd->month;die;
      

        try{ 

            if($monthEnd )
            {
                //pd($monthEnd);
                $leadgerMonth=$monthEnd->month;
                $leadgerYear=$monthEnd->year; 
                $logName = 'commission/commission_investment_monthly-'.date('Y-m-d',strtotime(now())).'.log';
                    $this->cronService->startCron($this->signature,$logName);
                    Log::channel('commissioninvestment')->info('Commission  Start');         

                

                $query = DB::select('
                    SELECT
                        SUM(day_books.deposit) AS total_amount,
                        day_books.account_no ,
                        day_books.company_id, 
                        member_investments.id AS investment_id,
                        member_investments.plan_id,
                        member_investments.deposite_amount,
                        member_investments.branch_id,
                        member_investments.maturity_date,
                        member_investments.tenure,
                        member_investments.is_mature,
                        member_investments.created_at AS created_at_investment,
                        member_investments.associate_id AS investment_assocaite_id,
                        plans.plan_category_code AS plan_category_code
                    FROM
                        day_books
                    JOIN
                        member_investments
                    ON
                        member_investments.account_number  = day_books.account_no
                    JOIN
                        plans
                    ON
                        plans.id = member_investments.plan_id
                    WHERE
                        plans.plan_category_code != "S"
                        AND day_books.associate_id != 1
                        AND day_books.is_deleted = 0
                        AND MONTH(day_books.created_at) = ' . $leadgerMonth . '
                        AND YEAR(day_books.created_at) = ' . $leadgerYear . '
                        AND DATE(day_books.created_at) <= member_investments.maturity_date
                        AND day_books.transaction_type IN (2, 4)
                    GROUP BY
                        day_books.account_no, day_books.company_id
                '); 

                $getData = $query; 
                
                
                if(count($getData) > 0)
                {    
                    Log::channel('commissioninvestment')->info('Commission count  '.count($getData));
                    $collection = collect($getData);
                    $lMcount=0;
                    $tcount=0;

                    foreach ($collection->chunk(20)  as  $val) 
                    {                 
                        
                        foreach ($val as $valcom) 
                        {  
                            $this->cronService->inProgress();

                                $total_amount = $valcom->total_amount;
                            // $associate_id = $valcom->associate_id;
                                $investment_id = $valcom->investment_id;
                                $company_id = $valcom->company_id;
                                $branch_id = $valcom->branch_id;
                                $plan_id = $valcom->plan_id;
                                $maturity_date = $valcom->maturity_date;
                                $tenure = $valcom->tenure;
                                $is_mature = $valcom->is_mature;
                                $plan_category_code=$valcom->plan_category_code;
                            
                                $created_at_investment = $valcom->created_at_investment;
                                
                                $investment_assocaite_id = $valcom->investment_assocaite_id;
                                $deposite_amount= $valcom->deposite_amount;     
                            
                                
                                $getCarryAmount=0;
                                $getCarryMonth=0;
                                $getCarryYear=0;
                                
                                $final_total_amount=$total_amount + $getCarryAmount;

                                $investmentMonthdetail = DB::select('call getInvestmentMonthByLedgerDate(?,?,?,?)',[$investment_id,$leadgerMonth,$leadgerYear,$plan_category_code]);

                                // pd($investmentMonthdetail);
                                // die;
                                

                                $monthQualifying=$investmentMonthdetail[0]->monthQualifying;
                                $monthByDate=$investmentMonthdetail[0]->monthByDate;
                                $monthByDeposit=$investmentMonthdetail[0]->monthByDeposit;
                                $balanceTillDate=$investmentMonthdetail[0]->tillLedgerDeposit;
                                
                                $finalTenure= $tenure*12;
                                if($plan_category_code=='D')
                                {
                                    $finalTenurebal= $finalTenure*30.5;
                                    if($getCarryAmount>0 && $monthQualifying<=2 && $getCarryMonth=Month($created_at_investment) && $getCarryYear=Year($created_at_investment))
                                    {
                                        $monthQualifying=1;
                                    }
                                }
                                else
                                {
                                    $finalTenurebal= $finalTenure;
                                }

                                $investmentTotalDeposit = $deposite_amount*$finalTenurebal;

                                if($plan_category_code=='D')
                                {
                                    $getDailySetting=\App\Models\CommissionDailySetting::where('status',1)->orderBy('id','DESC')->first();

                                    $dateToTest = $leadgerYear."-".$leadgerMonth."-01";
                                    $lastday = date('t',strtotime($dateToTest));

                                    $daily_min_setting=$getDailySetting->min_days;
                                    //$daily_max_setting=$getDailySetting->max_days; 
                                    $daily_max_setting=$lastday;
                                    $deposite_amount_max=$deposite_amount*$daily_max_setting;
                                    $deposite_amount_min=$deposite_amount*$daily_min_setting;
                                    
                                    
                                    //$planDenoAmounnt= $deposite_amount_min;

                                    //not call min call max assign by anup sir in feb 2023
                                    $deposite_amount_minNew=$deposite_amount*$daily_max_setting;

                                    $planDenoAmounnt= $deposite_amount_minNew;
                                }
                                else
                                {
                                    $planDenoAmounnt= $deposite_amount;
                                }

                                if($monthQualifying==($finalTenure+1))
                                {                                
                            //  die('1');
                                    $commissionReleased=$investmentTotalDeposit-$balanceTillDate;
                                    if($final_total_amount<$commissionReleased)
                                    {
                                        $commissionReleased=$final_total_amount;
                                    }
                                    if($commissionReleased>0)
                                    {
                                        if($plan_id==2)
                                        {
                                            $finalTenurecom= 216;
                                        }
                                        else
                                        {
                                            $finalTenurecom= $finalTenure;
                                        }                          
                                        
                                        $commission =CommanCommissionController:: commissionDistributeInvestmentRenew($company_id,$investment_assocaite_id,$monthQualifying,$plan_id,$finalTenurecom,$branch_id,$investment_id,$commissionReleased,$leadgerMonth,$leadgerYear,$final_total_amount);   
                                                                                            
                                        Log::channel('commissioninvestment')->info('MemberId = '.$investment_assocaite_id.' InvestmentID = '.$investment_id.' totalAmount = '.$commissionReleased.' monthQualifying = '.$monthQualifying.' msg = last month commission generate ');
                                        echo ' last month commission generate / ';
                                    }
                                    else
                                    {
                                        Log::channel('commissioninvestment')->info('MemberId = '.$investment_assocaite_id.' InvestmentID = '.$investment_id.' totalAmount = '.$commissionReleased.' monthQualifying = '.$monthQualifying.' msg = last month commission not  generate ');
                                        echo ' last month commission not generate / ';
                                    }
                                    $lMcount++;
                                    
                                }                            
                                else
                                {

                                    $commissionFinalAmt= $comFinalAmt= $final_total_amount;
                                    $comDenoamount=$planDenoAmounnt;
                                    $month=$monthQualifying;
                                    if($plan_id==2)
                                    {
                                        $finalTenurecom= 216;
                                    }
                                    else
                                    {
                                        $finalTenurecom= $finalTenure;
                                    } 
                                    
                                    while($comFinalAmt > 0) 
                                    {  
                                        if($plan_category_code=='D')
                                        {
                                            if($comFinalAmt>=$deposite_amount_max)
                                            {
                                                $comDenoamount=$deposite_amount_max;
                                            }
                                        }
                                        if($comFinalAmt>=$comDenoamount)
                                        {
                                            //die('2');

                                            $comReleasedAmt=$comDenoamount;
                                            $comMonth=$month;

                                            $commission =CommanCommissionController:: commissionDistributeInvestmentRenew($company_id,$investment_assocaite_id,$comMonth,$plan_id,$finalTenurecom,$branch_id,$investment_id,$comReleasedAmt,$leadgerMonth,$leadgerYear,$final_total_amount);   
                                                                                            
                                            Log::channel('commissioninvestment')->info('MemberId = '.$investment_assocaite_id.' InvestmentID = '.$investment_id.' totalAmount = '.$comReleasedAmt.' monthQualifying = '.$month);
                                        }
                                        else
                                        {
                                            //die('3');
                                            $comReleasedAmt=$comFinalAmt;
                                            $comMonth=$month;
                                        
                                                $commission =CommanCommissionController:: commissionDistributeInvestmentRenew($company_id,$investment_assocaite_id,$comMonth,$plan_id,$finalTenurecom,$branch_id,$investment_id,$comReleasedAmt,$leadgerMonth,$leadgerYear,$final_total_amount);   
                                                                                            
                                                Log::channel('commissioninvestment')->info('MemberId = '.$investment_assocaite_id.' InvestmentID = '.$investment_id.' totalAmount = '.$comReleasedAmt.' monthQualifying = '.$comMonth);
                                            
                                        }
                                    
                                        $comFinalAmt=$comFinalAmt-$comDenoamount;
                                        $month++;
                                    }  
                                    $tcount++;
                                } 
                                
                                
                        } 
                        
                        
                    }
                    Log::channel('commissioninvestment')->info('Commission  -- Generate End >>> tcount== '.$tcount.' lMcount === '.$lMcount);
                    $end = CommisionMonthEnd::where('month',$leadgerMonth)->where('year',$leadgerYear)->update([ 'investment_commission' => 1]); 
                    $this->cronService->completed();
                        $ldate = Carbon::now()->format('Y-m-d H:i:s');
                        $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                        $sms_text = "Software Cron - InvestmetCommissionGenerate completed with status - success on $ldate Samraddh Bestwin";
                        $templateId = 1207170141602955251;
                        $contactNumber = $processedNumbers;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
                    
                     Artisan::call('commission:summonthly'); 
                }
            }
        
        
            DB::commit();
        }
        catch(\Exception $e)
        {
            /** error log in cron is use to store error message on cron_log table to get why the cron has not complete the task. with file name , line number and message. */
                        $ldate = Carbon::now()->format('Y-m-d H:i:s');
                        $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                        $sms_text = "Software Cron - InvestmetCommissionGenerate completed with status - error on $ldate Samraddh Bestwin";
                        $templateId = 1207170141602955251;
                        $contactNumber = $processedNumbers;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            $this->cronService->errorLogs(2, $e->getMessage() . ' -Line No ' . $e->getLine().'-File Name - '.$e->getFile().'',$this->signature);
        }
                
    }
}
