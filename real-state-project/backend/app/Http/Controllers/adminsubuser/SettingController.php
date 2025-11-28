<?php

namespace App\Http\Controllers\adminsubuser;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminMedia;
use App\Models\AdminScheduleTime;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    protected $auth;
    public function __construct()
    {
        $this->auth = auth()->user();
    }

    public function index()
    {
        $title = 'Settings';
        $active_page = 'setting';
        $admin = Auth::user();
        $role = $admin->getRoleNames()->first();
        $is_slot = $admin->adminScheduleTime()->first();
        $start_time = $is_slot?->start_time ? Carbon::parse($is_slot?->start_time, 'Africa/Johannesburg')->format('h:i A') : '';
        $end_time = $is_slot?->start_time ? Carbon::parse($is_slot?->end_time, 'Africa/Johannesburg')->format('h:i A') : '';
        $role = $admin?->getRoleNames()->first() ?: '';
        $agentEvents = [];
        $today = Carbon::now()->toDateString();
        if ($role == 'agency') {
            $agents = Admin::where('admin_id', $admin->id)->get();
            $totalProperties = 0;
            $totalMatchProperties = 0;
            foreach ($agents as $agent) {
                $totalProperties += $agent->property()->count();
                $totalMatchProperties += $agent->sendInternalPropertyUser()->count();
            }
        } else {
            $totalProperties = $admin->property()->count();
            $totalMatchProperties = $admin->sendInternalPropertyUser()->count();
        }
        $data = [
            'totalProperty' => $totalProperties,
            'totalMatchProperties' => $totalMatchProperties,
        ];
        return view('adminsubuser.setting.index', compact('active_page', 'admin', 'is_slot', 'start_time', 'end_time', 'role', 'title', 'data'));
    }

    public function update_admin_credential(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $user = Auth::user();
            $data = [
                'name' => $request->name,
            ];
            $id = $user->id;
            Admin::where('id', $id)->update($data);
            return response()->json([
                'status' => 'success',
                'msg' => 'Credential Updated Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something Went Wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_pass' => 'required',
            'new_pass' => [
                'required',
                'string',
                'min:8',
            ],
            'con_pass' => 'required|same:new_pass',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'serverside_error',
                'msg' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (!Hash::check($request->old_pass, $user->password)) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Incorrect old password',
            ]);
        }
        $user->password = Hash::make($request->new_pass);
        $user->password_text = $request->new_pass;
        $user->save();
        return response()->json([
            'status' => 'success',
            'msg' => 'Password Updated Successfully',
        ]);
    }

    public function whatsApp_notification(Request $request)
    {
        $admin = Admin::findOrFail($request->user()->id);

        DB::beginTransaction();
        $admin->is_whatsapp_notification = $request->status_whatsweb ?? false;
        $admin->save();

        DB::commit();
        return response()->json([
            'status' => 'success',
            'msg' => 'Successfully Updated',
        ]);
    }

    public function uploadProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|max:2048|image'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'serverside_error',
                'msg' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $newImage = null;
        $auth = Auth::user();
        try {
            DB::beginTransaction();
            $admin = Admin::find($this->auth->id);
            if (!$admin) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->hasFile('profile_image')) {
                $filePath = $this->__admin_image($request->file('profile_image'), 'admin/' . $auth->user_role);
                $imageData = [
                    'admin_id' => $this->auth->id,
                    'type' => 'image',
                    'path' => $filePath,
                ];
                $newImage = AdminMedia::create($imageData);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'msg' => 'Updated Successfully',
                'path' => Storage::exists($newImage->path) ? Storage::url($newImage->path) : null,
            ]);

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
