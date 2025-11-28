<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(create_admin::class);
        $this->call(EnquiryEmail::class);
        $this->call(PropertyRangeprice::class);
        $this->call(PlanAmount::class);
    }
}
