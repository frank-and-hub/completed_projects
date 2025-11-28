<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\DeleteOldNotifications;
use App\Console\Commands\subscriptionExpiryCheckForUsers;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::command('app:send-meal-notifications')->dailyAt('08:00');
Schedule::command('app:send-meal-notifications')->dailyAt('13:00');
Schedule::command('app:send-meal-notifications')->dailyAt('19:00');

Schedule::command('app:send-workout-notifications')->dailyAt('06:00');

Schedule::command('app:send-challenges-notifications')->everyMinute();
