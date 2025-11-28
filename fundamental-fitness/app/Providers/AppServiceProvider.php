<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        View::composer('*', function ($view) {
            $view->with([
                'currentUserInfo'   => auth()->user(),
            ]);
        });

        RateLimiter::for('otp_limit', function (Request $request) {          
            return Limit::perMinute(40, 5)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many OTP requests. Please try again later.'
                ], 429);
            });
        });
        
        DB::listen(function($query) {
            Log::info(
                $query->sql,
                [
                    'bindings' => $query->bindings,
                    'time' => $query->time
                ]
            );
        });

        DB::listen(function ($query) {
            $sql = $query->sql;
            $time = $query->time;
            foreach ($query->bindings as $binding) {
                $value = is_numeric($binding) ? $binding : "'{$binding}'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            Log::channel('sqllog')->info("\n SQL: {$sql} \n At Time: {$time}ms \n");
        });
    }
}
