<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DataFetchService;

class DataFetchServiceProvider extends ServiceProvider
{
    
     
   
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    { 
        $this->app->bind('data-fetch-facade', function () {
            return new DataFetchService();

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
