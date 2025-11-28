<?php

namespace App\Http\Controllers\adminsubuser\Agency;

use App\Exceptions\InvalidActionException;
use App\Helpers\Helper;
use App\Helpers\ResponseBuilder;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Calendar;
use App\Models\InternalProperty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AgentController extends Controller
{
    function __construct()
    {
        if (!auth()->guard('admin')->user()->hasRole('agency')) {
            throw new InvalidActionException();
        }
    }

    protected $active_page = 'agency_agent';

    public function index(Request $request)
    {
        $active_page = $this->active_page;
        $title = 'Agents';
        if ($request->ajax()) {
            $user = Auth::user();
            $data = $user->agency_agents()
                ->with([
                    'calendars:id,user_id,internal_propertie_id,admin_id,status',
                    'property:id,admin_id,is_agency',
                    'sendInternalPropertyUser:id,user_id,internal_property_id,search_id,admin_id'
                ])
                ->get();

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('name', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row?->image) {
                        $image = Storage::url($row?->image);
                    }
                    $string = ucwords($row?->name);
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string
                            </div>";
                    return $name;
                })
                ->addColumn('email', function ($row) {
                    return $row->email;
                })
                ->addColumn('phone', function ($row) {
                    return $row->dial_code . ' ' . $row->phone;
                })
                ->addColumn('properties_count', function ($row) {
                    $propertiesCount = $row->property_count;
                    $html = "";
                    if ($propertiesCount > 0) {
                        $html .= "<a href='#' class='text-primary _model_table' data-agent_id='$row->id' data-model_title='Properties' data-type='properties_count' data-toggle='tooltip' data-placement='top' data-original-title='View Properties'> $propertiesCount </a>";
                    } else {
                        $html .= "<span class='text-muted'>$propertiesCount</span>";
                    }
                    return $html;
                })
                ->addColumn('tenant_count', function ($row) {
                    $tenantCount = $row->sendInternalPropertyUser()->whereHas('user')
                        ->pluck('user_id')
                        ->unique()
                        ->count() ?? 0;
                    $html = "";
                    if ($tenantCount > 0) {
                        $html .= "<a href='#' class='text-primary _model_table' data-agent_id='$row->id' data-model_title='Tenants' data-type='tenant_count' data-toggle='tooltip' data-placement='top' data-original-title='View Tenants'> $tenantCount </a>";
                    } else {
                        $html .= "<span class='text-muted'>$tenantCount</span>";
                    }
                    return $html;
                })
                ->addColumn('active_requests', function ($row) {
                    $activeRequests = $row?->calendars()
                        ->whereStatus(Calendar::STATUS_ACCEPTED)
                        ->count() ?? 0;
                    $html = "";
                    if ($activeRequests > 0) {
                        $html .= "<a href='#' class='text-primary _model_table' data-agent_id='$row->id' data-model_title='Events' data-type='active_requests' data-toggle='tooltip' data-placement='top' data-original-title='View Requests'> $activeRequests </a>";
                    } else {
                        $html .= "<span class='text-muted'>$activeRequests</span>";
                    }
                    return $html;
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status;
                    $btn = '<div class="actions-container">';
                    if ($status == 0) {
                        $btn .= "<div class='badge badge-outline-danger badge-pill'>Blocked</div>";
                        // $btn .= "<button type = 'button' class='btn  btn-rounded btn-fw'>Blocked</button>";
                    } else {
                        // $btn .= "<button type = 'button' class='btn btn-success btn-rounded btn-fw'>Unblocked</button>";
                        $btn .= "<div class='badge badge-outline-success badge-pill'>Unblocked</div>";
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $checked = ($row->status) ? 'checked' : '';
                    $btn .= '<label class="switch">
                    <input type="checkbox" ' . $checked . ' switch="manual" class="changestatus" data-id = "' . $row->id . '" data-datatable = "AgentTable">
                    <span class="slider round"></span>
                    </label>';
                    $btn .= '<a href="' . route('adminSubUser.agent.view', $row->id) . '" data-toggle="tooltip" data-placement="top" data-original-title="View Details"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="' . route('adminSubUser.agent.edit', $row->id) . '" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-edit edit-icon"></i></a>';
                    $btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-datatable = "adminsubuser-property" data-url = "' . route('adminSubUser.property.delete', $row->id) . '" class="deletemodel"><i class="fa fa-trash delete-icon"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'action', 'created_at', 'status', 'properties_count', 'tenant_count', 'active_requests'])
                ->make(true);
        }

        return view('adminsubuser.agency.agent.index', compact('title', 'active_page'));
    }

    public function create()
    {
        $active_page = $this->active_page;
        $title = 'Add Agent';
        return view('adminsubuser.agency.agent.create', compact('active_page', 'title'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:30',
            'email' => 'required|email|unique:' . Admin::class . ',email',
            'dial_code' => 'required',
            'contact' => [
                'required',
                'numeric',
                'unique:admins,phone',
                'digits_between:7,15',
                'regex:/^\+?[0-9]+$/u',
            ],
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'serverside_error',
                'msg' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $auth = Auth::user();

        $admin = $auth->agency_agents()->create([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->contact,
            'dial_code' => '+' . $request->dial_code,
            'password'  => Hash::make(Helper::defaultPassword($request->password)),
            'password_text' => $request->password,
            'country'   => $auth->country,
            'timeZone'  => $auth->timeZone
        ]);

        $admin->assignRole('agent');
        $credentialData = [
            'agency_name' => $auth->name,
            'name' => $admin->name,
            'email' => $admin->email,
            'type' => 'agent',
            'password' => $admin->password_text,
        ];
        Helper::sendCredentialMail($credentialData);
        WhatsappTemplate::agentWelcomeMessage($admin->dial_code, $admin->phone, $admin->name, $auth->name, $admin->email, $admin->password_text);
        return ResponseBuilder::success(null, 'Successfully! Created Agent.');
    }

    public function edit($id)
    {
        $auth = Auth::user();
        $agent = $auth->agency_agents()->where('id', $id)->first();
        if (!$agent) {
            throw new InvalidActionException('Invalid Action');
        }
        $active_page = $this->active_page;
        $title = 'Agent';

        return view('adminsubuser.agency.agent.edit', compact('title', 'active_page', 'agent'));
    }

    public function update(Request $request, $id)
    {
        $auth = Auth::user();
        $admin = $auth->agency_agents()->where('id', $id)->first();
        if (!$admin) {
            throw new InvalidActionException('Invalid Action');
        }

        $request->merge([
            'contact' => str_replace(' ', '', $request->contact), // Example: removing any spaces from contact
            // 'email' => strtolower($request->email), // Example: converting email to lowercase
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:30',
            'email' => 'required|email|unique:' . Admin::class . ',email,' . $admin->id,
            'dial_code' => 'required',
            'contact' => [
                'required',
                'numeric',
                'unique:admins,phone,' . $admin->id,
                // 'digits_between:7,15',
                'regex:/^\+?[0-9]+$/u',
            ],
            // 'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'serverside_error',
                'msg' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $auth = Auth::user();

        $admin = $auth->agency_agents()->where('id', $id)->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->contact,
            'dial_code' => $request->dial_code,
        ]);

        if ($request->password) {
            $admin = $auth->agency_agents()->where('id', $id)->update([
                'password'  => Hash::make(Helper::defaultPassword($request->password))
            ]);
        }

        return ResponseBuilder::success(null, 'Successfully! Updated Agent.');
    }

    public function status(Request $request)
    {
        try {
            $authUser = Auth::user();
            // return $request->all();
            $dataId = $request->dataId;
            $dataStatus = $request->datastatus;
            $user = $authUser->agency_agents()->find($dataId);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }
            DB::beginTransaction();
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

    public function show(Request $request, $id)
    {
        $admin = Admin::with([
            'agencyRegister.country_',
            'agencyRegister.state_',
            'agencyRegister.city_'
        ])->find($id);

        $role = $admin->roles()->first()?->name ?: '';
        if (!$role) {
            return redirect()->back();
        }

        if (!in_array($role, ['agent', 'agency'])) {
            return redirect()->back();
        }

        $title = ucwords($role == 'agent' ? 'agents' : ($role == 'agency' ? 'agencies' : 'private landlord'));
        $active_page = ($role == 'agency') ? 'agencies' : $role;

        $admin->__created_at = $this->convertToSouthAfricaTime($admin->created_at);

        return view('adminsubuser.agency.agent.view-detail', compact('active_page', 'admin', 'title', 'role'));
    }

    public function agent_property_list(Request $request, $admin_id = null)
    {
        $search = $request->search['value'] ?? null;
        $query = InternalProperty::with(
            'contract',
            'sentProperties',
            'calendars'
        )->select('id', 'title', 'country', 'province', 'suburb', 'town', 'status', 'admin_id', 'created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('country', 'LIKE', '%' . $search . '%')
                    ->orWhere('province', 'LIKE', '%' . $search . '%')
                    ->orWhere('suburb', 'LIKE', '%' . $search . '%')
                    ->orWhere('town', 'LIKE', '%' . $search . '%');
            });
        }

        $is_agency_role = null;
        if ($admin_id) {
            $admin = Admin::findOrFail($admin_id);
            $agency_array_ids = $admin->agency_agents
                ->pluck('id')
                ->toArray();
            $all_ids = array_merge([$admin_id], $agency_array_ids);
            $query->whereIn('admin_id', $all_ids);
            $is_agency_role = $admin->hasRole('agency');
        }

        $data = $query->latest()->get();
        // dd($data->toArray());
        return DataTables::of($data)->addIndexColumn()
            ->addColumn('DT_RowId', function ($row) {
                return $row->id;
            })
            ->addColumn('title', function ($row) {
                return ($row->title);
            })
            ->addColumn('property_address', function ($row) {

                $suburb = $row->suburb ?? '';
                $town = $row->town ?? '';
                $province = $row->province ?? '';
                return ($suburb ? $suburb . ', ' : '') . ($town ? $town . ', ' : '') . $province;
            })
            ->addColumn('active_requests', function ($row) {
                return $row->calendars()?->whereStatus(Calendar::STATUS_ACCEPTED)->count();
            })
            ->addColumn('matched_tenant_count', function ($row) {
                return $row->sentProperties()->count();
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="actions-container">';
                $btn .= '<a href="' . route('adminSubUser.property.view', $row->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                $btn .= '<a href="' . url('/') . '/property-detail?property_id=' . $row->id . '&updateKey=internal" target="_blank" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="Front Panel View"><i class="fa fa-image "></i></a>';

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['name', 'status', 'action', 'created_at', 'property_address', 'matched_tenant_count', 'active_requests'])
            ->make(true);
    }

    public function datatable_model(Request $request)
    {
        $agentId = $request->agent_id;
        $type = $request->type;

        if (!$agentId || !$type) {
            return ResponseBuilder::error('Invalid request', 400);
        }

        $auth = Auth::user();
        $agent = $auth->agency_agents()->find($agentId);
        if (!$agent) {
            return ResponseBuilder::error('Agent not found', 404);
        }

        switch ($type) {
            case 'properties_count':
                $data = $agent?->property()
                    ->select('id', 'title', 'status', 'admin_id', 'created_at')
                    ->get() ?? collect();
                break;
            case 'tenant_count':
                $data = $agent?->sendInternalPropertyUser()->whereHas('user')
                    ->with('user:id,name,status,created_at,phone,country_code')
                    ->get()->pluck('user')->unique('id')
                    ->values() ?? collect();
                break;
            case 'active_requests':
                $data = $agent?->calendars()
                    ->where('status', Calendar::STATUS_ACCEPTED)
                    ->select('id', 'title', 'event_datetime', 'admin_id', 'created_at')
                    ->get() ?? collect();
                break;
            default:
                $data = [];
        }

        return DataTables::of($data)->addIndexColumn()
            ->addColumn('DT_RowId', function ($row) {
                return $row->id;
            })
            ->addColumn('name', function ($row) {
                return $row->name ?? ($row->title ?? 'N/A');
            })
            ->addColumn('phone', function ($row) {
                return $row->phone ?  $row->country_code . ' ' . $row->phone : 'N/A';
            })
            ->addColumn('event_datetime', function ($row) {
                return $row->event_datetime ?? '';
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at ?? '';
            })
            ->addColumn('description', function ($row) {
                return $row->description ?? '';
            })
            ->addColumn('action', function ($row) use ($type) {
                $btn = '<div class="actions-container">';
                switch ($type) {
                    case 'properties_count':
                        $btn .= '<a href="' . route('adminSubUser.property.view', $row->id) . '" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                        break;
                    case 'tenant_count':
                        $btn .= '';
                        // $btn .= '<a href="' . route('adminSubUser.tenant.view', $row->id) . '" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                        break;
                    case 'active_requests':
                        $btn .= '';
                        // $btn .= '<a href="' . route('adminSubUser.event.view', $row ->id) . '" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                        break;
                    default:
                        $btn .= '<span class="text-muted">N/A</span>';
                        break;
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['name', 'action', 'created_at', 'event_datetime'])
            ->make(true);
    }
}
