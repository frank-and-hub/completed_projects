<?php

use Illuminate\Database\Seeder;

class OccupationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::unguard();
        DB::table('occupations')->truncate();
        DB::table('occupations')->insert([[
            'name'         => 'Government Employee', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Private Employee', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Self Employees', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'House wife', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Farmer', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Student', 
            'status'    => 1,
            'is_deleted'    => 0,
        ]]);
       

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
