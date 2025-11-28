<?php

namespace App\Http\Controllers;

use App\Jobs\SendSMS;
use Illuminate\Http\Request;
use App\Jobs\SendEmail;
use App\Mail\AdminMail;
use Illuminate\Support\Facades\Mail;
use App\Services\Email;
class JobController extends Controller
{

    public function processQueue()
    {
    	$adminEmail = new Email();
	   $res =  $adminEmail->sendEmail('bajrang@mailinator.com', 'hello welcome');
	    dd( $res );
/*
	    $adminEmail = (new SendEmail())->delay( now()->addSeconds(10) );

	    $res = dispatch( $adminEmail );*/
	    /*dd("Hello123", $res);
	    $email =  new AdminMail('Pankaj Sood');
	   /* $data = array('name'=>"Virat Gandhi");
	   $re =  Mail::send(['text'=>'templates.admin.settings.email'], function($message) {
		    $message->to('admin@mailinator.com', 'Tutorials Point');
	    });
	  */
	   /* dd( Mail::send(['text' => 'templates.admin.settings.email'])->to('admin@mailinator.com') );
        dd ( dispatch(new SendEmail('Pankaj Sood'))->delay( now()->addSeconds(10)) );*/
        echo 'Mail Sent';
    }

    public function sms()
    {
        $contactNumber = array('9694820918');
        $text = 'Dear Ram Welcome to Micro Finance !';
        $numberWithMessage = array();
        $numberWithMessage['contactNumber'] = $contactNumber;
        $numberWithMessage['message'] = $text;
        dd( "TT", new SendSMS( $numberWithMessage ) );
        $smsJob = ( new SendSMS( $numberWithMessage ))->delay( now()->addSeconds(10));
	    $res = dispatch( $smsJob );
        dd( "Test", $smsJob, $res );

        // dispatch(new App\Jobs\SendSMS( $contactNumber, $text ) );

    }
}
