<?php

namespace App\Services;

use App\Mail\AdminMail;
use App\Mail\AdminOtpMail;
use App\Mail\AdminLoginMail;
use Illuminate\Support\Facades\Mail;

class Email
{
    public function sendEmail( $mailId = null, $message = null )
    {

    	if ( $mailId && $message ) {
		    Mail::to($mailId)->send( new AdminMail($message));
		    return true;
	    } else {
		    return false;
	    }
    }

    public function sendOtpEmail( $mailId = null, $message = null )
    {
		
    	if ( $mailId && $message ) {
		    Mail::to($mailId)->send( new AdminOtpMail($message));
		    return true;
	    } else {
		    return false;
	    }
    }


     public function sendLoginEmail( $mailId = null, $array )
    {

    	if ( $mailId && $array ) {
		    Mail::to($mailId)->send( new AdminLoginMail($array));
		    return true;
	    } else {
		    return false;
	    }
    }
}
