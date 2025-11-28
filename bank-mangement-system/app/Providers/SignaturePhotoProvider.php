<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SignaturePhotoService;

class SignaturePhotoProvider extends ServiceProvider
{
    
     
   
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    { 
        $this->app->bind('signature-photo-facade', function () {
            return new SignaturePhotoService();

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
