<?php

use Illuminate\Database\Seeder;

class ReligionSeeder extends Seeder
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
        DB::table('religions')->truncate();
        DB::table('religions')->insert([[
            'name'         => 'Hindu', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Muslim', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Sikh', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Christian', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Jain', 
            'status'    => 1,
            'is_deleted'    => 0,
        ]]);
       

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
