<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Facades\CheckLoginExist;

class CheckLoginExistProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('check-login-exist',function(){
            return new CheckLoginExist();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
