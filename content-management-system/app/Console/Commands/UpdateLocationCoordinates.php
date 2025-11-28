<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateLocationCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:update-coordinates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update location latitude and longitude using Mapbox API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $accessToken = config('services.MAP_BOX_ACCESS_TOKEN'); // Store your token in config/services.php or .env
        if (!$accessToken) {
            $this->error('Mapbox access token not found.');
            return 1;
        }

        Location::chunk(1, function ($locations) use ($accessToken) {
            foreach ($locations as $location) {
                $query = trim($location->city . ', ' . $location->state . ', ' . $location->country);
                $this->info("Fetching coordinates for: $query");

                $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/" . urlencode($query) . ".json", [
                    'access_token' => $accessToken,
                    'limit' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['features'][0]['center'])) {
                        $longitude = $data['features'][0]['center'][0];
                        $latitude = $data['features'][0]['center'][1];

                        if ($location->location_longitude !== $longitude) {
                            $location->location_longitude = $longitude;
                        }

                        if ($location->location_latitude !== $latitude) {
                            $location->location_latitude = $latitude;
                        }

                        $location->save();

                        $this->info("Updated: lat=$latitude, lng=$longitude");
                    } else {
                        $this->warn("No coordinates found for: $query");
                    }
                } else {
                    $this->error("Failed API request for: $query");
                }

                sleep(2); // to avoid hitting API rate limits
            }
        });
        $this->info('Update complete.');

        // return 0;
        return Command::SUCCESS;
    }
}
