<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminTwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $fullname;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $fullname)
    {
        $this->otp = $otp;
        $this->fullname = $fullname;
    }

    public function build()
    {
        return $this->subject('Your Admin Verification Code')
                    ->view('emails.admin_otp')
                    ->with([
                        'otp' => $this->otp,
                        'fullname' => $this->fullname,
                    ]);
    }
}

