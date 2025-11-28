<?php

namespace App\Jobs;

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Suburb;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateProvinceCitySuburb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }



    public function handle(): void
    {
        try {
            $auth = base64_encode(config('services.entegral.username') . ":" . config('services.entegral.password'));

            $confi_url  = config("services.entegral.entegral_area_url");

            $countries = explode(',',config('constants.ENTEGRAL_AREA_URL_COUNTRY'));//['South Africa', 'Namibia', 'zimbabwe', 'nigeria', 'kenya'];

            foreach ($countries as $country_n) {
                if($country = Country::where('name', $country_n)->first()){

                    $confi_url_upddate = $confi_url.'?country='.$country_n;

                    $response = Http::withHeaders([
                        "Authorization" => "Basic $auth"
                    ])->get($confi_url_upddate);

                    if ($response->successful()) {
                        $data = collect($response->json());

                        // Process data in chunks of 1000
                        $data->chunk(1000)->each(function ($chunk) use ($country) {
                            foreach ($chunk as $item) {
                                try {
                                    $province = Province::firstOrCreate([
                                        'province_name'     => $item['province'],
                                        'country_id'        => $country->id
                                    ]);

                                    $town = City::firstOrCreate([
                                        'city_name'         => $item['town'],
                                        'province_id'       => $province->id,
                                        'country_id'        => $country->id
                                    ]);

                                    Suburb::firstOrCreate([
                                        'suburb_name'   => $item['suburb'],
                                        'province_id'   => $province->id,
                                        'city_id'       => $town->id,
                                    ]);

                                } catch (\Exception $e) {
                                    Log::error("Error processing item: " . json_encode($item) . ", Error: " . $e->getMessage());
                                }
                            }
                        });
                    } else {
                        Log::error("Failed to fetch data from API, Status: " . $response->status());
                    }
                }

            }


        } catch (\Exception $e) {
            Log::error($e);
            Log::error("Error in handle method: " . $e->getMessage());
        }
    }
    
    /**
     * Execute the job.
     */
    public function handle_old(): void
    {
        try {
            $auth = base64_encode(config('services.entegral.username') . ":" . config('services.entegral.password'));
            $response = Http::withHeaders([
                "Authorization" => "Basic $auth"
            ])->get(config("services.entegral.entegral_area_url"));

            if ($response->successful()) {
                $data = collect($response->json());

                // Process data in chunks of 1000
                $data->chunk(1000)->each(function ($chunk) {
                    foreach ($chunk as $item) {
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
                });
            } else {
                Log::error("Failed to fetch data from API, Status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Error in handle method: " . $e->getMessage());
        }
    }
}
