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
use App\Http\Controllers\Admin\TestController;

class outstanndingEmi extends Command
{

    /**
     * The name and signature of the console command.
     *use Carbon\Carbon;

     * @var string
     */
    protected $signature = 'outstandingEmi:update';

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
        TestController::ROIAmountUpdateOld();

        
    }
}
