<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictsSeeder extends Seeder
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
        DB::table('districts')->truncate();
	    $districts = array(
	    	
			array('name' => 'Ajmer', 'state_id' => 1),
			array('name' => 'Alwar', 'state_id' => 1),
			array('name' => 'Banswara', 'state_id' => 1),
			array('name' => 'Bandikui', 'state_id' => 1),
			array('name' => 'Baran', 'state_id' => 1),
			array('name' => 'Barmer', 'state_id' => 1),						
			array('name' => 'Bharatpur', 'state_id' => 1,),								
			array('name' => 'Bhilwara', 'state_id' => 1,),
			array('name' => 'Bikaner', 'state_id' => 1),
			array('name' => 'Bundi', 'state_id' => 1),				
			array('name' => 'Chittaurgarh', 'state_id' => 1),
			array('name' => 'Churu', 'state_id' => 1),		
			array('name' => 'Dausa', 'state_id' => 1),
			array('name' => 'Dholpur', 'state_id' => 1),
			array('name' => 'Dungarpur', 'state_id' => 1),
			array('name' => 'Ganganagar', 'state_id' => 1),
			array('name' => 'Hanumangarh', 'state_id' => 1),
			array('name' => 'Jaipur', 'state_id' => 1),
			array('name' => 'Jaisalmer', 'state_id' => 1),
			array('name' => 'Jalor', 'state_id' => 1),
			array('name' => 'Jhalawar', 'state_id' => 1),
			array('name' => 'Jhunjhunu', 'state_id' => 1),			
			array('name' => 'Jodhpur', 'state_id' => 1),
			array('name' => 'Karauli', 'state_id' => 1),
			array('name' => 'Kota', 'state_id' => 1),
			array('name' => 'Nagaur', 'state_id' => 1),
			array('name' =>'Pali', 'state_id' => 1),
			array('name' => 'Pratapgarh', 'state_id' => 1),
			array('name' =>'Rajsamand', 'state_id' => 1),
			array('name' =>'Sawai Madhopur', 'state_id' => 1),
			array('name' => 'Sikar', 'state_id' => 1),
			array('name' =>'Sirohi', 'state_id' => 1),
			array('name' =>'Tonk', 'state_id' => 1),
			array('name' =>'Udaipur', 'state_id' => 1),
	     );
        foreach ($districts as $district ) {
            DB::table('districts')->insert($district);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
