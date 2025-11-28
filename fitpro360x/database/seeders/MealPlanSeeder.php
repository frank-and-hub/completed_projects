<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MealPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(config('tables.meal_plans'))->insert([
            [
                'type' => 'Breakfast',
                'title' => 'Oatmeal Power Bowl',
                'image' => NULL,
                'description' => NULL,
                'created_at' => '2025-04-23 18:29:02',
                'updated_at' => '2025-04-23 18:29:02',
                'deleted_at' => '2025-04-23 18:29:02',
            ],
            [
                'type' => 'Breakfast',
                'title' => 'High-Protein Scramble',
                'image' => NULL,
                'description' => NULL,
                'created_at' => '2025-04-23 18:29:02',
                'updated_at' => '2025-04-23 18:29:02',
                'deleted_at' => '2025-04-23 18:29:02',
            ],
            [
                'type' => 'Lunch',
                'title' => '',
                'image' => NULL,
                'description' => NULL,
                'created_at' => '2025-04-23 18:29:02',
                'updated_at' => '2025-04-23 18:29:02',
                'deleted_at' => '2025-04-23 18:29:02',
            ],
            [
                'type' => 'Dinner',
                'title' => '',
                'image' => NULL,
                'description' => NULL,
                'created_at' => '2025-04-23 18:29:02',
                'updated_at' => '2025-04-23 18:29:02',
                'deleted_at' => '2025-04-23 18:29:02',
            ],
        ]);
    }
}
