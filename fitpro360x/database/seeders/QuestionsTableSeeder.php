<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table(config('tables.questions'))->insert([
          
            [
                'title_for_web' => 'Age',
                'title_for_app' => 'Craft Your Ideal Body',
                'sub_title_for_app' => 'according to your age and BMI',
                'type_for_app' => 1,
                'type_for_web' => 2,
                'showing_in' => 3, // Both
                'question_order_for_web' => 13,
                'question_order_for_app' => 1,
            ],

            [
                'title_for_web' => 'goal',
                'title_for_app' => 'Choose your main goal',
                'sub_title_for_app' => 'according to your age and BMI',
                'type_for_app' => 1,
                'type_for_web' => 2,
                'showing_in' => 3, // Both
                'question_order_for_web' => 1,
                'question_order_for_app' => 2,
            ],
            [
                'title_for_web' => 'Current Looks',
                'title_for_app' => 'How do you think you look right now?',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 2,
                'question_order_for_app' => 3,
            ],
            [
                'title_for_web' => 'Target Areas',
                'title_for_app' => 'Choose your target areas',
                'sub_title_for_app' => null,
                'type_for_app' => 2, // Multiple choice
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 3,
                'question_order_for_app' => 4,
            ],
            [
                'title_for_web' => 'Fitness Range',
                'title_for_app' => 'What`s your level of fitness',
                'sub_title_for_app' => 'On scale of 1 to 10, where would you rate your current fitness level, so I can help tailor a workout plan that fits just right',
                'type_for_app' => 4, // slider
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 4,
                'question_order_for_app' => 5,
            ],
            [
                'title_for_web' => 'Pushups',
                'title_for_app' => 'How many push-ups can you do?',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 5,
                'question_order_for_app' => 6,
            ],
            [
                'title_for_web' => 'Sleep',
                'title_for_app' => 'How much sleep do you get?',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 6,
                'question_order_for_app' => 7,
            ],
            [
                'title_for_web' => 'Water',
                'title_for_app' => 'How much water do you drink daily?',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 7,
                'question_order_for_app' => 8,
            ],

            [
                'title_for_web' => 'Diet',
                'title_for_app' => 'Do you follow any of these diets?',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 8,
                'question_order_for_app' => 9,
            ],
            [
                'title_for_web' => 'Organised Person',
                'title_for_app' => 'Are you an organised person?',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 9,
                'question_order_for_app' => 10,
            ],
            [
                'title_for_web' => 'Height(Feet)',
                'title_for_app' => "What's your current weight?",
                'sub_title_for_app' => null,
                'type_for_app' => 3, 
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 10,
                'question_order_for_app' => 11,
            ],
            [
                'title_for_web' => 'Current Weight(KG)',
                'title_for_app' => "What's your current weight?",
                'sub_title_for_app' => 'We need this data to calculate your BMI and create a personalized plan',
                'type_for_app' => 3, // TEXT
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 11,
                'question_order_for_app' => 12,
            ],
            [
                'title_for_web' => 'Target Weight(KG)',
                'title_for_app' => 'What’s your target weight?',
                'sub_title_for_app' => 'We need this data to calculate your BMI and create a personalized plan',
                'type_for_app' => 3,
                'type_for_web' => 2,
                'showing_in' => 3,
                'question_order_for_web' => 12,
                'question_order_for_app' => 13,
            ],
            [
                'title_for_web' => null,
                'title_for_app' => 'What`s your age?',
                'sub_title_for_app' => 'We need this data to create a workout plan that takes into account age-related changes in metabolism',
                'type_for_app' => 3,
                'type_for_web' => null,
                'showing_in' => 2,
                'question_order_for_web' => null,
                'question_order_for_app' => 14,
            ],
            [
                'title_for_web' => 'Workout location *',
                'title_for_app' => 'Choose your workout location',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => 1,
                'showing_in' => 3,
                'question_order_for_web' => 14,
                'question_order_for_app' => 15,
            ],
            [
                'title_for_web' => null,
                'title_for_app' => 'Summary of your body metrics',
                'sub_title_for_app' => null,
                'type_for_app' => 5,
                'type_for_web' => null,
                'showing_in' => 2,
                'question_order_for_web' => null,
                'question_order_for_app' => 16,
            ],
            [
                'title_for_web' => null,
                'title_for_app' => 'Is there a special occasion you`re aiming to gain muscle for?',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => null,
                'showing_in' => 2,
                'question_order_for_web' => null,
                'question_order_for_app' => 17,
            ],
            [
                'title_for_web' => null,
                'title_for_app' => 'When`s your event?',
                'sub_title_for_app' => 'We`ll keep this important event in mind for your personal fitness plan',
                'type_for_app' => 1,
                'type_for_web' => null,
                'showing_in' => 2,
                'question_order_for_web' => null,
                'question_order_for_app' => 18,
            ],
            [
                'title_for_web' => null,
                'title_for_app' => 'The ultimate plan to loose weight',
                'sub_title_for_app' => 'Get ready to look stunning on your special occasion and reach your dream weight',
                'type_for_app' => 5,
                'type_for_web' => null,
                'showing_in' => 2,
                'question_order_for_web' => null,
                'question_order_for_app' => 19,
            ],
            [
                'title_for_web' => null,
                'title_for_app' => 'Are you ready to make the commitment? ',
                'sub_title_for_app' => null,
                'type_for_app' => 1,
                'type_for_web' => null,
                'showing_in' => 2,
                'question_order_for_web' => null,
                'question_order_for_app' => 20,
            ],
        ]);
    }
}
