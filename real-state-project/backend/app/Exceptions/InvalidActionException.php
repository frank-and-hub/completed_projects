<?php

namespace App\Exceptions;

use App\Helpers\ResponseBuilder;
use Exception;

class InvalidActionException extends Exception
{
    public function render($request)
    {
        // Custom error handling logic
        if($request->ajax()){
            return ResponseBuilder::error("Invalid Action", 500);
        }

        return redirect()->route('adminSubUser.dashboard')->with('error', 'Invalid Action');
    }
}
