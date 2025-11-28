<?php

use App\Http\Controllers\api\v1\ApiAuthController;
use App\Http\Controllers\api\v1\CountriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\UserWorkoutPlanController;
use App\Http\Controllers\api\v1\QuestionController;
use App\Http\Controllers\api\v1\ChallengeController;
use App\Http\Controllers\api\v1\SubscriptionController;
use App\Http\Controllers\api\v1\UserWeekWorkoutPlanController;
use App\Http\Controllers\api\v1\ChallengeWeekController;
use App\Http\Controllers\api\v1\ExerciseController;
use App\Http\Controllers\api\v1\MealsController;
use App\Http\Controllers\api\v1\ChallengeSubscriptionController;

// Api Routes
//->middleware(['ApiAuthMiddleware:admin']) // Middleware after prefix

Route::post('/checkEmail', [ApiAuthController::class, 'checkEmail']);
Route::post('/send-push', [NotificationController::class, 'sendPushNotification']);

Route::get('/testtt', [SubscriptionController::class, 'testtt']);


Route::post('/auth/subscription/android-subscriptions-webhook', [SubscriptionController::class, 'androidSubscriptionsWebhook'])->name('androidSubscriptionsWebhook');
Route::post('/auth/subscription/ios-subscriptions-webhook', [SubscriptionController::class, 'iosSubscriptionsWebhook'])->name('iosSubscriptionsWebhook');
Route::get('/auth/subscription/getSubscriptionDetailsv2/{package}/{token}', [SubscriptionController::class, 'getSubscriptionDetailsv2'])->name('getSubscriptionDetailsv2');


Route::controller(ApiAuthController::class)->group(function () {

    Route::post('/signup', 'signup');
    Route::post('/send-otp', 'sendOtpVerification')->middleware('throttle:otp_limit');
    Route::post('/verify-otp', 'verifyOtp');
    Route::post('/reset-password', 'resetPassword')->name('password.reset');
    Route::post('/forgot-password', 'forgotPassword');
});


Route::post('/testResponse', [SubscriptionController::class, 'testResponse']);



// AUTH ROUTES
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public Routes - No authentication required
Route::prefix('auth')->group(function () {

    // User login route (No authentication required)
    Route::post('/login', [ApiAuthController::class, 'login']);
});

