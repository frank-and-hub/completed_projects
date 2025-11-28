<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\CommanController;
use DB;

class CommissionGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Commission';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

       die("Commission Change!");
        


                $commissionEntryGet = \App\Models\CommissionEntryDetail::where('status',0) 
                ->where(\DB::raw('MONTH(renew_date)'),11)
                ->where(\DB::raw('YEAR(renew_date)'),2022)  
                ->whereDate('renew_date','<','2022-12-01')
                ->where('is_deleted',0)
                ->get();
              

       DB::beginTransaction();        
        try 
        {
          \Log::info("-----Commission Change start ------------"); 
                if(count($commissionEntryGet) > 0)
                {             

                    foreach ($commissionEntryGet->chunk(10) as  $valCom) 
                    { 
                      \Log::info("-----inter1 ------------");
                        foreach ($valCom as $valcom1) 
                        {  
                       
                          // dd($valcom1);
                          //\Log::info("-----inter2 ------------");
                            $dateForRenew=$valcom1->renew_date;
                            $renewal_date_time=$valcom1->renewal_date_time;
                            $investment_id=$valcom1->investment_id;
                            $tenureMonth=$valcom1->tenure_month;
                            $amountkey=$valcom1->amount;
                            $daybook_id=$valcom1->daybook_id;
                            $plan_id=$valcom1->investment_plan_id;
                            $investment_associte_id=$valcom1->investment_associte_id;
                            $collector_id=$valcom1->collector_id;
                            $branch_id=$valcom1->branch_id;   

                            $Commission=getMonthlyWiseRenewalNewChanges($investment_id,$amountkey,$dateForRenew,$daybook_id); 
                        //print_r($Commission);die;
                            foreach ($Commission as  $val) 
                            { 
                               // print_r($val['month'].'<='.($tenureMonth*30.5));die;
                              //\Log::info("-----ganerate ------------");
                                if($plan_id==7)
                                {
                                    if($val['month']<=($tenureMonth*30.5))
                                    { 
                                       \Log::info("-----ganerate DD= ".$daybook_id."------------"); 
                                        $commission =CommanController:: commissionDistributeInvestmentRenew($investment_associte_id,$investment_id,3,$val['amount'],$val['month'],$plan_id,$branch_id,$tenureMonth,$daybook_id,$val['type']); 

                                        $commission_collection =CommanController::commissionCollectionInvestmentRenew($collector_id,$investment_id,5,$val['amount'],$val['month'],$plan_id,$branch_id,$tenureMonth,$daybook_id,$val['type']);                                   
                                    }
                                    else
                                    { 
                                        \Log::info("tenure complete =".$investment_id." , Did=".$daybook_id );   
                                    }
                                }
                                else
                                {
                                    if($val['month']<=$tenureMonth)
                                    { 
                                        \Log::info("-----ganerate All= ".$daybook_id."------------");
                                    $commission =CommanController:: commissionDistributeInvestmentRenew($investment_associte_id,$investment_id,3,$val['amount'],$val['month'],$plan_id,$branch_id,$tenureMonth,$daybook_id,$val['type']); 

                                    $commission_collection =CommanController::commissionCollectionInvestmentRenew($collector_id,$investment_id,5,$val['amount'],$val['month'],$plan_id,$branch_id,$tenureMonth,$daybook_id,$val['type']);

                                    
                                    }
                                    else
                                    { 
                                        \Log::info("tenure complete =".$investment_id." , Did=".$daybook_id ); 
                                    }
                                }
                                
                                /*----- ------  credit business start ---- ---------------   */

                                $creditBusiness =CommanController::associateCreditBusiness($investment_associte_id,$investment_id,1,$val['amount'],$val['month'],$plan_id,$tenureMonth,$daybook_id);

                                /*----- ------  credit business end ---- ---------------   */  
                                  

                            }


                           $comDataUpdate = \App\Models\CommissionEntryDetail::where('id',$valcom1->id)->update([ 'status' => 1 ]); 

                            \Log::info("Commission generate sucess!"); 
                        }
                    }
                }

 \Log::info("Commission Change end!"); 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
            \Log::info("error ==".$ex->getMessage()); 

        }

    }
}
