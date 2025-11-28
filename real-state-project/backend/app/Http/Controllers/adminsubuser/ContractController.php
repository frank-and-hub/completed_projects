<?php

namespace App\Http\Controllers\adminsubuser;

use App\Helpers\Helper;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Contract;
use App\Models\ContractRecord;
use App\Models\ContractStatus;
use App\Models\Country;
use App\Models\InternalProperty;
use App\Models\ManuallyContractSend;
use App\Models\Property;
use App\Models\SentInternalPropertyUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
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
        $user = $this->auth;
        $admin_ids = $user->agency_agents()->pluck('id')->toArray();
        array_push($admin_ids, $user->id);

        if ($request->ajax()) {
            $users = Admin::whereIn('id', $admin_ids);
            $search = $request->search['value'] ?? null;
            $userData = $users->with([
                'contracts' => function ($que) use ($search) {
                    $que->when($search, function ($q) use ($search) {
                        $q->with([
                            'properties' => function ($q) use ($search) {
                                $q->where('title', 'like', '%' . $search . '%');
                            },
                            'tenants' => function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                            }
                        ]);
                    });
                }
            ])
                ->latest()
                ->get();

            $data = $userData->pluck('contracts')->flatten();

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
                    $name = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                $string
                            </div>";
                    return $string ? $name : '';
                })
                ->addColumn('phone', function ($row) {
                    $tenant = $row?->tenant ?? null;
                    $tenant_phone = $tenant ? ($tenant->country_code . ' ' . $tenant->phone) : null;
                    $offline = $row?->offline_tenant ?? null;
                    $offline_tenant_phone = $offline ? ($offline->phonecode . ' ' . $offline->contact_no) : null;
                    return $tenant_phone ?? $offline_tenant_phone;
                })
                ->addColumn('property', function ($row) {
                    $image = $demoImage = asset('assets/logo-mini.png');
                    if ($row?->property?->media[0]) {
                        $image = Storage::url(findMainImage($row?->property?->media));
                    }
                    $string = $row->property ? ucwords($row->property?->title) : null;
                    $file = "<div class='text-truncate'>
                                <img src='" . $image . "' class='img-fluid rounded-circle object-fit-cover' onerror=`this.onerror=null; this.src=$demoImage;`>
                                " . $string . "
                            </div>";
                    return $string ? $file : '';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    if (count($row->property_ids) == 0) {
                        $btn .= '<a href="' . route('adminSubUser.contract.edit', $row->uuid) . '" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-edit edit-icon text-dark"></i></a>';
                    }
                    if (count($row->property_ids) == 0) {
                        $btn .= '<a href="javascript:void(0)" data-id="' . $row->uuid . '" class="product_update" data-toggle="tooltip" data-original-title="Update product"><i class="fa fa-map-marker view-icon text-success"></i></a>';
                    }
                    if (count($row->property_ids) > 0) {
                        if (count($row->offline_tenants_ids) == 0 && count($row->tenants_ids) == 0) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->uuid . '" class="tenant_update" data-offline="' . $row?->tenant?->id . '" data-toggle="tooltip" data-original-title="Update tenants " ><i class="fa fa-user view-icon text-info"></i></a>';
                        }
                        if (count($row->tenants_ids) !== 0) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->uuid . '" class="tenants_list" data-toggle="tooltip" data-original-title="View user tenants" ><i class="fa fa-user-check view-icon text-warning"></i></a>';
                        }
                        if (count($row->offline_tenants_ids) !== 0) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->uuid . '" class="offline_tenants_list" data-toggle="tooltip" data-original-title="View offline tenants" ><i class="fa fa-users view-icon text-warning"></i></a>';
                        }
                    }
                    if (count($row->property_ids) == 0 && count($row->tenants_ids) == 0) {
                        $btn .= '<a href="javascript:void(0)" data-id="' . $row->uuid . '" data-toggle="tooltip" data-placement="top" data-method="DELETE" data-original-title="Delete" data-datatable="ContractTable" data-url = "' . route('adminSubUser.contract.destroy', $row->uuid) . '" class="deletemodel"><i class="fa fa-trash delete-icon"></i></a>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->addColumn('status', function ($row) {
                    $status = $row->status;
                    if ($status == 0) {
                        $btnStatus = "Completed";
                    } else {
                        $btnStatus = "Active";
                    }
                    return $btnStatus;
                })
                ->rawColumns(['action', 'created_at', 'tenant', 'property'])
                ->make(true);
        }
        $data['title'] = 'Contracts';
        $data['active_page'] = 'contract';
        $adminId = $user->id;
        $data['admin'] = Admin::firstWhere('id', $adminId);
        $data['contract'] = Contract::whereAdminId($adminId)->get();
        $data['countries'] = Country::select('phonecode', 'name')->get();
        return view('adminsubuser.contract.index', $data);
    }

    public function create()
    {
        $data['title'] = $data['active_page'] = 'Contracts';
        $data['contract_template'] = view('adminsubuser.contract.template');

        $admin_ids = $this->auth->agency_agents()->pluck('id')->toArray();
        array_push($admin_ids, $this->auth->id);

        $data['properties'] = InternalProperty::doesntHave('contract')->select('id', 'title')
            ->whereIn('admin_id', $admin_ids)
            ->get();

        return view('adminsubuser.contract.add', $data);
    }

    public function store(Request $request)
    {
        $user = $this->auth;
        $request->validate([
            'structure' => 'required|string',
            'property' => 'nullable|array',
            'property.*' => 'nullable|exists:' . InternalProperty::class . ',id',
        ]);
        try {
            DB::beginTransaction();
            $structure = $request->structure;

            $newContract = Contract::create([
                'structure' => $structure,
                'admin_id' => $user->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($newContract && !empty($request->property)) {
                $propertyData = [];
                foreach ($request->property as $propertyId) {
                    $propertyData[$propertyId] = [
                        'id' => Str::uuid(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $property = InternalProperty::findOrFail($propertyId);
                }
                $newContract->properties()->sync($propertyData);
                $newContract->properties()->update(['status' => 0]);
            }

            $uuid = $newContract->id;

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadview('adminsubuser.contract.view', [
                'structure' => $structure
            ])
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);

            $fileName = "$uuid-contact.pdf";
            $path = $this->__contractSave($pdf, $fileName, 'contracts');

            // $filePath = storage_path("app/public/$uuid-contact.pdf");
            // $pdf->save($filePath);
            // $path = asset("storage/$uuid-contact.pdf");

            $contractRecord = [
                'contract_id' => $newContract->id,
                'internal_property_id' => $request->property[0] ?? null,
                'tenant_id' => null,
                'status' => 'Created',
                'user_id' => $user->id,
                'title' => 'Contract created',
                'description' => 'by ' . ucwords($user->name) . ' (' . ($user?->designation()) . ') ',
                'date_time' => Carbon::now(),
            ];
            ContractRecord::create($contractRecord);

            if ($request->submit === 'Preview') {
                DB::rollBack();
            } else {
                $newContract->update(['path' => $path]);
                DB::commit();
            }
            $return = response()->json([
                'status' => 'success',
                'msg' => $request->submit == 'preview' ? 'Added Successfully' : '',
                'path' => $path,
            ]);
            return $return;
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($contractId)
    {
        $contract = Contract::find($contractId);
        if (!$contract) {
            return response()->json([
                'status' => 'error',
                'message' => 'Record not found',
            ]);
        }
        $data['title'] = $data['active_page'] = 'Contracts';
        $data['contract_template'] = $contract->structure;

        $admin_ids = $this->auth->agency_agents()->pluck('id')->toArray();
        array_push($admin_ids, $this->auth->id);

        $data['properties'] = InternalProperty::select('id', 'title')
            ->whereIn('admin_id', $admin_ids)
            ->get();

        $data['contract'] = $contract;
        return view('adminsubuser.contract.add', $data);
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'structure' => 'required|string',
            'property' => 'nullable|array',
            'property.*' => 'nullable|exists:' . InternalProperty::class . ',id',
        ]);
        $user = $this->auth;
        try {
            DB::beginTransaction();
            $structure = $request->structure;

            $authId = $user->id;
            $contract->update([
                'structure' => $structure,
                'admin_id' => $authId,
                'created_by' => $authId,
                'updated_by' => $authId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($request->property)) {
                if (!in_array(null, $request->property, true)) {
                    $propertyData = [];
                    $contract->properties()->update(['status' => 1]);
                    $contract->properties()->detach();
                    foreach ($request->property as $propertyId) {
                        $propertyData[$propertyId] = [
                            'id' => Str::uuid(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    $contract->properties()->sync($propertyData);
                    $contract->properties()->update(['status' => 0]);
                }
            } else {
                $contract->properties()->update(['status' => 1]);
                $contract->properties()->detach();
            }
            $uuid = $contract->id;
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadview('adminsubuser.contract.view', ['structure' => $structure])
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);

            $fileName = "$uuid-contact.pdf";
            $path = $this->__contractSave($pdf, $fileName, 'contracts', $contract->path);

            // $filePath = storage_path("app/public/$uuid-contact.pdf");
            // $pdf->save($filePath);
            // $path = asset("storage/$uuid-contact.pdf");

            ContractRecord::create([
                'contract_id' => $contract->id,
                'internal_property_id' => $request->property[0] ?? null,
                'tenant_id' => null,
                'status' => 'Updated',
                'user_id' => $user->id,
                'title' => 'Upload By ' . ($user?->designation()),
                'description' => 'Updated by ' . ucwords($user->name) . ' (' . ($user?->designation()) . ') ',
                'date_time' => Carbon::now(),
            ]);

            if ($request->submit == 'preview') {
                DB::rollBack();
            } else {
                $contract->update(['path' => $path]);
                DB::commit();
            }

            $return = response()->json([
                'status' => 'success',
                'msg' => $request->submit == 'preview' ? 'Updated Successfully' : '',
                'path' => $path,
            ]);
            return $return;
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
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
            $user = $this->auth;
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Incorrect password',
                ]);
            }
            $dataId = $request->dataId;
            $contract = Contract::find($dataId);
            if (!$contract) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found',
                ]);
            }
            $filePath = $contract->path;
            // If the file path is a URL, extract the relative path
            if (filter_var($filePath, FILTER_VALIDATE_URL)) {
                // Parse the URL to get the path part
                $parsedUrl = parse_url($filePath);
                $filePath = ltrim($parsedUrl['path'], '/'); // Remove leading slash
            }

            // Check if the file exists in the storage and delete it
            if ($filePath && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            $contract->properties()->update(['status' => 1]);
            $contract->properties()->detach();
            $contract->tenants()->detach();
            $contract->offline_tenants()->delete();

            ContractRecord::create([
                'contract_id' => $contract->id,
                'internal_property_id' => null,
                'tenant_id' => null,
                'status' => 'Deleted',
                'user_id' => $user->id,
                'title' => 'contract removed',
                'description' => 'by ' . ucwords($user->name) . ' (' . ($user?->designation()) . ') ',
                'date_time' => Carbon::now(),
            ]);

            $contract->delete();
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

    public function get_properties(Request $request, $contract_id = null)
    {
        if ($contract_id) {
            $contract = Contract::findOrFail($contract_id);
        }

        $admin_ids = $this->auth?->agency_agents()
            ->pluck('id')
            ->toArray();
        array_push($admin_ids, $this->auth?->id);

        $search = $request->input('q');
        $page = $request->input('page', 1);

        // Query countries with pagination (10 per page)
        // $countries = $this->auth?->property()->select('id', 'title')

        $properties = InternalProperty::whereIn('admin_id', $admin_ids)
            ->doesntHave('contract')
            ->select('id', 'title')
            ->whereStatus(1)
            ->when($search != '', function ($que) use ($search) {
                $que->where('title', 'like', '%' . $search . '%');
            })
            ->paginate(10, ['*'], 'page', $page);

        // Format results for Select2
        $results = $properties->getCollection()->map(function ($property) {
            return [
                'id' => $property->id,
                'text' => $property->title,
            ];
        });

        // Return in Select2 format
        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $properties->hasMorePages()],
        ]);
    }

    public function update_contracts_property(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:' . Contract::class . ',id',
            'property' => 'nullable|array',
            'property.*' => 'nullable|exists:' . InternalProperty::class . ',id',
        ]);
        DB::beginTransaction();
        try {
            $user = $this->auth;
            $contract = Contract::findOrFail($request->contract_id);
            $contract->properties()->detach();
            if (isset($request->property) && !in_array(null, $request->property, true)) {
                $propertyData = [];
                foreach ($request->property as $propertyId) {
                    $propertyData[$propertyId] = [
                        'id' => Str::uuid(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $contract->properties()->sync($propertyData);
                InternalProperty::whereIn('id', $request->property)->update(['status' => 0]);
            } else {
                InternalProperty::whereIn('id', $contract->property_ids)->update(['status' => 1]);
            }

            ContractRecord::create([
                'contract_id' => $contract->id,
                'internal_property_id' => $request->property[0] ?? null,
                'tenant_id' => null,
                'status' => (isset($request->property) ? 'Added' : 'Removed'),
                'title' => 'Property ' . (isset($request->property) ? 'added' : 'removed'),
                'description' => 'by ' . ucwords($user->name) . ' (' . ($user?->designation()) . ') ',
                'date_time' => Carbon::now(),
            ]);

            DB::commit();
            $return = response()->json([
                'status' => 'success',
                'msg' => 'Updated Successfully',
            ]);
            return $return;
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function get_tenants(Request $request, $contract_id = null)
    {
        if ($contract_id) {
            $contract = Contract::find($contract_id);
        }

        $authUser = $this->auth;
        $search = $request->input('q');
        $page = $request->input('page', 1);

        $userArray = SentInternalPropertyUser::with(['property'])
            ->where('admin_id', $authUser->id)
            ->pluck('user_id')
            ->toArray();

        $tenants = User::whereIn('id', $userArray)
            ->select('id', 'name')
            ->whereStatus(1)
            ->where('name', 'like', '%' . $search . '%')
            ->paginate(10, ['*'], 'page', $page);

        $results = $tenants->getCollection()->map(function ($tenant) use ($contract) {
            return [
                'id' => $tenant->id,
                'text' => $tenant->name,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $tenants->hasMorePages()],
        ]);
    }

    public function update_contracts_tenants(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:' . Contract::class . ',id',
            'tenant' => 'nullable|array',
            'tenant.*' => 'nullable|exists:' . User::class . ',id',
        ]);
        DB::beginTransaction();

        try {
            $authUser = $this->auth;
            $contract = Contract::findOrFail($request->contract_id);

            // Detach all tenants first
            $contract?->tenants()->detach();

            // Ensure tenant is set and contains no null values
            if (isset($request->tenant) && !in_array(null, $request->tenant, true)) {
                $arrayData = [];
                foreach ($request->tenant as $tenantId) {
                    $arrayData[$tenantId] = [
                        'id' => Str::uuid(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    // Check if the contract has already been sent to the tenant
                    $contractStatus = ContractStatus::whereUserId($tenantId)->whereContractId($contract->id)->first();

                    // Ensure there is a contract status and it is pending
                    // if ($contractStatus) {
                    $user = User::findOrFail($tenantId);
                    $properties = SentInternalPropertyUser::where('user_id', $user->id)
                        ->where('internal_property_id', $contract->property->id)
                        ->where('admin_id', $authUser->id)
                        ->get();

                    $dynamicContractLink = $contract->id;
                    // Send contract details to each property
                    foreach ($properties as $property) {
                        $propertyAddress = $property->property->propertyAddress();
                        WhatsappTemplate::ContractSend(
                            $user->country_code,
                            $user->phone,
                            $user->name,
                            $propertyAddress,
                            $authUser?->name,
                            $dynamicContractLink
                        );
                    }

                    // Record the manual contract send
                    ManuallyContractSend::create([
                        'admin_id' => $authUser->id,
                        'first_name' => $user->name,
                        'last_name' => '',
                        'contact_no' => $user->phone,
                        'country' => $user?->country_code,
                        'phonecode' => $user->country_code,
                        'email' => $user->email,
                        'contract_id' => $contract->id,
                    ]);

                    $filePath = $contract->path;
                    // If the file path is a URL, extract the relative path

                    if (filter_var($filePath, FILTER_VALIDATE_URL)) {
                        // Parse the URL to get the path part
                        $parsedUrl = parse_url($filePath);
                        $filePath = ltrim($parsedUrl['path'], '/'); // Remove leading slash
                    }

                    // Update contract status to pending
                    ContractStatus::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'contract_id' => $contract->id,
                        ],
                        [
                            'user_id' => $user->id,
                            'admin_id' => $authUser->id,
                            'contract_id' => $contract->id,
                            'contract_path' => $filePath,
                            'status' => ContractStatus::STATUS_TENANT_PENDING,
                        ]
                    );
                    // }
                }

                ContractRecord::create([
                    'contract_id' => $contract->id,
                    'internal_property_id' => null,
                    'tenant_id' => $tenantId ?? null,
                    'status' => (isset($tenantId) ? 'Added' : 'Removed'),
                    'user_id' => $authUser->id,
                    'title' => 'Tenant ' . (isset($tenantId) ? 'added' : 'removed'),
                    'description' => 'by ' . ucwords($authUser->name) . ' (' . ($authUser?->designation()) . ') ',
                    'date_time' => Carbon::now(),
                ]);

                // Sync the contract tenants with the new list
                $contract->tenants()->sync($arrayData);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'msg' => 'Updated Successfully',
            ]);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function offline_tenants(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email',
            'contact_no' => 'required|string',
            'country' => 'required|string',
            'contract_id' => 'required|exists:' . Contract::class . ',id',
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
            $contract = Contract::findOrFail($request->contract_id);
            $user = $this->auth;
            $ManuallyContractSend = ManuallyContractSend::create([
                'admin_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'contact_no' => $request->contact_no,
                'country' => '+' . $request->phonecode,
                'phonecode' => '+' . $request->phonecode,
                'email' => $request->email,
                'contract_id' => $contract->id,
            ]);

            $name = ucwords($request->first_name . ' ' . $request->last_name ?? '');
            $dynamicContractLink = $contract->id;

            if (empty($contract?->properties[0])) {
                $return = response()->json([
                    'status' => 'error',
                    'msg' => 'Property Not Added'
                ], 422);
                return $return;
            }

            if ($contract?->properties[0]->id) {
                foreach ($contract?->properties as $property) {
                    $address = $property->propertyAddress();
                    WhatsappTemplate::ContractSend("+" . $request->phonecode, $request->contact_no, $name, $address, $user?->name, $dynamicContractLink);
                }
            }

            ContractRecord::create([
                'contract_id' => $contract->id,
                'internal_property_id' => null,
                'tenant_id' => $request?->tenant ?? null,
                'status' => 'Offline Tenant',
                'user_id' => $user->id,
                'title' => 'offline tenant ' . (isset($request->first_name) ? 'added' : 'removed'),
                'description' => 'by ' . ucwords($user->name) . ' (' . ($user?->designation()) . ') ',
                'date_time' => Carbon::now(),
            ]);

            DB::commit();
            $return = response()->json([
                'status' => 'success',
                'msg' => 'Added Successfully'
            ]);
            return $return;
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getFile(),
            ], 500);
        }
    }

    public function get_contract($id) // property id
    {
        return Helper::get_contract_path($id);
    }

    public function get_all_properties(Request $request)
    {
        $admin_ids = $this->auth?->agency_agents()->pluck('id')->toArray();
        array_push($admin_ids, $this->auth?->id);

        $search = $request->input('q');
        $page = $request->input('page', 1);

        // Query countries with pagination (10 per page)
        // $countries = $this->auth?->property()
        $properties = InternalProperty::whereIn('admin_id', $admin_ids)
            ->doesntHave('contract')
            ->whereStatus(1)
            ->select('id', 'title')->where('title', 'like', '%' . $search . '%')
            ->paginate(10, ['*'], 'page', $page);

        // Format results for Select2
        $results = $properties->getCollection()->map(function ($property) {
            return [
                'id' => $property->id,
                'text' => $property->title,
            ];
        });

        // Return in Select2 format
        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $properties->hasMorePages()],
            // 'selectedIds' => $contract?->property_ids,
        ]);
    }

    public function view_pdf($url_id, $id)
    {
        $contract = Contract::find($id);

        if (!$contract || !$contract->path) {
            abort(404, 'Contract not found or file not available');
        }

        $filePath = $contract->path;

        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            return redirect()->to($filePath);
        } else {
            abort(404, 'File not found');
        }
    }

    public function get_selected_tenants(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:' . Contract::class . ',id'
        ]);

        $contract = Contract::findOrFail($request->contract_id);
        $tenants = $contract->tenants()
            ->select('users.id as tenant_id', 'users.name')
            ->get();
        return response()->json(['tenants' => $tenants]);
    }

    public function get_selected_properties(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:' . Contract::class . ',id'
        ]);

        $contract = Contract::findOrFail($request->contract_id);
        $property = $contract->properties()->select('internal_properties.id as property_id', 'internal_properties.title')->get();
        return response()->json(['property' => $property]);
    }

    public function offline_tenants_list($contract_id)
    {
        $users = ManuallyContractSend::with(['contract'])
            ->where('last_name', '!=', '')
            ->where('contract_id', $contract_id)
            ->get();

        $html = '<table class="table table-hover table-responsive{-sm|-md|-lg|-xl}"><tr><th>Sr. No.</th><th>First Name</th><th>Last Name</th><th>Contact no</th><th>Email</th></tr>';
        foreach ($users as $k => $user) {
            $html .= '<tr>';
            $html .= '<td>' . ($k + 1) . ' </td>';
            $html .= "<td>$user->first_name</td>";
            $html .= "<td>$user->last_name</td>";
            $html .= "<td>$user->phone</td>";
            $html .= "<td>$user->email</td>";
            $html .= '</tr>';
        }
        $html .= '<table>';
        return response()->json(['html' => $html]);
    }

    public function tenants_list($contract_id)
    {
        $contracts = ContractStatus::with(['contract', 'user'])
            ->where('contract_id', $contract_id)
            ->get();

        $status = ContractStatus::STATUS_ARRAY();

        $html = '<table class="table modal_table table-responsive{-sm|-md|-lg|-xl}">
        <thead>
        <tr>
        <th>Sr.No.</th>
        <th>First Name</th>
        <th>Contact no</th>
        <th>Status</th>
        <th>Contract</th>
        <th>Action</th>
        </tr>
        </thead>
        <tbody>';

        foreach ($contracts as $k => $contract) {

            $id = $contract->id;

            $form = '';
            $form .= '<form enctype="multipart/form-data">';
            $form .= '<label for="contract_status' . $id . '" class="btn " >';
            $form .= '<input id="contract_status' . $id . '"  type="file" data-input-id="' . $id . '" class="d-none" name="updated_contract" />';
            $form .= '<i class="fa fa-upload"data-original-title="Upload Contract"></i>';
            $form .= '</label>';
            $form .= '</form>';

            $select = '';
            $select .= '<select class="form-control select2  change_contract_status" data-id="' . $id . '" name="status_change" ' . ($contract->status == (ContractStatus::STATUS_APPROVAL_PENDING) ? '' : 'disabled') . ' >';
            if ($contract->status === ContractStatus::STATUS_APPROVAL_PENDING) {
                $select .= '<option value="" >Select Status</option>';
            }
            foreach ($status as $key => $val) {
                if ($contract->status === ContractStatus::STATUS_APPROVAL_PENDING) {
                    if (in_array($key, [2, 3])) {
                        $select .= '<option value="' . $val . '" >' . ucwords(str_replace('_', ' ', $val)) . '</option>';
                    }
                } else {
                    $select .= '<option disabled value="' . $val . '" ' . ($contract->status == $key ? 'selected' : '') . '>' . ucwords(str_replace('_', ' ', $val)) . '</option>';
                }
            }
            $select .= '</select>';
            $download = '<a data-toggle="tooltip" data-original-title="View Contract" onClick="previewFile(`' . $contract->contract_path . '`)"><i   class="fa fa-download" ></i></a>';
            $html .= '<tr class="text-normal">';
            $html .= '<td scope="row" >' . ($k + 1) . ' </th>';
            $html .= "<td>" . $contract->user->name . "</th>";
            $html .= "<td>" . $contract->user->contact_no . "</th>";
            $html .= "<td class='p-0' >" . $select . "</th>";
            $html .= "<td>" . ($contract->status === ContractStatus::STATUS_TENANT_PENDING ? 'N/A' : $download) . "</th>";
            $html .= "<td>" . ($contract->status === ContractStatus::STATUS_AGENCY_PENDING ? $form : 'N/A') . "</th>";
            $html .= '</tr>';
        }
        $html .= '</tbody><table>';
        return response()->json(['html' => $html]);
    }

    public function uploadAgencyContract(Request $request)
    {
        $request->validate([
            'id' => 'required|string|exists:' . ContractStatus::class . ',id',
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);
        DB::beginTransaction();
        try {
            $user = $this->auth;
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $contract = ContractStatus::findOrFail($request->id);
                $path = $this->__contractSave($request->file('file'), null, 'contracts', $contract->contract_path);
                if ($contract) {
                    $contract->update([
                        'contract_path' => $path,
                        'status' => ContractStatus::STATUS_COMPLETED,
                    ]);
                }
                Contract::findOrFail($contract->contract_id)->update(['status' => 0]);
                if ($contract) {
                    return response()->json(['message' => 'File uploaded successfully!']);
                }
                ContractRecord::create([
                    'contract_id' => $contract->id,
                    'internal_property_id' => null,
                    'tenant_id' => $user->id,
                    'status' => 'Upload Contract',
                    'user_id' => $user->id,
                    'title' => 'Upload By ' . ($user?->designation()),
                    'description' => 'Contract uploaded by ' . ucwords($user->name) . ' (' . ($user?->designation()) . ') ',
                    'date_time' => Carbon::now(),
                ]);
            }
            DB::commit();
            return response()->json(['error' => 'Something went wrong!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 0);
        }
        return response()->json(['error' => 'Invalid file'], 400);
    }

    public function change_contract_status(Request $request)
    {
        $state = $request->status;
        $approvedStatus = 'agency_pending';
        if ($state == 'approved') {
            $state = $approvedStatus;
        }
        $user = $this->auth;
        $id = $request->id;
        $status = ContractStatus::CONTRACT_STATUS;
        $newStatus = $status[$state];

        $contract_status = ContractStatus::findOrFail($id);
        $contract = Contract::findOrFail($contract_status->contract_id);
        $contract_status->update([
            'status' => $newStatus
        ]);

        ContractRecord::create([
            'contract_id' => $contract->id,
            'internal_property_id' => null,
            'tenant_id' => $request->tenant[0] ?? null,
            'status' => 'Upload Contract Status',
            'user_id' => $user->id,
            'description' => 'by ' . ucwords($user->name) . ' (' . ($user?->designation()) . ') ',
            'title' => 'Contract ' . (str_replace('_', ' ', ($request->status))),
            'date_time' => Carbon::now(),
        ]);

        return response()->json(['message' => 'status updated successfully!']);
    }
}
