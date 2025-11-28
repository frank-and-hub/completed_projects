<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
// use function App\helper.php\notifyUser;
require_once app_path('helpers.php');

class SendMealNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-meal-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nowIST = Carbon::now('Asia/Kolkata')->format('H:i');

        $mealConfig = [
            '08:00' => [
                'type'    => 'Breakfast',
                'title'   => "Breakfast Called — It’s Lonely ☕🥚",
                'message' => "Power up your morning! Your meal plan’s all set to fuel the day ahead."
            ],
            '13:00' => [
                'type'    => 'Lunch',
                'title'   => "Don’t Ghost Your Lunch 🍛",
                'message' => "Midday magic is here — time to refuel like a pro. Stick to the plan, champion."
            ],
            '19:00' => [
                'type'    => 'Dinner',
                'title'   => "Dinner’s on Deck 🍽️",
                'message' => "Wrap up the day strong — your balanced meal is waiting. You’ve earned it."
            ],
        ];

        $mealMeta = $mealConfig[$nowIST] ?? null;

        if (!$mealMeta) {
            Log::info("No meal type matched for current time: $nowIST");
            return Command::SUCCESS;
        }


        $users = User::whereNotNull('device_id')
            ->whereHas('activeWorkoutPlan.meals')
            ->whereHas('subscriptions', function($q) {
                $q->where('subscription_id', 3)
                  ->where('expires_at', '>', now());
            })
            //user must not be soft deleted
            ->whereNull('deleted_at')
            // ->whereIn('id', function ($query) {
            //     $query->select('ft_user_subscriptions.user_id')
            //         ->from('ft_user_subscriptions')
            //         ->join('ft_user_workout_plans', function ($join) {
            //             $join->on('ft_user_subscriptions.user_id', '=', 'ft_user_workout_plans.user_id')
            //                 ->on('ft_user_subscriptions.workout_program_id', '=', 'ft_user_workout_plans.workout_program_id')
            //                 ->where('ft_user_workout_plans.is_active', 1);
            //         })
            //         ->where('ft_user_subscriptions.subscription_id', 3);
            // })

            ->with('activeWorkoutPlan.meals')
            ->get();

        if ($users->isEmpty()) {
            Log::info("No users found with active workout plans and meals for meal alert ({$mealMeta['type']})");
            return Command::SUCCESS;
        }

        Log::info("Final eligible users: " . $users->count());

        foreach ($users as $user) {
            Log::info("Sending meal alert to user {$user->id}");

            notifyUser([
                'deviceToken' => $user->device_id,
                'type'        => 'is_meal',
                'title'       => $mealMeta['title'],
                'message'     => $mealMeta['message'],
                'readcount'   => 0,
                'item'        => "Meal Alerts",
                'id'          => $user->id,
                'user_id'     => $user->id,
                'meal_id'     => '0',
                'meal_type'   => $mealMeta['type'],
            ]);
        }


        return Command::SUCCESS;
    }
//     public function handle()
// {
//     $mealConfig = [
//         [
//             'type'    => 'Breakfast',
//             'title'   => "Breakfast Called — It’s Lonely ☕🥚",
//             'message' => "Power up your morning! Your meal plan’s all set to fuel the day ahead."
//         ],
//         [
//             'type'    => 'Lunch',
//             'title'   => "Don’t Ghost Your Lunch 🍛",
//             'message' => "Midday magic is here — time to refuel like a pro. Stick to the plan, champion."
//         ],
//         [
//             'type'    => 'Dinner',
//             'title'   => "Dinner’s on Deck 🍽️",
//             'message' => "Wrap up the day strong — your balanced meal is waiting. You’ve earned it."
//         ],
//     ];

//     // Rotate every 2 minutes
//     $minute = Carbon::now('Asia/Kolkata')->minute;
//     $mealIndex = floor($minute / 2) % 3;

//     // Safety check to avoid undefined index
//     if (!isset($mealConfig[$mealIndex])) {
//         Log::warning("Invalid meal index: $mealIndex at minute $minute");
//         return Command::SUCCESS;
//     }

//     $mealMeta = $mealConfig[$mealIndex];

//     Log::info("Selected meal for minute $minute (index $mealIndex): " . $mealMeta['type']);

//     $users = User::whereNotNull('device_id')
//         ->whereHas('activeWorkoutPlan.meals')
//         ->whereHas('subscriptions', function($q) {
//                 $q->where('subscription_id', 3)
//                   ->where('expires_at', '>', now());
//             })
//         ->with('activeWorkoutPlan.meals')
//         ->get();

//     if ($users->isEmpty()) {
//         Log::info("No users found for meal alert ({$mealMeta['type']})");
//         return Command::SUCCESS;
//     }

//     Log::info("Sending meal notifications to " . $users->count() . " users");

//     foreach ($users as $user) {
//         Log::info("Sending {$mealMeta['type']} alert to user {$user->id}");

//         notifyUser([
//             'deviceToken' => $user->device_id,
//             'type'        => 'is_meal',
//             'title'       => $mealMeta['title'],
//             'message'     => $mealMeta['message'],
//             'readcount'   => 0,
//             'item'        => "Meal Alerts",
//             'id'          => $user->id,
//             'user_id'     => $user->id,
//             'meal_id'     => '0',
//             'meal_type'   => $mealMeta['type'],
//         ]);
//     }

//     return Command::SUCCESS;
// }

}
