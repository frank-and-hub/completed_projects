<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = route('password.reset.form') .
            '?token=' . $this->token .
            '&email=' . $notifiable->getEmailForPasswordReset();

        return (new MailMessage)
            ->subject('Reset Your Reset Your Password - ' . config('app.name'))
            ->view('emails.reset-password', [
                'name' => $notifiable->fullname,
                'resetUrl' => $resetUrl,
            ]);
    }
}
