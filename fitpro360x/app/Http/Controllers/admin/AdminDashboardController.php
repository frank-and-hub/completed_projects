<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workout_Programs;
use App\Models\BodyType;
use App\Models\Exercise;
use App\Models\FitnessChallenge;
use App\Models\MuscleMaster;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $total_users = User::where('role',2)->count();
        $total_workouts = Workout_Programs::count();
        $total_exercise= Exercise::count();
        $total_fitnessChallenges= FitnessChallenge::count();
        $total_musclemaster = MuscleMaster::count();
        $total_bodytype = BodyType::count();
        return view('admin.dashboard.index',compact('total_users','total_workouts','total_exercise','total_fitnessChallenges','total_musclemaster','total_bodytype'));
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

            $data->status = $status;
            if ($data->save()) {
                return response()->json(['status' => 'success', 'message' => 'Status updated successfully.',]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
}
