<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CommanTransactionService;

class CommanTransactionServiceProvider extends ServiceProvider
{
    
     
   
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    { 
        $this->app->bind('comman-transction-insert', function () {
            return new CommanTransactionService();

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
