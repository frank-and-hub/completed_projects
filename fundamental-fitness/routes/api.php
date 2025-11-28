<?php

use App\Http\Controllers\api\v1\ApiAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Api Routes
Route::prefix('v1')
    ->group(function () {

        Route::post('checkEmail', [ApiAuthController::class, 'checkEmail']);

        Route::controller(ApiAuthController::class)->group(function () {
            Route::post('signup', 'signup');
            Route::post('send-otp', 'sendOtpVerification')->middleware('throttle:otp_limit');
            Route::post('verify-otp', 'verifyOtp');
            Route::post('forgot-password', 'forgotPassword')->middleware('throttle:otp_limit');
            Route::post('forgot-otp-verify', 'forgotPasswordVerifyOtp');
            Route::post('reset-password', 'resetPassword');
        });

        // AUTH ROUTES
        Route::get('user', function (Request $request) {
            return $request->user();
        })->middleware('auth:sanctum');

        // Public Routes - No authentication required
        Route::prefix('auth')->group(function () {
            // User login route (No authentication required)
            Route::post('login', [ApiAuthController::class, 'login']);
        });

        // Protected Routes - Authentication required (Requires 'auth:sanctum' middleware)
        Route::middleware(['auth:sanctum'])->group(function () {

            // Group all routes under the 'auth' prefix for authenticated users
            Route::prefix('auth')->group(function () {

                // Logout route (Authenticated users only)
                Route::controller(ApiAuthController::class)->group(function () {
                    Route::post('logout', 'logout');
                    Route::post('change-password', 'changePassword');
                    Route::delete('delete', [ApiAuthController::class, 'destroy']);
                });
            });
        });
    });

Route::prefix('v1')->group(function () {
    require base_path('routes/api_v1.php');
});

Route::prefix('v2')->group(function () {
    require base_path('routes/api_v2.php');
});

Route::fallback(function (Request $request) {
    if ($request->expectsJson() || $request->is('api/*')) { // Important check
        return response()->json(['error' => 'User Not Found.'], 404);
    }

    // Handle non-API requests (if any) - perhaps redirect
    // return redirect('404'); // Or whatever is appropriate for your application
});
