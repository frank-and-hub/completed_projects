<?php

use App\Http\Controllers\admin\AdminAuthController;
use App\Http\Controllers\admin\AdminUserController;
use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\ExerciseController;
use App\Http\Controllers\api\v1\ApiAuthController;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// ----------------------- FRONT ROUTES -----------------------
Route::view('/', 'welcome');
Route::view('privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('terms-condition', 'terms-condition')->name('terms-condition');
Route::view('about', 'about')->name('about');
Route::get('test/{id}', [AdminAuthController::class, 'testUser']);
Route::get('phpinfo', fn() => phpinfo())->name('phpinfo');

Route::get('email', function () {
    $otp = '123456';
    $name = 'test';
    return new ForgotPasswordMail($otp, $name);
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    return response()->json(['message' => 'Application cache cleared']);
});

// Redirect /admin -> login
Route::get('/admin{slash?}', fn() => redirect('/admin/login'))
    ->where('slash', '\/?');

// ------------------ APP (MOBILE) AUTH ------------------------
Route::prefix('app')->controller(ApiAuthController::class)->group(function () {
    Route::get('reset-password', 'showResetForm')->name('password.reset.form');
    Route::post('reset-password', 'reset')->name('password.reset.update');
    Route::view('thankyou', 'thankyou')->name('thankyou');
});

// ----------------------- ADMIN AUTH -------------------------
Route::prefix('admin')->name('admin.')->middleware(['guest:admin', 'prevent-back-history'])->group(function () {
    Route::view('login', 'admin.auth.login')->name('login');
    Route::view('forgot-password', 'admin.auth.forgot-password')->name('forgotPassword');

    Route::controller(AdminAuthController::class)->group(function () {
        Route::post('login', 'loginAuth')->name('loginAuth');
        Route::get('otp-verify', 'otpForm')->name('otpForm');
        Route::post('otp-verify', 'verifyOtp')->name('verifyOtp');
        Route::post('resend-otp', 'resendOtp')->name('resendOtp');
        Route::post('forgot-password', 'sendResetToken')->name('sendResetToken');
        Route::get('password/reset/{token}', 'showResetForm')->name('showResetForm');
        Route::post('password/reset/{token}', 'resetPassword')->name('resetPassword');
        Route::post('password/reset', 'passwordReset')->name('passwordReset');
    });
});

// ---------------------- ADMIN PANEL -------------------------
Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'prevent-back-history'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::view('profile', 'admin.profile.index')->name('profile');

    Route::post('update-status', [AdminDashboardController::class, 'updateStatus'])->name('updateStatus');
    Route::get('change-password', [AdminAuthController::class, 'changePassowrd'])->name('password.change');
    Route::post('update-password', [AdminAuthController::class, 'updatePassword'])->name('password.update');

    // Users
    Route::prefix('users')->controller(AdminUserController::class)->group(function () {
        Route::get('/', 'index')->name('userIndex');
        Route::delete('delete/{id?}', 'userDelete')->name('userDelete');
    });

    // Sidebar toggle
    Route::post('toggle-sidebar', function () {
        $current = session('menu_collapse', false);
        session(['menu_collapse' => !$current]);
        return response()->json(['status' => 'success', 'collapsed' => !$current]);
    })->name('toggle.sidebar');

    // Exercises
    Route::prefix('exercises')->controller(ExerciseController::class)->group(function () {
        Route::get('', 'index')->name('exerciseIndex');
        Route::get('add', 'exerciseAdd')->name('exerciseAdd');
        Route::post('save', 'exerciseSave')->name('exerciseSave');
        Route::get('edit/{id?}', 'exerciseEdit')->name('exerciseEdit');
        Route::post('update/{id?}', 'exerciseUpdate')->name('exerciseUpdate');
        Route::delete('delete/{id?}', 'exerciseDelete')->name('exerciseDelete');

        Route::get('meso/{id}/weeks', 'getMesoWeeks')->name('exerciseMesoWeeks');
        Route::get('check-existing-exercise-data', 'checkExistingData')->name('checkExistingExerciseData');
    });
});
