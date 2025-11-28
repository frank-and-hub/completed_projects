<?php

namespace Database\Seeders;

use App\Models\WorkoutFrequency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkoutFrequenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => '3 days',
                'days_in_week' => 3,
                'created_at' => now(),
            ],
            [
                'name' => '4 days',
                'days_in_week' => 4,
                'created_at' => now(),
            ],
            [
                'name' => '5 days',
                'days_in_week' => 5,
                'created_at' => now(),
            ],
            [
                'name' => '6 days',
                'days_in_week' => 6,
                'created_at' => now(),
            ],
        ];

        foreach($data as $d){
            WorkoutFrequency::updateOrCreate($d);
        }
    }
}
