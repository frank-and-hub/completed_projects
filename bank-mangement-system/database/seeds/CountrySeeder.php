<?php

use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
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
        DB::table('country')->truncate();
        DB::table('country')->insert([
            'name'         => 'India',
            'code'   => 'IN',
            'status'    => 1,
            'is_deleted'    => 0,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
