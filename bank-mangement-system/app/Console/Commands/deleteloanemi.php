<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grouploans;
use App\Models\LoanEmiNew1;
use App\Models\LoanDayBooks;
use App\Models\Loans;
use App\Models\AllHeadTransactionNew;
use App\Models\SamraddhBank;
use DB;
use Carbon\Carbon;
use App\Models\SamraddhBankDaybook;
use Illuminate\Support\Facades\Log;

class deleteloanemi extends Command
{

    /**
     * The name and signature of the console command.
     *use Carbon\Carbon;

     * @var string
     */
    protected $signature = 'deleteloanemi:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create New Emi of Current date';

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
       
        \App\Models\Memberloans::where('status',4)->whereHas('loan',function($q){
            $q->where('loan_type','!=','G');
        })->where('company_id',1)->chunk(2000,function($datas){
           
            foreach($datas as $data)
            {
             
                  \App\Models\LoanDayBooks::where('account_number',$data->account_number)->where('loan_sub_type',0)->where('is_deleted',0)->chunk(2000,function($records){
 
                 foreach($records as $i =>  $value)
                 {
                     $d = \App\Models\Memberloans::where('account_number',$value->account_number)->first();
                     // $d->update(['accrued_interest'=>0]);
 
                     $date = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where('loan_sub_type',0)->where('is_deleted',0)->whereId($value->id)->orderBY('created_at','desc')->first();
                        \App\Models\AllHeadTransaction::where('daybook_ref_id',$date->daybook_ref_id)->update(['is_deleted' => 1,'is_query'=>1]);
                        \Log::info("Delete Emi !".$date->account_number);
                     
                 }
                     
                
                     
                 
                   
                     
            });
 
                
            }
            
         });
        
       
    }
}
