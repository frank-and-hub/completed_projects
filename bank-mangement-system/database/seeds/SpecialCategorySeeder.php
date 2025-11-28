<?php

use Illuminate\Database\Seeder;

class SpecialCategorySeeder extends Seeder
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
        DB::table('special_categories')->truncate();
        DB::table('special_categories')->insert([[
            'name'         => 'Physically Challenge', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'General Senior Citizen', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Ex-Service Man', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Widow only', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Military Retired', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Woman', 
            'status'    => 1,
            'is_deleted'    => 0,
        ]]);
       

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
