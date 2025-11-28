<?php

namespace App\Http\Controllers\admin;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminMedia;
use App\Models\PropertyRange;
use App\Models\Setting;
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
        $active_page = 'setting';
        $title = 'Settings';
        $property_price_range = PropertyRange::first();
        $setting = Setting::first();
        $admin = Auth::user();
        return view('setting.index', compact('active_page', 'property_price_range', 'admin', 'setting', 'title'));
    }

    public function property_price_update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_price' => 'required|numeric|min:2000',
                'end_price' => 'required|numeric|gt:start_price',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $data = [
                'start_price' => $request->start_price,
                'end_price' => $request->end_price,
            ];
            $price_id = $request->price_id;
            PropertyRange::where('id', $price_id)->update($data);
            return response()->json([
                'status' => 'success',
                'msg' => 'price Update Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something Went Wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function update_admin_credential(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:' . Admin::class . ',email',
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
                'email' => $request->email,
                // 'password' => Hash::make($request->password),
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
                // 'regex:/[A-Z]/',        // at least one uppercase letter
                // 'regex:/[a-z]/',        // at least one lowercase letter
                // 'regex:/[0-9]/',        // at least one digit
                // 'regex:/[@$!%*?&]/',    // at least one special character
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

    public function frontend_setting(Request $request)
    {
        $is_frontend_map = $request->is_frontend_map ?? 0;

        $setting = Setting::first();
        $setting->is_map_show_frontend = $is_frontend_map;
        $setting->save();
        return response()->json([
            'status' => 'success',
            'msg' => 'Successfully! Updated',
        ]);
    }

    public function uploadProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
                function ($attribute, $value, $fail) use ($request) {
                    $image = $request->file('profile_image');
                    $imageSize = getimagesize($image);

                    if ($imageSize === false) {
                        return $fail('Invalid image file.');
                    }
                },
            ],
        ]);

        $validator->sometimes('agent', 'required|string|max:255', function ($input) {
            return $this->auth->hasRole('agency');
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'serverside_error',
                'msg' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $newImage = null;

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
                $filePath = $this->__admin_image($request->file('profile_image'), 'admin/profile-image');
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
