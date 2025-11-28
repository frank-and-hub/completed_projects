<?php

use App\Jobs\SendEmailToAdminUsers;
use App\Jobs\SendPropertiesToUserJob;
use App\Jobs\UpdatePropertiesData;
use App\Jobs\UpdateProvinceCitySuburb;
use App\Jobs\UpdateSubscriptionJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// $schedule = app(Schedule::class);

// $schedule->command('telescope:prune --hours=24')->daily();
// $this->job(new App\Jobs\UpdateProvinceCitySuburb)->everyMinute();

/**
 * Prune Telescope data every 72 hours
 */
Schedule::command('telescope:prune --hours=72')->daily();

/**
 * Update properties data every 4 hours
 */
Schedule::job(new UpdatePropertiesData())->everyFourHours();

Schedule::command('app:send-internal-properties-to-user')->hourly();

/**
 * Update province, city and suburb data every month
 */
Schedule::job(new UpdateProvinceCitySuburb())->quarterly();
// Schedule::job(new UpdateProvinceCitySuburb())->everyFiveMinutes();

/**
 * Update subscription status every day
 */
Schedule::job(new UpdateSubscriptionJob())->daily();

/**
 * Send properties to user every day
 */
Schedule::job(new SendPropertiesToUserJob())->hourly();

/**
 * Send properties to user every day
 */
Schedule::job(new SendEmailToAdminUsers())->daily();

// testing command
Schedule::command('app:test-commands')->daily();
