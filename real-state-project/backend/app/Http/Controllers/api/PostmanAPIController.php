<?php

namespace App\Http\Controllers\api;

use App\Helpers\Property as HelpersProperty;
use App\Http\Resources\PropertyAPIResource;
use App\Models\Admin;
use App\Models\AdminExternalPropertyUsers;
use App\Models\Country;
use App\Models\InternalProperty;
use App\Models\InternalPropertyMedia;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PostmanAPIController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $country = $req->authenticated_user->country;
        $agencyIds = $req->authenticated_user->agencies_ids ?? [];
        $admin_ids = [];

        if (!empty($agencyIds)) {
            $admins = Admin::with(['agency_agents'])
                ->whereIn('id', $agencyIds)
                ->get();

            $statuses = AdminExternalPropertyUsers::whereIn('admin_id', $agencyIds)
                ->pluck('status', 'admin_id');

            foreach ($admins as $admin) {
                if (isset($statuses[$admin->id]) && $statuses[$admin->id] == 1) {
                    $admin_ids = array_merge($admin_ids, $admin->agency_agents->pluck('id')->toArray());
                    $admin_ids[] = $admin->id;
                }
            }

            $admin_ids = array_unique($admin_ids);
        }

        // old logic
        // $properties = InternalProperty::when(!empty($admin_ids), function ($que) use ($admin_ids) {
        //     $que->whereIn('admin_id', $admin_ids);
        // })->when(empty($agencyIds), function ($que) use ($country) {
        //     $que->whereCountry($country);
        // })->get();

        // new logic
        $properties = InternalProperty::whereIn('admin_id', $admin_ids)->when(empty($agencyIds), function ($que) use ($country) {
            $que->whereCountry($country);
        })->get();

        $data = PropertyAPIResource::collection($properties);
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        $newCountry = $req->authenticated_user->country;
        $country_code = $req->authenticated_user->country_code;
        $insert = [];
        try {
            // Validate the request
            $validator = Validator::make($req->all(), [
                'properties' => 'required|array',
                'properties.*.property_agent_details' => 'required',
                'properties.*.property_agent_details.phone' => 'required|string',
                'properties.*.title' => 'required|string|max:255',
                'properties.*.landSize' => 'numeric',
                'properties.*.buildingSize' => 'numeric',
                'properties.*.propertyType' => 'required|string',
                'properties.*.propertyStatus' => 'required|string',
                'properties.*.bedroom' => 'required|numeric',
                'properties.*.bathroom' => 'required|numeric',
                'properties.*.description' => 'required|string',
                'properties.*.country' => 'required|string',
                'properties.*.state' => 'required|string',
                'properties.*.city' => 'required|string',
                'properties.*.suburb' => 'required|string',
                'properties.*.streetNumber' => 'required|string',
                'properties.*.streetName' => 'required|string',
                'properties.*.unitNumber' => 'string',
                'properties.*.complexName' => 'string',
                'properties.*.price' => 'required|numeric',
                'properties.*.depositRequired' => 'required|numeric',
                'properties.*.leasePeriod' => 'required|numeric',
                'properties.*.latitude' => 'required',
                'properties.*.longitude' => 'required',
                'properties.*.ismain_image' => [
                    'required',
                    function ($attribute, $value, $fail) use ($req) {
                        // Check if the input is a file upload
                        if ($req->hasFile('ismain_image')) {
                            $image = $req->file('ismain_image');
                            $imageSize = getimagesize($image);

                            // Validate the image if it's an uploaded file
                            if ($imageSize === false) {
                                return $fail('Invalid image file.');
                            }
                        }
                    }
                ],
                'properties.*.files.*' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        // Check if the input is a file upload
                        if (is_file($value)) {
                            $imageSize = getimagesize($value);

                            // Validate the image if it's an uploaded file
                            if ($imageSize === false) {
                                return $fail('Invalid image file.');
                            }
                        }
                    }
                ],

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $insertedId = [];
            $properties = $req->properties;
            $is_agency = 0;
            $agent_not_found = [];

            DB::beginTransaction();
            foreach ($properties as $k => $prop) {

                $phone = $prop['property_agent_details']['phone'] ?? null;
                if ($phone && strpos($phone, (string) $country_code) === 0) {
                    $phone = str_replace($country_code, '', $phone);
                }
                $email = $prop['property_agent_details']['email'] ?? null;
                $admin = Admin::where('phone', $phone)->where('email', $email)->first();
                if ($admin) {
                    $admin_id = $admin->id;
                    if ($newCountry != $prop['country']) {
                        $agent_not_found[] = [
                            'phone' => $phone,
                            'email' => $email
                        ];
                    } else if (in_array($admin->getRoleNames()->first(), ['agent', 'agency'])) {

                        if ($admin->hasRole('agency')) {
                            $is_agency = 1;
                        }

                        $country = Country::select('id', 'currency_symbol', 'currency', 'name')
                            ->where('name', $prop['country'])
                            ->first();

                        $financial = [
                            'currency_symbol' => $country->currency_symbol,
                            'currency' => $country->currency,
                            'price' => $prop['price'],
                            'ratesAndTaxes' => $prop['ratesAndTaxes'],
                            'levy' => $prop['levy'],
                            'depositRequired' => $prop['depositRequired'],
                            'leasePeriod' => $prop['leasePeriod'],
                            'isReduced' => isset($prop['isReduced']) ? 1 : 0,
                        ];

                        $address = [
                            'streetNumber' => $prop['streetNumber'],
                            'streetName' => $prop['streetName'],
                            'unitNumber' => $prop['unitNumber'] ?? null,
                            'complexName' => $prop['complexName'] ?? null,
                        ];

                        $lat = $prop['latitude'];
                        $lng = $prop['longitude'];

                        $data = [
                            'title' => isset($prop['title']) ? $prop['title'] : null,
                            'propertyType' => isset($prop['propertyType']) ? $prop['propertyType'] : null,
                            'propertyStatus' => isset($prop['propertyStatus']) ? $prop['propertyStatus'] : null,
                            'bedrooms' => isset($prop['bedroom']) ? $prop['bedroom'] : null,
                            'bathrooms' => isset($prop['bathroom']) ? $prop['bathroom'] : null,
                            'description' => isset($prop['description']) ? $prop['description'] : null,
                            'country' => $country->name,
                            'province' => isset($prop['state']) ? $prop['state'] : null,
                            'town' => isset($prop['city']) ? $prop['city'] : null,
                            'suburb' => isset($prop['suburb']) ? $prop['suburb'] : null,
                            'address' => $address,
                            'financials' => $financial,
                            'admin_id' => $admin_id,
                            'is_agency' => $is_agency,
                            'landSize' => isset($prop['landSize']) ? json_encode($prop['landSize']) : null,
                            'buildingSize' => isset($prop['buildingSize']) ? json_encode($prop['buildingSize']) : null,
                            'lat' => $lat,
                            'lng' => $lng,
                            'coordinate' => DB::raw("ST_GeomFromText('POINT($lat $lng)')"),
                        ];

                        $columns = HelpersProperty::featureColumnsByCategory();

                        if ($k === 0) {
                            function insertKeyValuePair(&$array, $key, $value)
                            {
                                $array[$key] = $value;
                            }
                        }

                        foreach ($columns as $key => $column) {
                            $array = [];
                            foreach ($column as $k => $insideColumns) {
                                foreach ($insideColumns as $i => $insideColumn) {
                                    $val = $prop['advanced_feature'][$key][$k][$i] ?? false;
                                    if ($val) {
                                        $array[$i] = $val;
                                    }
                                }
                                if (count($array)) {
                                    $data[strtolower($k)] = $array;
                                }
                            }
                        }

                        $insert = InternalProperty::create($data);
                        $insertGetId = $insert->id;
                        array_push($insertedId, $insertGetId);
                        if ($req->hasFile('ismain_image')) {
                            $isMainFilePath = $this->__property_image($req->file('ismain_image'), 'property-image');
                            $isMainData = [
                                'internal_property_id' => $insert->id,
                                'isMain' => 1,
                                'path' => $isMainFilePath,
                            ];
                            InternalPropertyMedia::create($isMainData);
                        }
                        if ($req->hasFile('files')) {
                            foreach ($req->file('files') as $file) {
                                $isFilePath = $this->__property_image($file, 'property-image');
                                $imgData = [
                                    'internal_property_id' => $insert->id,
                                    'isMain' => 0,
                                    'path' => $isFilePath,
                                ];
                                // Save each image data in the database
                                InternalPropertyMedia::create($imgData);
                            }
                        }
                    }
                } else {
                    $agent_not_found[] = [
                        'phone' => $phone,
                        'email' => $email
                    ];
                }
            }
            DB::commit();
            $all_new_properties = InternalProperty::whereIn('id', $insertedId)->get();
            $response = PropertyAPIResource::collection($all_new_properties);
            return response()->json([
                'status' => 'success',
                'msg' => 'Added Successfully',
                'data' => $response,
                'agents' => count($agent_not_found) > 0 ? $agent_not_found : 0
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

    /**
     * Display the specified resource.
     */
    public function show(Request $req, $id)
    {
        $country = $req->authenticated_user->country;
        $agencies = $req->authenticated_user->agencies_ids ?? [];
        $admin_ids = [];

        if (!InternalProperty::whereId($id)->exists()) {
            return response()->json([
                'msg' => 'Incorrect property client id',
                'errors' => 'Data not found',
            ], 404);
        }

        if (!empty($agencies)) {
            foreach ($agencies as $agencyId) {
                $user = Admin::findOrFail($agencyId);
                if ($user->selected_agency_api_status == 1) {
                    $admin_ids = $user->agency_agents()->pluck('id')->toArray();
                    array_push($admin_ids, $user->id);
                }
            }
        }

        $properties = InternalProperty::whereId($id)->when(!empty($admin_ids), function ($que) use ($admin_ids) {
            $que->whereIn('admin_id', $admin_ids);
        })->when(empty($agencies), function ($que) use ($country) {
            $que->whereCountry($country);
        })
            ->first();

        if (!$properties) {
            return response()->json([
                'msg' => 'Validation failed',
                'errors' => "Property not found",
            ], 404);
        }

        $data = new PropertyAPIResource($properties);
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $req)
    {
        $newCountry = $req->authenticated_user->country;
        $country_code = $req->authenticated_user->country_code;

        try {
            // Validate the request
            $validator = Validator::make($req->all(), [
                'properties.*.property_agent_details.phone' => 'required|string',
                'properties.*.properties.*.clientPropertyID' => 'required|exists:' . InternalProperty::class . ',id',
                'properties.*.title' => 'required|string|max:255',
                'properties.*.landSize' => 'numeric',
                'properties.*.buildingSize' => 'numeric',
                'properties.*.propertyType' => 'required|string',
                'properties.*.propertyStatus' => 'required|string',
                'properties.*.bedroom' => 'required|numeric',
                'properties.*.bathroom' => 'required|numeric',
                'properties.*.description' => 'required|string',
                'properties.*.country' => 'required|string',
                'properties.*.state' => 'required|string',
                'properties.*.city' => 'required|string',
                'properties.*.suburb' => 'required|string',
                'properties.*.streetNumber' => 'required|string',
                'properties.*.streetName' => 'required|string',
                'properties.*.unitNumber' => 'string',
                'properties.*.complexName' => 'string',
                'properties.*.price' => 'required|numeric',
                'properties.*.depositRequired' => 'required|numeric',
                'properties.*.leasePeriod' => 'required|numeric',
                'properties.*.latitude' => 'required',
                'properties.*.longitude' => 'required',
                'properties.*.ismain_image' => [
                    'required',
                    function ($attribute, $value, $fail) use ($req) {
                        // Check if the input is a file upload
                        if ($req->hasFile('ismain_image')) {
                            $image = $req->file('ismain_image');
                            $imageSize = getimagesize($image);

                            // Validate the image if it's an uploaded file
                            if ($imageSize === false) {
                                return $fail('Invalid image file.');
                            }
                        }
                    }
                ],
                'properties.*.files.*' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        // Check if the input is a file upload
                        if (is_file($value)) {
                            $imageSize = getimagesize($value);

                            // Validate the image if it's an uploaded file
                            if ($imageSize === false) {
                                return $fail('Invalid image file.');
                            }
                        }
                    }
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }


            $insertedId = [];
            $agent_not_found = [];

            $properties = $req->properties;
            $is_agency = 0;

            DB::beginTransaction();
            foreach ($properties as $k => $prop) {
                $phone = $prop['property_agent_details']['phone'] ?? null;
                if ($phone && strpos($phone, (string) $country_code) === 0) {
                    $phone = substr($phone, strlen((string) $country_code));
                }
                $email = $prop['property_agent_details']['email'] ?? null;
                $admin = Admin::where('phone', $phone)->where('email', $email)->first();
                if ($admin) {
                    $admin_id = $admin->id;
                    if ($newCountry != $prop['country']) {
                        // return response()->json([
                        //     'msg' => 'Validation failed',
                        //     'errors' => "User not allowed to enter " . $prop['country'] . " country properties",
                        // ], 422);
                        $agent_not_found[] = [
                            'phone' => $phone,
                            'email' => $email
                        ];
                    } else if (in_array($admin->getRoleNames()->first(), ['agent', 'agency'])) {
                        $internalProperty = InternalProperty::where('admin_id', $admin_id)->find($prop['clientPropertyID']);

                        if (!$internalProperty) {
                            return response()->json([
                                'msg' => 'Incorrect property client id',
                                'errors' => 'Data not found',
                            ], 404);
                        }

                        // Ensure the user has a valid subscription
                        if ($internalProperty->sentProperties()->exists()) {
                            return response()->json([
                                'status' => 'error',
                                'is_redirect' => route('adminSubUser.property.index'),
                                'msg' => 'You can not edit',
                            ], 500);
                        }


                        if ($admin->hasRole('agency')) {
                            $is_agency = 1;
                        }
                        $country = Country::select('id', 'currency_symbol', 'currency', 'name')
                            ->where('name', $prop['country'])
                            ->first();

                        $financial = [
                            'currency_symbol' => $country->currency_symbol,
                            'currency' => $country->currency,
                            'price' => $prop['price'],
                            'ratesAndTaxes' => $prop['ratesAndTaxes'],
                            'levy' => $prop['levy'],
                            'depositRequired' => $prop['depositRequired'],
                            'leasePeriod' => $prop['leasePeriod'],
                            'isReduced' => $prop['isReduced'] ? 1 : 0,
                        ];

                        $address = [
                            'streetNumber' => $prop['streetNumber'],
                            'streetName' => $prop['streetName'],
                            'unitNumber' => $prop['unitNumber'] ?? null,
                            'complexName' => $prop['complexName'] ?? null,
                        ];

                        $lat = $prop['latitude'];
                        $lng = $prop['longitude'];

                        $data = [
                            'title' => isset($prop['title']) ? $prop['title'] : null,
                            'propertyType' => isset($prop['propertyType']) ? $prop['propertyType'] : null,
                            'propertyStatus' => isset($prop['propertyStatus']) ? $prop['propertyStatus'] : null,
                            'bedrooms' => isset($prop['bedroom']) ? $prop['bedroom'] : null,
                            'bathrooms' => isset($prop['bathroom']) ? $prop['bathroom'] : null,
                            'description' => isset($prop['description']) ? $prop['description'] : null,
                            'country' => $country->name,
                            'province' => isset($prop['state']) ? $prop['state'] : null,
                            'town' => isset($prop['city']) ? $prop['city'] : null,
                            'suburb' => isset($prop['suburb']) ? $prop['suburb'] : null,
                            'address' => $address,
                            'financials' => $financial,
                            'admin_id' => $admin_id,
                            'is_agency' => $is_agency,
                            'landSize' => $prop['landSize'],
                            'buildingSize' => $prop['buildingSize'],
                            'lat' => $lat,
                            'lng' => $lng,
                            'coordinate' => DB::raw("ST_GeomFromText('POINT($lat $lng)')"),
                        ];

                        $columns = HelpersProperty::featureColumnsByCategory();

                        if ($k === 0) {
                            function insertKeyValuePair(&$array, $key, $value)
                            {
                                $array[$key] = $value;
                            }
                        }

                        foreach ($columns as $key => $column) {
                            $array = [];
                            foreach ($column as $k => $insideColumns) {
                                foreach ($insideColumns as $i => $insideColumn) {
                                    $val = $prop['advanced_feature'][$key][$k][$i] ?? false;
                                    if ($val) {
                                        $array[$i] = $val;
                                    }
                                }
                                if (count($array)) {
                                    $data[strtolower($k)] = $array;
                                }
                            }
                        }

                        $insert = $internalProperty->update($data);
                        $insertGetId = $internalProperty->id;
                        array_push($insertedId, $insertGetId);
                        $InternalPropertyMedia_ids = [];

                        if ($req->hasFile('ismain_image')) {
                            $isMainFilePath = $this->__property_image($req->file('ismain_image'), 'property-image');
                            $isMainData = [
                                'internal_property_id' => $insert->id,
                                'isMain' => 1,
                                'path' => $isMainFilePath,
                            ];
                            InternalPropertyMedia::where('internal_property_id', $insertGetId)->where('isMain', 1)->delete();
                            InternalPropertyMedia::create($isMainData);
                        }
                        if ($req->hasFile('files')) {
                            foreach ($req->file('files') as $file) {
                                $isFilePath = $this->__property_image($file, 'property-image');
                                $imgData = [
                                    'internal_property_id' => $insert->id,
                                    'isMain' => 0,
                                    'path' => $isFilePath,
                                ];
                                InternalPropertyMedia::where('internal_property_id', $insertGetId)->where('isMain', 0)->delete();
                                InternalPropertyMedia::create($imgData);
                            }
                        }

                        if (count($InternalPropertyMedia_ids)) {
                            InternalPropertyMedia::whereIn('id', $InternalPropertyMedia_ids)->delete();
                        }
                        // } else {
                        //     return response()->json([
                        //         'msg' => 'Agent phone number is incorrect please contact to administrator',
                        //         'errors' => $validator->errors(),
                        //     ], 422);
                    }
                } else {
                    $agent_not_found[] = [
                        'phone' => $phone,
                        'email' => $email
                    ];
                }
            }
            DB::commit();
            $all_new_properties = InternalProperty::whereIn('id', $insertedId)->get();
            $response = PropertyAPIResource::collection($all_new_properties);
            return response()->json([
                'status' => 'success',
                'msg' => 'Updated Successfully',
                'data' => $response,
                'agents' => count($agent_not_found) > 0 ? $agent_not_found : 0
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $req, $id)
    {
        try {
            DB::beginTransaction();
            $country = $req->authenticated_user->country;
            $agencies = $req->authenticated_user->agencies_ids ?? [];
            $admin_ids = [];

            if (!empty($agencies)) {
                foreach ($agencies as $agencyId) {
                    $user = Admin::findOrFail($agencyId);
                    if ($user->selected_agency_api_status == 1) {
                        $admin_ids = $user->agency_agents()->pluck('id')->toArray();
                        array_push($admin_ids, $user->id);
                    }
                }
            }

            $properties = InternalProperty::whereId($id)->when(!empty($admin_ids), function ($que) use ($admin_ids) {
                $que->whereIn('admin_id', $admin_ids);
            })->when(empty($agencies), function ($que) use ($country) {
                $que->whereCountry($country);
            })
                ->first();

            if (!$properties) {
                return response()->json([
                    'msg' => 'Property not found',
                    'errors' => 'data not found',
                ], 404);
            }
            $properties->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'msg' => 'Deleted Successfully',
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
