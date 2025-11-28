<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnquiryEmail extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = [
            'email' => 'team4pairroxz@gmail.com',
        ];

        \App\Models\EnquiryEmail::firstOrCreate(["email" => $email['email']], $email);
    }
}
