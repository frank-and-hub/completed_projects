<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuccessfulSignupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $fullname;

    public function __construct($user)
    {
        $this->user = $user;
        //fullname from user model
        $this->fullname = isset($user->fullname) ? $user->fullname : (isset($user['fullname']) ? $user['fullname'] : null);
    }

    public function build()
    {
        return $this->subject('Welcome to ' . config('app.name') . ' - Let’s Get Started!')
            ->markdown('emails.signup.success')
            ->with(['user' => $this->user]);
    }
}
