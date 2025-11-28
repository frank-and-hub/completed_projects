<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Property as HelpersProperty;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\InternalProperty;
use App\Models\Property;
use App\Models\PropertyClientOffice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PropertyController extends Controller
{
    private $active_page = 'property';
    public function index(Request $request)
    {
        // $active_page = 'Client office';
        $title = 'Property';
        $search = $request->search['value'] ?? '';
        if ($request->ajax()) {
            $data = PropertyClientOffice::withCount([
                'properties' => function ($query) {
                    $query->where('propertyStatus', '!=', 'Inactive');
                }
            ])->when($search != '', function ($que) use ($search) {
                $que->where('name', 'like', "%" . $search . "%");
            })->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('logo', function ($row) {
                    return "<img src='" . $row->logo . "' onerror='this.onerror=null; this.src='$row->logo';'>";
                })
                ->addColumn('total_count', function ($row) {
                    // Access the 'properties_count' that was added by withCount
                    return $row->properties_count;
                })
                ->rawColumns(['name', 'total_count', 'logo'])
                ->make(true);
        }
        return view('api_property.index', compact('title'));
    }

    public function property_list(Request $request, $admin_id = null)
    {
        $title = 'Properties';
        $search = $request->search['value'] ?? null;
        $dataTable = [];
        if ($request->ajax()) {
            // $data = User::where('type', '!=', 'admin')->latest()->get();
            $data = InternalProperty::with('contract', 'sentProperties');

            if ($search) {
                $data = $data->where(function ($q) use ($search) {
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
                $data = $data->whereIn('admin_id', $all_ids);
                $is_agency_role = $admin->hasRole('agency');
            }

            $data = $data->latest()->get();

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('title', function ($row) {

                    return ($row->title);
                })
                ->addColumn('agent', function ($row) {
                    return "<a href='" . route('admin_user.role_type_view', $row->admin_id) . "'>" . ucwords($row->admin->name) . '</a>';
                })
                ->addColumn('property_type', function ($row) {
                    return $row->propertyType;
                })
                ->addColumn('property_status', function ($row) {
                    return ucwords($row->propertyStatus);
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
                ->addColumn('matched_tenant_count', function ($row) {
                    return $row->sentProperties->count();
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . route('property.view', $row->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="' . url('/') . '/property-detail?property_id=' . $row->id . '&updateKey=internal" target="_blank" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="Front Panel View"><i class="fa fa-image "></i></a>';

                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->rawColumns(['name', 'status', 'action', 'created_at', 'agent', 'matched_tenant_count'])
                ->make(true);
        }

        $active_page = $this->active_page;
        $adminId = $admin_id ?? 0;
        return view('property.index2', compact('active_page', 'title', 'dataTable', 'adminId'));
    }

    public function dataTable(Request $request, $admin_id = 0)
    {
        $search = $request->input('search');

        if ($search && !mb_check_encoding($search, 'UTF-8')) {
            return response()->json(['error' => 'Invalid search query encoding'], 400);
        }

        $dataTable = InternalProperty::with([
            'sentProperties' => function ($que) use ($admin_id) {
                if ($admin_id != 0) {
                    $que->whereAdminId($admin_id);
                }
            },
            'contract',
            'media',
            'admin',
        ])
            ->select('id', 'title', 'financials', 'propertyType', 'country', 'bedrooms', 'bathrooms', 'address', 'suburb', 'town', 'province', 'country', 'status', 'admin_id');

        if ($search) {
            $dataTable = $dataTable->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('country', 'LIKE', '%' . $search . '%')
                    ->orWhere('province', 'LIKE', '%' . $search . '%')
                    ->orWhere('suburb', 'LIKE', '%' . $search . '%')
                    ->orWhere('town', 'LIKE', '%' . $search . '%');
            });
        }

        if ($admin_id != 0) {
            $admin = Admin::findOrFail($admin_id);
            $agency_array_ids = $admin->agency_agents
                ->pluck('id')
                ->toArray();
            $all_ids = array_merge([$admin_id], $agency_array_ids);
            $dataTable = $dataTable->whereIn('admin_id', $all_ids);
        }

        $data = $dataTable->latest()->get()->toArray();
        return response()->json([
            'data' => $data,
        ])->header('Content-Type', 'application/json; charset=utf-8');
    }

    public function property_view(Request $request, $id)
    {
        $active_page = $this->active_page;
        $title = 'Property Details';
        if (!($data = InternalProperty::where('id', $id)->first())) {
            return redirect()->back();
        }
        $data->created_date = $this->convertToSouthAfricaTime($data->created_at, time: false);
        $property_feature_columns = HelpersProperty::featureColumnsByCategory();

        return view('adminsubuser.property.view', compact('active_page', 'data', 'property_feature_columns', 'title'));
    }

    public function external_property_view(Request $request, $id)
    {
        $active_page = $this->active_page;
        $title = 'External Property Details';
        if (!($data = Property::where('id', $id)->first())) {
            return redirect()->back();
        }
        $data->created_date = Carbon::parse($data->created_at, 'Africa/Johannesburg')->format('d M y');
        $property_feature_columns = HelpersProperty::featureColumnsByCategory();

        return view('adminsubuser.property.view-external', compact('active_page', 'data', 'property_feature_columns', 'title'));
    }
}
