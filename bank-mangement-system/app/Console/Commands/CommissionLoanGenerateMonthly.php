<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Schema;
use App\Models\LoanDayBooks;
use App\Models\CommisionMonthEnd;
use App\Models\CommissionLeaserMonthly;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;
use Illuminate\Support\Facades\Artisan;
use App\Services\Sms;
use Carbon\Carbon;

class CommissionLoanGenerateMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:loangeneratemonthly'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan transction commission generate Monthly';

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
        
       // dd($lastLedger);
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
       $monthEnd=CommisionMonthEnd::where('month',$monthForLedger)->where('year',$yearForLedger)->where('loan',0)->orderBy('id','DESC')->first();

      // pd($monthEnd);
        try{  
                if($monthEnd )
                {
                    $logName = 'commission/commission_loan_monthly-'.date('Y-m-d',strtotime(now())).'.log';
                    $this->cronService->startCron($this->signature,$logName);
                    Log::channel('commissionloan')->info('Loan Collection -- Generate Start');

                    $leadgerMonth=$monthEnd->month;
                    $leadgerYear=$monthEnd->year;
                    // $getData= LoanDayBooks::select(DB::raw('sum(deposit) as total_amount'), DB::raw('loan_id as loan_id') ,DB::raw('associate_id as associate_id'),DB::raw('loan_type as loan_type'),DB::raw('company_id as company_id')) 
                    //     ->where('loan_sub_type',0)
                    //     ->where('is_deleted',0)
                    //     ->where('associate_id','!=',1)
                    //     ->where(\DB::raw('MONTH(created_at)'),$leadgerMonth)
                    //     ->where(\DB::raw('YEAR(created_at)'),$leadgerYear)
                    //     ->whereIn('loan_type',array(1,3))
                    //     ->groupBy(DB::raw('loan_id,associate_id,loan_type','company_id'))->get(); 

                        $query = "
                            SELECT
                                SUM(ldb.deposit) AS total_amount,
                                ldb.loan_id AS loan_id,
                                ldb.associate_id AS associate_id,
                                l.loan_category AS loan_type,
                                ldb.company_id AS company_id
                            FROM
                                loan_day_books AS ldb
                            JOIN
                                loans AS l ON l.id = ldb.loan_type
                            WHERE
                                ldb.loan_sub_type = 0
                                
                                AND ldb.is_deleted = 0
                                AND ldb.associate_id != 1
                                AND MONTH(ldb.created_at) = " . $leadgerMonth . "
                                AND YEAR(ldb.created_at) = " . $leadgerYear . "
                                AND l.loan_category IN (1, 3)
                                AND l.id !=10
                            GROUP BY
                                ldb.loan_id,
                                ldb.associate_id,
                                ldb.loan_type,
                                ldb.company_id;
                        ";

                        $getData = DB::select($query);

                  //print_r(count($getData));  die; 
                    $a=0;
        
                    if(count($getData) > 0)
                    {    
                        Log::channel('commissionloan')->info('Loan Collection '.count($getData));
                        $collection = collect($getData);

                        foreach ($collection->chunk(10) as  $val) 
                        {                 
                            foreach ($val as $valcom) 
                            {  
                                $a++;
                            // pd($valcom->loan_type);//die;
                                $perDetail= DB::select('call loanCarderPerNew(?,?,?)',[$valcom->associate_id,$valcom->loan_id,$valcom->loan_type]);

                           // print_r($perDetail[0]->perSum1);//die;
                                /*---------- commission script  start  ---------*/ 
                                                    
                                $percentage = $perDetail[0]->perSum1;
                                $cadre_from = $perDetail[0]->cadreid_from;
                                $cadre_to = $perDetail[0]->cadreid_to;
                                $total_amount = $valcom->total_amount;
                                $associate_id = $valcom->associate_id;
                                $company_id = $valcom->company_id;
                                $branch_id = $perDetail[0]->loanBranchId;

                                $percentInDecimal = $percentage / 100;
                                $commission_amount = round($percentInDecimal * $total_amount, 4); 
                                $month='NULL';
                                if($valcom->loan_type==3)
                                {
                                    $type=3;
                                }
                                else{
                                    $type=2;
                                }
                                $sub_type=2;
                                $type_id=$valcom->loan_id; 


                                $associateCommission['assocaite_id'] = $associate_id;
                                $associateCommission['type'] = $type;
                                $associateCommission['sub_type'] = $sub_type;
                                $associateCommission['type_id'] = $type_id;
                                // $associateCommission['month'] = $month;
                                $associateCommission['total_amount'] = $total_amount;
                                $associateCommission['qualifying_amount'] = $total_amount;
                                $associateCommission['commission_amount'] = $commission_amount;
                                $associateCommission['percentage'] = $percentage;
                                $associateCommission['cadre_from'] = $cadre_from;
                                $associateCommission['cadre_to'] = $cadre_to;
                                $associateCommission['commission_for_month'] = $leadgerMonth;
                                $associateCommission['commission_for_year'] = $leadgerYear;
                                $associateCommission['type_id_branch'] = $branch_id;
                                $associateCommission['created_by'] = 1;                               
                                $associateCommission['created_at'] = date("Y-m-d h:i:s");
                                $associateCommission['created_by_id'] = 1;
                                $associateCommission['company_id'] =  $company_id;

                              // print_r($associateCommission);//die;
                            
                                $associateCommissionInsert = \App\Models\AssociateMonthlyCommission::create($associateCommission); 

                                Log::channel('commissionloan')->info('Loan id = '.$type_id. ' Total Amount = '.$total_amount. ' Commission Amount = '.$commission_amount . ' percentage = '.$percentage);   
                                $this->cronService->inProgress();

                                /*---------- commission script  end  ---------*/
                            }
                        }

                        Log::channel('commissionloan')->info('Loan Collection -- Generate End(count=='.$a.')');
                        
                        $end = CommisionMonthEnd::where('month',$leadgerMonth)->where('year',$leadgerYear)->update([ 'loan' => 1]);                    
                        
                        $this->cronService->completed(); 

                        $ldate = Carbon::now()->format('Y-m-d H:i:s');
                        $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                        $sms_text = "Software Cron - LoanCommission completed with status - success on $ldate Samraddh Bestwin";
                        $templateId = 1207170141602955251;
                        $contactNumber = $processedNumbers;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);

                        Artisan::call('commission:investmentgeneratemonthlycollection');
                    }
                } 
                DB::commit();
        }
        catch(\Exception $e)
        {
                        $ldate = Carbon::now()->format('Y-m-d H:i:s');
                        $processedNumbers = [8946984812, 9694690124, 9415085891,9889010578];
                        $sms_text = "Software Cron - LoanCommission completed with status - error on $ldate Samraddh Bestwin";
                        $templateId = 1207170141602955251;
                        $contactNumber = $processedNumbers;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
            /** error log in cron is use to store error message on cron_log table to get why the cron has not complete the task. with file name , line number and message. */
            $this->cronService->errorLogs(2, $e->getMessage() . ' -Line No ' . $e->getLine().'-File Name - '.$e->getFile().'',$this->signature);
        }
      
             
             
    }
}
