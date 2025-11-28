<?php

use Illuminate\Database\Seeder;

class CardersSeeder extends Seeder
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
        DB::table('carders')->truncate();
	    $carders = array(
	    	array('name' => 'Sales Associate', 'short_name' => 'Carder-1'),
	    	array('name' => 'Sr. Sales Associate', 'short_name' => 'Carder-2'),
	    	array('name' => 'Sales Organizer', 'short_name' => 'Carder-3'),
	    	array('name' => 'Sr. Sales Organizer', 'short_name' => 'Carder-4'),
	    	array('name' => 'Sales Co-ordinator', 'short_name' => 'Carder-5'),
	    	array('name' => 'Sr. Sales Co-ordinator', 'short_name' => 'Carder-6'),
	    	array('name' => 'Sales Executive', 'short_name' => 'Carder-7'), 
	    	array('name' => 'Sr. Sales Executive', 'short_name' => 'Carder-8'),
	    	array('name' => 'Regional Sales Manager', 'short_name' => 'Carder-9'),
	    	array('name' => 'Sr. Regional Sales Manager', 'short_name' => 'Carder-10'),
	    	array('name' => 'Area Sales Manager', 'short_name' => 'Carder-11'),
	    	array('name' => 'Sr. Area Sales Manager', 'short_name' => 'Carder-12'),
	    	array('name' => 'Divisional Manager', 'short_name' => 'Carder-13'),
	    	array('name' => 'Sr. Divisional Manager', 'short_name' => 'Carder-14'),
	    	array('name' => 'Zonal Sales Manager', 'short_name' => 'Carder-15'),
	     );
        foreach ($carders as $val ) {
            DB::table('carders')->insert($val);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
