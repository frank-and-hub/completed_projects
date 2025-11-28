<?php
namespace App\Listeners;

use App\Events\UserUpdated;
use App\Models\Notification;

class LogUserUpdated
{
    public function handle(UserUpdated $event)
    {
        $changes = $event->user->getChanges(); // Get the updated columns and their new values
        $message = 'User ' . $event->user->first_name . ' updated: ';
        
        foreach ($changes as $field => $newValue) {
            $msg = $event->user->getOriginal($field) ?? 'empty';
            $message .= "$field from $msg to $newValue, ";
        }

        // Remove the trailing comma and space
        $message = rtrim($message, ', ');
        $create = [
            'user_id' => $event->user->id,
            'message' => $message,
        ];
        $notification = Notification::create($create);
    }
}
