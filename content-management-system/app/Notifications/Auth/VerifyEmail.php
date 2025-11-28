<?php

namespace App\Notifications\Auth;

use App\Models\Verification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;


class VerifyEmail extends Notification
{
    use Queueable;

    /**
     * The callback that should be used to create the verify email URL.
     *
     * @var \Closure|null
     */
    public static $createUrlCallback;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private Verification $verification)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // $verificationUrl = $this->verificationUrl($notifiable);

        // if (static::$toMailCallback) {
        //     return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        // }

        $otp = $this->verification->otp;

        return (new MailMessage)->view(
            'Mail.verify_email',
            ['otp' => $otp]
        )
            ->subject(__('Verify Email Address'));
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $otp
     * @param  string  $valid_upto
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage(string $otp, string $valid_upto): MailMessage
    {
        return (new MailMessage)->view(
            'Mail.verify_email',
            ['otp' => $otp]
        )
            ->subject(__('Verify Email Address'));
        // ->line(__('Please find the OTP below to verify your email address.'))
        // ->line($otp)
        // ->line("This OTP is valid for the next 15 minutes only.")
        // // ->action(__('Verify Email Address'), $url)
        // ->line(__('If you did not create an account, no further action is required.'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Set a callback that should be used when creating the email verification URL.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
