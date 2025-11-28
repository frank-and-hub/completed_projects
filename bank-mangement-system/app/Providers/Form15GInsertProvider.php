<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Form15GInsertService;

class Form15GInsertProvider extends ServiceProvider
{
    
     
   
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    { 
        $this->app->bind('form15g-insert-facade', function () {
            return new Form15GInsertService();

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
