<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the role seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::unguard();
        DB::table('roles')->truncate();
        DB::table('roles')->insert([[
            'name'         => 'Super Admin', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Admin', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Branch Manager', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Associate Members', 
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'Members', 
            'status'    => 1,
            'is_deleted'    => 0,
        ]]);
       

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
