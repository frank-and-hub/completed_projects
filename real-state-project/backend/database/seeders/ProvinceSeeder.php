<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use App\Models\Province;
use App\Models\State_City;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1');
        // $this->insertProvince();
        $this->insertCity();
    }

    protected function insertProvince()
    {
        $existingProvinceNames = array_flip(Province::pluck('province_name')->toArray());

        State::chunk(1000, function ($provinces) use ($existingProvinceNames) {
            $insertProvince = [];
            $count = count($provinces);

            for ($i = 0; $i < $count; $i++) {
                $province = $provinces[$i];

                if (!isset($existingProvinceNames[$province->name])) {
                    $insertProvince[] =  [
                        'id' => (string) Str::uuid(),
                        'province_name' => $province->name,
                        'country_id'    => $province->country_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($insertProvince)) {
                Province::insert($insertProvince);
            }
        });
    }

    protected function insertCity()
    {
        $startTime = microtime(true);
        // Step 2: Fetch all states to map state_id -> state_name
        $stateIdToName = State::pluck('name', 'id')->toArray();
        $existingCityNames = array_flip(City::pluck('city_name')->toArray());
        $provinceMap = Province::all()->keyBy('province_name');

        // Step 3: Create cities from State_City
        State_City::doesntHave('city')
            ->chunk(1000, function ($cities) use ($stateIdToName, $existingCityNames, $provinceMap) {
                $insertData = [];
                $count = count($cities);
                DB::beginTransaction();
                try {
                    for ($i = 0; $i < $count; $i++) {
                        $city = $cities[$i];

                        $stateName = $stateIdToName[$city->state_id] ?? null;
                        if (!$stateName) {
                            continue;
                        }

                        $province = $provinceMap[$stateName] ?? null;
                        if (!$province) {
                            continue;
                        }

                        if (!isset($existingCityNames[$city->name])) {
                            $insertData[] = [
                                'id' => (string) Str::uuid(),
                                'city_name' => $city->name,
                                'province_id' => $province->id,
                                'country_id' => $province->country_id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                    }

                    // Batch insert
                    if (!empty($insertData)) {
                        City::insert($insertData);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    log::error('Error inserting cities', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'data' => $insertData
                    ]);
                    return false;
                }
            });

        Log::debug('done seeding');
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        Log::info('insertCity() executed in ' . $executionTime . ' seconds');
    }
}
