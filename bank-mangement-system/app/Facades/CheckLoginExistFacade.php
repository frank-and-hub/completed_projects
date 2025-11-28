<?php
namespace App\Facades;
use Illuminate\Support\Facades\Facade;

class CheckLoginExistFacade extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'check-login-exist';
    }

    
}


