<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class AdminContractRecordController extends Controller
{
    protected $auth;
    public function __construct()
    {
        $this->auth = Auth::user();
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = $this->auth;

            $search = $request->search['value'] ?? null;
            $data = Contract::whereHas('records')->with(['records', 'admin'])
                ->when($search, function ($q) use ($search) {
                    $q->with([
                        'properties' => function ($q) use ($search) {
                            $q->where('title', 'like', '%' . $search . '%');
                        },
                        'tenants' => function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');
                        }
                    ]);
                })->latest()
                ->get();

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($row) {
                    return $row->id;
                })
                ->addColumn('tenant', function ($row) {
                    $tenant = $row?->tenant ?? null;
                    $offline = $row?->offline_tenant ?? null;
                    $string = $tenant ? ucwords($tenant->name) : ($offline ? ucwords(($offline->first_name ?? '') . ' ' . ($offline->last_name ?? '')) : '');
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($tenant?->image) {
                        $image = Storage::url($tenant->image);
                    }
                    $file = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string
                            </div>";
                    return $file;
                })
                ->addColumn('admin', function ($row) {
                    $admin = $row?->admin ?? null;
                    $string = $admin ? ucwords($admin->name) : '';
                    $image = $demoImage = asset('assets/default_user.png');
                    if ($admin?->image()) {
                        $image  = $admin->image()->first()?->path && Storage::exists($admin->image()->first()?->path) ? Storage::url($admin->image()->first()?->path) : asset('assets/default_user.png');
                    }
                    $designation = $admin?->designation() ?? null;
                    $file = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string ( $designation )
                            </div>";
                    return $file;
                })
                ->addColumn('phone', function ($row) {
                    $tenant = $row?->tenant ?? null;
                    $tenant_phone = $tenant ? ($tenant->country_code . ' ' . $tenant->phone) : null;
                    $offline = $row?->offline_tenant ?? null;
                    $offline_tenant_phone = $offline ? ($offline->phonecode . ' ' . $offline->contact_no) : null;
                    return $tenant_phone ?? $offline_tenant_phone;
                })
                ->addColumn('property', function ($row) {
                    return $row->property ? ($row->property->title) : '';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . route('contract_records.show', $row?->uuid) . '" data-toggle="tooltip" data-placement="top" data-original-title="Record"><i class="fa fa-eye"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->addColumn('status', function ($row) {
                    $status = $row?->status;
                    $btnStatus = ($status == 0) ? "Completed" : "Active";
                    return $btnStatus;
                })
                ->rawColumns(['action', 'tenant', 'property', 'admin', 'status'])
                ->make(true);
        }
        $data['title'] = 'Contract Records';
        $data['active_page'] = 'records';
        return view('adminsubuser.contract-record.index', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $contract = Contract::findOrFail($id);
        $data['records'] = $contract->records()->get();
        $data['title'] = 'Record Details';
        $data['active_page'] = 'contract_records';
        foreach ($data['records'] as $record) {
            $record->_created_at = $this->convertToSouthAfricaTime($record->created_at);
        }
        return view('adminsubuser.contract-record.view', $data);
    }
}
