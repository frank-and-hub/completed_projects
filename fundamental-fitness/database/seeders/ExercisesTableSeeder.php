<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExercisesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Bench Press', 'status' => 1, 'created_at' => now()],
            ['name' => 'Single Arm Dumbbell Press', 'status' => 1, 'created_at' => now()],
            ['name' => 'Single arm standing ohp', 'status' => 1, 'created_at' => now()],
            ['name' => 'Single arm tricep extension', 'status' => 1, 'created_at' => now()],
            ['name' => 'Running', 'status' => 1, 'created_at' => now()],
            ['name' => 'Deadlift', 'status' => 1, 'created_at' => now()],
            ['name' => 'Single arm row', 'status' => 1, 'created_at' => now()],
            ['name' => 'Single arm lat pull over', 'status' => 1, 'created_at' => now()],
            ['name' => 'Squat', 'status' => 1, 'created_at' => now()],
            ['name' => 'Single leg step ups', 'status' => 1, 'created_at' => now()],
        ];

        foreach ($data as $d) {
            Exercise::updateOrCreate($d);
        }
    }
}
