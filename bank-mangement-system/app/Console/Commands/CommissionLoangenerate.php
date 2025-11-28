<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CommissionLoangenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:loangenerate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan transction commission generate';

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

         die("Commission loanChange!");
        //die('hi');
         \Log::info("Loan commission generate start!"); 

            $commissionEntryGet = \App\Models\CommissionEntryLoan::where('status',1)
            ->where(\DB::raw('MONTH(created_at)'),12)
            ->where(\DB::raw('YEAR(created_at)'),2022)  
            ->whereDate('created_at','<','2023-01-01')
            ->get();
                if(count($commissionEntryGet) > 0)
                {             

                    foreach ($commissionEntryGet->chunk(5) as  $valCom) 
                    { 
                        foreach ($valCom as $valcom1) 
                        { 
                            $associateCommission['member_id'] = $valcom1->member_id;
                            $associateCommission['branch_id'] = $valcom1->branch_id;
                            $associateCommission['type'] = $valcom1->type;
                            $associateCommission['type_id'] = $valcom1->type_id;
                            $associateCommission['day_book_id'] = $valcom1->day_book_id;
                            $associateCommission['total_amount'] = $valcom1->total_amount;
                            $associateCommission['month'] = $valcom1->month;
                            $associateCommission['commission_amount'] = $valcom1->commission_amount;
                            $associateCommission['percentage'] = $valcom1->percentage;
                            $associateCommission['commission_type'] = $valcom1->commission_type;
                            $associateCommission['created_at'] = $valcom1->created_at;
                            $associateCommission['pay_type'] = $valcom1->pay_type;
                            $associateCommission['carder_id'] = $valcom1->carder_id;
                            $associateCommission['associate_exist'] = $valcom1->associate_exist;
                            $associateCommissionInsert = \App\Models\AssociateCommission::create($associateCommission);

                            $comDataUpdate = \App\Models\CommissionEntryLoan::where('id',$valcom1->id)->update([ 'status' => 2 ]);
                        }
                    }
                    \Log::info("Loan commission generate sucess!"); 
                }
         

    }
}
