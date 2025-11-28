<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailToAdminUsers;
use Illuminate\Console\Command;

class testCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-commands';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SendEmailToAdminUsers::dispatch();
        $this->info('job has been dispatched.');
    }
}
