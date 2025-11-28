<?php

namespace Database\Seeders;

use App\Models\PlanFeature;
use App\Models\Plans;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanAmount extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'plan_name' => 'Basic',
                'type'      => 'agency',
                'amount' => '39.99',
                'plan_feature'  => [
                    [
                        'planType_value'    => 1,
                        'planType'          => 'months'
                    ]
                ]
            ],
            [
                'plan_name' => 'Basic',
                'type'      => 'privatelandlord',
                'amount' => '69.99',
                'plan_feature'  => [
                    [
                        'planType_value'    => 1,
                        'planType'          => 'count'
                    ]
                ]
            ]
        ];

        foreach ($plans as $data) {
            $plan = Plans::firstOrcreate([
                'plan_name' => $data['plan_name'],
                'type'      => $data['type']
            ],[
                'amount'    => $data['amount']
            ]);

            foreach (['count', 'months'] as $key => $value) {
                # code...
                $planFeature = PlanFeature::firstOrCreate([
                    'plan_id'   => $plan->id,
                    'planType'  => $value
                ],[
                    'planType_value'    => 1
                ]);
            }
        }
    }
}
