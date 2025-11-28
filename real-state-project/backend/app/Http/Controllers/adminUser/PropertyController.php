<?php

namespace App\Http\Controllers\adminUser;

use App\Helpers\Property;
use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminExternalPropertyUsers;
use App\Models\Contract;
use App\Models\ContractRecord;
use App\Models\InternalProperty;
use App\Models\PropertyTimeSlot;
use App\Rules\NoLoremIpsum;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Property as PropertyHelper;
use App\Models\City;
use App\Models\Country;
use App\Models\InternalPropertyMedia;
use App\Models\Plans;
use App\Models\Province;
use App\Models\State;
use App\Models\State_City;
use App\Models\Suburb;
use Carbon\Carbon;

class PropertyController extends Controller
{

    protected $auth;
    public function __construct()
    {
        $this->auth = auth()->user();
    }

    public function index(Request $request)
    {
        $title = 'Properties';
        $user = Auth::user();
        $dataTable = [];
        if ($request->ajax()) {
            $admin_ids = $user->agency_agents()->pluck('id')->toArray();
            array_push($admin_ids, $user->id);

            $order_by = $request->columns[$request->order[0]['column']]['data'] ?? 'create_at';
            $order_asc = $request->order[0]['dir'] ?? 'desc';
            $search = $request->search['value'] ?? null;

            $internalProperty = InternalProperty::with('sentProperties', 'contract')
                ->whereIn('admin_id', $admin_ids);

            if ($search) {
                $internalProperty = $internalProperty->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('country', 'LIKE', '%' . $search . '%')
                        ->orWhere('province', 'LIKE', '%' . $search . '%')
                        ->orWhere('suburb', 'LIKE', '%' . $search . '%')
                        ->orWhere('town', 'LIKE', '%' . $search . '%');
                });
            }

            $dataTable = $internalProperty->orderby($order_by, $order_asc);

            return DataTables::of($dataTable)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $checked = ($row->status) ? 'checked' : '';
                    $btn .= '<label class="switch">';
                    $btn .= '<input type="checkbox" ' . $checked . ' switch="manual" class="' . (($row->contract()->exists()) ? 'changeContractProperty' : 'changeStatusProperty') . '" data-id = "' . $row->id . '" data-datatable = "adminsubuser-property" />';
                    $btn .= '<span class="slider round"></span></label>';

                    if (!$row->sentProperties()->exists() && !$row->contract()->exists()) {
                        $btn .= '<a data-sub="' . $row->availableSubscription_id . '" href="' . route('adminSubUser.property.edit', $row->id) . '"data-toggle="tooltip" class="btn btn-xs" data-placement="top" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-edit edit-icon"></i></a>';
                    }

