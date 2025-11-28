<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        $resetUrl = url('/reset-password') .
        '?token=' . $this->token .
        // '&email=' . urlencode($notifiable->email);
        '&email=' . $notifiable->getEmailForPasswordReset();
 
        return (new MailMessage)
            ->subject('Reset Your Reset Your Password - FitPro360X')
            ->view('emails.reset-password', [
                'name' => $notifiable->fullname,
                'resetUrl' => $resetUrl,
            ]);
    }
    }