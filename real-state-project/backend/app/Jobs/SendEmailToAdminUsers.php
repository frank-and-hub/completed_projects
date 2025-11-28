<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\AdminSubscription;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;

class SendEmailToAdminUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('start agency crone for plan expire check');

            $upcomingDate = Carbon::now()->addDays(2)->format('Y-m-d');

            AdminSubscription::whereDate('expired_at', Carbon::now())
                ->whereIn('status', ['ongoing'])
                ->update(['status' => 'expired']);

            $agencies = AdminSubscription::with('admin', 'plan')
                ->whereDate('expired_at', $upcomingDate)
                ->whereIn('status', ['ongoing'])
                ->whereHas('plan', fn($q) => $q->whereType('agency'))
                ->get();
                
            foreach ($agencies as $agency) {
                Helper::sendAgenciesPlainMail($agency->admin, $agency);
            }

        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
