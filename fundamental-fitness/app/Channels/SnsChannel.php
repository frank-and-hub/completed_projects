<?php
namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\SnsNotificationService;

class SnsChannel
{
    protected SnsNotificationService $snsService;

    public function __construct(SnsNotificationService $snsService)
    {
        $this->snsService = $snsService;
    }

    /**
     * Send the notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        // Get phone number or device token from the notifiable model
        $phoneNumber = $notifiable->phone_number ?? null;
        $deviceToken = $notifiable->device_id ?? null;

        // Get message from notification (with a fallback message)
        $message = method_exists($notification, 'toSns') 
            ? $notification->toSns($notifiable) 
            : 'You have a new notification.';

        // Ensure a message is available
        if (empty($message)) {
            return ['success' => false, 'error' => 'Notification message is empty.'];
        }

        try {
            if ($phoneNumber) {
                return $this->snsService->sendSms($phoneNumber, $message);
            }

            if ($deviceToken) {
                return $this->snsService->sendPushNotification($deviceToken, $message);
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return ['success' => false, 'error' => 'No valid recipient found.'];
    }
}
