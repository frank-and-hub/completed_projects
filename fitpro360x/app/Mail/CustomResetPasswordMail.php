<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomResetPasswordMail extends Mailable
{
    use SerializesModels;

    public $user;
    public $token;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $token
     */
    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $url = 'https://fitnessapp-uat.24livehost.com/app/reset-password/' . $this->token . '?email=' . urlencode($this->user->email);

        return $this->subject('Password Reset Request')
            ->view('emails.resetPassword') // You need to create this view
            ->with([
                'url' => $url,
                'name' => $this->user->fullname,
            ]);
    }
}
