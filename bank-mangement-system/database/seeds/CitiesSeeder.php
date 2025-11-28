<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
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
        DB::table('cities')->truncate();
	    $cities = array(
	    	array('name' => 'Bali', 'district_id' => 27, 'state_id' => 1),
			array('name' => 'Banswara', 'district_id' => 3, 'state_id' => 1),
			array('name' => 'Ajmer', 'district_id' => 1, 'state_id' => 1),
			array('name' => 'Alwar', 'district_id' => 2, 'state_id' => 1),
			array('name' => 'Bandikui', 'district_id' => 4, 'state_id' => 1),
			array('name' => 'Baran', 'district_id' => 5, 'state_id' => 1),
			array('name' => 'Barmer', 'district_id' => 6, 'state_id' => 1),
			array('name' => 'Bikaner', 'district_id' => 9, 'state_id' => 1),
			array('name' => 'Fatehpur', 'district_id' => 31, 'state_id' => 1),
			array('name' => 'Jaipur', 'district_id' => 18, 'state_id' => 1),
			array('name' => 'Jaisalmer', 'district_id' => 19, 'state_id' => 1),
			array('name' => 'Jodhpur', 'district_id' => 23, 'state_id' => 1),
			array('name' => 'Kota', 'district_id' => 25, 'state_id' => 1),
			array('name' => 'Lachhmangarh', 'district_id' => 31, 'state_id' => 1),
			array('name' => 'Ladnu', 'district_id' => 26, 'state_id' => 1),
			array('name' => 'Lakheri', 'district_id' => 10, 'state_id' => 1),
			array('name' => 'Lalsot', 'district_id' => 13, 'state_id' => 1),
			array('name' => 'Losal', 'district_id' => 31, 'state_id' => 1),
			array('name' => 'Makrana', 'district_id' => 26, 'state_id' => 1),
			array('name' => 'Malpura', 'district_id' => 33, 'state_id' => 1),
			array('name' =>'Mandalgarh', 'district_id' => 8, 'state_id' => 1),
			array('name' => 'Mandawa', 'district_id' => 22, 'state_id' => 1),
			array('name' => 'Mangrol', 'district_id' => 5, 'state_id' => 1),
			array('name' => 'Merta City', 'district_id' => 26, 'state_id' => 1),
			array('name' => 'Mount Abu', 'district_id' => 32, 'state_id' => 1),
			array('name' => 'Nadbai', 'district_id' => 7, 'state_id' => 1),
			array('name' => 'Nagar', 'district_id' => 16, 'state_id' => 1),
			array('name' => 'Nagaur', 'district_id' => 26, 'state_id' => 1), 
			array('name' => 'Nasirabad', 'district_id' => 1, 'state_id' => 1),
			array('name' => 'Nathdwara', 'district_id' => 29, 'state_id' => 1), 
			array('name' => 'Nawalgarh', 'district_id' => 22, 'state_id' => 1),
			array('name' => 'Neem-Ka-Thana', 'district_id' => 31, 'state_id' => 1), 
			array('name' =>'Nimbahera', 'district_id' => 11, 'state_id' => 1), 
			array('name' => 'Niwai', 'district_id' => 33, 'state_id' => 1),
			array('name' => 'Nohar', 'district_id' => 17, 'state_id' => 1),
			array('name' =>'Nokha', 'district_id' => 9, 'state_id' => 1),
			array('name' =>'Pali', 'district_id' => 27, 'state_id' => 1),
			array('name' =>'Phalodi', 'district_id' => 23, 'state_id' => 1),
			array('name' => 'Phulera', 'district_id' => 18, 'state_id' => 1),
			array('name' => 'Pilani', 'district_id' => 22, 'state_id' => 1),
			array('name' => 'Pilibanga', 'district_id' => 17, 'state_id' => 1),
			array('name' => 'Pindwara', 'district_id' => 32, 'state_id' => 1),
			array('name' => 'Pipar City', 'district_id' => 23, 'state_id' => 1),
			array('name' => 'Pratapgarh', 'district_id' => 28, 'state_id' => 1),
			array('name' => 'Pushkar', 'district_id' => 1, 'state_id' => 1),
			array('name' => 'Raisinghnagar', 'district_id' => 16, 'state_id' => 1),
			array('name' => 'Rajakhera', 'district_id' => 15, 'state_id' => 1),
			array('name' => 'Rajaldesar', 'district_id' => 12, 'state_id' => 1),
			array('name' => 'Rajgarh', 'district_id' => 2, 'state_id' => 1),
			array('name' => 'Rajgarh', 'district_id' => 12, 'state_id' => 1),
			array('name' =>'Rajsamand', 'district_id' => 29, 'state_id' => 1),
			array('name' => 'Ramganj Mandi', 'district_id' =>25, 'state_id' => 1),
			array('name' =>'Ramngarh', 'district_id' => 31, 'state_id' => 1),
			array('name' => 'Ratangarh', 'district_id' => 12, 'state_id' => 1),
			array('name' => 'Rawatbhata', 'district_id' => 11, 'state_id' => 1),
			array('name' => 'Rawatsar', 'district_id' => 17, 'state_id' => 1),
			array('name' => 'Reengus', 'district_id' => 31, 'state_id' => 1),
			array('name' =>'Sadri', 'district_id' => 27, 'state_id' => 1),
			array('name' => 'Sadulshahar', 'district_id' => 16, 'state_id' => 1),
			array('name' => 'Sagwara', 'district_id' => 15, 'state_id' => 1),
			array('name' => 'Sambhar', 'district_id' => 18, 'state_id' => 1),
			array('name' => 'Sanchore', 'district_id' => 20, 'state_id' => 1),
			array('name' => 'Sangaria', 'district_id' => 17, 'state_id' => 1),
			array('name' =>'Sardarshahar', 'district_id' => 12, 'state_id' => 1),
			array('name' =>'Sawai Madhopur', 'district_id' => 30, 'state_id' => 1),
			array('name' => 'Shahpura', 'district_id' => 18, 'state_id' => 1),
			array('name' => 'Shahpura', 'district_id' => 8, 'state_id' => 1),
			array('name' =>'Sheoganj', 'district_id' => 32, 'state_id' => 1),
			array('name' => 'Sikar', 'district_id' => 31, 'state_id' => 1),
			array('name' =>'Sirohi', 'district_id' => 32, 'state_id' => 1),
			array('name' => 'Sojat', 'district_id' => 31, 'state_id' => 1),
			array('name' => 'Sri Madhopur', 'district_id' => 1, 'state_id' => 1),
			array('name' =>'Sujangarh', 'district_id' => 12, 'state_id' => 1),
			array('name' => 'Sumerpur', 'district_id' => 27, 'state_id' => 1),
			array('name' =>'Suratgarh', 'district_id' => 16, 'state_id' => 1),
			array('name' =>'Taranagar', 'district_id' => 12, 'state_id' => 1),
			array('name' =>'Todabhim', 'district_id' => 24, 'state_id' => 1),
			array('name' =>'Todaraisingh', 'district_id' => 33, 'state_id' => 1),
			array('name' =>'Tonk', 'district_id' => 33, 'state_id' => 1),
			array('name' =>'Udaipur', 'district_id' => 34, 'state_id' => 1),
			array('name' =>'Udaipurwati', 'district_id' => 22, 'state_id' => 1),
			array('name' =>  'Vijainagar', 'district_id' => 1, 'state_id' => 1),
	     );
        foreach ($cities as $city ) {
            DB::table('cities')->insert($city);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