// Protected Routes - Authentication required (Requires 'auth:sanctum' middleware)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('auth')->group(function () {

        // Logout route (Authenticated users only)
        Route::controller(ApiAuthController::class)->group(function () {
            Route::post('/logout', 'logout');
            Route::post('/change-password', 'changePassword');
            Route::delete('delete', [ApiAuthController::class, 'destroy']);
            Route::post('update-profile', [ApiAuthController::class, 'updateProfile']);
            Route::post('support-request', [ApiAuthController::class, 'sendSupportEmail']);
        });


        //questions route

        Route::get('/questions', [QuestionController::class, 'index']);
        Route::post('/questionsanswer', [QuestionController::class, 'store']);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Notifications Routes

        Route::post('/sendnotifications', [NotificationController::class, 'sendnotifications']);  // notifications
        Route::get('/getallnotifications', [NotificationController::class, 'getNotifications']); // Get all notifications
        Route::delete('/deletenotifications', [NotificationController::class, 'deleteNotification']); // Delete a specific notification
        Route::get('/get-notification-count', [NotificationController::class, 'getNotificationCount']); // Get notification count
        Route::delete('/delete-all-notifications', [NotificationController::class, 'deleteAllNotifications']); // Delete all notification
        Route::post('/toggle-notifications', [NotificationController::class, 'toggleNotifications']); // Toggle notifications on/off

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        Route::get('/getExercise/{id}', [UserWeekWorkoutPlanController::class, 'getExerciseDetails']);

        Route::get('/challenges', [ChallengeController::class, 'getChallenges']);

        Route::prefix('challenge')->controller(ChallengeWeekController::class)->group(function () {
            //=======28-05-2025========
            // Get current plan
            Route::get('/list', 'challengesPlan')->name('challenge.getCurrent');

            // Get challengesPlans with all weeks
            Route::get('/{id}/weeks', 'getAllWeeksDataByChallengeId')->name('challenge.getAllWeeksDataByChallengeId');

            // Get current plan for a specific week
            Route::get('/{id}/week/{week_no}', 'challengesPlanByWeek')->name('challenge.challengesPlanByWeek');

            // Get current plan for a specific week and day
            Route::get('/{id}/week/{week_no}/day/{day}', 'challengesPlanByWeekDay')->name('challenge.challengesPlanByWeekDay');
            //=========end===========
        });

        Route::prefix('meal')->controller(MealsController::class)->group(function () {
            //=======12-06-2025========
            // Get meal
            Route::get('/getDietPreference', 'getDietPreference')->name('meal.getDietPreference');

            Route::post('/updateDietPreference', 'updateDietPreference')->name('meal.updateDietPreference');

            Route::get('/getMealTypesByDietPreference', 'getMealTypesByDietPreference')->name('meal.getMealTypesByDietPreference');

            Route::post('/detailMeal', 'detailMeal')->name('meal.detailMeal');

            Route::post('/getMealByTypeAndDietPreference', 'getMealByTypeAndDietPreference')->name('meal.getMealByTypeAndDietPreference');

            //=========end===========
        });

        Route::prefix('exercise')->controller(ExerciseController::class)->group(function () {
            //=======16-06-2025========
            // Get exercise by body type
            Route::get('/getBodyTypes', 'getBodyTypes')->name('exercise.getBodyTypes');
            Route::post('/getExercisesByBodyType', 'getExercisesByBodyType')->name('exercise.getExercisesByBodyType');
            Route::post('/getExerciseDetails', 'getExerciseDetails')->name('exercise.getExerciseDetails');

            //=========end===========
        });

        Route::prefix('subscription')->controller(SubscriptionController::class)->group(function () {
            //=======16-06-2025========
            // save purchase log
            Route::post('/savePurchaseLog', 'savePurchaseLog')->name('subscription.savePurchaseLog');
            Route::post('/verifyAndSavePurchase', 'verifyAndSavePurchase')->name('subscription.verifyAndSavePurchase');
            Route::post('/cancelAndroidSubscription', 'cancelAndroidSubscription')->name('subscription.cancelAndroidSubscription');
            Route::get('/getUserSubscriptions', 'getUserSubscriptions')->name('subscription.getUserSubscriptions');
            Route::post('/verifyUserSubscriptions', 'testverifyreceipt')->name('subscription.verifyUserSubscriptions');
            Route::post('/replaceUserSubscription', 'replaceUserSubscription')->name('subscription.replaceUserSubscription');

            Route::post('/iosSubscriptionRestore', 'iosSubscriptionRestore')->name('subscription.iosSubscriptionRestore'); 
            Route::post('/androidSubscriptionRestore', 'androidSubscriptionRestore')->name('subscription.androidSubscriptionRestore');




            //=========end===========
        });

        Route::prefix('challengesubscription')->controller(ChallengeSubscriptionController::class)->group(function () {
            //=======20-06-2025========
            // save purchase log for challenges
            Route::post('/saveChallengeSubscription', 'saveChallengeSubscription')->name('challengesubscription.saveChallengeSubscription');

            //=========end===========
        });

        Route::post('/get-challenges', [ChallengeController::class, 'getChallengeById']);
        Route::post('/workout-exercise-progress', [ChallengeController::class, 'saveWorkoutExerciseProgress']);
        Route::post('/challenge-exercise-progress', [ChallengeController::class, 'saveChallengeExerciseProgress']);
    });
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::prefix('workoutPlan')->controller(UserWeekWorkoutPlanController::class)->group(function () {
            //=======28-05-2025========
            // Get current plan
            Route::get('/getCurrentPlan', 'getCurrentPlanNew')->name('workoutPlan.getCurrent');

            // Get current plan with all weeks
            Route::get('/{id}/allWeeks', 'getCurrentPlanAllWeeks')->name('workoutPlan.getCurrent.allWeeks');

            // Get current plan for a specific week
            Route::get('/{id}/week/{week_no}', 'getCurrentPlanByWeek')->name('workoutPlan.getCurrent.week');

            // Get current plan for a specific week and day
            Route::get('/{id}/week/{week_no}/day/{day}', 'getCurrentPlanByWeekDay')->name('workoutPlan.getCurrent.weekDay');

            Route::get('/{id}/getExercise/{exercise_id}', 'getExerciseDetails');
            //=========end===========
        });

        Route::prefix('workout_plan')->controller(UserWorkoutPlanController::class)->group(function () {

            Route::get('/user/getCurrentPlan', 'getCurrentPlan')->name('getCurrentPlan');
            Route::post('/user/saveCurrentPlan', 'store')->name('saveCurrentPlan');
            Route::post('/user/completeDay', 'completeDay')->name('completeDay');
            Route::post('/admin/assignPlan', 'assignPlan')->name('assignPlan');
            Route::get('/admin/getUserPlans', 'getUserPlans')->name('getUserPlans');
        });
    });
});
Route::fallback(function (Request $request) {
    if ($request->expectsJson() || $request->is('api/*')) { // Important check
        return response()->json(['error' => 'Route Not Found.'], 404);
    }

    // Handle non-API requests (if any) - perhaps redirect
    // return redirect('/404'); // Or whatever is appropriate for your application
});
