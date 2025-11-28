<?php

use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = array([
        	'id' => 1,
            'name' => 'RAJASTHAN',
            'code' => '1011',
            'country_id' => '1',
        ],[
	        'id' => 2,
            'name' => 'HIMACHAL PRADESH',
            'code' => '1211',
            'country_id' => '1',
        ], [
        	'id' => '3',
        	'name' => 'MADYAPRADESH',
            'code' => '1461',
            'country_id' => '1',
            ],
            [
	            'id' => '4',
                'name' => 'HARYANA',
                'code' => '1711',
                'country_id' => '1',
            ],
            [
	            'id' => '5',
                'name' => 'UTTARPRADESH',
                'code' => '3211',
                'country_id' => '1',
            ],[
		        'id' => '6',
                'name' => 'GUJARAT',
                'code' => '3711',
                'country_id' => '1',
            ],[
		        'id' => '7',
                'name' => 'UTTARANCHAL',
                'code' => '3961',
                'country_id' => '1',
            ],[
		        'id' => '8',
                'name' => 'MAHARASHTRA',
                'code' => '4461',
                'country_id' => '1',
            ],[
		        'id' => '9',
                'name' => 'PUNJAB',
                'code' => '4711',
                'country_id' => '1',
            ],[
		        'id' => '10',
                'name' => 'DELHI',
                'code' => '4811',
                'country_id' => '1',
            ],[
		        'id' => '11',
                'name' => 'GOA',
                'code' => '4911',
                'country_id' => '1',
            ],[
		        'id' => '12',
                'name' => 'ANDRAPRADESH',
                'code' => '5061',
                'country_id' => '1',
            ],[
		        'id' => '13',
                'name' => 'TAMILNADU',
                'code' => '5211',
                'country_id' => '1',
            ],[
		        'id' => '14',
                'name' => 'KARNATAKA',
                'code' => '5361',
                'country_id' => '1',
            ],[
		        'id' => '15',
                'name' => 'TELAGANA',
                'code' => '5461',
                'country_id' => '1',
            ],[
		        'id' => '16',
                'name' => 'BIHAR',
                'code' => '5961',
                'country_id' => '1',
            ],[
		        'id' => '17',
                'name' => 'CHHATTISGARH',
                'code' => '6061',
                'country_id' => '1',
            ],[
		        'id' => '18',
                'name' => 'WESTBENGAL',
                'code' => '6561',
                'country_id' => '1',
            ],[
		        'id' => '19',
                'name' => 'JHARKHAND',
                'code' => '7061',
                'country_id' => '1',
            ],[
		        'id' => '20',
                'name' => 'ASSAM',
                'code' => '7161',
                'country_id' => '1',
            ],[
		        'id' => '21',
                'name' => 'ARUNACHALPRADESH',
                'code' => '7261',
                'country_id' => '1',
            ],[
		        'id' => '22',
                'name' => 'MANIPUR',
                'code' => '7361',
                'country_id' => '1',
            ],[
		        'id' => '23',
                'name' => 'MEGHALAYA',
                'code' => '7461',
                'country_id' => '1',
            ],[
		        'id' => '24',
                'name' => 'MIZORAM',
                'code' => '7561',
                'country_id' => '1',
            ],[
		        'id' => '25',
                'name' => 'NAGALAND',
                'code' => '7661',
                'country_id' => '1',
            ],[
		        'id' => '26',
                'name' => 'ORISSA',
                'code' => '8161',
                'country_id' => '1',
            ],[
		        'id' => '27',
                'name' => 'SIKKIM',
                'code' => '8261',
                'country_id' => '1',
            ],[
		        'id' => '28',
                'name' => 'TRIPURA',
                'code' => '8361',
                'country_id' => '1',
            ],[
		        'id' => '29',
                'name' => 'KERALA',
                'code' => '8611',
                'country_id' => '1',
            ] );
        foreach ($states as $state ) {
            DB::table('states')->insert($state);
        }
    }
}
