<?php

namespace App\Http\Controllers\admin;

use App\Models\InternalProperty;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Property;
use Yajra\DataTables\DataTables;

class ExternalPropertyController extends Controller
{
    private $active_page = 'partner-property';

    public function index(Request $request)
    {
        $title = 'Partner Properties';
        $search = $request->search['value'] ?? null;
        if ($request->ajax()) {
            // $data = User::where('type', '!=', 'admin')->latest()->get();
            $data = Property::when($search, function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('description', 'LIKE', '%' . $search . '%')
                    ->orWhere('country', 'LIKE', '%' . $search . '%')
                    ->orWhere('province', 'LIKE', '%' . $search . '%')
                    ->orWhere('suburb', 'LIKE', '%' . $search . '%')
                    ->orWhere('town', 'LIKE', '%' . $search . '%');
            });

            $is_agency_role = null;
            $data = $data->latest();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('title', function ($row) {
                    return ($row->title);
                })
                // ->addColumn('agent', function ($row) {
                //     return "<a href='" . route('admin_user.role_type_view', $row->admin_id) . "'>" . ucwords($row->admin->name) . '</a>';
                // })
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
                ->addColumn('action', function ($row) {
                   $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . route('property.view-external', $row->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="' . url('/') . '/property-detail?property_id=' . $row->id . '&updateKey=external" target="_blank" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="Front Panel View"><i class="fa fa-image "></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->rawColumns(['name', 'status', 'action', 'created_at', 'agent'])
                ->make(true);
        }
        $active_page = $this->active_page;
        return view('external_property.index', compact('active_page', 'title'));
    }
}
