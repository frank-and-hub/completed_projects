<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);

        // if (!str_contains(config('app.url'), 'localhost') && !str_contains(config('app.url'), 'http://parkscape.pairroxz.in/')) {
        //     URL::forceScheme('https');
        // }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // Log::error(App::environment('production'));

        if(App::environment('production')) {
            URL::forceScheme('https');
        }


        // Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
