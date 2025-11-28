<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\WatchedVideos;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use App\Models\{SubscriptionPlan, User, Workout};

class AdminDashboardController extends Controller
{
    use Common_trait;

    public function index()
    {
        $firstSubscription = SubscriptionPlan::whereStatus('1')->first()?->price;
        $total_subscription_revenue = User::withoutTrashed()->whereIsSubscribe(1)->count('id') * $firstSubscription;
        $total_exercises = Workout::count();
        $total_users = User::withoutTrashed()->whereRole(2)->count();
        $total_exercise_video_views = WatchedVideos::sum('video_count');
        $total_weekly_workout_completion_rate = $this->getTotalWeeklyWorkoutCompletionRate();
        return view('admin.dashboard.index', compact('total_subscription_revenue', 'total_exercises', 'total_users', 'total_exercise_video_views', 'total_weekly_workout_completion_rate'));
    }

    public function updateStatus(Request $request)
    {
        $input = $request->all();
        $id     = $input['id'];
        $model  = $input['model'];
        $status = $input['status'];
        
        try {
            $modelClass = "App\\Models\\" . $model;

            if (!class_exists($modelClass)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
            };
            $data = $modelClass::find($id);

            if (!$data) {
                return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
            }

            if ($status == 0 && $model == 'User') {
                $data->last_token_id = null;
            }

            $data->status = $status;
            if ($data->save()) {
                return response()->json(['status' => 'success', 'message' => 'Status updated successfully.',]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
}
