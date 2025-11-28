<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContractStatusResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PropertyneedResource;
use App\Http\Resources\PropertyNeedResourceNew;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\State_CityResource;
use App\Http\Resources\StateResource;
use App\Http\Resources\SuburbResources;
use App\Http\Resources\UserMetchingPropertyResource;
use App\Models\City;
use App\Models\Contract;
use App\Models\ContractRecord;
use App\Models\ContractStatus;
use App\Models\Country;
use App\Models\Property;
use App\Models\InternalProperty;
use App\Models\PropertyContact;
use App\Models\PropertyImage;
use App\Models\PropertyRange;
use App\Models\Province;
use App\Models\SentPropertyUser;
use App\Models\State;
use App\Models\State_City;
use App\Models\Suburb;
use App\Models\UserSearchProperty;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Property as PropertyHelper;
use App\Http\Resources\InternalPropertyResource;
use App\Http\Resources\PropertyMapLatLng;
use App\Http\Resources\SuburbResource;
use App\Models\Admin;
use App\Models\SentInternalPropertyUser;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use function Laravel\Prompts\error;
use Laravel\Passport\Token;

class PropertyController extends Controller
{

    public function columns(Request $request)
    {
        $property = [PropertyHelper::featureColumnsByCategory()];
        return ResponseBuilder::success($property, '');
    }

    public function search_property(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'province_name' => 'required|string|max:255',
                'suburb_name' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'property_type' => 'required|string|max:255',
                'start_price' => 'required|numeric',
                'end_price' => 'required|numeric',
                'no_of_bedroom' => 'required|integer|min:1',
                'no_of_bathroom' => 'required|integer|min:1',
                'pet_friendly' => 'sometimes|boolean',
                'parking' => 'sometimes|boolean',
                'pool' => 'sometimes|boolean',
                'fully_furnished' => 'sometimes|boolean',
                'garage' => 'sometimes|boolean',
                'garden' => 'sometimes|boolean',
                'move_in_date' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return ResponseBuilder::error($error, $this->validationStatus);
            }

            $user = Auth::user();
            if ($user->subscription != 1) {
                return ResponseBuilder::error(__('message.no_active_plan'), $this->validationStatus);
            }
            $checkRequest = $user->user_subscription->where('is_active', 1)->first();

            if ($checkRequest->total_request == 5) {
                return ResponseBuilder::error(__('message.max_requests_submitted'), $this->validationStatus);
            }
            $total_request = $checkRequest->total_request + 1;
            $additional_features = [
                'pet_friendly' => $request->pet_friendly ? 1 : 0,
                'parking' => $request->parking ? 1 : 0,
                'pool' => $request->pool ? 1 : 0,
                'fully_furnished' => $request->fully_furnished ? 1 : 0,
                'garage' => $request->garage ? 1 : 0,
                'garden' => $request->garden ? 1 : 0,
                'move_in_date' => $request->move_in_date,
            ];
            $data = [
                'user_id' => $user->id,
                'user_subscription_id' => $checkRequest->id,
                'province_name' => $request->province_name,
                'suburb_name' => $request->suburb_name,
                'city' => $request->city,
                'property_type' => $request->property_type,
                'start_price' => $request->start_price,
                'end_price' => $request->end_price,
                'no_of_bedroom' => $request->no_of_bedroom,
                'no_of_bathroom' => $request->no_of_bathroom,
                'additional_features' => json_encode($additional_features),
            ];

