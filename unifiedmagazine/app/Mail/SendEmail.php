<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

	public $subject;
	public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $data)
    {
      $this->subject = $subject;
      $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      return $this->view('layouts.send')->subject($this->subject);
    }
}
