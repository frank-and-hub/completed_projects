<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Memberloans;
use App\Models\Grouploans;
use DB;
use Illuminate\Support\Facades\Log;
use App\Services\CronStoreInfo;

class EmiOuststanding extends Command
{

    /**
     * The name and signature of the console command.
     *use Carbon\Carbon;

     * @var string
     */
    protected $signature = 'EmiOuststanding:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Outstanding';

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
        try {
            $currentDate = date('Y-m-d');
            $data = Memberloans::select('account_number')->where('status', '4')->where('ecs_type','0')->whereHas('loans', function ($q) {
                $q->where('loan_type', 'L');
            })->get();
            $logName = 'loan/loanEmiquery-' . date('Y-m-d', strtotime(now())) . '.log';
            $cronChannel = 'EmiOuststanding';

            $this->cronService->startCron($this->signature, $logName);

            foreach ($data as $key => $value) {
                $this->cronService->inProgress();
                $accountNumber = $value->account_number;

                $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $accountNumber, 1]);
                Log::channel('loanEmiquery')->info('Run Update Emu' . $accountNumber);

            }

            $groupdata = Grouploans::select('account_number')->where('status', '4')->where('ecs_type','0')->whereHas('loans', function ($q) {
                $q->where('loan_type', 'G');
            })->get();
            foreach ($groupdata as $key => $value) {
                $this->cronService->inProgress();
                $accountNumbers = $value->account_number;

                $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $accountNumbers, 1]);
                Log::channel('loanEmiquery')->info('Run Update Emu' . $accountNumbers);
            }

            $this->cronService->completed();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $this->cronService->errorLogs(4, $e->getMessage() . ' - Line No ' . $e->getLine() . ' - File Name - ' . $e->getFile() . '', $this->signature, $cronChannel, $logName);
            return back()->with('alert', $e->getMessage());
        } finally {
            if (isset($e) && $e instanceof \Exception) {
                $this->cronService->sentCronSms(false);
            } else {
                $this->cronService->sentCronSms(true);
            }
        }
    }
}
