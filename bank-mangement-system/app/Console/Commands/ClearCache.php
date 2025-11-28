<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Artisan;


class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clearSamraddh:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Cache';

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
         Artisan::call('cache:clear');
         \Log::info("Cache is cleared!");
    }
}
