<?php

namespace Database\Seeders;

use App\Models\PropertyRange;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyRangeprice extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data  = [
            'start_price' => '2000',
            'end_price' => '50000',
            'currency' => 'ZAR'
        ];

        PropertyRange::create($data);
    }
}
