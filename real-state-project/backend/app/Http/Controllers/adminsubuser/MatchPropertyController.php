<?php

namespace App\Http\Controllers\adminsubuser;

use App\Http\Controllers\Controller;
use App\Models\CreditReport;
use App\Models\InternalProperty;
use App\Models\SentInternalPropertyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class MatchPropertyController extends Controller
{
    private $active_page = "match-property";
    private $is_s3bucket = false;

    public function index(Request $request)
    {
        $title = 'Matched Tenants';
        $admin = Auth()->user();
        $dataTable = [];
        $agency_array_ids = $admin->agency_agents->pluck('id')->toArray();
        $all_ids = array_merge([$admin->id], $agency_array_ids);

        $properties = InternalProperty::whereIn('admin_id', $all_ids)->where('status', 1)->pluck('title', 'id');

        if ($request->ajax()) {
            $dataTable = SentInternalPropertyUser::with(['property'])->whereIn('admin_id', $all_ids)->latest()->get();

            $is_agency_role = $admin->hasRole('agency');

            return DataTables::of($dataTable)->addIndexColumn()
                // ->addColumn('s_no', function ($row) {
                //     return $row->id;
                // })
                ->addColumn('title', function ($row) {
                    return $row?->property?->title;
                })
                ->addColumn('tenant', function ($row) {
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($row->user?->image) {
                        $image = Storage::url($row->user->image);
                    }
                    $string = "<a href='" . route('user_view', $row->user_id) . "'>" . ucwords($row->user->name) . '</a>';
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string
                            </div>";
                    return $name;
                })
                ->addColumn('agent', function ($row) use ($is_agency_role, $agency_array_ids) {
                    return $is_agency_role ? (in_array($row->admin_id, $agency_array_ids) ? ("<a href='" . route('admin_user.role_type_view', $row->admin_id) . "'>" . ucwords($row->admin->name) . '</a>') : '-') : '-';
                })
                ->addColumn('property_type', function ($row) {
                    return $row?->property?->propertyType;
                })
                ->addColumn('property_status', function ($row) {
                    return ucwords($row?->property?->propertyStatus);
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status;
                    $btn = '<div class="actions-container">';
                    if ($status == 0) {
                        $btn .= "<button type = 'button' class='btn btn-rounded btn-fw'>Blocked</button>";
                    } else {
                        $btn .= "<button type = 'button' class='btn btn-success btn-rounded btn-fw'>Unblocked</button>";
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . route('adminSubUser.match-property.view', $row->id) . '"  data-toggle="tooltip" class="btn btn-xs" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    if (!auth()->guard('admin')->user()->hasRole('agency')) {
                        $btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" class="eventschedulemodel btn calendar-link" data-toggle="tooltip"  class="btn btn-xs" data-placement="top" data-original-title="Create event"><i class="fa fa-calendar"></i></a>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('created_at', function ($row) {
                    $input = "";
                    if (!auth()->guard('admin')->user()->hasRole('agency')) {
                        // $input .= "<input type='checkbox' name='properties_[]' data-id='" . $row->id . "' id='properties_" . $row->id . "' />";
                    }
                    $input .= "<label for='properties_" . $row->id . "' >" . $this->convertToSouthAfricaTime($row->created_at) . "</label>";
                    return $input;
                })
                ->rawColumns(['tenant', 'agent', 'action', 'created_at'])
                ->make(true);
        }

        $role = $admin->roles()->first()?->name ?: '';
        return view('adminsubuser.match-property.index2', compact('role', 'title', 'dataTable', 'properties'));
    }

    public function view(Request $request, $id)
    {
        $admin = auth()->user();
        $sentInternalPropertyUser = SentInternalPropertyUser::with(['user', 'property', 'admin'])->find($id);
        if (!$sentInternalPropertyUser) {
            return redirect()->back();
        }
        $active_page = $this->active_page;
        $title = "Matched Tenants";

        $role = $admin->roles()->first()->name;
        return view('adminsubuser.match-property.view', compact('sentInternalPropertyUser', 'active_page', 'title', 'role'));
    }

    public function dataTable(Request $request)
    {
        $admin = Auth()->user();
        $search = $request->input('search');
        $propertyId = $request->input('property');

        if ($search && !mb_check_encoding($search, 'UTF-8')) {
            return response()->json(['error' => 'Invalid search query encoding'], 400);
        }

        $agency_array_ids = $admin?->agency_agents->pluck('id')->toArray();
        $all_ids = array_merge([$admin->id], $agency_array_ids);

        $dataTable = User::with([
            'sentInternalProperties',
            'credit_report:id,user_id,credit_report_pdf,first_name,last_name,email,phone_number,date_of_birth,marital_status',
            'sentInternalProperties:id,user_id,internal_property_id,search_id,admin_id,credit_reports_status,notes',
            'sentInternalProperties.admin:id,name,phone',
            'sentInternalProperties.property:id,title,financials,propertyType,suburb,town,province,country',
            'sentInternalProperties.property.media:id,isMain,path,internal_property_id',
            'calendar2:id,user_id,event_datetime,internal_propertie_id,admin_id',
            'user_employment:id,user_id,emplyee_type,live_with'
        ])
        ->when($propertyId, function ($query) use ($propertyId) {
            $query->with([
                'sentInternalProperties' => function ($query) use ($propertyId) {
                    $query->where('internal_property_id', $propertyId);
                },
                'calendar2' => function ($query) use ($propertyId) {
                    $query->where('internal_propertie_id', $propertyId);
                }
            ]);
        })
        ->whereHas('sentInternalProperties', function ($que) use ($all_ids) {
            $que->whereIn('admin_id', $all_ids);
        })
            ->select('id', 'name', 'phone', 'image', 'country_code', 'email', 'country', 'status');

        if ($search) {
            $dataTable = $dataTable->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('country', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('sentInternalProperties', function ($query) use ($search) {
                        $query->whereHas('property', function ($query) use ($search) {
                            $query->where('title', 'LIKE', '%' . $search . '%');
                        })->orWhereHas('admin', function ($query) use ($search) {
                            $query->where('name', 'LIKE', '%' . $search . '%');
                        });
                    });
            });
        }

        if ($propertyId) {
            $dataTable = $dataTable->where(function ($q) use ($propertyId) {
                $q->whereHas('sentInternalProperties', function ($query) use ($propertyId) {
                    $query->where('internal_property_id', $propertyId);
                });
            });
        }

        $data = $dataTable
            ->latest()
            ->get()
            ->toArray();

        return response()->json([
            'data' => $data,
        ])->header('Content-Type', 'application/json; charset=utf-8');
    }
}
