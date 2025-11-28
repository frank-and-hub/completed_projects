<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function fetch($id)
    {
        $notifications = Notification::query()
            ->where('user_id', $id)
            ->where('is_read', false)
            ->orderByDesc('id')
            ->paginate(10);
        return response()->json($notifications);
    }

    public function markAsRead(Request $request)
    {
        Notification::where('id', $request->id)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'Notification marked as read']);
    }
    public function fetchAll()
    {
        $limit = 10;
        $offset = 0;
        $notifications = Notification::orderByDesc('id')
            // ->whereUserId(4137)
            ->limit($limit)
            ->offset($offset)
            ->get();
        $url = [];
        foreach ($notifications as $k => $v) {
            $scholershipId = getUserScholershipId($v->user_id);
            $route = route('admin.applicantDetails', [$v->user_id, $scholershipId ?? null]);
            if ($scholershipId) {
                $url[$k] = $route;
            }
        }
        $data = [
            "notifications" => $notifications,
            "url" => $url
        ];
        return response()->json($data);
    }
}
