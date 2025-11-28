<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FitnessProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed fitness goals
        $fitnessGoals = [
            ['name' => 'Muscle Gain'],
            ['name' => 'Weight Loss'],
            ['name' => 'Get Shredded']
        ];

        DB::table(config('tables.fitness_goals'))->insert($fitnessGoals);

        // Seed body areas
        $bodyAreas = [
            ['name' => 'Back'],
            ['name' => 'Pecs'],
            ['name' => 'Arms'],
            ['name' => 'Belly'],
            ['name' => 'Legs']
        ];

        DB::table(config('tables.target_areas'))->insert($bodyAreas);

        // Seed body types
        $bodyTypes = [
            ['name' => 'Slim'],
            ['name' => 'Average'],
            ['name' => 'Heavy'],
            ['name' => 'Over-Weight']
        ];

        DB::table(config('tables.current_looks'))->insert($bodyTypes);

        // Seed fitness ranges
        $fitnessRanges = [
            ['name' => '10-24%'],
            ['name' => '25-49%'],
            ['name' => '50-74%'],
            ['name' => '75-100%']
        ];

        DB::table(config('tables.fitness_range'))->insert($fitnessRanges);

        // Seed sleep ranges
        $sleepRanges = [
            ['name' => 'Upto 6 hours'],
            ['name' => '6+ hours']
        ];

        DB::table(config('tables.sleeps'))->insert($sleepRanges);

        // Seed diet types
        $dietTypes = [
            ['name' => 'Veg'],
            ['name' => 'Non-veg'],
            ['name' => 'Keto'],
            ['name' => 'Vegan']
        ];

        DB::table(config('tables.diet_types'))->insert($dietTypes);

        // Seed height ranges
        $heightRanges = [
            ['name' => 'Upto 5 feet'],
            ['name' => '5.1 - 6 feet'],
            ['name' => '6+ feet']
        ];

        DB::table(config('tables.height_ranges'))->insert($heightRanges);

        // Seed weight ranges
        $weightRanges = [
            ['name' => '0-44'],
            ['name' => '45-54'],
            ['name' => '55-64'],
            ['name' => '65-74'],
            ['name' => '75-94'],
            ['name' => '95+']
        ];

        DB::table(config('tables.weight_ranges'))->insert($weightRanges);

        // Target  weight ranges
        $weightRanges = [
            ['name' => '0-44'],
            ['name' => '45-54'],
            ['name' => '55-64'],
            ['name' => '65-74'],
            ['name' => '75-94'],
            ['name' => '95+']
        ];

        DB::table(config('tables.target_weight_ranges'))->insert($weightRanges);

        // Seed age ranges
        $ageRanges = [
            ['name' => '18-29'],
            ['name' => '30-39'],
            ['name' => '40-49'],
            ['name' => '50+']
        ];

        DB::table(config('tables.age_ranges'))->insert($ageRanges);

        // Seed organization levels
        $organizationLevels = [
            ['name' => 'Highly'],
            ['name' => 'Moderate'],
            ['name' => 'Not organised'],
            ['name' => 'Need assistan']
        ];

        DB::table(config('tables.organization_person'))->insert($organizationLevels);

        // Seed water consumption types
        $waterConsumptionTypes = [
            ['name' => 'Only Coffee/Tea'],
            ['name' => '2-6 glasses'],
            ['name' => '6+ glasses']
        ];

        DB::table(config('tables.water_consumption_types'))->insert($waterConsumptionTypes);

        // Seed pushup ranges
        $pushupRanges = [
            ['name' => '12 and above'],
            ['name' => 'Less than 12']
        ];

        DB::table(config('tables.pushups'))->insert($pushupRanges);

        // Seed workout locations
        $workoutLocations = [
            ['name' => 'Home'],
            ['name' => 'Gym']
        ];

        DB::table(config('tables.workout_locations'))->insert($workoutLocations);
    }
}
