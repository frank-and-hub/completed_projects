<?php

use Illuminate\Database\Seeder;

class FaCodeSeeder extends Seeder
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
        DB::table('fa_codes')->truncate();
        DB::table('fa_codes')->insert([[
            'name'         => 'MEMBER ID',
            'code'  => '701',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'ASSOCITE JOINING',
            'code'  => '702',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'SAVING ACCOUNT',
            'code'  => '703',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'RECURRING DEPOSIT',
            'code'  => '704',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'FLEXI FIXED DEPOSIT',
            'code'  => '705',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'FIXED DEPOSIT',
            'code'  => '706',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'FLEXI RECURRING DEPOSIT',
            'code'  => '707',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'MONEY BACK',
            'code'  => '708',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'KANYADAN',
            'code'  => '709',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'DAILY DEPOSIT',
            'code'  => '710',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'GULLAK PLAN',
            'code'  => '711',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'MONTHLY INCOME SCHEME',
            'code'  => '712',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'JEEVAN BANDHAN',
            'code'  => '713',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'STAFF LOAN',
            'code'  => '714',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'PERSONAL LOAN',
            'code'  => '715',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'DEPOSIT AGAINST LOAN',
            'code'  => '716',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'GROUP LOAN',
            'code'  => '717',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'SAMRADDH BHAVISHYA',
            'code'  => '718',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'CERTIFICATE CODE',
            'code'  => '719',
            'status'    => 1,
            'is_deleted'    => 0,
        ],
        [
            'name'         => 'PASSBOOK CODE',
            'code'  => '720',
            'status'    => 1,
            'is_deleted'    => 0,
        ]]);
       

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
