<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminOtpMail extends Mailable
{
    use Queueable, SerializesModels;
	public $name = 'Admin';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $message )
    {
	    $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->subject('OTP confirmation mail')
                ->markdown('templates.admin.settings.otp_email',['bodyMessage' => $this->message]);

	   // return $this->view('templates.admin.settings.otp_email',['bodyMessage' => $this->message] );
    }
}
