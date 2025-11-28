<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**     * Display a listing of the resource.     */
    public function index(Request $req)
    {
        $perPage = $req->input("per_page", config('constants.per_page', 10));
        $isPaginate = filter_var($req->input('is_paginate', 'true'), FILTER_VALIDATE_BOOLEAN);
        $query = Notification::where('user_id', $req->user()->id)->where('type', '!=', 'chat')->latest();
        if ($isPaginate) {
            $notifications = $query->paginate($perPage);
            return ApiResponse::paginate($notifications, NotificationResource::collection($notifications));
        }
        $notifications = $query->get();
        return ApiResponse::success(NotificationResource::collection($notifications));
    }

    public function markAsRead($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Notification']));
        }
        if ($notification->read_at) {
            $type = 'unread';
            $notification->markAsUnRead();
        } else {
            $type = 'read';
            $notification->markAsRead();
        }
        return ApiResponse::success(new NotificationResource($notification), "Notification $type successfully");
    }

    public function markAllAsRead(Request $req)
    {
        Notification::where('user_id', $req->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return ApiResponse::success([], "All notification readed successfully");
    }

    public function readStatus(Request $req)
    {
        $statusCount = Notification::where('user_id', $req->user()->id)->whereNotIn('type', ['chat'])->whereNull('read_at')->count();
        return response()->json(['status' => ($statusCount > 0) ? true : false]);
    }

    public function destroy($id)
    {
        $notification = Notification::find($id);
        $notification->delete();
        return ApiResponse::success([], "Notification deleted successfully");
    }

    public function destroy_all(Request $req)
    {
        $currentUser = $req->user();
        $notification = Notification::whereUserId($currentUser->id);
        $notification->delete();
        return ApiResponse::success([], "All notification deleted successfully");
    }
}
