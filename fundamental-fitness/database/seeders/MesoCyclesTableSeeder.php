<?php

namespace Database\Seeders;

use App\Models\MesoCycle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MesoCyclesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Meso 1',
                'workout_frequency_id' => 1,
                'week_number' => 1,
                'notes' => null,
                'created_at' => '2025-09-17 06:46:21',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'name' => 'Meso 2',
                'workout_frequency_id' => 1,
                'week_number' => 2,
                'notes' => null,
                'created_at' => '2025-09-18 06:46:23',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'name' => 'Meso 3',
                'workout_frequency_id' => 1,
                'week_number' => 3,
                'notes' => null,
                'created_at' => '2025-09-24 06:46:25',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'name' => 'Meso 4',
                'workout_frequency_id' => 1,
                'week_number' => 4,
                'notes' => null,
                'created_at' => '2025-09-24 06:46:27',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ];
        foreach ($data as $d) {
            MesoCycle::updateOrCreate($d);
        }
    }
}
