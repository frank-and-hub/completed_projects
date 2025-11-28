<?php

use App\Http\Controllers\api\v1\ApiAuthController;
use App\Http\Controllers\api\v1\ApiSubscriptionController;
use App\Http\Controllers\api\v1\SocialLoginController;
use App\Http\Controllers\api\v1\WorkOutController;
use App\Http\Controllers\api\v1\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\MesoCycleController;

Route::post('checkEmail', [ApiAuthController::class, 'checkEmail']);
Route::post('social-login', [SocialLoginController::class, 'socialLogin']);
Route::controller(ApiAuthController::class)->group(function () {
    Route::post('signup', 'signup');
    Route::post('send-otp', 'sendOtpVerification')->middleware('throttle:otp_limit');
    Route::post('verify-otp', 'verifyOtp');
    Route::post('reset-password', 'resetPassword');
    Route::post('forgot-password', 'forgotPassword');
});

Route::get('user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')
    ->group(function () {
        Route::post('login', [ApiAuthController::class, 'login'])->name('login');
        Route::post('social-login', [SocialLoginController::class, 'socialLogin']);
    });

// Route::prefix('/webhook')
//     ->controller(ApiSubscriptionController::class)
//     ->group(function () {
//         Route::post('android', 'androidWebhook');
//         Route::post('ios', 'iosWebhook');
//     });

Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::prefix('auth')
            ->controller(ApiAuthController::class)
            ->group(function () {
                Route::post('logout', 'logout');
                Route::post('change-password', 'changePassword');
                Route::delete('delete', 'destroy');
                Route::post('update-profile', 'updateProfile');
                Route::get('profile', 'profile');
                Route::get('profile-detail', 'userProfileDetail');
                Route::post('support-request', 'sendSupportEmail');
                Route::post('reset-profile', 'resetProfile');
                Route::post('update-notification-status', 'update_notifications_status');
            });
        Route::prefix('purchase')
            ->controller(ApiSubscriptionController::class)
            ->group(function () {
                Route::post('buy', 'buyProducts');
                Route::get('get-subscription', 'getSubscription');
            });
    });

Route::fallback(function (Request $request) {
    if ($request->expectsJson() || $request->is('api/*')) { // Important check
        return response()->json(['error' => 'User Not Found.'], 404);
    }
});

Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::prefix('mesho-cycle')
            ->controller(MesoCycleController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('user-dashboard', 'userDashboard');
                Route::get('list', 'list');
            });

        Route::prefix('workout')
            ->controller(WorkOutController::class)
            ->group(function () {
                Route::post('mark-completed', 'markCompleted');
                Route::post('update-workout-process', 'updateWorkoutProcess');
                Route::get('get-exercise', 'getExercise');
                Route::get('get-completed-exercises', 'getCompletedExerciseSetId');
                Route::post('update-sets-weight', 'updateSetsWeight');
                Route::post('video-count', 'videoCount');
                Route::get('logs', 'workoutLogs');
                Route::get('log-filter', 'logFilterData');
                Route::get('reset-exercise-set', 'setReset');
            });

        Route::controller(NotificationController::class)
            ->prefix('notifications')
            ->group(function () {
                Route::get('', 'index');
                Route::post('{id}/read', 'markAsRead');
                Route::post('read-all', 'markAllAsRead');
                Route::delete('{id}',  'destroy');
                Route::delete('delete/all',  'destroy_all');
            });
    });
