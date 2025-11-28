<?php

// use App\Console\Commands\AddWorkoutPlanCommand;
use App\Console\Commands\{CloneWeeklyWorkoutPlan, SendProgressUpdate, SendWorkoutReminder};
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clone weekly workout plan every hour
Schedule::command(CloneWeeklyWorkoutPlan::class)->hourly();

// Daily workout reminder at 08:00
Schedule::command(SendWorkoutReminder::class)->dailyAt('08:00');

// Weekly progress update every Monday at 18:00
Schedule::command(SendProgressUpdate::class)->weeklyOn(1, '18:00');

// delete all previous notifications
Schedule::command('app:delete-old-notifications')->dailyAt('12:00');
