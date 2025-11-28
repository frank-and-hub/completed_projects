<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
class HeadClosing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:headClosingGenerate';

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
        $branches = \App\Models\Branch::where('status',1)->get();
        $endDate = '31-03-2022';
        $endDate = date('Y-m-d H:i:s',strtotime(convertDate($endDate)));
        $startDate = '01-04-2021';
        $startDate = date('Y-m-d H:i:s',strtotime(convertDate($startDate)));
        $companyId =1;
        foreach($branches as $value)
        {
            DB::select('call headclosing_procedure(?,?,?,?)',[$value->id,$startDate,$endDate,$companyId] );

        }
          
       \Log::info("Head Closing!"); 
               
         

    }
}
