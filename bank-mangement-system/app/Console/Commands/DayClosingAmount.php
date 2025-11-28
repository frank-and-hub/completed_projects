<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DayClosingAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dayclosing:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dayclosing of Branch';

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
      die("diw");
        $branches = \App\Models\Branch::where('status',1)->get();
      
        foreach ($branches as $key => $branch) {
            $getBranchId = $branch->id;
            $stateid = $branch->state_id;
            $end_date = headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
            $end_date = date('Y-m-d',strtotime(convertDate($end_date)));
            $start_date = date('Y-m-d',strtotime(convertDate($branch->date)));
          
            if($branch->cron_date != $end_date)
            {
              
               // date("Y-m-d");
              $getTotal_DR=getBranchTotalBalanceAllTranDRnew($start_date,$end_date,$getBranchId);
              $getTotal_CR=getBranchTotalBalanceAllTranCRnew($start_date,$end_date,$getBranchId);
              $getBranchOpening_cash = $branch;
              $balance_cash = 0;
              $cash_in_hand = $branch->cash_in_hand;
            

            $closing_balance = 0;
            if($getBranchOpening_cash->date == $start_date)
            {
               
              $balance_cash = $getBranchOpening_cash->total_amount;
            }
            if($getBranchOpening_cash->date < $start_date)
            {
                
              $getBranchTotalBalance_cash = getBranchTotalBalanceAllTran($end_date, $branch->date, $branch->total_amount, $getBranchId);
              $balance_cash = $getBranchTotalBalance_cash;
            }
            $totalBalance=$getTotal_CR-$getTotal_DR;
            $closing_balance = $balance_cash + $totalBalance;
            $d = \App\Models\Branch::find($getBranchId);
            $d->update(['day_closing_amount'=> $closing_balance,'first_login'=>'0','cron_date'=>$end_date]);
            \Log::info("branch".$branch->id); 
          }
          \Log::info("Cron Date All ready Exist");
        }   

        
    }
}
