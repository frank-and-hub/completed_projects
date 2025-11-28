<?php

namespace App\Services;

use App\Mail\test;
use Illuminate\Support\Facades\Mail;

class testEmail
{
    public function sendEmail( $mailId = null, $message = null )
    {

    	if ( $mailId && $message ) {
		    Mail::to($mailId)->send( new test($message));
		    return true;
	    } else {
		    return false;
	    }
    }
}