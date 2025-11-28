<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class InvestmentFacade extends Facade
{
    protected static function getFacadeAccessor()
    {  
        return 'investment-facade';
    }
}

?>