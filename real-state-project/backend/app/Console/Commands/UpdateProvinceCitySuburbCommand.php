<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProvinceCitySuburb;
use Illuminate\Console\Command;

class UpdateProvinceCitySuburbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-province-city-suburb-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert new city province suburb by third ENTEGRAL api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new UpdateProvinceCitySuburb());
    }
}
