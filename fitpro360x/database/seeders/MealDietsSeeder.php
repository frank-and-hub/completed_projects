<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class MealDietsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(config('tables.meal_diets'))->insert([
            [
                'name' => 'Veg',
                'description' => 'Achieve a healthy weight faster than others',
            ],
            [
                'name' => 'Non-veg',
                'description' => '',
            ],
            [
                'name' => 'Vegan',
                'description' => 'Excludes all animal products',
            ],
            [
                'name' => 'Keto',
                'description' => 'Low-carb, high-fat',
            ],
        ]);

    }
}
