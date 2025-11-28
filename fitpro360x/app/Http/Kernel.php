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
            // \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        'auth' => \App\Http\Middleware\Authenticate::class,
    ];

    protected function schedule(Schedule $schedule)
{
    // Meal Notifications (8 AM, 1 PM, 7 PM)
    $schedule->command('app:send-meal-notifications')->dailyAt('08:00');
    $schedule->command('app:send-meal-notifications')->dailyAt('13:00');
    $schedule->command('app:send-meal-notifications')->dailyAt('19:00');

    // Workout Notifications (6 AM)
    $schedule->command('app:send-workout-notifications')->dailyAt('06:00');

    // Challenge Notifications (every minute)
    $schedule->command('app:send-challenges-notifications')->everyMinute();
}
}
