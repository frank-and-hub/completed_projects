<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChallengePackagesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::parse('2025-06-19 11:54:27');

        DB::table('ft_challenge_packages')->insert([
            [
                'id' => 1,
                'plan_name' => 'Plan One',
                'type' => 2,
                'duration' => null,
                'amount' => null,
                'description' => '300 Squats',
                'product_id' => 'come.fitpro360.planone',
                'active' => 1,
                'status' => 0,
                'created_at' => $now,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'plan_name' => 'Plan Two',
                'type' => 2,
                'duration' => null,
                'amount' => null,
                'description' => '50 Pullups',
                'product_id' => 'com.fitpro360.plantwo',
                'active' => 1,
                'status' => 0,
                'created_at' => $now->copy()->addSeconds(4),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'plan_name' => 'Plan Three',
                'type' => 2,
                'duration' => null,
                'amount' => null,
                'description' => '100 push-ups',
                'product_id' => 'com.fitpro360.planthree',
                'active' => 1,
                'status' => 0,
                'created_at' => $now->copy()->addSeconds(7),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'plan_name' => 'Plan Four',
                'type' => 2,
                'duration' => null,
                'amount' => null,
                'description' => '300 Sit-ups',
                'product_id' => 'com.fitpro360.planfour',
                'active' => 1,
                'status' => 0,
                'created_at' => $now->copy()->addSeconds(10),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'plan_name' => 'Plan Five',
                'type' => 2,
                'duration' => null,
                'amount' => null,
                'description' => '150 Lunges',
                'product_id' => 'com.fitpro360.planfive',
                'active' => 1,
                'status' => 0,
                'created_at' => $now->copy()->addSeconds(13),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'plan_name' => 'Plan Six',
                'type' => 2,
                'duration' => null,
                'amount' => null,
                'description' => '50 Chin-Ups',
                'product_id' => 'com.fitpro360.plansix',
                'active' => 1,
                'status' => 0,
                'created_at' => $now->copy()->addSeconds(16),
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
