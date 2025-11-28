<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Member;
use App\Services\CronStoreInfo;

class MemberDisable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'member:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Photo and Signature not upload member Inactive';

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
            $logName = 'memberBlock/member_block-' . date('Y-m-d', strtotime(now())) . '.log';
            $this->cronService->startCron($this->signature, $logName);

            $member = Member::whereNull('signature')
                ->whereNull('photo')
                ->where('id', '!=', 1)
                ->where('created_at', '<', Carbon::now()->subMonth())
                ->update(['is_block' => 1]);

            $this->cronService->inProgress();

            \Log::channel('memberBlock')->info("CustomerId-  " . json_encode($member));

            $this->cronService->completed();
        } catch (\Exception $e) {
            $this->cronService->errorLogs(4, $e->getMessage() . ' -Line No ' . $e->getLine() . '-File Name - ' . $e->getFile() . '', $this->signature);
        } finally {
            if (isset($e) && $e instanceof \Exception) {
                $this->cronService->sentCronSms(false);
            } else {
                $this->cronService->sentCronSms(true);
            }
        }
    }
}
