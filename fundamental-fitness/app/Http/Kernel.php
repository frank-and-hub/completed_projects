<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // Global middleware
        \App\Http\Middleware\PreventBackHistory::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        'auth' => \App\Http\Middleware\Authenticate::class,
    ];


    protected function schedule(Schedule $schedule)
    {
        // Meal Notifications (IST)
        $schedule->command('app:send-meal-notifications')->dailyAt('08:00');  // 8 AM IST
        $schedule->command('app:send-meal-notifications')->dailyAt('13:00');  // 1 PM IST
        $schedule->command('app:send-meal-notifications')->dailyAt('19:00');  // 7 PM IST

        // Workout Notifications (IST)
        $schedule->command('app:send-workout-notifications')->dailyAt('06:00');  // 6 AM IST

        // Challenges (every minute)
        $schedule->command('app:send-challenges-notifications')->everyMinute();

        $schedule->command('app:clone-weekly-workout-plan')->everyMinute();

    }
}
