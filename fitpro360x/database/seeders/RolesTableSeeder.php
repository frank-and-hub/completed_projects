<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table(config('tables.roles'))->insert([
            ['name' => 'Admin'],
            ['name' => 'User'],
        ]);
    }
}
