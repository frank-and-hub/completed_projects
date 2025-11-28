<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class  SignaturePhotoFacade extends Facade
{
    protected static function getFacadeAccessor()
    {  
        return 'signature-photo-facade';
    }
}

?>