<?php

use App\Http\Middleware\IsLoggedIn;
use App\Http\Middleware\PreventBackHistory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthenticateApi;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'ApiAuthMiddleware' => AuthenticateApi::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'isAdminLogin' => IsLoggedIn::class,
            'prevent-back-history' => PreventBackHistory::class,
            'is_subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);

        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })->withExceptions(function (Exceptions $exceptions) {

        $exceptions->report(function (Throwable $e) {
            if ($e instanceof \RuntimeException) {
                Log::warning('Runtime Exception: ' . $e->getMessage());
            }
        });
        
    })->create();
