<?php

namespace App\Http\Controllers\adminsubuser;

use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Admin;
use App\Models\Calendar;
use App\Models\SentInternalPropertyUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CalendarControlller extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::user();
        $data['title'] = 'Calendar';

        $selectedDate = isset($request->selectedDate) ? $request->selectedDate : '';
        $agency_array_ids = $admin->agency_agents->pluck('id')->toArray();
        $all_ids = array_merge([$admin->id], $agency_array_ids);

        if ($request->ajax()) {
            $dataData = Calendar::whereIn('admin_id', $all_ids)
                ->when($selectedDate != '', function ($eventsQuery) use ($selectedDate) {
                    $searchDate = Carbon::parse($selectedDate, 'Africa/Johannesburg')->toDateString();
                    $eventsQuery->whereDate('event_datetime', $searchDate);
                })
                ->get();

            return DataTables::of($dataData)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('tenant', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row->user?->image) {
                        $image = Storage::url($row->user->image);
                    }
                    $string = ucwords($row->user?->name);
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string
                            </div>";
                    return $name;
                })
                ->addColumn('address', function ($row) {

                    $string = ucwords($row?->property?->propertyAddress());
                    return $string;
                })
                ->addColumn('agent', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row->admin?->image) {
                        $image = Storage::url($row->admin->image);
                    }
                    $string = ucwords($row->admin?->name);
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string
                            </div>";
                    return $name;
                })
                ->addColumn('status', function ($row) {
                    return $this->status_btn($row->status, $row->isExpiry());
                })
                ->addColumn('action', function ($row) {
                    return $this->action_btn($row->sent_internal_property_user_id);
                })
                ->rawColumns(['status', 'created_at', 'action', 'tenant', 'date_time', 'agent', 'address'])
                ->make(true);
        }

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
                $agentEvents = $agent->calendars()->with(['property:id,title', 'admin:id,name'])
                    ->where('status', 'accepted')
                    ->whereDate('event_datetime', $today)
                    ->get();
            }
        } else {
            $totalProperties = $admin->property()->count();
            $totalMatchProperties = $admin->sendInternalPropertyUser()->count();
            $agentEvents = $admin->calendars()->with('property:id,title')
                ->where('status', 'accepted')
                ->whereDate('event_datetime', $today)
                ->get();
        }

        $data['role'] = $admin->roles()->first()->name;
        $data['agentEvents'] = $agentEvents;
        return view('adminsubuser.calendar.index', $data);
    }

    public function action_btn($sent_internal_property_user_id)
    {
        $btn = '<div class="actions-container">';
        if ($sent_internal_property_user_id) {
            $btn .= '<a href="' . route('adminSubUser.match-property.view', ['id' => $sent_internal_property_user_id]) . '"data-toggle="tooltip" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
        }
        $btn .= '</div>';
        return $btn;
    }

    public function status_btn($status, $isExpiry)
    {
        $btn = '<div class="actions-container">';

        switch ($status) {
            case 'pending':
                $btn .= "<div class='badge badge-pill badge-light pvr-status-badge " . ($isExpiry ? 'Expired' : 'Pending') . "' style='background:" . ($isExpiry ? '#6c757d' : '#17a2b8') . "'> <div class='d-flex align-item-center justify-content-center'><p class='mb-0 text-white small font-weight-bold'>" . ucwords($status) . "</p> </div>  </div>";
                break;
            case 'accepted':
                $btn .= "<div class=' badge badge-pill badge-light pvr-status-badge' style='background:#0087ff'> <div class='d-flex align-item-center justify-content-center'> <p class='mb-0 text-white small font-weight-bold'>" . ucwords($status) . "</p> </div> </div>";
                break;
            case 'completed':
                $btn .= "<div class=' badge badge-pill badge-light pvr-status-badge' style='background:#04b76b'><div class='d-flex align-item-center justify-content-center'><p class='mb-0 text-white small font-weight-bold'>" . ucwords($status) . "</p> </div></div>";
                break;
            default:
                $btn .= "<div class=' badge badge-pill badge-light pvr-status-badge' style='background:#dc3545'><div class='d-flex align-item-center justify-content-center'><p class='mb-0 text-white small font-weight-bold'>" . ucwords($status) . "</p></div></div>";
                break;
        }

        $btn .= '</div>';

        return $btn;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SentInternalPropertyUser_id' => 'required|array',
            'SentInternalPropertyUser_id.*' => 'exists:' . SentInternalPropertyUser::class . ',id',
            'title' => 'required',
            'date' => 'required',
            'time' => 'required',
            'defaultDate' => 'required',
            'defaultTime' => 'required',
            'd' => 'required',
            't' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            //code...
            foreach ($request->SentInternalPropertyUser_id as $id) {
                $sentInternalPropertyUser = SentInternalPropertyUser::with(['user', 'admin', 'property'])->find($id);
                $tenant = $sentInternalPropertyUser->user;

                if ($tenant->status == 0) {
                    return response()->json([
                        'status' => 'error',
                        'msg' => 'Tenant Not Allowed',
                    ]);
                }

                $agent = $sentInternalPropertyUser->admin;
                $property = $sentInternalPropertyUser->property;

                $data = [
                    'internal_propertie_id' => $sentInternalPropertyUser->internal_property_id,
                    'user_id' => $sentInternalPropertyUser->user_id,
                    'admin_id' => $sentInternalPropertyUser->admin_id,
                    'sent_internal_property_user_id' => $sentInternalPropertyUser->id,
                    'event_datetime' => $request->defaultDate . ' ' . $request->defaultTime,
                    'title' => $request->title,
                    'description' => $request->description,
                    'link' => $request->link,
                ];
                $insert = Calendar::create($data);
                $date = dateF($request->d . ' ' . $request->t);
                $time = date('h:i:a', strtotime($request->t));
                WhatsappTemplate::eventMessage($tenant->country_code, $tenant->phone, $tenant->name, $property->title, $agent->name, $property->propertyAddress(), $date, $time, $property->lng, $property->lat, $insert->id,$property->id);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'msg' => 'Successfully! Scheduled',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Deletion process encountered an error: ' . $th->getMessage(),
            ]);
        }
    }

    public function data(Request $request)
    {
        $admin = Auth::user();
        $filter = $request->input('filter');

        $agency_array_ids = $admin->agency_agents->pluck('id')->toArray();
        $all_ids = array_merge([$admin->id], $agency_array_ids);

        $eventsQuery = Calendar::whereIn('admin_id', $all_ids);
        if ($filter) {
            if ($filter === 'upcoming') {
                $eventsQuery->where('event_datetime', '>=', Carbon::now()->toDateString());
            } elseif ($filter === 'past') {
                $eventsQuery->where('event_datetime', '<', Carbon::now()->toDateString());
            }
        }
        $events = $eventsQuery->get();
        $data = EventResource::collection($events);
        return response()->json($data);
    }

    // pvr
    public function pvr_index(Request $request)
    {
        $admin = Auth::user();
        $previousUrl = url()->previous();
        $now = Carbon::now();
        if (strpos($previousUrl, '/matched-tenants') !== false) {
            $now = Carbon::now()->format('Y-m-d');
        }
        $selectedType = $request->input('selectedType');
        $data['title'] = 'Property Viewing Request';
        $data['active_page'] = 'PropertyViewingRequest';
        $data['role'] = $admin->roles()->first()->name;
        $agency_array_ids = $admin->agency_agents->pluck('id')->toArray();
        $all_ids = array_merge([$admin->id], $agency_array_ids);

        if ($request->ajax()) {
            $dataData = Calendar::whereIn('admin_id', $all_ids)
                ->with('property')
                ->when($selectedType, function ($q) use ($selectedType, $now) {
                    if ($selectedType === 'upcoming') {
                        $q->where('event_datetime', '>', $now);
                    } else if ($selectedType === 'completed') {
                        $q->where('event_datetime', '<', $now);
                    }
                })
                ->when($selectedType === null, function ($q) use ($now) {
                    $q->whereDate('event_datetime', $now);
                })
                ->get();

            return DataTables::of($dataData)
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('property', function ($row) {
                    $image = $demoImage = asset('assets/logo-mini.png');
                    if ($row?->property?->media[0]) {
                        $image = Storage::url(findMainImage($row?->property?->media));
                    }
                    $string = ucwords($row->property?->title);
                    $file = "<div class='text-truncate text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                " . $string . "
                            </div>";
                    return $file;
                })
                ->addColumn('tenant', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row->user?->image) {
                        $image = Storage::url($row->user->image);
                    }
                    $string = ucwords($row->user?->name);
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string
                            </div>";
                    return $name;
                })
                ->addColumn('status', function ($row) {
                    return $this->status_btn($row->status, $row->isExpiry());
                })
                ->addColumn('action', function ($row) {
                    return $this->action_btn($row->sent_internal_property_user_id);
                })
                ->addColumn('property_type', function ($row) {
                    return ucwords($row->property?->propertyType);
                })
                ->rawColumns(['status', 'created_at', 'action', 'tenant', 'event_datetime', 'agent', 'property'])
                ->make(true);
        }

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
                $agentEvents = $agent->calendars()->with(['property:id,title', 'admin:id,name'])
                    ->where('status', 'accepted')
                    ->whereDate('event_datetime', $today)
                    ->get();
            }
        } else {
            $totalProperties = $admin->property()->count();
            $totalMatchProperties = $admin->sendInternalPropertyUser()->count();
            $agentEvents = $admin->calendars()->with('property:id,title')
                ->where('status', 'accepted')
                ->whereDate('event_datetime', $today)
                ->get();
        }

        $data['role'] = $admin->roles()->first()->name;
        $data['agentEvents'] = $agentEvents;

        return view('adminsubuser.pvr.index', $data);
    }

    public function history_dataTable(Request $request)
    {
        $userId = $request->input('id');
        $admin = Auth::user();
        $agency_array_ids = $admin->agency_agents->pluck('id')->toArray();
        $all_ids = array_merge([$admin->id], $agency_array_ids);

        $data = Calendar::whereIn('admin_id', $all_ids)
            ->whereUserId($userId)
            ->with([
                'admin:id,name,phone',
                'user:id,name,phone',
                'property:id,title,financials'
            ])
            ->select('id', 'admin_id', 'user_id', 'event_datetime', 'title', 'description', 'internal_propertie_id', 'status')
            ->latest()
            ->get()
            ->toArray();

        return response()->json([
            'data' => $data,
        ])->header('Content-Type', 'application/json; charset=utf-8');
    }

    public function createNote(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sipu_id' => 'required|exists:' . SentInternalPropertyUser::class . ',id',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {

            $sentInternalPropertyUser = SentInternalPropertyUser::find($request->input('sipu_id'));
            $sentInternalPropertyUser->update([
                'notes' => $request->input('description')
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'msg' => 'Successfully! Scheduled',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Deletion process encountered an error: ' . $th->getMessage(),
            ]);
        }
    }
}
