<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSearchProperty;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $active_page = 'users';
        $title = 'Users';
        if ($request->ajax()) {
            // $data = User::where('type', '!=', 'admin')->latest()->get();
            $data = User::with('otpVerification')->where('type', '!=', 'admin')->latest()->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('name', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row->image) {
                        $image = Storage::url($row->image);
                    }
                    $name = "<div class='table_user_profile'>
                                <figure>
                                    <img src='" . $image . "' class='img-fluid' onerror='this.onerror=null; this.src='$image';>
                                </figure>
                                <figcaption>
                                    <h6>" . $row->name . "</h6>
                                    <p class='text-lowercase'>" . $row->email . "</p>
                                </figcaption>
                            </div>";
                    return $name;
                })
                ->addColumn('phonenumber', function ($row) {
                    if ($row->phone == null) {
                        return 'N/A';
                    } else {
                        $virfy = '';
                        if ($row->otpVerification && $row->otpVerification->otp_verified_at) {
                            $virfy = '<span style="font-size:22px;color:#25d366;position: relative"><i class="fab fa-whatsapp" aria-hidden="true"></i><span>';
                        }
                        return $row->country_code . ' ' . $row->phone . ' ' . $virfy;
                    }
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status;
                    $btn = '<div class="actions-container">';
                    if ($status == 0) {
                        $btn .= "<button type = 'button' class='btn  btn-rounded btn-fw'>Blocked</button>";
                    } else {
                        $btn .= "<button type = 'button' class='btn btn-success btn-rounded btn-fw'>Unblocked</button>";
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $checked = ($row->status) ? 'checked' : '';
                    $btn .= '<label class="switch ml-2 mt-1">
                    <input type="checkbox" ' . $checked . ' switch="manual" class="changestatus" data-id = "' . $row->id . '" data-datatable = "UserTable">
                    <span class="slider round"></span>
                    </label>';
                    $btn .= '<a href="' . route('user_view', $row->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    // $btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-datatable = "UserTable" data-url = "' . route('user_delete') . '" class="btn  btn-xs  delete_btn "><i class="fa fa-trash"></i></a>';
                    $btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-datatable = "UserTable" data-url = "' . route('user_delete') . '" class="btn  btn-xs  deletemodel "><i class="fa fa-trash"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->rawColumns(['name', 'phonenumber', 'status', 'action', 'created_at'])
                ->make(true);
        }
        return view('user.index', compact('active_page', 'title'));
    }

    public function change_update(Request $request)
    {
        try {
            DB::beginTransaction();

            $dataId = $request->dataId;
            $dataStatus = $request->datastatus;
            $user = User::find($dataId);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }
            $user->status = ($dataStatus == 'unblock') ? 1 : 0;
            $user->save();
            // Delete tokens for inactive users
            if ($dataStatus == 'block' && $user->tokens()->exists()) {
                $user->tokens()->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'type' => ($dataStatus == 'unblock') ? 'unblock' : 'block',
                'msg' => ($dataStatus == 'unblock') ? 'Unblocked Successfully' : 'Blocked Successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Update process encountered an error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function user_delete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $user = Auth::user();
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'msg' =>  'Incorrect password',
                ]);
            }
            $dataId = $request->dataId;
            $user = User::find($dataId);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found'
                ]);
            }
            $user->otpVerification()->delete();
            $user->delete();
            return response()->json([
                'status' => 'success',
                'msg' => 'Deleted Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Deletion process encountered an error: ' . $e->getMessage()
            ]);
        }
    }
    public function view(Request $request, $id)
    {
        $active_page = 'users';
        $title = 'Users Details';
        $user = User::findOrFail($id);
        $user->__created_at = $this->convertToSouthAfricaTime($user->created_at);

        return view('user.view', compact('active_page', 'user', 'title'));
    }

    public function user_subscription(Request $request, $id)
    {
        $active_page = 'users';
        $title = 'Users Details';
        if ($request->ajax()) {
            $data = UserSubscription::with('plan')->where('user_id', $id)->latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('subs_name', function ($row) {
                    return $row->plan->plan_name;
                })
                ->addColumn('started_at', function ($row) {
                    return $this->convertToSouthAfricaTime($row->started_at);
                })
                ->addColumn('expired_at', function ($row) {
                    return $this->convertToSouthAfricaTime($row->expired_at);
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 'ongoing') {
                        $class = 'primary';
                        $text = 'Ongoing';
                    } elseif ($row->status == 'expired') {
                        $class = 'danger';
                        $text = 'Expired';
                    } elseif ($row->status == 'pending') {
                        $class = 'info';
                        $text = 'Pending';
                    } else {
                        $class = 'warning';
                        $text = 'Cancelled';
                    }

                    return '<button type="button" class="btn btn-' . $class . ' btn-rounded btn-fw">' . $text . '</button>';
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        return view('user.view', compact('active_page', 'user', 'title'));
    }
    public function user_property_request(Request $request, $id)
    {
        $title = 'Users Details';
        $active_page = 'users';
        if ($request->ajax()) {
            $data = UserSearchProperty::where('user_id', $id)->latest()->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . route('submitted_property_view', $row->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['created_at', 'user_name', 'action'])
                ->make(true);
        }
        return view('user.view', compact('active_page', 'user', 'title'));
    }
}