            $insert = UserSearchProperty::create($data);
            if ($insert) {
                $checkRequest->update(['total_request' => $total_request]);
                if ($checkRequest->total_request == 5) {
                    $user->update(['subscription' => 0]);
                    // $user->subscription = 0;
                    // $user->save();
                    $data = ['total_request' => 5];
                    return ResponseBuilder::success($data, __('message.max_requests_submitted'), $this->successStatus);
                }
                return ResponseBuilder::success('', 'Request Send Successfully', $this->successStatus);
            } else {
                return ResponseBuilder::error('Somenthing went wrong', $this->errorStatus);
            }
        } catch (Exception $e) {
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function search_property_v2(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_name' => 'required|string|exists:countries,name',
                'province_name' => 'required|string|exists:province,province_name',
                'city' => 'required|string|exists:city,city_name',
                'suburb_name' => 'required|string|exists:suburb,suburb_name',
                'start_price' => 'required|numeric',
                'end_price' => 'required|numeric',
                'move_in_date' => 'nullable|date',
                'property_type' => 'required|string|max:255',

                'no_of_bedroom' => 'required|integer|min:1',
                'no_of_bathroom' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return ResponseBuilder::error($error, $this->validationStatus);
            }

            $user = Auth::user();
            if ($user->subscription != 1) {
                return ResponseBuilder::error(__('message.no_active_plan'), $this->validationStatus);
            }
            $checkRequest = $user->user_subscription->where('is_active', 1)->first();

            $tenant_total_requests_count = config('services.property.tenant_request_per_payment');

            if ($checkRequest->total_request == $tenant_total_requests_count) {
                return ResponseBuilder::error(__('message.max_requests_submitted'), $this->validationStatus);
            }

            $additional_features = [
                'move_in_date' => $request->move_in_date,
            ];

            $country = Country::where('name', $request->country_name)->first();
            $data = [
                'user_id' => $user->id,
                'user_subscription_id' => $checkRequest->id,
                'country' => strtolower($request->country_name),
                'province_name' => strtolower($request->province_name),
                'suburb_name' => strtolower($request->suburb_name),
                'city' => strtolower($request->city),
                'property_type' => $request->property_type,
                'start_price' => $request->start_price,
                'end_price' => $request->end_price,
                'no_of_bedroom' => $request->no_of_bedroom,
                'no_of_bathroom' => $request->no_of_bathroom,
                'additional_features' => json_encode($additional_features),
                'currency' => $country->currency,
                'currency_name' => $country->currency_name,
                'currency_symbol' => $country->currency_symbol,
            ];

            $columns = PropertyHelper::featureColumns();

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

            $insert = UserSearchProperty::create($data);
            if ($insert) {
                $total_request = $checkRequest->total_request + 1;
                $checkRequest->update(['total_request' => $total_request]);
                if ($total_request == 5) {
                    $user->update(['subscription' => 0]);
                    $data = ['total_request' => 5];
                    return ResponseBuilder::success($data, __('message.max_requests_submitted'), $this->successStatus);
                }
                return ResponseBuilder::success('', 'Request Send Successfully', $this->successStatus);
            } else {
                return ResponseBuilder::error('Somenthing went wrong', $this->errorStatus);
            }
        } catch (Exception $e) {
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function price_range()
    {
        $price = PropertyRange::get(['start_price', 'end_price', 'currency']);
        return ResponseBuilder::success($price, '');
    }

    public function property_needs(Request $request)
    {
        $user = Auth::user();
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Format start_date and end_date to include time
        if ($start_date) {
            $start_date = Carbon::parse($start_date, 'Africa/Johannesburg')->startOfDay();
        }
        if ($end_date) {
            $end_date = Carbon::parse($end_date, 'Africa/Johannesburg')->endOfDay();
        }

        $query = UserSearchProperty::where('user_id', $user->id);

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $property_needs = $query->latest()->get();
        // Check if property_needs collection is not empty
        $date_range_count = 0;
        if ($property_needs->isNotEmpty()) {
            $last_created_at = $property_needs->last()->created_at;
            $date_range_count = $last_created_at->diffInDays(Carbon::now());
        }
        $date_range_count += 1;
        $date_range_count = number_format($date_range_count);

        $total_count = $query->count();
        $data = PropertyneedResource::collection($property_needs);
        // Prepare the response data
        $response_data = [
            'property_needs' => $data,
            'total_count' => $total_count,
            'date_range_count' => $date_range_count,
        ];

        return ResponseBuilder::success($response_data, '');
    }

    public function user_metching_property_old(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ResponseBuilder::error('Unauthorized', 401);
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Format start_date and end_date to include time
        if ($start_date) {
            $start_date = Carbon::parse($start_date, 'Africa/Johannesburg')->startOfDay();
        }
        if ($end_date) {
            $end_date = Carbon::parse($end_date, 'Africa/Johannesburg')->endOfDay();
        }

        // Build the query
        $query = SentPropertyUser::with([
            'property',
            'property.photos' => function ($query) {
                $query->where('isMain', 1);
            }
        ])
            ->where('user_id', $user->id);

        // Apply the date range filter if both dates are provided
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $sendProperty = $query->get();
        $total_count = $query->count();
        $propertydata = UserMetchingPropertyResource::collection($sendProperty);
        $data = [
            'property' => $propertydata,
            'total_count' => $total_count
        ];

        return ResponseBuilder::success($data, '');
    }

    // version 2
    public function user_metching_property(Request $request)
    {
        $page = $request->page ?? 1;
        $user = Auth::user();
        if (!$user) {
            return ResponseBuilder::error('Unauthorized', 401);
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Format start_date and end_date to include time
        if ($start_date) {
            $start_date = Carbon::parse($start_date, 'Africa/Johannesburg')->startOfDay();
        }
        if ($end_date) {
            $end_date = Carbon::parse($end_date, 'Africa/Johannesburg')->endOfDay();
        }

        // Build the query
        $query = SentPropertyUser::with([
            'property',
            'property.photos' => function ($query) {
                $query->where('isMain', 1);
            }
        ])
            ->where('user_id', $user->id);

        // Apply the date range filter if both dates are provided
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $que = SentInternalPropertyUser::with([
            'property',
            'property.imageIsMain'
        ])
            ->where(['user_id' => $user->id]);

        // Apply the date range filter if both dates are provided
        if ($start_date && $end_date) {
            $que->whereBetween('created_at', [$start_date, $end_date]);
        }

        $sendInternalProperty_count = $que->count();

        $sendInternalProperty = $que->paginate(10);
        $sendInternalProperty_data = UserMetchingPropertyResource::collection($sendInternalProperty);

        $sendProperty_count = $query->count();

        $sendProperty = $query->paginate(10);
        $sendProperty_data = UserMetchingPropertyResource::collection($sendProperty);

        $combinedData = $sendInternalProperty_data->merge($sendProperty_data);

        $total_page = max(ceil((max($sendInternalProperty_count, $sendProperty_count) / 10)), 1);

        return response()->json([
            'status' => true,
            'data' => [
                'property' => $combinedData,
                'total_count' => $sendInternalProperty_count + $sendProperty_count
            ],

            'meta' => [
                'total_page' => $total_page,
                'current_page' => $page,
                'total_item' => $sendInternalProperty_count + $sendProperty_count,
                // 'per_page' => 20,
            ],
            // "link" => [
            //     'next' => $query->hasMorePages(),
            //     'prev' => boolval($query->previousPageUrl())
            // ]
        ]);
        // return ResponseBuilder::successWithPagination($data, $combinedData);
    }

    public function property()
    {
        try {
            $response = Http::get('http://sync.entegral.net/api/listings?type=officelistings&ref=10038-22');

            if ($response->successful()) {
                $propertiesData = $response->json();
                return $propertiesData;
            } else {
                Log::error('API Request Failed', [
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);
                return [
                    'status' => $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception Occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function getProvinceSuburbCity()
    {
        try {
            $auth = base64_encode(config('services.entegral.username') . ":" . config('services.entegral.password'));
            $response = Http::withHeaders([
                "Authorization" => "Basic $auth"
            ])->get(config("services.entegral.area"));

            if ($response->successful()) {
                foreach ($response->json() as $item) {
                    try {
                        $province = Province::updateOrCreate(['province_name' => $item['province']]);
                        $town = City::updateOrCreate([
                            'city_name' => $item['town'],
                            'province_id' => $province->id,
                        ]);
                        Suburb::updateOrCreate([
                            'suburb_name' => $item['suburb'],
                            'province_id' => $province->id,
                            'city_id' => $town->id,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error processing item: " . json_encode($item) . ", Error: " . $e->getMessage());
                    }
                }
            } else {
                Log::error("Failed to fetch data from API, Status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Error in getProvinceSuburbCity method: " . $e->getMessage());
        }
    }

    public function province()
    {
        $data = Province::get(['id', 'province_name']);
        return ResponseBuilder::success($data, '');
    }
    public function city($id)
    {
        $data = City::where('province_id', $id)->get(['id', 'city_name']);
        return ResponseBuilder::success($data, '');
    }
    public function suburb($id)
    {
        // $data = Suburb::where('province_id', $id)->paginate(10);
        // $suburb_name = SuburbResources::collection($data);
        // return ResponseBuilder::successWithPagination($data, $suburb_name, '');
        $data = Suburb::where('city_id', $id)->get(['suburb_name']);
        return ResponseBuilder::success($data, '');
    }

    /**
     *
     * $type - internal / external(api data)
     */
    public function property_detail($id, $type = "external")
    {
        if ($type == "external") {
            $property = Property::with(['photos', 'contacts', 'clientOffice'])->find($id);
        } else {
            $property = InternalProperty::find($id);
        }
        if (!$property) {
            return ResponseBuilder::error('Property not found', $this->errorStatus);
        }
        $data = new PropertyResource($property);
        return ResponseBuilder::success($data, '');
    }

    public function internal_property_detail(Request $request, $id)
    {
        $property = InternalProperty::with(['admin.media', 'images'])->find($id);
        if (!$property) {
            return ResponseBuilder::error('Property not found', $this->errorStatus);
        }
        $data = new InternalPropertyResource($property);
        return ResponseBuilder::success($data, '');
    }

    public function sent_client_mail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required',
            'email' => 'required|email',
            'phone' => [
                'required',
                'numeric',
                'digits_between:7,16',
                'regex:/^\+?[0-9]+$/u',
            ],
            'full_name' => 'required',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->validationStatus);
        }

        $property = Property::find($request->property_id);
        if (!$property) {
            $internalProperty = InternalProperty::find($request->property_id);
            if (!$internalProperty) {
                return ResponseBuilder::error('Property not found', $this->errorStatus);
            }
            $property = $internalProperty;
        }

        $getClientEmail = PropertyContact::where('properties_id', $request->property_id)->first();
        if (!$getClientEmail) {
            $getClientEmail = Admin::whereId($internalProperty->admin_id)->first();
        }

        $fullName = $getClientEmail?->fullName ?? $getClientEmail?->name;
        $user = ['name' => $request->full_name, 'email' => $request->email, 'phone' => $request->phone, 'message' => $request->message];
        $agent = ['name' => $fullName, 'email' => $getClientEmail?->email];
        $property = ['title' => $property->title, 'link' => url('/') . '/property-detail?property_id=' . $property->id];
        Helper::sendAgentsMail($user, $agent, $property);
        return ResponseBuilder::success('', 'Email sent successfully', $this->successStatus);
    }

    public function countries(Request $request)
    {
        try {
            $search = $request->search ?? '';
            $countries = Country::when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            })->paginate(10);
            $data = CountryResource::collection($countries);
            return ResponseBuilder::successWithPagination($countries, $data);
        } catch (\Throwable $th) {
            return ResponseBuilder::error('Something Went Wrong', 500);
        }
    }

    public function states(Request $request, $id)
    {
        try {
            $search = $request->search ?? '';

            $countries = Province::where('country_id', $id)->when($search, function ($q) use ($search) {
                $q->where('province_name', 'LIKE', '%' . $search . '%');
            })->paginate(10);

            $data = StateResource::collection($countries);
            return ResponseBuilder::successWithPagination($countries, $data);
        } catch (\Throwable $th) {
            ResponseBuilder::error('Something Went Wrong', 500);
        }
    }

    public function cities(Request $request, $id)
    {
        try {
            $search = $request->search ?? '';
            $city = City::where('province_id', $id)->when($search, function ($q) use ($search) {
                $q->where('city_name', 'LIKE', '%' . $search . '%');
            })->paginate(10);

            $data = State_CityResource::collection($city);
            return ResponseBuilder::successWithPagination($city, $data);
        } catch (\Throwable $th) {
            ResponseBuilder::error('Something Went Wrong', 500);
        }
    }

    public function search_filter(Request $request)
    {
        $countryName = $request->country;
        $provinceName = $request->province;
        $cityName = $request->city;
        $suburbName = $request->suburb;

        // Step 1: Find country
        $country = Country::when($countryName, function ($query) use ($countryName) {
            $query->where('name', $countryName);
        })->first();

        // Step 2: Find province only if country is found
        $province = null;
        if ($country) {
            $province = Province::where('country_id', $country->id)
                ->when($provinceName, function ($query) use ($provinceName) {
                    $query->where('province_name', $provinceName);
                })->first();
        }

        // Step 3: Find city only if province is found
        $city = null;
        if ($province) {
            $city = City::where('province_id', $province->id)
                ->when($cityName, function ($query) use ($cityName) {
                    $query->where('city_name', $cityName);
                })->first();
        }

        // Step 4: Find suburb only if city is found
        $suburb = null;
        if ($city) {
            $suburb = Suburb::where('city_id', $city->id)
                ->when($suburbName, function ($query) use ($suburbName) {
                    $query->where('suburb_name', $suburbName);
                })->first();
        }

        $formatSelect = fn($item, $nameField = 'name') => $item ? [
            'id' => $item->id,
            'label' => $item->{$nameField},
            'value' => $item->id,
            'currency' => $item?->currency_symbol ?? null,
        ] : null;

        // Final structured response
        $data = [
            'country' => $formatSelect($country, 'name'),
            'province' => $formatSelect($province, 'province_name'),
            'city' => $formatSelect($city, 'city_name'),
            'suburb' => $formatSelect($suburb, 'suburb_name'),
        ];

        return ResponseBuilder::success($data, 'Filtered data.');
    }


    public function property_map(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'nullable',
                'longitude' => 'required_with:latitude',
                'distance' => 'required_with:latitude|numeric',
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->validationStatus);
            }

            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $distance = $request->distance; // in km

            $is_map_show = Setting::first()->is_map_show_frontend;
            $data = [
                'is_show_map' => $is_map_show,
            ];

            if ($latitude) {

                $a = InternalProperty::with([
                    'media' => function ($q) {
                        $q->where('isMain', 1);
                    }
                ])->select(
                    'id',
                    'lat',
                    'lng',
                    'title',
                    'financials',
                    'suburb',
                    'town',
                    'province',
                    DB::raw(
                        "(6371 * acos(cos(radians($latitude)) * cos(radians(lat)) * cos(radians(lng) - radians($longitude)) + sin(radians($latitude)) * sin(radians(lat)))) as distance"
                    )
                )
                    ->having('distance', '<=', $distance)  // Filter for km
                    ->orderBy('distance', 'asc')
                    ->get();

                $b = Property::with([
                    'photos' => function ($q) {
                        $q->where('isMain', 1);
                    }
                ])
                    ->where('latlng', '!=', ' ')
                    ->select(
                        'id',
                        'latlng',
                        'title',
                        'suburb',
                        'town',
                        'price',
                        'currency',
                        'province',
                        DB::raw(
                            "(6371 * acos(cos(radians($latitude)) * cos(radians(SUBSTRING_INDEX(latlng, ',', 1))) * cos(radians(SUBSTRING_INDEX(latlng, ',', -1)) - radians($longitude)) + sin(radians($latitude)) * sin(radians(SUBSTRING_INDEX(latlng, ',', 1)))) ) as distance"
                        )
                    )
                    ->orderBy('distance', 'asc')
                    ->get();

                $internalProperty = $a->merge(items: $b);

                $data['lat_lng'] = $internalProperty;
                $data['lat_lng'] = PropertyMapLatLng::collection($internalProperty);
            }

            return ResponseBuilder::success($data, '');
        } catch (\Throwable $th) {
            Log::error($th);
            return ResponseBuilder::error('Something Went Wrong', 500);
        }
    }

    public function suburbs_hey(Request $request)
    {
        $apiKey = config('constants.GOOGLE_MAP_BACKEND_KEY');
        $address = "See point, Western cape, Cape town, South Africa";
        $url = "https://maps.googleapis.com/maps/api/geocode/json";

        return $response = Http::get($url, [
            'address' => $address, // Correct parameter name
            'key' => $apiKey,
        ]);

        $client = new \GuzzleHttp\Client();

        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        // $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json';

        return $response = $client->get($url, [
            'query' => [
                'query' => 'Jaipur, Rajasthan, India',
                'key' => $apiKey,
            ]
        ]);

        return $data = json_decode($response->getBody(), true);

        foreach ($data['results'] as $result) {
            echo $result['name'] . "\n"; // Should display Vaishali Nagar if available
        }
    }

    public function suburbs(Request $request, $id)
    {
        try {
            $search = $request->search ?? '';

            $city = City::with(['province', 'country'])->findOrFail($id);

            if (!$city->suburb()->first()) {
                PropertyHelper::suburbsInsertByGoogle($city);
            }

            $countries = Suburb::where('city_id', $id)->when($search, function ($q) use ($search) {
                $q->where('suburb_name', 'LIKE', '%' . $search . '%');
            })->paginate(10);
            $data = SuburbResource::collection($countries);
            return ResponseBuilder::successWithPagination($countries, $data);
        } catch (\Throwable $th) {
            Log::error($th);
            return ResponseBuilder::error('Something Went Wrong', 500);
        }
    }

    public function tenant_upload_contract(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:' . User::class . ',id',
            'file' => 'required|file|mimes:pdf|max:10240',
            'contract_id' => 'required|exists:' . Contract::class . ',id',
            'admin_id' => 'exists:' . Admin::class . ',id'
        ]);

        $contract = Contract::findOrFail($request->contract_id);
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $path = $this->__contractSave($request->file('file'), null, 'contracts', $contract->path);

            $contractData = ContractStatus::where('contract_id', $request->contract_id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$contractData) {
                $insert = [
                    'user_id' => $request->user_id,
                    'admin_id' => $request->admin_id,
                    'contract_id' => $request->contract_id,
                    'contract_path' => $path,
                    'status' => ContractStatus::STATUS_APPROVAL_PENDING,
                ];
                $contractData = ContractStatus::create($insert);
            } else {
                $contractData->update([
                    'contract_path' => $path,
                    'status' => ContractStatus::STATUS_APPROVAL_PENDING,
                ]);
            }

            $user = Auth::user();
            ContractRecord::create([
                'contract_id' => $request->contract_id,
                'internal_property_id' => null,
                'tenant_id' => $user->id,
                'status' => 'Upload Contract',
                'user_id' => $user->id,
                'title' => 'Contract Uploaded',
                'description' => 'by ' . ucwords($user->name) . ' (Tenant) ',
                'date_time' => Carbon::now(),
            ]);

            $data = new ContractStatusResource($contractData);
            return ResponseBuilder::success($data, '');
        }
        return response()->json(['error' => 'Invalid file'], 400);
    }

    public function property_request_data(Request $request)
    {
        $id = $request->id;
        $userSearch = UserSearchProperty::findOrFail($id);
        $internal_property_data = UserMetchingPropertyResource::collection($userSearch->internal_property);
        $external_property_data = UserMetchingPropertyResource::collection($userSearch->external_property);

        $completeData = $internal_property_data->merge($external_property_data);
        return ResponseBuilder::success($completeData);
    }

    public function property_request_all(Request $request)
    {
        $user = Auth::user();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $page = $request->input('page', 1);

        // Format start_date and end_date to include time
        if ($start_date) {
            $start_date = Carbon::parse($start_date, 'Africa/Johannesburg')->startOfDay();
        }

        if ($end_date) {
            $end_date = Carbon::parse($end_date, 'Africa/Johannesburg')->endOfDay();
        }

        $query = UserSearchProperty::where('user_id', $user->id);

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $property_needs = $query->latest()->get();
        $date_range_count = 0;

        if ($property_needs->isNotEmpty()) {
            $last_created_at = $property_needs->last()->created_at;
            $date_range_count = $last_created_at->diffInDays(Carbon::now());
        }
        $query = $query->paginate(10);
        $date_range_count += 1;
        $date_range_count = number_format($date_range_count);

        $data = PropertyNeedResourceNew::collection($property_needs);
        return ResponseBuilder::successWithPagination($query, $data);
    }

    public function report_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:' . User::class . ',id',
            'search_id' => 'required|exists:' . UserSearchProperty::class . ',id',
            'status' => 'required|in:approved,unapproved',
            'client_id' => 'nullable|exists:' . Admin::class . ',id',
            'property_type' => 'required|in:internal,external',
        ]);

        $validator->sometimes('property_id', 'required|exists:' . InternalProperty::class . ',id', function ($input) {
            return $input->property_type === 'internal';
        });

        $validator->sometimes('property_id', 'required|exists:' . Property::class . ',id', function ($input) {
            return $input->property_type === 'external';
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $sentInternalPropertyUser = SentInternalPropertyUser::where('user_id', $request->user_id)
                ->where('internal_property_id', $request->property_id)
                ->where('search_id', $request->search_id)
                ->where('admin_id', $request->client_id)
                ->first();

            $sentPropertyUser = SentPropertyUser::where('user_id', $request->user_id)
                ->where('property_id', $request->property_id)
                ->where('search_id', $request->search_id)
                ->first();

            if ($request->property_type === 'external' && $sentPropertyUser) {
                $sentPropertyUser->update([
                    'credit_reports_status' => $request->status,
                ]);
            } elseif ($request->property_type === 'internal' && $sentInternalPropertyUser) {
                $sentInternalPropertyUser->update([
                    'credit_reports_status' => $request->status,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'No matching record found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Report viewing request ' . $request->status . ' successfully',
            ]);
        } catch (QueryException $e) {
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Something Went Wrong: ' . $e->getMessage()
            ]);
        }
    }

    public function top_city_rent_count(Request $request)
    {
        try {
            $internal = SentInternalPropertyUser::with(['property'])->get();

            $external = SentPropertyUser::with(['property'])->get();

            $sentInternalPropertyIds = $internal
                ->pluck('property.id')
                ->filter()
                ->unique();

            $sentExternalPropertyIds = $external
                ->pluck('property.id')
                ->filter()
                ->unique();

            $externalData = Property::select('town', DB::raw('count(*) as total'))
                ->groupBy('town')
                ->whereNotIn('id', $sentExternalPropertyIds)
                ->orderBy('total', 'desc')
                ->get();

            $internalData = InternalProperty::select('town', DB::raw('count(*) as total'))
                ->groupBy('town')
                ->whereNotIn('id', $sentInternalPropertyIds)
                ->orderBy('total', 'desc')
                ->get();

            $combined = collect();

            foreach ($internalData as $item) {
                $combined->put($item->town, $item->total);
            }

            foreach ($externalData as $item) {
                if ($combined->has($item->town)) {
                    $combined[$item->town] += $item->total;
                } else {
                    $combined->put($item->town, $item->total);
                }
            }

            // Convert to collection of objects like: [ { town: 'TownName', total: 10 }, ... ]
            $finalData = $combined->map(function ($total, $town) {
                return ['town' => $town, 'total' => $total];
            })->sortByDesc('total')->values();

            return ResponseBuilder::success($finalData, '');
        } catch (\Throwable $th) {
            Log::error($th);
            return ResponseBuilder::error('Something Went Wrong', 500);
        }
    }
}
