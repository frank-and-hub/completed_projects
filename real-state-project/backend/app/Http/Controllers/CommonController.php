<?php

namespace App\Http\Controllers;

use App\Helpers\Property as PropertyHelper;
use App\Models\Calendar;
use App\Models\City;
use App\Models\Country;
use App\Models\InternalProperty;
use App\Models\Property;
use App\Models\Province;
use App\Models\Suburb;
use App\Services\S3Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
    /**
     * Class CommonController
     *
     * This controller handles common functionalities such as fetching countries, states, cities, suburbs,
     * and properties based on latitude and longitude. It also provides methods for rendering email templates.
     *
     * @package App\Http\Controllers
     */

    protected $s3Service;

    /**
     * Constructor to inject S3Service dependency.
     *
     * @param S3Service $s3Service
     */
    public function __construct(S3Service $s3Service)
    {
        // Inject the S3Service into the controller
        $this->s3Service = $s3Service;
    }

    /**
     * Fetches a paginated list of countries with optional search functionality.
     * Formats the results for Select2 dropdown.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function country(Request $request)
    {
        $search = $request->input('q');
        $page = $request->input('page', 1);

        // Query countries with pagination (10 per page)
        $countries = Country::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->paginate(10, ['*'], 'page', $page);

        // Format results for Select2
        $results = $countries->getCollection()->map(function ($country) {
            return [
                'id' => $country->id,
                'text' => $country->name,
                'currency_symbol' => $country->currency_symbol,
                'currency' => $country->currency,
            ];
        });

        // Return in Select2 format
        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $countries->hasMorePages()],
            'selected' => 17,
        ]);
    }

    /**
     * Fetches a paginated list of states (provinces) with optional search and country filtering.
     * Formats the results for Select2 dropdown.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function state(Request $request)
    {
        $search = $request->input('q');
        $page = $request->input('page', 1);
        $country_id = $request->input('country_id', '');

        // Query countries with pagination (10 per page)
        $provinces = Province::query()
            ->when($search, function ($query, $search) {
                return $query->where('province_name', 'like', '%' . $search . '%');
            })
            ->when($country_id, function ($query, $country_id) {
                return $query->where('country_id', $country_id);
            })
            ->paginate(10, ['*'], 'page', $page);

        // Format results for Select2
        $results = $provinces->getCollection()->map(function ($province) {
            return [
                'id' => $province->id,
                'text' => $province->province_name,
            ];
        });

        // Return in Select2 format
        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $provinces->hasMorePages()],
        ]);
    }

    /**
     * Fetches a paginated list of cities with optional search, state, and country filtering.
     * Formats the results for Select2 dropdown.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function city(Request $request)
    {
        $search = $request->input('q');
        $page = $request->input('page', 1);
        $country_id = $request->input('country_id', '');
        $state_id = $request->input('state_id', '');

        // Query countries with pagination (10 per page)
        $countries = City::query()
            ->when($search, function ($query, $search) {
                return $query->where('city_name', 'like', '%' . $search . '%');
            })
            ->when($state_id, function ($query, $state_id) {
                return $query->where('province_id', $state_id);
            })
            ->when($country_id, function ($query, $country_id) {
                return $query->where('country_id', $country_id);
            })
            ->paginate(10, ['*'], 'page', $page);

        // Format results for Select2
        $results = $countries->getCollection()->map(function ($country) {
            return [
                'id' => $country->id,
                'text' => $country->city_name,
            ];
        });

        // Return in Select2 format
        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $countries->hasMorePages()],
        ]);
    }

    /**
     * Fetches properties within a geographical boundary based on latitude, longitude, and zoom level.
     * Combines results from InternalProperty and Property models.
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function property_lat_lng(Request $request)
    {
        $centerLat = $request->input('lat');
        $centerLng = $request->input('lng');
        $zoom = $request->input('zoom');
        $bounds = $this->calculateBounds($centerLat, $centerLng, $zoom);

        $a = InternalProperty::with([
            'images' => function ($q) {
                $q->select('path', 'internal_property_id')->where('isMain', 1);
            }
        ])
            ->select('id', 'lat', 'lng', 'financials', 'title', 'suburb', 'town', 'province')
            ->whereBetween('lat', [$bounds['min_lat'], $bounds['max_lat']])
            ->whereBetween('lng', [$bounds['min_lng'], $bounds['max_lng']])
            ->get();

        $b = Property::with([
            'photos' => function ($q) {
                $q->select('imgUrl', 'properties_id')->where('isMain', 1);
            }
        ])->select(
            'id',
            'latlng',
            DB::raw("SUBSTRING_INDEX(TRIM(latlng), ',', 1) as lat"),
            DB::raw("SUBSTRING_INDEX(TRIM(latlng), ',', -1) as lng"),
            'price',
            'title',
            'suburb',
            'town',
            'province',
        )
            ->whereBetween(DB::raw("CAST(SUBSTRING_INDEX(TRIM(latlng), ',', 1) AS DECIMAL(10, 6))"), [$bounds['min_lat'], $bounds['max_lat']])
            ->whereBetween(DB::raw("CAST(SUBSTRING_INDEX(TRIM(latlng), ',', -1) AS DECIMAL(10, 6))"), [$bounds['min_lng'], $bounds['max_lng']])
            ->get();
        $combined = $a->merge($b);
        return $combined;
    }

    /**
     * Calculates geographical bounds (latitude and longitude) based on center coordinates and zoom level.
     *
     * @param float $lat
     * @param float $lng
     * @param int $zoom
     * @return array
     */
    private function calculateBounds($lat, $lng, $zoom)
    {
        // Radius of the Earth in kilometers
        $earth_radius = 6371;

        // Google Maps zoom level to meters per pixel
        $zoom_factor = 256 << $zoom; // 256 * 2^zoom (approximating)

        // Convert latitude and longitude degrees to meters
        $lat_to_meters = $earth_radius * (pi() / 180);
        $lng_to_meters = $lat_to_meters * cos(deg2rad($lat));

        // Estimate the number of meters covered by a single pixel
        $meters_per_pixel_lat = $lat_to_meters / $zoom_factor;
        $meters_per_pixel_lng = $lng_to_meters / $zoom_factor;

        // Define the boundary in terms of latitude and longitude
        $lat_diff = $meters_per_pixel_lat * 5000; // Adjust this multiplier to control bounds size
        $lng_diff = $meters_per_pixel_lng * 5000; // Adjust this multiplier to control bounds size

        return [
            'min_lat' => $lat - $lat_diff,
            'max_lat' => $lat + $lat_diff,
            'min_lng' => $lng - $lng_diff,
            'max_lng' => $lng + $lng_diff,
        ];
    }

    /**
     * Renders an email template for agency credentials.
     *
     * @return \Illuminate\View\View
     */
    public function emailtemplate()
    {
        if (config('session.live') === 1) {
            $credentialData = [
                'agency_name' => 'kjbhj',
                'name' => 'jkbjhb',
                'email' => 'lkjbjhv',
                'type' => 'agent',
                'password' => 'lkjbh',
            ];
            $user['name'] = 'sourab';
            $agency['expired_at'] = date('d m Y');

            // return view('email.logincredential', compact('credentialData'));
            return view('email.agency', compact('user', 'agency'));
        }
    }

    /**
     * Fetches a paginated list of suburbs with optional search and city filtering.
     * If no suburbs exist for a city, it attempts to insert them using Google API.
     * Formats the results for Select2 dropdown.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function suburb(Request $request)
    {
        // return $request->all();
        $search = $request->input('q');
        $page = $request->input('page', 1);
        $city_id = $request->input('city_id', '');
        // $state_id = $request->input('state_id', '');

        if ($city_id) {
            $city = City::with(['province', 'country'])->findOrFail($city_id);
            if (!$city->suburb()->first()) {
                PropertyHelper::suburbsInsertByGoogle($city);
            }
        }

        // Query countries with pagination (10 per page)
        $countries = Suburb::query()
            ->when($search, function ($query, $search) {
                return $query->where('suburb_name', 'like', '%' . $search . '%');
            })
            ->when($city_id, function ($query, $city_id) {
                return $query->where('city_id', $city_id);
            })
            ->paginate(10, ['*'], 'page', $page);

        // Format results for Select2
        $results = $countries->getCollection()->map(function ($country) {
            return [
                'id' => $country->id,
                'text' => $country->suburb_name,
            ];
        });

        // Return in Select2 format
        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $countries->hasMorePages()],
        ]);
    }

}
