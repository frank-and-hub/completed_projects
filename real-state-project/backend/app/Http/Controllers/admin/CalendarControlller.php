<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Admin;
use App\Models\Calendar;
use App\Models\InternalProperty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class CalendarControlller extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = 'Calendar';
        $admin = Auth::user();

        if ($request->ajax()) {
            $agency_array_ids = $admin->agency_agents->pluck('id')->toArray();
            $all_ids = array_merge([$admin->id], $agency_array_ids);
            $selectedDate = isset($request->selectedDate) ? $request->selectedDate : '';

            $data = Calendar::when($selectedDate != '', function ($eventsQuery) use ($selectedDate) {
                $searchDate = Carbon::parse($selectedDate, 'Africa/Johannesburg')->toDateString();
                $eventsQuery->whereDate('event_datetime', $searchDate);
            })
                ->latest();
            if ($sent_internal_property_user_id = $request->sent_internal_property_user_id) {
                $data = $data->where('sent_internal_property_user_id', $sent_internal_property_user_id);
            }

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                // ->addColumn('event_datetime', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->event_datetime);
                // })
                ->addColumn('tenant', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row->user?->image) {
                        $image = Storage::url($row->user->image);
                    }
                    $string = ucwords($row->user?->name);
                    $url = "<a href='" . route('user_view', $row->user_id) . "'>" . $string . "</a>";
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $url
                            </div>";
                    return $name;
                })
                ->addColumn('agent', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row->admin?->image) {
                        $image = Storage::url($row->admin->image);
                    }
                    $string = ucwords($row->admin?->name);
                    $url = "<a href='" . route('admin_user.role_type_view', $row->admin_id) . "'>" . $string . "</a>";
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $url
                            </div>";
                    return $name;
                })
                ->addColumn('address', function ($row) {
                    $string = ucwords($row?->property?->propertyAddress());
                    return $string;
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status;
                    $btn = '<div class="actions-container">';
                    if ($status == 'pending') {
                        if ($row->isExpiry()) {
                            $btn .= "<button type = 'button' class='btn btn-xs btn-rounded btn-fw'>" . ucwords($status) . "</button>";
                        } else {
                            $btn .= "<button type = 'button' class='btn btn-warning btn-rounded btn-fw'>Expired</button>";
                        }
                    } elseif ($status == 'completed') {
                        $btn .= "<button type = 'button' class='btn btn-success btn-rounded btn-fw'>" . ucwords($status) . "</button>";
                    } else {
                        $btn .= "<button type = 'button' class='btn  btn-rounded btn-fw'>" . ucwords($status) . "</button>";
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-datatable = "adminsubuser-property" data-url = "' . route('adminSubUser.property.delete', $row->id) . '" class="btn  btn-xs  deletemodel "><i class="fa fa-trash"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->rawColumns(['status', 'created_at', 'tenant', 'agent', 'event_datetime'])
                ->make(true);
        }

        $today = Carbon::now()->toDateString();

        $everyEvents = Calendar::with(['property:id,title', 'admin:id,name'])
            ->where('status', 'accepted')
            ->whereDate('event_datetime', $today)
            ->get();

        $data['agentEvents'] = $everyEvents;

        return view('calendar.index', $data);
    }

    public function data(Request $request)
    {
        $admins = Admin::all();
        $filter = $request->input('filter');
        $events = collect();
        foreach ($admins as $admin) {
            $eventsQuery = $admin->calendars();

            if ($filter) {
                if ($filter === 'upcoming') {
                    $eventsQuery->where('event_datetime', '>=', Carbon::now()->toDateString());
                } elseif ($filter === 'past') {
                    $eventsQuery->where('event_datetime', '<', Carbon::now()->toDateString());
                }
            }

            $adminEvents = $eventsQuery->get();
            $events = $events->merge($adminEvents);
        }

        $data = EventResource::collection($events);

        return response()->json($data);
    }
}