                    $btn .= '<a href="' . route('adminSubUser.property.view', $row->id) . '"data-toggle="tooltip" class="btn btn-xs" data-placement="top" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye view-icon"></i></a>';
                    $btn .= '<a href="' . url('/') . '/property-detail?property_id=' . $row->id . '&updateKey=internal" target="_blank" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="Front Panel View"><i class="fa fa-image "></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })

                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })

                ->rawColumns(['name', 'action', 'created_at'])
                ->make(true);
        }
        $active_page = "property";
        return view('adminsubuser.property.index2', compact('active_page', 'title', 'user', 'dataTable'));
    }

    public function dataTable(Request $request)
    {
        $search = $request->input('search');

        if ($search && !mb_check_encoding($search, 'UTF-8')) {
            return response()->json(['error' => 'Invalid search query encoding'], 400);
        }

        $user = Auth::user();
        $admin_ids = $user->agency_agents()->pluck('id')->toArray();
        array_push($admin_ids, $user->id);

        $dataTable = InternalProperty::whereIn('admin_id', $admin_ids)
            ->with([
                'sentProperties' => function ($que) use ($admin_ids) {
                    $que->whereIn('admin_id', $admin_ids);
                },
                'contract',
                'media',
                'admin',
                'propertyTimeSlot'
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

        $data = $dataTable->latest()->get()->toArray();
        return response()->json([
            'data' => $data,
        ])->header('Content-Type', 'application/json; charset=utf-8');
    }

    public function add(Request $request)
    {
        $user = $this->auth;
        $role = $user->getRoleNames()->first();
        $title = 'Add Property';
        // Ensure the user has a valid subscription
        if (!$user->isAvailableSubscription()) {
            $plan = Plans::where('type', $role)->where('status', 1)->first();
            $if_isSubscriptionPopShow = $plan->amount == 0 ? 0 : 1;
            return redirect()->route('adminSubUser.property.index')->with(['isSubscriptionPopShow' => $if_isSubscriptionPopShow]);
        }

        $agent = null;
        if ($user->hasRole('agency')) {
            $agent = $user->agency_agents()->select('id', 'name')->get();
        }

        $checkbox_columns = Property::featureColumnsByCategory();
        $active_page = 'property';
        return view('adminsubuser.property.add', compact('checkbox_columns', 'active_page', 'agent', 'title'));
    }


    public function insert(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'title' => ['required', 'string', 'max:255', new NoLoremIpsum],
                'propertyType' => 'required|string',
                'propertyStatus' => 'required|string',
                // 'landSize' => 'required|string',
                // 'bedroom' => 'required|string',
                'bathroom' => 'required|string',
                'description' => ['required', 'string', new NoLoremIpsum],
                'country' => 'required|exists:' . Country::class . ',id',
                'state' => 'required|exists:' . State::class . ',id',
                'city' => 'required|exists:' . State_City::class . ',id',
                'suburb' => 'required|exists:' . Suburb::class . ',id',
                'streetNumber' => 'required|string',
                'streetName' => 'required|string',
                'unitNumber' => 'nullable|string',
                'complexName' => 'nullable|string',
                // 'currency' => 'required|string',
                'price' => 'required|string',
                // 'ratesAndTaxes' => 'required|string',
                // 'levy' => 'required|string',
                'depositRequired' => 'required|string',
                'leasePeriod' => 'required|string',
                "latitude" => 'required',
                "longitude" => 'required',
                // 'priceUnit' => 'required|string',
                'ismain_image' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:2048',
                    function ($attribute, $value, $fail) use ($request) {
                        $image = $request->file('ismain_image');
                        $imageSize = getimagesize($image);

                        if ($imageSize === false) {
                            return $fail('Invalid image file.');
                        }
                    },
                ],
                'files.*' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:2048',
                    function ($attribute, $value, $fail) {
                        $imageSize = getimagesize($value);
                        if ($imageSize === false) {
                            return $fail('Invalid image file.');
                        }
                    },
                ],
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'start_day_of_week' => 'required|integer|between:0,6',
                'end_day_of_week' => 'required|integer|between:0,6',
            ]);

            $validator->sometimes('agent', 'required|string|max:255', function ($input) {
                return $this->auth->hasRole('agency');
            });

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $admin = Admin::findOrFail($this->auth->id);

            // Ensure the user has a valid subscription
            if (!($availableSubscription = $admin->isAvailableSubscription())) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'your Subscription Expired',
                ], 500);
            }
            $admin_id = $admin->id;
            $is_agency = 0;
            if ($admin->hasRole('agency')) {
                $admin_id = $request->agent;
                $is_agency = 1;
            }
            $country = Country::select('id', 'currency_symbol', 'currency', 'name')->find($request->country);
            $financials = [
                // 'currency' => null,
                'currency_symbol' => $country->currency_symbol,
                'currency' => $country->currency,
                'price' => $request->price,
                'ratesAndTaxes' => $request->ratesAndTaxes,
                'levy' => $request->levy,
                'depositRequired' => $request->depositRequired,
                'leasePeriod' => $request->leasePeriod,
                // 'priceUnit' => $request->priceUnit ?? null,
                'isReduced' => $request->isReduced ? 1 : 0,
            ];
            $address = [
                'streetNumber' => $request->streetNumber,
                'streetName' => $request->streetName,
                'unitNumber' => $request->unitNumber ?? null,
                'complexName' => $request->complexName ?? null,
            ];
            $data = [
                'title' => $request->title,
                'propertyType' => $request->propertyType,
                'propertyStatus' => $request->propertyStatus,
                'bedrooms' => $request->bedroom,
                'bathrooms' => $request->bathroom,
                'description' => $request->description,
                'country' => $country->name,
                'province' => Province::select('province_name')->find($request->state)->province_name,
                'town' => City::select('city_name')->find($request->city)->city_name,
                'suburb' => Suburb::select('suburb_name')->find($request->suburb)->suburb_name,
                'address' => $address,
                'financials' => $financials,
                'admin_id' => $admin_id,
                'is_agency' => $is_agency,
                'landSize' => $request->landSize ?? null,
                'buildingSize' => $request->buildingSize ?? null,
                'lat' => $request->latitude,
                'lng' => $request->longitude,
                'coordinate' => DB::raw("ST_GeomFromText('POINT($request->latitude $request->longitude)')"),
                'availableSubscription_id' => $availableSubscription->id
            ];

            $columns = Property::featureColumns();

            DB::beginTransaction();
            function insertKeyValuePair(&$array, $key, $value)
            {
                $array[$key] = $value;
            }
            foreach ($columns as $key => $column) {
                $array = [];
                foreach ($column as $insiedcolumn) {
                    if ($value = ($request->input($key, [])[$insiedcolumn[0]] ?? 0)) {
                        $array[] = $insiedcolumn[0];
                    }
                }
                if (count($array)) {
                    $data[$key] = ($array);
                }
            }

            $insert = InternalProperty::create($data);
            if ($request->hasFile('ismain_image')) {
                $ismainFilePath = $this->__property_image($request->file('ismain_image'), 'property-image');
                $ismainData = [
                    'internal_property_id' => $insert->id,
                    'isMain' => 1,
                    'path' => $ismainFilePath,
                ];
                // Save main image data in the database
                InternalPropertyMedia::create($ismainData);
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    // Save the image
                    $filePath = $this->__property_image($file, 'property-image');

                    // Create data for each image record
                    $imgData = [
                        'internal_property_id' => $insert->id,
                        'isMain' => 0,
                        'path' => $filePath,
                    ];

                    // Save each image data in the database
                    InternalPropertyMedia::create($imgData);
                }
            }

            $start_time = $request->input('start_time');
            $end_time = $request->input('end_time');
            $start_day_of_week = nameOfWeeks()[$request->input('start_day_of_week')];
            $end_day_of_week = nameOfWeeks()[$request->input('end_day_of_week')];

            PropertyTimeSlot::updateOrCreate([
                'property_id' => null,
                'internal_property_id' => $insert->id,
            ], [
                'user_id' => $admin->id,
                'start_time' => $start_time,
                'end_time' =>  $end_time,
                'start_day_of_week' => $start_day_of_week,
                'end_day_of_week' => $end_day_of_week,
            ]);

            // subscription operation
            $availableSubscription->increment('total_property');
            $admin->isAvailableSubscription();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'msg' => 'Added Successfully',
            ]);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function view(Request $request, $id)
    {
        $active_page = 'Properties';
        $title = 'Property Details';
        if (!($data = InternalProperty::where('id', $id)->first())) {
            return redirect()->back();
        }
        $data->created_date = $this->convertToSouthAfricaTime($data->created_at, time: false);
        $property_feature_columns = Property::featureColumnsByCategory();
        return view('adminsubuser.property.view', compact('active_page', 'data', 'property_feature_columns', 'title'));
    }

    public function status(Request $request)
    {
        try {
            $id = $request->dataId;
            $status = $request->datastatus;

            $pro = InternalProperty::find($id);
            if (!$pro) {
                return ResponseBuilder::error("Data not found", 402);
            }

            DB::beginTransaction();
            if ($pro->status && $status == "block") {
                $pro->status = 0;
                $msg = "Successfully! In-active";
            } else if ($pro->status == 0 && $status == 'unblock') {
                $pro->status = 1;
                $msg = "Successfully! Active";
            }
            $pro->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'msg' => $msg,
                'type' => $pro->status
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return ResponseBuilder::error($th->getMessage(), 500);
        }
    }

    public function status_contract(Request $request)
    {
        try {
            $id = $request->dataId;
            $status = $request->datastatus;

            $pro = InternalProperty::find($id);
            if (!$pro) {
                return ResponseBuilder::error("Data not found", 402);
            }
            $contractId = $pro->contract[0]->id;
            DB::beginTransaction();
            if ($pro->status && $status == "block") {
                $pro->status = 0;
                $msg = "Successfully! In-active";
            } else if ($pro->status == 0 && $status == 'unblock') {
                $pro->status = 1;
                $msg = "Successfully! Active";
            }
            $contract = Contract::findOrFail($contractId);
            $contract->update(["status" => 1]);

            $contract->properties()->detach($id);
            $contract->tenants()->detach();
            $contract->offline_tenants()->delete();

            $pro->save();
            $user = $this->auth;

            ContractRecord::create([
                'contract_id' => $contractId,
                'internal_property_id' => null,
                'tenant_id' => $user->id,
                'status' => 'Removed from property',
                'user_id' => $user->id,
                'title' => 'Upload By ' . ($user?->designation()),
                'description' => 'Removed from ' . $pro->title . ' property and tenants by ' . ucwords($user->name) . ' (' . ($user?->designation()) . ') ',
                'date_time' => Carbon::now(),
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'msg' => $msg,
                'type' => $pro->status
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return ResponseBuilder::error($th->getMessage(), 500);
        }
    }

    public function delete(Request $request)
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
            $InternalProperty = InternalProperty::find($dataId);
            if (!$InternalProperty) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found'
                ]);
            }
            $InternalProperty->delete();
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

    public function edit(Request $request, $id)
    {
        try {
            $user = $this->auth;
            $active_page = 'property';
            $title = 'Properties';
            $checkbox_columns = Property::featureColumnsByCategory();
            $data = InternalProperty::with(['contract', 'sentProperties'])->where('id', $id)->first();

            if ($data->sentProperties()->exists() || $data->contract()->exists()) {
                return redirect()->route('adminSubUser.property.index')->with('error', 'The Property Can not editable');
            }

            $agent = null;
            if ($user->hasRole('agency')) {
                $agent = $user->agency_agents()->select('id', 'name')->get();
            }

            $country = Country::where('name', $data->country)->first();
            $province = ($country ? Province::where('province_name', $data->province)->where('country_id', $country->id)->first() : null) ?? $data->province;
            $city = ($country ? City::where('city_name', $data->town)->where('country_id', $country->id)->first() : null) ?? $data->town;
            $suburb = (isset($city->id) ? Suburb::where('suburb_name', $data->suburb)->where('city_id', $city->id)->first() : null) ?? $data->suburb;
            return view('adminsubuser.property.edit', compact('title', 'checkbox_columns', 'active_page', 'data', 'agent', 'country', 'province', 'city', 'suburb'));
        } catch (\Throwable $th) {
            LOg::error($th);
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $internalProperty = InternalProperty::findOrFail($id);
            // Validate the request
            $validator = Validator::make($request->all(), [
                'title' => ['required', 'string', 'max:255', new NoLoremIpsum],
                'propertyType' => 'required|string',
                'propertyStatus' => 'required|string',
                // 'landSize' => 'required|string',
                'bedroom' => 'required|string',
                'bathroom' => 'required|string',
                'description' => ['required', 'string', new NoLoremIpsum],
                'country' => 'required|exists:' . Country::class . ',id',
                'state' => 'required|exists:' . State::class . ',id',
                'city' => 'required|exists:' . State_City::class . ',id',
                'suburb' => 'required|exists:' . Suburb::class . ',id',
                'streetNumber' => 'required|string',
                'streetName' => 'required|string',
                'unitNumber' => 'nullable|string',
                'complexName' => 'nullable|string',
                // 'currency' => 'required|string',
                'price' => 'required|string',
                // 'ratesAndTaxes' => 'required|string',
                // 'levy' => 'required|string',
                'depositRequired' => 'required|string',
                'leasePeriod' => 'required|string',
                "latitude" => 'required',
                "longitude" => 'required',
                // 'priceUnit' => 'required|string',
                'ismain_image' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:2048',
                    function ($attribute, $value, $fail) use ($request) {
                        $image = $request->file('ismain_image');
                        $imageSize = getimagesize($image);

                        if ($imageSize === false) {
                            return $fail('Invalid image file.');
                        }
                    },
                ],
                'files.*' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:2048',
                    function ($attribute, $value, $fail) {
                        $imageSize = getimagesize($value);
                        if ($imageSize === false) {
                            return $fail('Invalid image file.');
                        }
                    },
                ],
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'start_day_of_week' => 'required|integer|between:0,6',
                'end_day_of_week' => 'required|integer|between:0,6',
            ]);
            $validator->sometimes('agent', 'required|string|max:255', function ($input) {
                return $this->auth->hasRole('agency');
            });

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $admin = Admin::findOrFail($this->auth->id);

            // Ensure the user has a valid subscription
            if ($internalProperty->sentProperties()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'is_redirect' => route('adminSubUser.property.index'),
                    'msg' => 'You can not edit',
                ], 500);
            }
            $admin_id = $admin->id;
            $is_agency = 0;
            if ($admin->hasRole('agency')) {
                $admin_id = $request->agent;
                $is_agency = 1;
            }
            $country = Country::select('id', 'currency_symbol', 'currency', 'name')->find($request->country);
            $financials = [
                // 'currency' => null,
                'currency_symbol' => $country->currency_symbol,
                'currency' => $country->currency,
                'price' => $request->price,
                'ratesAndTaxes' => $request->ratesAndTaxes,
                'levy' => $request->levy,
                'depositRequired' => $request->depositRequired,
                'leasePeriod' => $request->leasePeriod,
                // 'priceUnit' => $request->priceUnit ?? null,
                'isReduced' => $request->isReduced ? 1 : 0,
            ];
            $address = [
                'streetNumber' => $request->streetNumber,
                'streetName' => $request->streetName,
                'unitNumber' => $request->unitNumber ?? null,
                'complexName' => $request->complexName ?? null,
            ];
            $data = [
                'title' => $request->title,
                'propertyType' => $request->propertyType,
                'propertyStatus' => $request->propertyStatus,
                'bedrooms' => $request->bedroom,
                'bathrooms' => $request->bathroom,
                'description' => $request->description,
                'country' => $country->name,
                'province' => Province::select('province_name')->find($request->state)->province_name,
                'town' => City::select('city_name')->find($request->city)->city_name,
                'suburb' => Suburb::select('suburb_name')->find($request->suburb)->suburb_name,
                'address' => $address,
                'financials' => $financials,
                'admin_id' => $admin_id,
                'is_agency' => $is_agency,
                'landSize' => $request->landSize ?? null,
                'buildingSize' => $request->buildingSize ?? null,
                'lat' => $request->latitude,
                'lng' => $request->longitude,
                'coordinate' => DB::raw("ST_GeomFromText('POINT($request->latitude $request->longitude)')"),
            ];

            $columns = Property::featureColumns();

            DB::beginTransaction();
            function insertKeyValuePair(&$array, $key, $value)
            {
                $array[$key] = $value;
            }
            foreach ($columns as $key => $column) {
                $array = [];
                foreach ($column as $insiedcolumn) {
                    if ($value = ($request->input($key, [])[$insiedcolumn[0]] ?? 0)) {
                        $array[] = $insiedcolumn[0];
                    }
                }
                if (count($array)) {
                    $data[$key] = ($array);
                }
            }

            $insert = $internalProperty->update($data);

            $InternalPropertyMedia_ids = [];
            if ($request->hasFile('ismain_image')) {
                $InternalPropertyMedia_ids = $InternalPropertyMedia_ids + $internalProperty->media()->where('isMain', 1)->pluck('id')->toArray();
                $ismainFilePath = $this->__property_image($request->file('ismain_image'), 'property-image');
                $ismainData = [
                    'internal_property_id' => $internalProperty->id,
                    'isMain' => 1,
                    'path' => $ismainFilePath,
                ];
                // Save main image data in the database
                InternalPropertyMedia::create($ismainData);
            }


            if ($request->hasFile('files')) {
                $InternalPropertyMedia_ids = $InternalPropertyMedia_ids + $internalProperty->media()->where('isMain', 0)->pluck('id')->toArray();
                foreach ($request->file('files') as $file) {
                    // Save the image
                    $filePath = $this->__property_image($file, 'property-image');

                    // Create data for each image record
                    $imgData = [
                        'internal_property_id' => $internalProperty->id,
                        'isMain' => 0,
                        'path' => $filePath,
                    ];

                    // Save each image data in the database
                    InternalPropertyMedia::create($imgData);
                }
            }

            if (count($InternalPropertyMedia_ids)) {
                InternalPropertyMedia::whereIn('id', $InternalPropertyMedia_ids)->delete();
            }

            $start_time = $request->input('start_time');
            $end_time = $request->input('end_time');
            $start_day_of_week = nameOfWeeks()[$request->input('start_day_of_week')];
            $end_day_of_week = nameOfWeeks()[$request->input('end_day_of_week')];

            PropertyTimeSlot::updateOrCreate([
                'property_id' => null,
                'internal_property_id' => $internalProperty->id,
            ], [
                'user_id' => $admin->id,
                'start_time' => $start_time,
                'end_time' =>  $end_time,
                'start_day_of_week' => $start_day_of_week,
                'end_day_of_week' => $end_day_of_week,
            ]);

            // subscription operation
            DB::commit();

            return response()->json([
                'status' => 'success',
                'msg' => 'Updated Successfully',
            ]);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function agency_status(Request $request, $id)
    {
        $data = AdminExternalPropertyUsers::whereAdminId($id)->update([
            'status' => $request->status
        ]);
        // return redirect()->back()->with($data ? ['success' => 'Api Successfully updated'] : ['error' => 'Something went wrong']);
        return response()->json([
            'status' =>  $data ? 'success' : 'error',
            'msg' => $data ? 'Api Successfully updated' : 'Something went wrong'
        ]);
    }

    public function update_time_zone(Request $request, $id)
    {
        $data = InternalProperty::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'start_day_of_week' => 'required|integer|between:0,6',
            'end_day_of_week' => 'required|integer|between:0,6',
        ]);

        $validator->sometimes('agent', 'required|string|max:255', function ($input) {
            return $this->auth->hasRole('agency');
        });

        try {
            DB::beginTransaction();
            $admin = Admin::findOrFail($this->auth->id);

            $admin_id = $admin->id;
            if ($admin->hasRole('agency')) {
                $admin_id = $request->agent ?? $admin->id;
            }

            $start_time = $request->input('start_time');
            $end_time = $request->input('end_time');
            $start_day_of_week = nameOfWeeks()[$request->input('start_day_of_week')];
            $end_day_of_week = nameOfWeeks()[$request->input('end_day_of_week')];

            PropertyTimeSlot::updateOrCreate([
                'property_id' => null,
                'internal_property_id' => $data->id,
            ], [
                'user_id' => $admin_id,
                'start_time' => $start_time,
                'end_time' =>  $end_time,
                'start_day_of_week' => $start_day_of_week,
                'end_day_of_week' => $end_day_of_week,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'msg' => 'Updated Successfully',
            ]);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
