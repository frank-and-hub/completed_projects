<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\api\v1\BaseApiController;
// log
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseApiController
{
    /**
     * Send a notification to the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // send notifications to user
    public function sendnotifications(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'deviceToken' => 'required|string',
            'type'        => 'required|string',
            'message'     => 'required|string',
            'workout_id' => 'nullable|integer',
            'challenge_id' => 'nullable|integer',
            'meal_id'     => 'nullable|integer',

        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }
        if (!$user->notifications_enabled) {
            return $this->sendError('Notifications are disabled for this user.', [], 403);
        }
        Log::info("Sending notification to user {$user->id}");
        notifyUser([
            'deviceToken' => $request->deviceToken,
            'type'        => $request->type,
            'message'     => $request->message,
            'id'          => $request->id ?? null,
            'item'        => $request->item ?? null,
            'user_id'     => $request->user_id ?? null,
            'meal_id'     => $request->meal_id ?? null,
            'workout_id'  => $request->workout_id ?? null,
            'challenge_id' => $request->challenge_id ?? null,

        ]);

        // pree($request->all());
        return $this->sendResponse(null, "Notification sent and logged successfully.");
    }

    //get all notifications
    public function getNotifications(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $notifications = $user->notifications()->latest()->paginate(10);

        foreach ($notifications as $notification) {
            if (is_null($notification->read_at)) {
                $notification->markAsRead(); // only marks current page
            }
        }

        return $this->sendResponse($notifications, "Notifications retrieved successfully.");
    }


    //delete a specific notification
    public function deleteNotification(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $notification = $user->notifications()->find($request->notification_id);
        if (!$notification) {
            return $this->sendError('Notification not found', [], 404);
        }

        $notification->delete();

        return $this->sendResponse(null, "Notification deleted successfully.");
    }

    // get notification count
    public function getNotificationCount(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }
        $count = $user->unreadNotifications()->count();
        // pree($count);
        // also return  notification_enabled status
        $notificationsEnabled = $user->notifications_enabled ? true : false;
        return $this->sendResponse(['count' => $count, 'notifications_enabled' => $notificationsEnabled], "Notification count retrieved successfully.");
    }
    // delete all notifications
    public function deleteAllNotifications(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }
        $user->notifications()->delete(); // Delete all notifications for the user

        return $this->sendResponse(null, "All notifications deleted successfully.");
    }

    //for toggling notifications on off
    public function toggleNotifications(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }
        $validator = Validator::make($request->all(), [
            'notifications_enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', null);
        }
        // Update the user's notification settings
        $user->notifications_enabled = $request->notifications_enabled;
        $user->save();

        $message = $user->notifications_enabled ? "Notifications enabled successfully." : "Notifications disabled successfully.";
        return $this->sendResponse(['notifications_enabled' => $user->notifications_enabled], $message);
    }
}
