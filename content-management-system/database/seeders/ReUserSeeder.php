<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Parks;
use App\Services\OpenAIService;
use App\Traits\CommonTraits;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReUserSeeder extends Seeder
{
    use CommonTraits;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->locationSeeder();
        // $this->parkSlugSeeder();
        // $this->openAiSeeder();
        // $this->AddAllLocationsInDb();
        $this->UpdateLatLngLocationsInDb();
    }

    public function locationSeeder()
    {
        // Location::query()->update([
        //     'city_slug' => null,
        //     'country_short_name' => null
        // ]);

        $locations = Location::all();

        // $countryShourName = [
        //     'United States' => 'us',
        //     'Canada' => 'ca',
        //     'Israel' => 'il',
        //     'Latvia' => 'lv',
        //     'Poland' => 'pl',
        // ];

        foreach ($locations as $location) {
            // Normalize the city name to create a slug
            // $slug = $this->generateUniqueSlug($location->city, null, Location::class, 'city_slug');
            // Update the city_slug field
            $location->update([
                // 'city_slug' => $slug,
                // 'country_short_name' => $countryShourName[$location->country] ?? null,
                'state_slug' => Str::slug($location->state)
            ]);
        }
    }

    public function parkSlugSeeder()
    {
        foreach (Parks::select('id', 'name', 'country', 'state', 'city')->get() as $park) {
            DB::beginTransaction();
            try {
                $stateSlug = Str::slug($park->state);
                $citySlug = Str::slug($park->city);
                $park->update([
                    'city_slug' => $citySlug,
                    'state_slug' => $stateSlug,
                ]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
            }
        }
    }

    public function openAiSeeder()
    {
        $openAI = new OpenAIService();
        $locations = Location::all();
        try {
            foreach ($locations as $location) {
                $prompt = "Write a unique SEO meta description (max 160 characters) for a park directory website focused on $location->city, $location->state, $location->country. Highlight outdoor activities, public parks, or recreational features. Make it catchy and location-specific.";
                $seoDescription = $openAI->generateText($prompt);
                $location->update([
                    'seo_description' => $seoDescription,
                ]);
            }
        } catch (\Exception $e) {
            echo "Failed to fetch answer: " . $e->getMessage() . "\n";
        }
    }

    public function AddAllLocationsInDb()
    {
        $path = database_path('data/world.csv');

        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception("CSV file not found or not readable.");
        }

        $batchSize = 100;
        $locations = [];
        $now = now();

        // Step 1: Build a set of existing city+state+country combinations from DB
        $existingCombinations = DB::table('locations')
            ->select('city', 'state', 'country')
            ->get()
            ->map(function ($item) {
                return strtolower(trim($item->city)) . '|' . strtolower(trim($item->state)) . '|' . strtolower(trim($item->country));
            })
            ->toArray();

        $existingSet = array_flip($existingCombinations); // For fast lookup

        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 0, ',');

            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $row = array_combine($header, $data);

                $key = strtolower(trim($row['city'])) . '|' . strtolower(trim($row['state'])) . '|' . strtolower(trim($row['country']));

                // Step 2: Skip if the combination already exists
                if (isset($existingSet[$key])) {
                    continue;
                }

                $locations[] = [
                    'city' => $row['city'],
                    'city_slug' => Str::slug($row['city']),
                    'state' => $row['state'],
                    'state_slug' => Str::slug($row['state']),
                    'country' => $row['country'],
                    'country_short_name' => Str::slug($row['country_short_name']),
                    'title' => '',
                    'subtitle' => '',
                    'status' => false,
                    'default_container_id' => '',
                    'location_latitude' => $row['latitude'],
                    'location_longitude' => $row['longitude'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($locations) >= $batchSize) {
                    $this->upsertLocations($locations);
                    $locations = [];
                }
            }

            // Insert remaining
            if (!empty($locations)) {
                $this->upsertLocations($locations);
            }

            fclose($handle);
        }
    }

    public function upsertLocations(array $locations)
    {
        DB::table('locations')->upsert(
            $locations,
            ['city', 'state'], // unique keys
            [ // fields to update on conflict
                'city_slug',
                'state_slug',
                'country_short_name',
                'title',
                'subtitle',
                'status',
                'default_container_id',
                'location_latitude',
                'location_longitude',
                'updated_at'
            ]
        );
    }

    public function bulkUpdateLatLng(array $rows)
    {
        try {
            DB::beginTransaction();
            foreach ($rows as $row) {
                DB::table('locations')
                    ->where('city', $row['city'])
                    ->where('state', $row['state'])
                    ->where('country', $row['country'])
                    ->where('country_short_name', Str::slug($row['country_short_name']))
                    ->update([
                        'location_latitude' => $row['location_latitude'],
                        'location_longitude' => $row['location_longitude'],
                        'updated_at' => $row['updated_at'],
                    ]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in bulkUpdateLatLng: " . $e->getMessage());
            return;
        }
    }

    public function UpdateLatLngLocationsInDb()
    {
        $path = database_path('data/world.csv');

        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception("CSV file not found or not readable.");
        }

        $batchSize = 100;
        $now = now();

        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 0, ',');

            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $row = array_combine($header, $data);

                $updates[] = [
                    'city' => $row['city'],
                    'state' => $row['state'],
                    'country' => $row['country'],
                    'location_latitude' => $row['latitude'],
                    'location_longitude' => $row['longitude'],
                    'updated_at' => $now,
                ];

                if (count($updates) >= $batchSize) {
                    $this->bulkUpdateLatLng($updates);
                    $updates = [];
                }
            }

            if (!empty($updates)) {
                $this->bulkUpdateLatLng($updates);
            }

            fclose($handle);
        }
    }
}
