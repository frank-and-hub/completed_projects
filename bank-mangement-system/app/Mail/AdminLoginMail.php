<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminLoginMail extends Mailable
{
    use Queueable, SerializesModels;
	public $name = 'Admin';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $array  = array())
    {
	    $this->array = $array;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
         return $this->subject('Login confirmation mail')
                ->markdown('templates.admin.settings.login_email',['bodyMessage' => $this->array]);

	    //return $this->view('templates.admin.settings.login_email',['bodyMessage' => $this->array] );
    }
}
