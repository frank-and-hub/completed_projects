<?php

namespace App\Console\Commands;

use App\Models\Verification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class DeleteOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to delete notifications that are older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logChannel = Log::channel('delete_notifications');

        $deleted = Notification::withTrashed()->whereNotNull('read_at')->where('created_at', '<', now()->subWeeks(1))->forceDelete();
        Verification::whereStatus(0)->where('created_at', '<', now()->subWeeks(1))->delete();
        Verification::withTrashed()->whereNotNull('deleted_at')->forceDelete();

        $this->info("Deleted $deleted notifications.");
        $logChannel->info("Deleted $deleted notifications.");
    }
}
