<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class successfulSignupVerification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
       $resetUrl = url('/successfulsignup') .
        '?token=' . $this->token .
        // '&email=' . urlencode($notifiable->email);
        '&email=' . $notifiable->getEmailForPasswordReset();
 
        return (new MailMessage)
            ->subject('Successful Signup Verification')
            ->view('emails.successfulsignup', [
                'name' => $notifiable->fullname,
                'resetUrl' => $resetUrl,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
     public function toArray($notifiable)
    {
        return $this->data;
    }
}
