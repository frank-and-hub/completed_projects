<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;

use App\Models\SentInternalPropertyUser;
use App\Models\SentPropertyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    public function listing(Request $request, $adminType)
    {
        $agent_for_agency = '';
        $active_page = strtolower($adminType);
        $title = 'Dashboard';
        switch ($active_page) {
            case 'agency':
                $title = 'Agencies';
                break;
            case 'agent':
                $title = 'Agent';
                $agent_for_agency = $request->agent ?? '';
                break;
            case 'privatelandlord':
                $title = 'Private Landlord';
                break;
            default:
                return redirect()->back();
        }

        if ($request->ajax()) {
            $data = Admin::with(['agencyRegister'])->role($active_page)->latest();

            if ($agent_for_agency) {
                $data = $data->where('admin_id', $agent_for_agency);
            }

            $data = $data->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('name', function ($row) use ($active_page) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row->image()->first()?->path ?: null) {
                        $image = Storage::url($row->image()->first()->path);
                    }
                    $name = "<div class='table_user_profile'>
                                <figure>
                                    <img src='" . $image . "' class='img-fluid' onerror='this.onerror=null; this.src='$image';'>
                                </figure>
                                <figcaption>
                                    <h6>" . (in_array($active_page, ['agency', 'agent']) ? ($row?->agencyRegister?->business_name ?? $row->name) : $row->name) . "</h6>
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
                        $dial_code = (substr($row->dial_code, 0, 1) === '+' ? $row->dial_code : '+' . $row->dial_code);
                        return $dial_code . ' ' . $row->phone . ' ' . $virfy;
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
                ////////////only for agent
                ->addColumn('agency', function ($row) {
                    if ($row->agent_agency?->agencyRegister) {
                        return $a = "<button class='btn btn-link btn-rounded btn-fw'><a href='" . route('admin_user.role_type_view', $row->agent_agency?->id) . "'>" . ucwords($row->agent_agency?->agencyRegister?->business_name) . "</a></button>";
                    }
                    return '';
                })
                ////////////only for agency
                ->addColumn('agent_count', function ($row) {
                    $count = count($row->agency_agents);
                    return $a = "<button class='btn btn-link btn-rounded btn-fw'><a href='" . route('admin_user.role_type_admin_list', ['admin_user' => 'agent', 'agent' => $row->id]) . "'>" . ucwords($count) . "</a></button>";
                })
                ->addColumn('property_count', function ($row) {
                    $property_count = $row->property_count;
                    $a = "<button class='btn btn-link btn-rounded btn-fw'><a href='" . (($property_count > 0) ? route('property.list', [$row->id]) : '#') . "'>" . ucwords($property_count) . "</a></button>";
                    return $a;
                })
                ->addColumn('action', function ($row) use ($active_page) {
                    $btn = '<div class="actions-container">';
                    $checked = ($row->status) ? 'checked' : '';
                    if ($active_page != 'agent') {
                        $btn .= '<label class="switch">
                        <input type="checkbox" ' . $checked . ' switch="manual" class="changestatus" data-id = "' . $row->id . '" data-datatable = "AdminTable">
                        <span class="slider round"></span>
                        </label>';
                    }
                    $btn .= '<a href="' . route('admin_user.role_type_view', $row->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';

                    // if($active_page == 'privatelandlord'){
                    //     $btn .= '<a href="'.route('admin_user.agency.edit', $row->id).'" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Edit" data-datatable = "AdminTable" data-url = "' . route('admin_user.agency.edit', $row->id) . '" class="btn  btn-xs  edit_btn"><i class="fa fa-edit"></i></a>';

                    // } else
                    if ($active_page == 'agency') {
                        $btn .= '<a href="' . route('admin_user.agency.edit', $row->id) . '" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Edit" data-datatable = "AdminTable" data-url = "' . route('admin_user.agency.edit', $row->id) . '" class="btn  btn-xs edit_btn"><i class="fa fa-edit"></i></a>';
                    }

                    if ($row->is_deleteByAdmin()) {
                        $btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-datatable = "AdminTable" data-url = "' . route('admin_user.role_type_delete', $row->id) . '" class="btn  btn-xs deletemodel"><i class="fa fa-trash"></i></a>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'phonenumber', 'status', 'action', 'created_at', 'agency', 'agent_count', 'property_count'])
                ->make(true);
        }
        return view('agencies.index', compact('active_page', 'title'));
    }

    public function admin_user_view($id)
    {
        $admin = Admin::with(['agencyRegister.country_', 'agencyRegister.state_', 'agencyRegister.city_'])->find($id);

        $role = $admin->roles()->first()?->name ?: '';
        if (!$role) {
            return redirect()->back();
        }

        if (!in_array($role, ['agent', 'agency', 'privatelandlord'])) {
            return redirect()->back();
        }

        $title = ucwords($role == 'agent' ? 'agents' : ($role == 'agency' ? 'agencies' : 'private landlord'));
        $active_page = ($role == 'agency') ? 'agencies' : $role;
        $admin->__created_at = $this->convertToSouthAfricaTime($admin->created_at);

        return view('agencies.view', compact('active_page', 'admin', 'title', 'role'));
    }

    public function admin_user_delete(Request $request)
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
                    'msg' => 'Incorrect password',
                ]);
            }
            $dataId = $request->dataId;
            $user = Admin::findOrFail($dataId);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found',
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
                'message' => 'Deletion process encountered an error: ' . $e->getMessage(),
            ]);
        }
    }

    public function change_update(Request $request, $admin_user)
    {
        try {
            DB::beginTransaction();
            $admin_user = strtolower($admin_user);
            $dataId = $request->dataId;
            $dataStatus = $request->datastatus;
            $user = Admin::role($admin_user)->find($dataId);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }
            $user->status = ($dataStatus == 'unblock') ? 1 : 0;
            $user->save();
            // Delete tokens for inactive users
            if ($dataStatus == 'inactive' && $user->tokens()->exists()) {
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

    public function request_verification(Request $request)
    {
        try {
            $dataId = $request->dataId;
            $verification_status = strtolower($request->verification_status);

            // if(!in_array($verification_status, ['accepted', 'cancelled'])){
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Verification type is not valid',
            //     ], 422);
            // }

            $user = Admin::role('agency')->find($dataId);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            DB::beginTransaction();
            $user->request_type = $verification_status;
            $user->save();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'type' => ($verification_status == 'accepted') ? 'accepted' : 'cancelled',
                'msg' => ($verification_status == 'accepted') ? 'Accepted Successfully' : 'Cancelled Successfully',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Update process encountered an error: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function match_property_list(Request $request, $admin_id = null)
    {
        if ($request->ajax()) {
            $sentInternalPropertyUserQuery = SentInternalPropertyUser::with(['property']);
            $sentPropertyUserQuery = SentPropertyUser::with(['property']);

            if ($admin_id) {
                $admin = Admin::findOrFail($admin_id);
                $agency_array_ids = $admin->agency_agents->pluck('id')->toArray();
                $all_ids = array_merge([$admin_id], $agency_array_ids);

                $sentInternalPropertyUserQuery->whereIn('admin_id', $all_ids);
                $sentPropertyUserQuery->whereIn('admin_id', $all_ids);
            }

            $user_search_property_id = $request->user_search_property_id;
            if ($user_search_property_id) {
                $sentInternalPropertyUserQuery->where('search_id', $user_search_property_id);
                $sentPropertyUserQuery->where('search_id', $user_search_property_id);
            }

            $sentInternalPropertyUsers = $sentInternalPropertyUserQuery->latest()->get();
            $sentPropertyUsers = $sentPropertyUserQuery->latest()->get();
            $data = $sentInternalPropertyUsers->merge($sentPropertyUsers);

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('title', function ($row) {
                    return $row->property->title;
                })
                ->addColumn('tenant', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row?->image) {
                        $image = Storage::url($row?->image);
                    }
                    $string = "<a href='" . route('user_view', $row->user_id) . "'>" . ucwords($row?->user?->name) . '</a>';
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string
                            </div>";
                    return $name;
                })
                ->addColumn('agent', function ($row) {
                    if ($row->admin_id == null) {
                        return 'No Agent';
                    }
                    return "<a href='" . route('admin_user.role_type_view', $row?->admin_id) . "'>" . ucwords($row?->admin?->name) . '</a>';
                })
                ->addColumn('property_type', function ($row) {
                    return $row->property->propertyType;
                })
                ->addColumn('property_status', function ($row) {
                    return ucwords($row->property->propertyStatus);
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
                    if ($row->admin_id == null) {
                        $btn .= '<a href="' . route('property.view-external', $row->property->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                        $btn .= '<a href="' . url('/') . '/property-detail?property_id=' . $row->property->id . '&updateKey=external" target="_blank" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="Front Panel View"><i class="fa fa-image "></i></a>';
                    } else {
                        $btn .= '<a href="' . route('property.view', $row->property->id) . '"data-toggle="tooltip" class="btn btn-xs" data-placement="top" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye view-icon"></i></a>';
                        $btn .= '<a href="' . url('/') . '/property-detail?property_id=' . $row->property->id . '&updateKey=internal" target="_blank" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="Front Panel View"><i class="fa fa-image "></i></a>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['tenant', 'agent', 'action', 'created_at'])
                ->make(true);
        }
    }

    public function match_property_view(Request $request, $id)
    {
        $sentInternalPropertyUser = SentInternalPropertyUser::with(['user', 'property', 'admin'])->find($id);
        if (!$sentInternalPropertyUser) {
            return redirect()->back();
        }
        $active_page = 'matchproperty';
        $title = "Matched Property";
        return view('matchproperty.view', compact('sentInternalPropertyUser', 'active_page', 'title'));
    }

    public function subscribe_list(Request $request)
    {
        if ($request->ajax()) {
            $auth = Admin::findOrFail($request->input('user_id'));
            Admin::findOrFail($request->input('user_id'))->isAvailableSubscription();
            $data = $auth->admin_subscription();
            if ($user_search_property_id = $request->user_search_property_id) {
                $data = $data->where('search_id', $user_search_property_id);
            }
            $column = $request->get('columns')[$request->get('order')[0]['column']]['data'];
            $direction = $request->get('order')[0]['dir'];
            $data = $data->orderBy($column, $direction);

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('plan_name', function ($row) {
                    return $row->plan_name;
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount . ' ZAR';
                })
                ->addColumn('status', function ($row) {
                    $btn = '<div class="actions-container">';
                    $status = ['pending' => 'warrning', 'ongoing' => 'success', 'cancelled' => 'danger', 'expired' => 'secondary'];
                    $btn .= "<div class='badge badge-outline-" . $status[$row->status] . " badge-pill'>" . ucwords($row->status) . "</div>";
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('expired_at', function ($row) {
                    return $this->convertToForShow($row->expired_at)->format('d M y');
                })
                ->rawColumns(['expired_at', 'status'])
                ->make(true);
        }
    }
}
