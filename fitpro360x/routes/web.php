<?php

use App\Http\Controllers\admin\AdminAuthController;
use App\Http\Controllers\admin\AdminUserController;
use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\AdminWorkoutController;
use App\Http\Controllers\admin\MuscleMasterController;
use App\Http\Controllers\admin\BodyTypeController;
use App\Http\Controllers\admin\ExerciseController;
use App\Http\Controllers\admin\MealsPlanController;
use App\Http\Controllers\admin\FitnessChallengeController;
use App\Http\Controllers\api\v1\ApiAuthController;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('home', function () {
    return view('welcome');
});
Route::get('/privacy-policies', function () {
    return view('privacy-policies');
});
Route::get('/terms-and-conditions', function () {
    return view('terms-and-conditions');
});
Route::get('/about', function () {
    return view('about');
});


//forgot password/reset api app
Route::view('/privacy-policy', 'privacy_policy')->name('privacy-policy');
Route::view('/terms-condition', 'terms_condition')->name('terms-condition');
Route::prefix('app')->controller(ApiAuthController::class)->group(function () {
    Route::get('/reset-password/{token}', 'showResetForm')->name('password.reset');
    Route::post('/reset-password', 'reset')->name('password.reset.app');

    Route::get('/thankyou', function () {
        return view('thankyou');
    })->name('thankyou');
});
// ---------------------------------- ADMIN ----------------------------------------------



