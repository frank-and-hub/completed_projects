<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ConstantsServiceProvider extends ServiceProvider
{
    
     
   
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    { 
         $configPath  = config_path('constants.php');
         $this->mergeConfigFrom($configPath,'constants');
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
