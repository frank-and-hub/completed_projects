<?php

namespace App\Console\Commands;

use App\Models\Parks;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateParkLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:park-location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $token_ = config('services.MAP_BOX_ACCESS_TOKEN');
            $guzzle = new \GuzzleHttp\Client();
            $parks = Parks::whereNull('city')->get();
            foreach ($parks as $park) {
                $lng = $park->longitude;
                $lat = $park->latitude;
                $request = $guzzle->get("https://api.mapbox.com/geocoding/v5/mapbox.places/$lng,$lat.json?access_token=$token_"); // Url of your choosing
                $res = json_decode($request->getBody(), true);
                $context = $res['features'];
                array_filter($context, function ($element) use ($park) {
                    if (explode('.', $element['id'])[0] == 'place') {
                        $park->update([
                            'city' => $element['text']
                        ]);
                    }
                });
                sleep(1); // to avoid hitting API rate limits
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
