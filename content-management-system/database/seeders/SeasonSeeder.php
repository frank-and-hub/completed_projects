<?php

namespace Database\Seeders;

use App\Models\Seasons;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SeasonSeeder extends Seeder
{
    public function run()
    {

        $seasons = [
            [
                'hemisphere' => 'north',
                'season' => 'winter',
                'start_date' => 'Dec 01',
                'end_date' => 'Feb 28',
                'season_start_date' => '2022-12-01',
                'season_end_date' => '2023-02-28',
            ],
            [
                'hemisphere' => 'north',
                'season' => 'spring',
                'start_date' => 'March 01',
                'end_date' => 'May 31',
                'season_start_date' => '2023-03-01',
                'season_end_date' => '2023-05-31',
            ],
            [
                'hemisphere' => 'north',
                'season' => 'summer',
                'start_date' => 'June 01',
                'end_date' => 'Aug 31',
                'season_start_date' => '2023-06-01',
                'season_end_date' => '2023-08-31',
            ],
            [
                'hemisphere' => 'north',
                'season' => 'autumn',
                'start_date' => 'Sep 01',
                'end_date' => 'Nov 30',
                'season_start_date' => '2023-09-01',
                'season_end_date' => '2023-11-30',
            ],

            // Southern season

            [
                'hemisphere' => 'south',
                'season' => 'summer',
                'start_date' => 'Dec 01',
                'end_date' => 'Feb 28',
                'season_start_date' => '2022-12-01',
                'season_end_date' => '2023-02-28',
            ],
            [
                'hemisphere' => 'south',
                'season' => 'autumn',
                'start_date' => 'March 01',
                'end_date' => 'May 31',
                'season_start_date' => '2023-03-01',
                'season_end_date' => '2023-05-31',
            ],
            [
                'hemisphere' => 'south',
                'season' => 'winter',
                'start_date' => 'June 01',
                'end_date' => 'Aug 31',
                'season_start_date' => '2023-06-01',
                'season_end_date' => '2023-08-31',
            ],
            [
                'hemisphere' => 'south',
                'season' => 'spring',
                'start_date' => 'Sep 01',
                'end_date' => 'Nov 30',
                'season_start_date' => '2023-09-01',
                'season_end_date' => '2023-11-30',
            ],

        ];


        foreach ($seasons as $season) {
            // Log::debug($season);
            Seasons::updateOrCreate(['season' => $season['season'], 'hemisphere' => $season['hemisphere']], $season);
        }
    }
}
