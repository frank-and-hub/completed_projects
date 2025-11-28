<?php

namespace Database\Seeders;

use App\Models\Week;
use Illuminate\Database\Seeder;

class WeeksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Week 1',
                'week_number' => 1,
                'created_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Week 2',
                'week_number' => 1,
                'created_at' => now(),
                'deleted_at' => now(),
            ],
            [
                'name' => 'Week 3',
                'week_number' => 2,
                'created_at' => now(),
                'deleted_at' => now(),
            ],
            [
                'name' => 'Week 4',
                'week_number' => 1,
                'created_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach($data as $d){
            Week::updateOrCreate($d);
        }
    }
}
