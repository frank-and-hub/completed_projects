<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(config('tables.questions'))->insert([
            [
                'title' => 'Craft your ideal body',
                'sub_title' => 'According to your age and BMI',
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'Choose your main goal',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'How do you think you look right now?',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'Choose your target areas',
                'sub_title' => NULL,
                'type' => 'multiple_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'What\'s your level of fitness?',
                'sub_title' => NULL,
                'type' => 'slider',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'How many push-ups can you do?',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'How much sleep do you get?',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'How much water do you drink daily?',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'Do you follow any of these diets?',
                'sub_title' => NULL,
                'type' => 'multiple_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'Are you an organized person?',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:33:10',
                'updated_at' => '2025-04-23 15:33:10',
            ],
            [
                'title' => 'What\'s your height?',
                'sub_title' => NULL,
                'type' => 'text',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
            [
                'title' => 'What\'s your current weight?',
                'sub_title' => NULL,
                'type' => 'text',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
            [
                'title' => 'What\'s your target weight?',
                'sub_title' => NULL,
                'type' => 'text',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
            [
                'title' => 'What\'s your age?',
                'sub_title' => NULL,
                'type' => 'text',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
            [
                'title' => 'Choose your workout location',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
            [
                'title' => 'Is there a special occasion you\'re aiming to gain...',
                'sub_title' => NULL,
                'type' => 'multiple_choice',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
            [
                'title' => 'When\'s your event?',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
            [
                'title' => 'The ultimate way to lose weight',
                'sub_title' => NULL,
                'type' => 'info',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
            [
                'title' => 'Are you ready to make the commitment?',
                'sub_title' => NULL,
                'type' => 'single_choice',
                'created_at' => '2025-04-23 15:51:52',
                'updated_at' => '2025-04-23 15:51:52',
            ],
        ]);
    }
}
