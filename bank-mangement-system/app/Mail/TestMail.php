<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;
	public $name = 'Test';

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
	    return $this->view('templates.admin.test',['bodyMessage' => $this->message] );
    }
}
