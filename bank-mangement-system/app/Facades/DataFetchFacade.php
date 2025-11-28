<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class DataFetchFacade extends Facade
{
    protected static function getFacadeAccessor()
    {  
        return 'data-fetch-facade';
    }
}

?>