Route::get('/forgot-password', [AdminAuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/reset-password', [AdminAuthController::class, 'reset'])->name('password.new');
Route::view('/login', 'admin.auth.login')->name('login')->middleware('isAdminLogin', 'prevent-back-history');
Route::view('/admin', 'admin.auth.login')->name('login')->middleware('isAdminLogin', 'prevent-back-history');
Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetForm'])->name('password.reset');
Route::post('update_password', [AdminAuthController::class, 'updatePassword'])->name('password.update');
Route::controller(AdminAuthController::class)->group(function () {
    Route::get('/reset-password', 'showResetForm')->name('password.reset');
});

Route::prefix('admin')->name('admin.')->middleware(['guest:admin'])->group(function () {

    Route::view('/login', 'admin.auth.login')->name('login');

    Route::controller(AdminAuthController::class)->group(function () {
        Route::post('login', 'loginAuth')->name('loginAuth');
    });
});


///////////////////////////////////////////////////////////////////////////
Route::prefix('admin')->name('admin.')->middleware('auth:admin', 'prevent-back-history')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::view('/profile', 'admin.profile.index')->name('getProfile');

    Route::post('update-status', [AdminDashboardController::class, 'updateStatus'])->name('updateStatus'); //ACTIVE INACTIVE
    Route::get('change_password', [AdminAuthController::class, 'changePassowrd'])->name('password.change');
    Route::post('update_password', [AdminAuthController::class, 'updatePassword'])->name('password.update');

    // admin user routes
    Route::get('users', [AdminUserController::class, 'index'])->name('userIndex');

    // admin workout plan routes  
    Route::get('workout-plan', [AdminWorkoutController::class, 'index'])->name('workoutPlans');
    Route::get('admin/workout-create', [AdminWorkoutController::class, 'createWorkout'])->name('createWorkout');
    Route::post('workout-save', [AdminWorkoutController::class, 'workoutSave'])->name('workoutSave');
    Route::post('/workout_update', [AdminWorkoutController::class, 'workoutupdate'])->name('workoutupdate');
    Route::delete('delete/{id}', [AdminWorkoutController::class, 'workoutPlanDelete'])->name('workoutPlanDelete');
    Route::get('/admin/meals', [AdminWorkoutController::class, 'getMealsByPreference'])->name('get.meals.by.preference');

    Route::post('/update_meal', [AdminWorkoutController::class, 'saveUserMeals'])->name('saveUserMeals');
    Route::get('/filter_meal', [AdminWorkoutController::class, 'mealfilter'])->name('mealfilter');

    Route::get('/diet-preference/{slug}', [AdminWorkoutController::class, 'show']);

    Route::prefix('muscle')->controller(MuscleMasterController::class)->group(function () {
        Route::get('/', 'index')->name('muscleIndex');
        Route::post('save', 'muscleSave')->name('muscleSave');
        Route::get('edit/{id}', 'muscleEdit')->name('muscleEdit');
        Route::post('update/{id}', 'muscleUpdate')->name('muscleUpdate');
        Route::delete('delete/{id}', 'muscleDelete')->name('muscleDelete');
    });

    Route::prefix('body-type')->controller(BodyTypeController::class)->group(function () {
        Route::get('/', 'index')->name('bodyTypeIndex');
        Route::post('save', 'bodyTypeSave')->name('bodyTypeSave');
        Route::get('edit/{id}', 'bodyTypeEdit')->name('bodyTypeEdit');
        Route::post('update/{id}', 'bodyTypeUpdate')->name('bodyTypeUpdate');
        Route::delete('delete/{id}', 'bodyTypeDelete')->name('bodyTypeDelete');
    });

    Route::prefix('meals-plan')->controller(MealsPlanController::class)->group(function () {
        Route::get('/', 'index')->name('mealsPlanIndex');
        Route::post('save', 'mealsPlanSave')->name('mealsPlanSave');
        Route::get('edit/{id}', 'mealsPlanEdit')->name('mealsPlanEdit');
        Route::post('update/{id}', 'mealsPlanUpdate')->name('mealsPlanUpdate');
        Route::delete('delete/{id}', 'mealsPlanDelete')->name('mealsPlanDelete');
        Route::get('meal-details', 'getMealDetails')->name('admin.getMealDetails');
    });

    Route::prefix('exercise')->controller(ExerciseController::class)->group(function () {
        Route::get('/', 'index')->name('exerciseIndex');
        Route::get('/add', 'exerciseAdd')->name('exerciseAdd');
        Route::post('save', 'exerciseSave')->name('exerciseSave');
        Route::get('edit/{id}', 'exerciseEdit')->name('exerciseEdit');
        Route::post('update/{id}', 'exerciseUpdate')->name('exerciseUpdate');
        Route::delete('delete/{id}', 'exerciseDelete')->name('exerciseDelete');
        Route::get('check-status/{id?}', 'checkExerciseStatus')->name('check_exercise_status');
    });

    Route::post('/admin/toggle-sidebar', function () {
        $current = session('menu_collapse', false);
        session(['menu_collapse' => !$current]);
        return response()->json(['status' => 'success', 'collapsed' => !$current]);
    })->name('toggle.sidebar');

    Route::prefix('workout-plan')->controller(AdminWorkoutController::class)->group(function () {
        Route::get('/', 'index')->name('workoutPlansIndex');
        Route::get('/add', 'workoutPlansAdd')->name('workoutPlansAdd');
        Route::post('save', 'store')->name('workoutPlansSave');
        Route::get('/workout_settings/{program_id}', 'workout')->name('workout');

        Route::get('/filter_meal', 'mealfilter')->name('mealfilter');
        Route::post('/workout_update/{programId}', 'workoutupdate')->name('workoutupdate');
        Route::get('edit/{id}', 'workoutPlansEdit')->name('workoutPlansEdit');
        Route::post('update/{id}', 'workoutPlansUpdate')->name('workoutPlansUpdate');
        Route::get('update/{id}', 'workoutPlansUpdate')->name('workoutPlansUpdate');
        Route::delete('delete/{id}', 'workoutPlansDelete')->name('workoutPlansDelete');
        Route::get('check-status/{id?}', 'checkWorkoutPlanStatus')->name('check_workout_plan_status');

        Route::get('edit-workout-settings/{id}', 'workoutSettingsEdit')->name('workoutSettingsEdit');
        Route::post('update-workout-settings/{id}', 'workoutSettingsUpdate')->name('workoutSettingsUpdate');
        Route::get('update-workout-settings/{id}', 'workoutSettingsUpdate')->name('workoutSettingsUpdate');
        Route::get('/create_meal/{id}', 'meal')->name('meal');
        Route::post('/save-meal/{id}', 'saveUserMeals')->name('saveUserMeals');
        Route::get('/get-meals-by-preference', 'getMealsByPreference')->name('getMealsByPreference');
        Route::get('/render-meal-section', 'renderMealSection')->name('renderMealSection');
        Route::get('/edit-workout-meal/{id}', 'editWorkoutMeal')->name('editWorkoutMeal');
        Route::post('/update-workout-meal/{id}', 'updateWorkoutMeal')->name('updateWorkoutMeal');
    });
    Route::prefix('fitness-challenge')->controller(FitnessChallengeController::class)->group(function () {
        Route::get('/', 'index')->name('fitnessChallengeIndex');
        Route::get('/add', 'fitnessChallengeAdd')->name('fitnessChallengeAdd');
        Route::post('save', 'fitnessChallengeSave')->name('fitnessChallengeSave');
        Route::get('edit/{id}', 'fitnessChallengeEdit')->name('fitnessChallengeEdit');
        Route::post('update/{id}', 'fitnessChallengeUpdate')->name('fitnessChallengeUpdate');
        Route::delete('delete/{id}', 'fitnessChallengeDelete')->name('fitnessChallengeDelete');
    });

    Route::get('/exercises/details', [FitnessChallengeController::class, 'getExerciseDetails'])
        ->name('exercises.details');
});
