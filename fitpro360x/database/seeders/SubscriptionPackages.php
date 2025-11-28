<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionPackages extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table(config('tables.subscription_packages'))->insert([
            [
                'plan_name' => 'Workout Only',
                'type' => 1, // 1 for Monthly , 2 for Yearly
                'duration' => 1, // Duration in months
                'amount' => 75,
                'description' => 'Access to Workout Plans',
                'active' => 1,
                'status' => 1,
                'product_id' => 'com.fitpro360.workoutonly',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'plan_name' => 'Gold',
                'type' => 1, // 1 for Monthly , 2 for Yearly
                'duration' => 1, // Duration in months
                'amount' => 100,
                'description' => 'Access to Workout Plans and all exercises ',
                'active' => 0,
                'status' => 1,
                'product_id' => 'com.fitpro360.gold',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'plan_name' => 'Platinum',
                'type' => 1, // 1 for Monthly , 2 for Yearly
                'duration' => 1, // Duration in months
                'amount' => 125,
                'description' => 'All features of the Workout Only and Gold Plans with additional feature Meals',
                'active' => 1,
                'status' => 1,
                'product_id' => 'com.fitpro360.platinum',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],

        ]);
    }
}
