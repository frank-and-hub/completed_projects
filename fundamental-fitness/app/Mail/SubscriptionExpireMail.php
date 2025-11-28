<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpireMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $fullname;
    public $expires_at;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $expiresAt = null)
    {
        $this->user = $user;
        $this->fullname = $user->fullname ?? ($user['fullname'] ?? null);
        $this->expires_at = $expiresAt;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reminder: Your '.config('app.name').' Subscription Is Expired')
                    ->markdown('emails.subscription.success')
                    ->with([
                        'user' => $this->user,
                        'fullname' => $this->fullname,
                        'expires_at' => $this->expires_at ?? now()->format('Y-m-d H:i:s'),
                    ]);
    }
}
