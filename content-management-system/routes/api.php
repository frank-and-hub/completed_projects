<?php

use App\Http\Controllers\Api\BookmarkListController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ParkController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('', function () {
    return "its working! 😁";
});

//Authentication routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('/send-verification-email', [AuthController::class, 'send_email_verification'])->middleware(['auth:sanctum', 'abilities:verify-email']);
Route::post('/verify-email', [AuthController::class, 'verify_email'])->middleware('auth:sanctum', 'abilities:verify-email');

Route::post('forgot-password', [PasswordResetController::class, 'index']);
Route::post('forgot-password/otp-check', [PasswordResetController::class, 'check_otp'])->middleware(['auth:sanctum', 'abilities:reset-password']);
Route::post('forgot-password/change-password', [PasswordResetController::class, 'change_password'])->middleware(['auth:sanctum', 'abilities:reset-password']);

Route::get('user/signout', [AuthController::class, 'signout'])->middleware(['auth:sanctum']);
Route::get('user/delete', [AuthController::class, 'delete'])->middleware(['auth:sanctum']);
Route::post('user/change_password', [AuthController::class, 'change_password'])->middleware(['auth:sanctum']);

//Data routes without authentication
Route::get('home', [DataController::class, 'home'])->middleware(['auth.optional:sanctum']);

Route::get('parks', [ParkController::class, 'getParks'])->middleware(['auth.optional:sanctum']);
Route::get('parks-list', [ParkController::class, 'getParksList'])->middleware(['auth.optional:sanctum']);
Route::get('parks/slug', [ParkController::class, 'getParksBySlug'])->middleware(['auth.optional:sanctum']);

Route::post('parks/filters', [ParkController::class, 'parks_filter'])->middleware(['auth.optional:sanctum']);
Route::post('search/filters', [ParkController::class, 'search_filter'])->middleware(['auth.optional:sanctum']);
Route::get('categories', [DataController::class, 'getCategories'])->middleware(['auth.optional:sanctum']);

Route::get('category/{category}/subcategories', [DataController::class, 'getSubcategories']);
Route::get('category/{slug}/subcategories/slug', [DataController::class, 'getSubcategoriesBySlug']);

Route::get('category-details/{category}', [DataController::class, 'getCategoryDetails'])->middleware(['auth.optional:sanctum']);
Route::get('category-details-slug', [DataController::class, 'getDetailsBySlug'])->middleware(['auth.optional:sanctum']);

Route::get('features', [DataController::class, 'getFeatures']);
Route::get('features/popular', [DataController::class, 'getFeaturesMap']);
Route::get('park/{park}/details', [ParkController::class, 'getParkDetails'])->middleware(['auth.optional:sanctum']);
Route::get('park/{park}/images', [ParkController::class, 'getParkImages'])->middleware(['auth.optional:sanctum']);
Route::get('park/{park}/ratings', [ParkController::class, 'getRatings'])->middleware(['auth.optional:sanctum']);
Route::get('park/{slug}/details/slug', [ParkController::class, 'getParkDetailsBySlug'])->middleware(['auth.optional:sanctum']);
Route::get('park/{slug}/images/slug', [ParkController::class, 'getParkImagesBySlug'])->middleware(['auth.optional:sanctum']);
Route::get('park/{slug}/ratings/slug', [ParkController::class, 'getRatingsBySlug'])->middleware(['auth.optional:sanctum']);

Route::post('lat-lng-details',[DataController::class, 'lat_lng_details'])->middleware(['auth.optional:sanctum']);
Route::get('pages/{slug}', [DataController::class, 'pages']);
Route::get('meta-data-validation', [DataController::class, 'metaDataValidation']);
Route::get('park/{park}/image-filter-labels', [ParkController::class, 'getNumberOfTotalImages'])->middleware(['auth.optional:sanctum']);
Route::post('parks/pins', [ParkController::class, 'park_map_pins'])->middleware(['auth.optional:sanctum']);

Route::get('park-category/{slug}', [DataController::class, 'parkCategories'])->middleware(['auth.optional:sanctum']);
Route::get('park-feature/{slug}', [DataController::class, 'parkFeatures'])->middleware(['auth.optional:sanctum']);
Route::get('nearby-parks', [DataController::class, 'nearbyParks'])->middleware(['auth.optional:sanctum']);
Route::get('parks-review', [DataController::class, 'parksReview'])->middleware(['auth.optional:sanctum']);
Route::get('top-feature', [DataController::class, 'topFeature'])->middleware(['auth.optional:sanctum']);
Route::get('frequently-asked-questions', [DataController::class, 'f_a_q'])->middleware(['auth.optional:sanctum']);
Route::get('side-map-slug', [DataController::class, 'side_map_slug'])->middleware(['auth.optional:sanctum']);
Route::get('is-feature-in-city', [DataController::class, 'isFeatureInCity'])->middleware(['auth.optional:sanctum']);

Route::middleware(['auth.optional:sanctum'])->group(function () {
    Route::resource('locations', LocationController::class);
    Route::get('location-slug', [LocationController::class, 'showBySlug']);
    Route::get('location-reviews/{location}', [LocationController::class, 'location_reviews']);
    Route::get('top/{slug?}/{type?}', [ParkController::class, 'TopSlug'])->middleware(['auth.optional:sanctum']);
});

Route::controller(DataController::class)->middleware('auth.optional:sanctum')->group(function () {
    Route::post('location-search', 'location_search');
    Route::post('location-info', 'place_search');
    Route::post('global-search', 'global_search');
    Route::get('park/container/slug', 'parkContainer');
});

Route::middleware(['auth:sanctum', 'ability:user'])->group(function () {

    Route::prefix('user')->group(function () {
        Route::prefix('profile')->group(function () {
            Route::get('', [ProfileController::class, 'index']);
            Route::post('update', [ProfileController::class, 'update']);
        });

        Route::post('/park/rating', [ParkController::class, 'add_rating']);
        Route::delete('/park/rating/{id}/delete', [ParkController::class, 'deleteRating']);
        Route::post('/park/image', [ParkController::class, 'add_image']);
        Route::get('park/like', [ParkController::class, 'likePark']);

        Route::get('my-ratings', [ParkController::class, 'my_ratings']);
        Route::get('my-images', [ParkController::class, 'my_park_images_list']);
        Route::get('/park/{id}/my-images', [ParkController::class, 'my_park_images']);

        Route::controller(BookmarkListController::class)->prefix('park/bookmark/list')->group(function () {
            Route::get('', 'list');
            Route::post('create', 'create');
            Route::patch('update', 'update');
            Route::delete('delete', 'delete');
            Route::delete('delete-all', 'delete_all');
        });

        Route::controller(BookmarkController::class)->prefix('park/bookmark')->group(function () {
            Route::get('', 'list');
            Route::post('create', 'create');
            // Route::patch('update','update');
            Route::delete('delete', 'delete');
            Route::post('is_bookmarked', 'is_bookmarked');
        });
    });
});
