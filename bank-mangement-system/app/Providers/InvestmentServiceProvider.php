<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\InvestmentService;

class InvestmentServiceProvider extends ServiceProvider
{
    
     
   
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    { 
        $this->app->bind('investment-facade', function () {
            return new InvestmentService();

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
