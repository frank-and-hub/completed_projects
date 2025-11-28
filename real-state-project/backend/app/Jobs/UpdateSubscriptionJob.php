<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateSubscriptionJob implements ShouldQueue
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
            Log::info('start clon');

            $expiredRowsAffected = UserSubscription::where('expired_at', '<=', Carbon::now())
                ->whereIn('status', ['ongoing', 'cancelled'])
                ->update(['status' => 'expired']);
            if ($expiredRowsAffected) {
                User::whereHas('user_subscription', function ($query) {
                    $query->where('expired_at', '<=', Carbon::now())
                        ->where('status', 'expired');
                })->chunk(1000, function ($users) {
                    $userIds = $users->pluck('id')->toArray();

                    // Update user_subscription field using mass update
                    User::whereIn('id', $userIds)->update(['subscription' => 0]);
                });
            }
            User::whereHas('user_subscription', function ($query) {
                $query->where('expired_at', '>=', Carbon::now())
                    ->whereIn('status', ['ongoing', 'cancelled']);
            })->update(['subscription' => 1]);
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
