<?php

namespace App\Console\Commands;

use App\Models\Parks;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateParkTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:park-timezone';

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
            $parks= Parks::whereNull('timezone')->get();
            foreach($parks as $park){
                $lng=$park->longitude;
                $lat=$park->latitude;
                $request = $guzzle->get("https://api.mapbox.com/v4/examples.4ze9z6tv/tilequery/$lng,$lat.json?access_token=$token_");// Url of your choosing
                $res = json_decode($request->getBody(),true);
                $timezone = $res['features'][0]['properties']['TZID'];
                $park->update([
                    'timezone'=>$timezone
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
