<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class MealDietPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Vegan',
            'Veg',
            'Non-Veg',
            'Keto',
            'Mixed',
        ];

        foreach ($data as $label) {
            DB::table(config('tables.meal_diet_preferences'))->insert([
                'name' => $label,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
