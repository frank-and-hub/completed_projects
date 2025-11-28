<?php

use Illuminate\Database\Seeder;

class IdTypesSeeder extends Seeder
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
        DB::table('id_types')->truncate();
        DB::table('id_types')->insert([[
            'name'         => 'Voter ID Card', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Driving Licence', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Aadhar Card', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Passport', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Pan Card No', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Other', 
            'status'    => 1,
            'is_deleted'    => 0,
        ]]);
       

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
