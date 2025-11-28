<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContainerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ParkController;
use App\Http\Controllers\Admin\ParkImageController;
use App\Http\Controllers\Admin\ParkPendingImageController;
use App\Http\Controllers\Admin\ParkReviewController;
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubadminController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::middleware('guest')->group(function () {
    Route::get('auth', function () {
        return redirect()->route('login');
    });
});

Route::get('auth/login', [AuthController::class, 'index'])->name('login');
Route::post('auth/login/post', [AuthController::class, 'login'])->name('admin.login.post');

Route::get('auth/login/otp', [TwoFactorController::class, 'otp'])->name('admin.login.otp');
Route::post('auth/login/otp_verify', [TwoFactorController::class, 'otp_verify'])->name('admin.login.otp_verify');
Route::get('auth/login/resend_otp', [TwoFactorController::class, 'resendOtp'])->name('admin.login.resend.otp');

Route::middleware(['auth', 'two_factor'])->group(function () {

    Route::get('/home', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::prefix('dashboard')->group(function () {
        Route::get('', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('dashboard-count', [DashboardController::class, 'dashboard_total_count'])->name('admin.dashboard.count');
        Route::get('day-wise-users-parks-bar-chart', [DashboardController::class, 'day_wise_users_and_parks_chart'])->name('admin.dashboard.day_wise_parks_users_bar_chart');
        Route::get('top-five-parks', [DashboardController::class, 'top_five_parks'])->name('admin.dashboard.top.five.parks');
        Route::get('top-five-users', [DashboardController::class, 'top_five_users'])->name('admin.dashoard.top.five.users');
    });

    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('admin.settings');
        Route::get('dt_list', [SettingController::class, 'dt_list'])->name('admin.settings.dt_list');
        Route::get('{user}/reset_password', [SettingController::class, 'aj_reset_password'])->name("admin.aj_reset_password");
        Route::post('password/update', [SettingController::class, 'password_update'])->name("admin.password.update");
        Route::post('profile/update', [SettingController::class, 'update_profile'])->name('admin.settings.profile.update');
        Route::get('create-custom-page', [SettingController::class, 'create_custom_page'])->name('admin.setttings.create.custom.page');
        Route::get('{customPage}/edit-custom-page', [SettingController::class, 'edit_custom_page'])->name('admin.settings.edit.custom.page');
        Route::post('save_custom_page', [SettingController::class, 'save_custom_page'])->name('admin.settings.save.custom.page');
        Route::post('{customPage}/update-custom-page', [SettingController::class, 'update_custom_page'])->name('admin.settings.update.custom.page');
        Route::get('{user}/reset_password', [SettingController::class, 'aj_reset_password'])->name("admin.aj_reset_password");
        Route::post('password/update', [SettingController::class, 'password_update'])->name("admin.password.update");
        Route::get('{user}/reset-profile-img', [SettingController::class, 'reset_img'])->name('admin.reset.profile.img');
    });

    Route::prefix('season')->group(function () {
        Route::get('/', [SeasonController::class, 'season'])->name('admin.season');
        Route::get('dt_list', [SeasonController::class, 'dt_list'])->name('admin.season.dt.list');
        Route::get('{season}/create', [SeasonController::class, 'create'])->name('admin.season.create');
        Route::post('{season}/update', [SeasonController::class, 'update_season'])->name('admin.season.update');
    });

    Route::prefix('categories')->group(function () {
        Route::get('', [CategoryController::class, 'index'])->name('admin.category.index');
        // Route::get('type/{type?}', [CategoryController::class,'index'])->name('admin.category.index');
        Route::get('dt_list', [CategoryController::class, 'dt_list'])->name('admin.category.dt_list');
        Route::get('create', [CategoryController::class, 'create'])->name('admin.category.create');
        Route::get('{category}/edit', [CategoryController::class, 'edit'])->name('admin.category.edit');
        Route::post('save', [CategoryController::class, 'save'])->name('admin.category.save');
        Route::get('{category}/subcategories', [CategoryController::class, 'subcategory_index'])->name('admin.subcategory.index');
        Route::get('subcategory/dt_list', [CategoryController::class, 'subcategory_dt_list'])->name('admin.subcategory.dt_list');
        Route::get('{category}/subcategory/create', [CategoryController::class, 'subcategory_create'])->name('admin.subcategory.create');
        Route::get('subcategory/{subcategory}/edit', [CategoryController::class, 'subcategory_edit'])->name('admin.subcategory.edit');
        Route::post('subcategory/save', [CategoryController::class, 'subcategory_save'])->name('admin.subcategory.save');
        Route::get('get_subcategories', [CategoryController::class, 'get_subcategories'])->name('admin.get_subcategories');
        Route::get('categories_list', [CategoryController::class, 'categories'])->name('admin.categories.list');
        Route::get('{subcategory}/delete-child-category', [CategoryController::class, 'delete_childCategory'])->name('admin.delete.child.category');
        Route::get('{category}/delete-category', [CategoryController::class, 'delete_category'])->name('admin.delete.category');
        Route::get('{subcategory}/update-child-status', [CategoryController::class, 'update_child_status'])->name('admin.update.child.status');
        Route::get('{category}/update-status', [CategoryController::class, 'update_status'])->name('admin.update.status');
        Route::get('{category?}/delete-image', [CategoryController::class, 'deleteImg'])->name('admin.category.delete.image');
        Route::get('{subcategory?}/delete-child-image', [CategoryController::class, 'deleteChildImg'])->name('admin.category.subcategory.delete.image');

        Route::get('priority', [PriorityController::class, 'index'])->name('admin.category.priority');
        Route::get('prioriy-dtlist', [PriorityController::class, 'dt_list'])->name('admin.category.priority.dtlist');
        Route::post('priority-update', [PriorityController::class, 'updatePriority'])->name('admin.category.priority.update');
        Route::get('{category}/list', [CategoryController::class, 'parkcategorylist'])->name('admin.category.parkcategory.list');
        Route::get('parkcategory-dtlist', [CategoryController::class, 'parkCategoryDt'])->name('admin.category.parkcategory.dt_list');

        Route::get('subcategory/parks/dt_list', [CategoryController::class, 'category_parks_dt_list'])->name('admin.category.park.dt_list');
    });

    Route::prefix('feature_types')->group(function () {
        Route::get('', [FeatureController::class, 'index'])->name('admin.feature_type.index');
        Route::get('dt_list', [FeatureController::class, 'dt_list'])->name('admin.feature_type.dt_list');
        Route::get('new_dt_list', [FeatureController::class, 'new_dt_list'])->name('admin.feature_type.new_dt_list');
        Route::get('create', [FeatureController::class, 'create'])->name('admin.feature_type.create');
        Route::get('{feature_type}/edit', [FeatureController::class, 'edit'])->name('admin.feature_type.edit');
        Route::post('save', [FeatureController::class, 'save'])->name('admin.feature_type.save');
        Route::get('{feature_type}/features', [FeatureController::class, 'feature_index'])->name('admin.feature.index');
        Route::get('feature/dt_list', [FeatureController::class, 'feature_dt_list'])->name('admin.feature.dt_list');
        Route::get('{feature_type}/feature/create', [FeatureController::class, 'feature_create'])->name('admin.feature.create');
        Route::get('feature/{feature}/edit', [FeatureController::class, 'feature_edit'])->name('admin.feature.edit');
        Route::post('feature/save', [FeatureController::class, 'feature_save'])->name('admin.feature.save');
        Route::get('get_features', [FeatureController::class, 'get_features'])->name('admin.get_features');
        Route::post('get_child_features', [FeatureController::class, 'child_features_dt'])->name('admin.child_feature.dt');
        Route::get('{feature}/delete_feature', [FeatureController::class, 'delete_feature'])->name('admin.delete.child_feature');
        Route::get('{feature_types}/delete-parent-feature', [FeatureController::class, 'delete_parent_feature'])->name('admin.delete.parent_feature');
        Route::get('{feature}/update-child-feature', [FeatureController::class, 'update_child_feature'])->name('admin.update.child_feature');
        Route::get('{feature_types}/update-status', [FeatureController::class, 'update_feature'])->name('admin.update.feature');
        Route::get('popular-chill-feature-db-list', [FeatureController::class, 'popular_childFeature_dblist'])->name('admin.popular.child.feature.db.list');
        Route::get('{feature_types}/reset-feature-uploaded-img', [FeatureController::class, 'reset_feature_img'])->name('admin.feature.reset.uploaded.img');
        Route::get('{feature}/reset-child-feature-uploaded-img', [FeatureController::class, 'reset_child_img'])->name('admin.feature.reset.child.feature.uploaded.img');
    });

    Route::prefix('parks')->group(function () {
        Route::get('', [ParkController::class, 'index'])->name('admin.park.index');
        Route::get('dt_list', [ParkController::class, 'dt_list'])->name('admin.park.dt_list');
        Route::get('create', [ParkController::class, 'create'])->name('admin.park.create');
        Route::get('{park}/edit', [ParkController::class, 'edit'])->name('admin.park.edit');
        Route::post('save', [ParkController::class, 'save'])->name('admin.park.save');
        Route::get('{park}/delete-park', [ParkController::class, 'delete_park'])->name('admin.delete.park');
        Route::get('{park}/update-status', [ParkController::class, 'update_status'])->name('admin.update.park.status');
        Route::get('{park}/details', [ParkController::class, 'details'])->name('admin.park.details');
        Route::get('unverified_users_images/{park?}', [ParkController::class, 'pending_user_images'])->name('admin.park.unverified_users_images');
        Route::post('unverify_images', [ParkController::class, 'unverify_images'])->name('admin.park.unverify_images');
        Route::post('delete_user_review', [ParkController::class, 'delteUserReview'])->name('admin.park.delete.user.review');
        Route::post('load_more_data', [ParkController::class, 'loadMoreReview'])->name('admin.park.load.more.reviews');
        Route::get('{park}/reviews_dt_list', [ParkController::class, 'reviews_dt_list'])->name('admin.park.user.reviews.dt_list');
    });

    Route::prefix('parks/image')->group(function () {
        Route::get('{park}/upload', [ParkImageController::class, 'upload'])->name('admin.park.image.upload');
        Route::post('save', [ParkImageController::class, 'save'])->name('admin.park.save.image');
        Route::post('delete', [ParkImageController::class, 'delete'])->name('admin.park.delete.image');
        Route::post('set-unset-banner', [ParkImageController::class, 'setUnsetBanner'])->name('admin.park.setunset.banner');
        Route::get('{park}/store', [ParkImageController::class, 'store'])->name('admin.park.store.image');
        Route::get('{park}/edit', [ParkImageController::class, 'edit'])->name('admin.park.image.edit');
        Route::get('{park}/view', [ParkImageController::class, 'view'])->name('admin.park.image.view');
        Route::post('delete-multiple-image', [ParkImageController::class, 'deletMultipleImages'])->name('admin.park.delete.multiple.image');
        Route::post('load-more-image', [ParkImageController::class, 'loadMoreImage'])->name('admin.park.load.more.image');
        Route::post('drag-sort-image', [ParkImageController::class, 'draggableSort'])->name('admin.park.sort.draggable.image');
        Route::post('{park}/filter-image', [ParkImageController::class, 'filter_images'])->name('admin.park.filter.image');
        Route::post('{park}/search-options', [ParkImageController::class, 'searchSelectPickerOptions'])->name('admin.park.search.optiions');
    });

    Route::prefix('users')->group(function () {
        Route::get('', [UserController::class, 'index'])->name('admin.user.index');
        Route::get('dt_list', [UserController::class, 'dt_list'])->name('admin.user.dt_list');
        Route::get('{user}/view', [UserController::class, 'view'])->name('admin.user.view');
        Route::post('verify-images', [UserController::class, 'verifyImges'])->name('admin.user.image.verify');
        Route::post('users-park-images', [UserController::class, 'users_park_images'])->name('admin.user.park.images');
        Route::get('{id}/bookmark_dt_list', [UserController::class, 'bookmark_dt_list'])->name('admin.user.bookmarkt.dt_list');
        Route::get('{user}/unverified_park_images_dt_list', [UserController::class, 'unverified_park_images_dt_list'])->name('admin.user.park.dt.list');
        Route::post('unachivedimage', [UserController::class, 'unachivedImage'])->name('admin.user.park.anachivedimage');
        Route::get('{user}/reviews_dt_list', [UserController::class, 'reviews_dt_list'])->name('admin.user.park.reviews.dt_list');
        Route::get('{user}/active_inactive', [UserController::class, 'active_inactive'])->name('admin.user.active.inactive');
    });

    Route::prefix('subadmin')->group(function () {
        Route::get('', [SubadminController::class, 'index'])->name('admin.subadmin.index');
        Route::get('create', [SubadminController::class, 'create'])->name('admin.subadmin.create');
        Route::get('{id}/edit', [SubadminController::class, 'edit'])->name('admin.subadmin.edit');
        Route::post('{subadmin}/update', [SubadminController::class, 'update'])->name('admin.subadmin.update');
        Route::post('store', [SubadminController::class, 'store'])->name('admin.subadmin.store');
        Route::get('dt_list', [SubadminController::class, 'dt_list'])->name('admin.subadmin.dtlist');
        Route::get('{user}/change_status', [SubadminController::class, 'changeStatus'])->name('admin.subadmin.changestatus');
        Route::get('{user}/reset_password', [SubadminController::class, 'aj_reset_password'])->name('admin.subadmin.resetpassword');
        Route::post('update_password', [SubadminController::class, 'password_update'])->name('admin.subadmin.update.password');
        Route::get('{user}/details', [SubadminController::class, 'view'])->name('admin.subadmin.details');
        Route::get('{user}/park-dt-list', [SubadminController::class, 'park_dt_list'])->name('admin.subadmin.park.dt_list');
        Route::get('{user}/delete', [SubadminController::class, 'delete'])->name('admin.subadmin.delete');
    });

    Route::prefix('pedning_image')->group(function () {
        Route::get('', [ParkPendingImageController::class, 'view'])->name('admin.park.pendingimage');
        Route::get('dt_list', [ParkPendingImageController::class, 'dt_list'])->name('admin.park.pendingimage.dt_list');
        Route::get('{park}/{user}/show_unverified_images', [ParkPendingImageController::class, 'show_unverified_images'])->name('admin.park.pendingimage.view');
        Route::post('verify-unverify-image', [ParkPendingImageController::class, 'verify_unverifyimg'])->name('admin.park.pendingimage.verifyunverify');
        Route::delete('{park}/{user}/deleteUserUploadedImage', [ParkPendingImageController::class, 'deleteUserUploadedImage'])->name('admin.park.delete.user.uploadedimg');
    });

    Route::prefix('pending_review')->group(function () {
        Route::get('', [ParkReviewController::class, 'view'])->name('admin.park.review');
        Route::get('dt_list', [ParkReviewController::class, 'dt_list'])->name('admin.park.pending.review.dt_list');
        Route::get('{rating}/pending-reviews', [ParkReviewController::class, 'pending_reviews'])->name('admin.park.pending.reviews');
        Route::post('verify-review', [ParkReviewController::class, 'verify_review'])->name('admin.park.verify.pending.review');
        Route::get('{rating}/delete', [ParkReviewController::class, 'delete_review'])->name('admin.park.delete.review');
    });

    Route::prefix('delete_account_requests')->group(function () {
        Route::get('', [UserController::class, 'delete_account_index'])->name('admin.delete.index');
        Route::get('dt_list', [UserController::class, 'delete_account_dt_list'])->name('admin.delete.dt_list');
    });

    Route::prefix('/')->name('admin.')->group(function () {
        // Route::resource('locations', LocationController::class);
        Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
        Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
        Route::get('locations/{location}', [LocationController::class, 'show'])->name('locations.show');
        Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
        Route::post('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
        Route::post('locations/{location}/default_container', [LocationController::class, 'update_default_container'])->name('locations.update_default_container');
        Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

        Route::get('locations/{location}/{type}/reset-uploaded-img', [LocationController::class, 'reset_image'])->name('locations.reset.uploaded.img');

        Route::get('locations/{location}/update-status', [LocationController::class, 'update_status'])->name('locations.status');
        // Route::get('uploadLatLng', [LocationController::class, 'uploadLatLng']);

        Route::get('locations/{location}/seo', [LocationController::class, 'seo'])->name('locations.get.seo');
        Route::post('locations/{location}/seo', [LocationController::class, 'update_seo'])->name('locations.update.seo');

        Route::prefix('/locations/{location}')->name('container.')->group(function () {
            Route::get('dt_park_list/{container?}', [ContainerController::class, 'dt_park_list'])->name('dt_park_list');
            Route::get('selected_park_list/{container?}', [ContainerController::class, 'selected_park_list'])->name('selected_park_list');

            Route::post('/container', [ContainerController::class, 'store'])->name('store');
            Route::get('/container/create', [ContainerController::class, 'create'])->name('create');
            Route::get('/container/{container}/edit', [ContainerController::class, 'edit'])->name('edit');
            Route::post('/container/{container}/update', [ContainerController::class, 'update'])->name('update');
            Route::delete('/container/{container}', [ContainerController::class, 'destroy'])->name('destroy');
            Route::get('/reset-uploaded-img/{container}', [ContainerController::class, 'reset_image'])->name('reset.uploaded.img');
        });
    });
});
