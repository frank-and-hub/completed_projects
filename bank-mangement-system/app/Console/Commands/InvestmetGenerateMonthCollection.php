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

class InvestmetGenerateMonthCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:investmentgeneratemonthlycollection';
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
       
        $monthEnd=CommisionMonthEnd::where('month',$monthForLedger)->where('year',$yearForLedger)->where('investment_collection',0)->orderBy('id','DESC')->first(); 
        try
        {
            if($monthEnd )
            {
                
                $leadgerMonth=$monthEnd->month;
                $leadgerYear=$monthEnd->year; 
              //  pd($leadgerMonth);

                $logName = 'commission/commission_collection_monthly-'.date('Y-m-d',strtotime(now())).'.log'; 
                $this->cronService->startCron($this->signature,$logName);
                Log::channel('commissioncollection')->info('Commission Collection  Start'); 

                $getData = DB::select("
                    SELECT
                        SUM(IF(day_books.transaction_type = 4, day_books.deposit, 0)) AS total_amount_fuel,
                        SUM(day_books.deposit) AS total_amount,
                        day_books.account_no,
                        day_books.company_id,
                        day_books.associate_id AS associate_id,
                        member_investments.id  AS investment_id,
                        member_investments.plan_id,
                        member_investments.deposite_amount,
                        member_investments.branch_id,
                        member_investments.maturity_date,
                        member_investments.tenure,
                        member_investments.is_mature,
                        member_investments.created_at AS created_at_investment,
                        member_investments.associate_id AS investment_associate_id,
                        plans.plan_category_code AS plan_category_code
                    FROM
                        day_books
                    JOIN
                        member_investments 
                    ON 
                        member_investments.account_number = day_books.account_no
                    JOIN
                        plans ON plans.id = member_investments.plan_id
                    WHERE
                        plans.plan_category_code IN ('D', 'M')
                        AND day_books.associate_id != 1
                        AND day_books.is_deleted = 0
                        AND MONTH(day_books.created_at) = " . $leadgerMonth . "
                        AND YEAR(day_books.created_at) = " . $leadgerYear . "
                        AND DATE(day_books.created_at) <= member_investments.maturity_date
                        AND day_books.transaction_type IN (2, 4)
                    GROUP BY
                        day_books.associate_id, day_books.account_no,day_books.company_id
                "); 
                 
                if(count($getData) > 0)
                {    
                    

                    Log::channel('commissioncollection')->info('Commission Collection -'.count($getData)); 

                    $collection = collect($getData);
                    $dailyFuleCount=0;
                    $dailyCollectionCount =0;
                    $monthFuleCount =0;
                    $monthcollectionCount =0;
                    foreach ($collection->chunk(50)  as  $val) 
                    {    
                        foreach ($val as $valcom) 
                        {  
                            $this->cronService->inProgress();

                                $total_amount = $valcom->total_amount;
                                $total_amount_fuel = $valcom->total_amount_fuel;
                                $associate_id = $valcom->associate_id;
                                $company_id = $valcom->company_id;
                                $investment_id = $valcom->investment_id;
                                $branch_id = $valcom->branch_id;
                                $plan_id = $valcom->plan_id;
                                $maturity_date = $valcom->maturity_date;
                                $tenure = $valcom->tenure;
                                $is_mature = $valcom->is_mature;
                                $created_at_investment = $valcom->created_at_investment;
                                $investment_assocaite_id = $valcom->investment_associate_id;
                                $deposite_amount= $valcom->deposite_amount; 
                                $plan_category_code=$valcom->plan_category_code;    
                                
                            
                                $final_total_amount=$total_amount ;

                                $investmentMonthdetail = DB::select('call getInvestmentMonthByLedgerDate(?,?,?,?)',[$investment_id,$leadgerMonth,$leadgerYear,$plan_category_code]);


                                $monthQualifying=$investmentMonthdetail[0]->monthQualifying;
                                $monthByDate=$investmentMonthdetail[0]->monthByDate;
                                $monthByDeposit=$investmentMonthdetail[0]->monthByDeposit;
                                $balanceTillDate=$investmentMonthdetail[0]->tillLedgerDeposit;
                                
                                $finalTenure= $tenure*12;
                                if($valcom->plan_category_code=='D')
                                {
                                    $finalTenurebal= $finalTenure*30.5;
                                }
                                else
                                {
                                    $finalTenurebal= $finalTenure;
                                }
                                $investmentTotalDeposit = $deposite_amount*$finalTenurebal;


                            if($valcom->plan_category_code=='D')
                            {
                                $getDailySetting=\App\Models\CommissionDailySetting::where('status',1)->orderBy('id','DESC')->first();
                                $daily_min_setting=$getDailySetting->min_days;
                                $daily_max_setting=$getDailySetting->max_days;                            
                                /**********   fule qualifying  *********** */ 
                                $deposite_amount_max=$deposite_amount*$daily_max_setting; 
                                if($total_amount_fuel>0)
                                {
                                    if($total_amount_fuel<=$deposite_amount_max)
                                    {
                                        $fule_qualifying_amount=$total_amount_fuel;
                                    }
                                    else
                                    {
                                        $fule_qualifying_amount=$deposite_amount_max;
                                    }
                                    if($fule_qualifying_amount>0)
                                    {
                                        $fuleInsert['associate_id'] = $associate_id;
                                        $fuleInsert['investment_id'] = $investment_id;
                                        $fuleInsert['month'] = $leadgerMonth; 
                                        $fuleInsert['year'] = $leadgerYear;
                                        $fuleInsert['total_amount'] = $total_amount_fuel;
                                        $fuleInsert['qualifying_amount'] = $fule_qualifying_amount;
                                        $fuleInsert['company_id'] = $company_id;   
                                        $fuleCreate= \App\Models\CommissionFuleCollection::create($fuleInsert);
                                        
                                        $dailyFuleCount++;
                                        
                                        Log::channel('commissionfule')->info('Daily Investment >>> MemberId = '.$associate_id.' InvestmentID = '.$investment_id.' totalAmount = '.$total_amount_fuel.' qualifying_amount = '.$fule_qualifying_amount); 
                                    }
                                }                          
                                
                                /*---------- fule qualifying   End  ---------*/  
                                /*---------- collection script  Start  ---------*/                          
                                if($monthQualifying <= ($finalTenure-3))
                                {                                
                                    if(($total_amount + $balanceTillDate) <= $investmentTotalDeposit)
                                    {
                                        $collection_qualifying_amount = $total_amount;  
                                    }
                                    else{                                    
                                        $collection_qualifying_amount = ($total_amount + $balanceTillDate) - $investmentTotalDeposit;  
                                         /*---------- new code add 1 may 2024 by alpana  ---------*/ 
                                         $extra_amount  = ($total_amount + $balanceTillDate) - $investmentTotalDeposit;
                                         $collection_qualifying_amount = $total_amount - $extra_amount;
                                    }
                                } 
                                else
                                {
                                    if($total_amount>$deposite_amount_max)
                                        {
                                            $collection_qualifying_amount = $deposite_amount_max; 
                                        } 
                                        else
                                        {
                                            $collection_qualifying_amount = $total_amount;
                                        }
                                }
                                if($collection_qualifying_amount>0 )
                                {
                                    if($monthQualifying > $finalTenure)
                                    {
                                        $monthQualifyingColl = $finalTenure;
                                    }
                                    else{
                                        $monthQualifyingColl = $monthQualifying;
                                    }
                                    $getCollectionPer =  DB::select('call investmentCollectionPer(?,?,?,?,?)',[$plan_id,'1',$finalTenure,$associate_id,$monthQualifyingColl]);
                                                                
                                    $percentageColl = $getCollectionPer[0]->collectorPer;
                                    $cadre_fromColl = $getCollectionPer[0]->cadreid_from;
                                    $cadre_toColl = $getCollectionPer[0]->cadreid_to;
                                    
                                    $percentInDecimalColl = $percentageColl / 100;
                                    $collectionAmount = round($percentInDecimalColl * $collection_qualifying_amount, 4); 
                                        
                                    $associateColl['assocaite_id'] = $associate_id;
                                    $associateColl['type'] = 1;
                                    $associateColl['sub_type'] = 2;
                                    $associateColl['type_id'] = $investment_id;
                                    $associateColl['month'] = $monthQualifyingColl;  
                                    $associateColl['qualifying_amount'] = $collection_qualifying_amount;
                                    $associateColl['total_amount'] = $total_amount;
                                    $associateColl['commission_amount'] = $collectionAmount;
                                    $associateColl['percentage'] = $percentageColl;
                                    $associateColl['cadre_from'] = $cadre_fromColl;
                                    $associateColl['cadre_to'] = $cadre_toColl;
                                    $associateColl['commission_for_month'] = $leadgerMonth;
                                    $associateColl['commission_for_year'] = $leadgerYear;
                                    $associateColl['type_id_branch'] = $branch_id;//investment branch id 
                                    $associateColl['created_by'] = 1;                               
                                    $associateColl['created_at'] = date("Y-m-d h:i:s");
                                    $associateColl['created_by_id'] = 1; 
                                    $associateColl['company_id'] = $company_id;  

                                    $associateCollInsert = \App\Models\AssociateMonthlyCommission::create($associateColl); 
                                    $dailyCollectionCount++;
            
                                    Log::channel('commissioncollection')->info('Daily Investment >>> MemberId = '.$associate_id.' InvestmentID = '.$investment_id.' totalAmount = '.$total_amount.' commission_amount = '.$collectionAmount.' qualifying_amount = '.$collection_qualifying_amount.'percentage=='.$percentageColl); 
                                } 
                                
                            }
                            else
                            {                            
                                /**********  fule qualifying  start ***********/                            
                                if($total_amount_fuel>0)
                                {
                                    if($total_amount_fuel<=$deposite_amount)
                                    {
                                        $fule_qualifying_amount=$total_amount_fuel;
                                    }
                                    else
                                    {
                                        $fule_qualifying_amount=$deposite_amount;
                                    }
                                    if($fule_qualifying_amount>0)
                                    {
                                        $fuleInsert['associate_id'] = $associate_id;
                                        $fuleInsert['investment_id'] = $investment_id;
                                        $fuleInsert['month'] = $leadgerMonth; 
                                        $fuleInsert['year'] = $leadgerYear;
                                        $fuleInsert['total_amount'] = $total_amount_fuel;
                                        $fuleInsert['qualifying_amount'] = $fule_qualifying_amount; 
                                        $fuleInsert['company_id'] = $company_id;  
                                        
                                        $fuleCreate= \App\Models\CommissionFuleCollection::create($fuleInsert); 

                                        $monthFuleCount++;

                                        Log::channel('commissionfule')->info('Monthly >> MemberId = '.$associate_id.' InvestmentID = '.$investment_id.' totalAmount = '.$total_amount_fuel.' carryAmount = '.$fule_qualifying_amount); 
                                    }
                                }
                                /**********  fule qualifying  End ***********/   
                                /*---------- collection script  Start  ---------*/
                                if($monthQualifying <= ($finalTenure-3))
                                {                                
                                    if(($total_amount + $balanceTillDate) <= $investmentTotalDeposit)
                                    {
                                        $collection_qualifying_amount = $total_amount;  
                                    }
                                    else{                                    
                                        $collection_qualifying_amount = ($total_amount + $balanceTillDate) - $investmentTotalDeposit;                                    
                                        /*---------- new code add 1 may 2024 by alpana  ---------*/ 
                                         $extra_amount  = ($total_amount + $balanceTillDate) - $investmentTotalDeposit;
                                         $collection_qualifying_amount = $total_amount - $extra_amount;
                                    }
                                } 
                                else
                                {
                                    if($total_amount>$deposite_amount)
                                        {
                                            $collection_qualifying_amount = $deposite_amount; 
                                        } 
                                        else
                                        {
                                            $collection_qualifying_amount = $total_amount;
                                        }
                                }
                                if($collection_qualifying_amount>0)
                                {
                                    if($plan_id==2)
                                    {
                                        $finalTenurecoll= 216;
                                    }
                                    else
                                    {
                                        $finalTenurecoll= $finalTenure;   
                                    }
                                    if($monthQualifying > $finalTenure)
                                    {
                                        $monthQualifyingColl = $finalTenure;
                                    }
                                    else{
                                        $monthQualifyingColl = $monthQualifying;
                                    }
                                    $getCollectionPer =  DB::select('call investmentCollectionPer(?,?,?,?,?)',[$plan_id,'1',$finalTenurecoll,$associate_id,$monthQualifyingColl]);
                                    // print_r($getCollectionPer);die;                                
                                    $percentageColl = $getCollectionPer[0]->collectorPer;
                                    $cadre_fromColl = $getCollectionPer[0]->cadreid_from;
                                    $cadre_toColl = $getCollectionPer[0]->cadreid_to;
                                            
                                    $percentInDecimalColl = $percentageColl / 100;
                                    $collectionAmount = round($percentInDecimalColl * $collection_qualifying_amount , 4); 
                                        
                                    $associateColl['assocaite_id'] = $associate_id;
                                    $associateColl['type'] = 1;
                                    $associateColl['sub_type'] = 2;
                                    $associateColl['type_id'] = $investment_id; 
                                    $associateColl['month'] = $monthQualifyingColl;
                                    $associateColl['qualifying_amount'] = $collection_qualifying_amount;
                                    $associateColl['total_amount'] = $total_amount;
                                    $associateColl['commission_amount'] = $collectionAmount;
                                    $associateColl['percentage'] = $percentageColl;
                                    $associateColl['cadre_from'] = $cadre_fromColl;
                                    $associateColl['cadre_to'] = $cadre_toColl;
                                    $associateColl['commission_for_month'] = $leadgerMonth;
                                    $associateColl['commission_for_year'] = $leadgerYear;
                                    $associateColl['type_id_branch'] = $branch_id;//investment branch id 
                                    $associateColl['created_by'] = 1;                               
                                    $associateColl['created_at'] = date("Y-m-d h:i:s");
                                    $associateColl['created_by_id'] = 1; 
                                    
                                    $associateColl['company_id'] = $company_id;  

                                    $associateCollInsert = \App\Models\AssociateMonthlyCommission::create($associateColl); 

                                    $monthcollectionCount++;
            
                                    Log::channel('commissioncollection')->info('Monthly >> MemberId = '.$associate_id.' InvestmentID = '.$investment_id.' totalAmount = '.$total_amount.' commission_amount = '.$collectionAmount.' qualifying_amount = '.$collection_qualifying_amount.'percentage = '.$percentageColl);                                 
                                }
                                
                                /*---------- collection script  end  ---------*/                 
                            }                       
                        }                     
                    }
                    Log::channel('commissioncollection')->info('Commission  -- Generate End >>> monthcollectionCount =='.$monthcollectionCount.', dailyCollectionCount = '.$dailyCollectionCount.', monthFuleCount = '.$monthFuleCount.' ,  dailyFuleCount = '.$dailyFuleCount);

                    Log::channel('commissionfule')->info('Fule   End >>>  monthFuleCount = '.$monthFuleCount.' ,  dailyFuleCount = '.$dailyFuleCount);
                    $end = CommisionMonthEnd::where('month',$leadgerMonth)->where('year',$leadgerYear)->update([ 'investment_collection' => 1]); 
                    $this->cronService->completed();

                        $ldate = Carbon::now()->format('Y-m-d H:i:s');
                        $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                        $sms_text = "Software Cron - InvestmetCollectionGenerate completed with status - success on $ldate Samraddh Bestwin";
                        $templateId = 1207170141602955251;
                        $contactNumber = $processedNumbers;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
                    
                    Artisan::call('commission:investmentgeneratemonthlycommission');//remove comment 
                }
            } 
            DB::commit();
        }
        catch(\Exception $e)
        {
            /** error log in cron is use to store error message on cron_log table to get why the cron has not complete the task. with file name , line number and message. */
                        $ldate = Carbon::now()->format('Y-m-d H:i:s');
                        $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                        $sms_text = "Software Cron - InvestmetCollectionGenerate completed with status - error on $ldate Samraddh Bestwin";
                        $templateId = 1207170141602955251;
                        $contactNumber = $processedNumbers;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            $this->cronService->errorLogs(2, $e->getMessage() . ' -Line No ' . $e->getLine().'-File Name - '.$e->getFile().'',$this->signature);
            
        }
                
                
    }
}
