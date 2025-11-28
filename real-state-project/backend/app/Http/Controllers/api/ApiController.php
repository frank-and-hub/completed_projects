<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Helpers\Property as HelpersProperty;
use App\Helpers\ResponseBuilder;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Http\Resources\BestFeaturesResources;
use App\Http\Resources\PlanResource;
use App\Jobs\UpdatePropertiesData;
use App\Models\Bestfeatures;
use App\Models\InternalProperty;
use App\Models\Plans;
use App\Models\Property;
use App\Models\User;
use App\Models\UserSearchProperty;
use App\Models\UserSubscription;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    /**
     * ApiController
     *
     * This controller handles API-related requests for the application.
     * It serves as a base controller for managing API endpoints and their logic.
     *
     * Methods:
     *
     * - features_list():
     *   Retrieves a list of the latest features from the Bestfeatures model and returns them
     *   as a collection of BestFeaturesResources wrapped in a success response.
     *
     * - plan_amount():
     *   Fetches plan details (id, plan_name, amount, type) from the Plans model and returns
     *   them as a collection of PlanResource wrapped in a success response.
     *
     * - test_function(Request $request):
     *   Uses the Google Maps API to search for sublocalities in a specified address (city, state, country).
     *   Filters the results based on specific types and returns an array of matching sublocality names.
     *   Handles pagination using the next_page_token provided by the API.
     *
     * - getCurrentTimeInUTC($timezone = 'Africa/Johannesburg'):
     *   Converts the current time in a specified timezone to UTC and returns it in the format 'H:i:s'.
     */
    public function features_list()
    {
        $featuresData = Bestfeatures::latest()->get();
        $data = BestFeaturesResources::collection($featuresData);
        return ResponseBuilder::success($data, '');
    }

    public function plan_amount()
    {
        $plan = Plans::get(['id', 'plan_name', 'amount', 'type']);
        $resource = PlanResource::collection($plan);
        return ResponseBuilder::success($resource, '');
    }

    /**
     * For
     */
    function test_function(Request $request)
    {

        $apiKey = config('constants.GOOGLE_MAP_BACKEND_KEY');
        $city = 'Africa';
        $state = 'Johannesburg';
        $country = 'South Africa';

        $address = "{$city}, {$state}, {$country}";
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json";


        $searchOnly = [
            "sublocality_level_1",
            "sublocality",
            "political"
        ];

        $notSearchOnly = [
            'sublocality_level_2'
        ];

        $array = [];
        $nextPageToken = null;

        do {

            // Add the nextPageToken to the request if it's available
            $params = [
                'query' => 'sublocality in ' . $address,
                'location' => $address,
                'radius' => 5000,
                'key' => $apiKey,
            ];

            if ($nextPageToken) {
                $params['pagetoken'] = $nextPageToken;
            }

            $response = Http::get($url, $params);

            if ($response->ok()) {
                $results = $response->json()['results'];
                foreach ($results as $result) {
                    $searchOnly_count = count(array_intersect($searchOnly, $result['types'])) > 0;
                    $NotSearchOnly_count = count(array_intersect($notSearchOnly, $result['types'])) == 0;
                    if (($searchOnly_count) && $NotSearchOnly_count) {
                        $array[] = $result['name'];
                    }
                }
            }

            $nextPageToken = $response->json()['next_page_token'] ?? null;

            if ($nextPageToken) {
                sleep(2);
            }
        } while ($nextPageToken);

        return $array;
    }

    function getCurrentTimeInUTC($timezone = 'Africa/Johannesburg')
    {
        $dateTime = new DateTime('now', new DateTimeZone($timezone));
        $dateTime->setTimezone(new DateTimeZone('UTC'));
        return $dateTime->format('H:i:s');
    }
}
