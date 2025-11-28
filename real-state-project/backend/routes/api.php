<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\api\CalendarController;
use App\Http\Controllers\api\ApiController;
use App\Http\Controllers\api\apiSaleCotroller;
use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\ContactusController;
use App\Http\Controllers\api\CreditReportController;
use App\Http\Controllers\api\DemoDataController;
use App\Http\Controllers\api\FaceDetectionController;
use App\Http\Controllers\api\paymentController;
use App\Http\Controllers\api\PropertyController;
use App\Http\Controllers\api\SocialLoginController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\WhatsappController;
use App\Http\Controllers\adminsubuser\PlanController as AdminSubUserPlanController;
use App\Http\Controllers\api\PostmanAPIController;
use App\Http\Middleware\PostmenAuthenticateApi;
use App\Http\Middleware\PropertyAuthCheckMiddleware;
use AWS\CRT\HTTP\Request;
use Illuminate\Support\Facades\Route;

$LIVE = env('LIVE', 0);
if ($LIVE == 1) {

    //admin notifiy route
    Route::post('/payfast_notify/{adminSubscription_id}/{admin_id}', [AdminSubUserPlanController::class, 'payfast_notify'])->name('web.payfast_notify');

    Route::controller(CreditReportController::class)->prefix('/webhook')->group(function () {
        Route::post('initiate-credit-report', 'initiateCreditReport');
        Route::post('getverified/credit-report', 'handleWebhook');
        Route::get('encryptPdf/{id?}', 'downloadAndEncryptPdf');
        Route::get('{t?}/{id?}', 'downloadAndDecryptPdf')->name('download_and_decrypt_Pdf');
    });

    Route::group(['prefix' => 'v1'], function () {
        Route::any('', function () {
            return 'its working pocket property! 😁';
        });

        Route::controller(apiSaleCotroller::class)->group(function () {
            Route::get('propertyneeds', 'listings')->name('listings');
        });

        Route::controller(paymentController::class)->group(function () {
            Route::get('payment-success/{subscription_id}/{user_id}', 'payfast_success')->name('payfast_success');
            Route::get('payment-cancel', 'payfast_cancel')->name('payfast_cancel');
            Route::post('payment-notify/{subscription_id}/{user_id}', 'payfast_notify')->name('payfast_notify');
        });

        Route::group(['prefix' => 'invite'], function () {
            Route::controller(CalendarController::class)->group(function () {
                Route::get('accept/{url_id}/{id}', 'accept_event')->name('accept_event');
                Route::match(['get', 'post'],'decline/{url_id}/{id}', 'decline_event')->name('decline_event');
                Route::post( 'reschedule/{url_id}/{id}', 'reschedule_event')->name('reschedule_event');
                Route::get('thank_you/{id}', 'thank_you')->name('thank_you');
            });
        });

        Route::controller(AuthenticationController::class)->group(function () {
            Route::post('register', 'register');
            Route::post('resend-otp', 'resend_otp');
            Route::post('otp-verify', 'otp_verify');
            Route::post('login', 'login');
            Route::post('forgot-password', 'forgot_password');
            Route::post('set-password', 'set_password');
        });

        Route::controller(AuthController::class)->group(function () {
            // Route::post('agent-signup', 'agentSignUp'); // trash
            Route::post('privatelandlord-signup', 'privatelandlord_signup');
            Route::post('privatelandlord-verify', 'privatelandlord_verify');
            Route::post('privatelandlord-resend-otp', 'privatelandlord_resend_otp');
            Route::post('agency-signup', 'agencySignup');
        });

        Route::controller(SocialLoginController::class)->group(function () {
            Route::post('social-login', 'social_login');
        });

        Route::controller(ContactusController::class)->group(function () {
            Route::post('contact-us', 'contact_us');
        });

        Route::controller(ApiController::class)->group(function () {
            Route::get('features-list', 'features_list');
            Route::get('plans-amount', 'plan_amount');
        });

        Route::controller(PropertyController::class)->group(function () {
            Route::middleware([PropertyAuthCheckMiddleware::class])->group(function () {
                Route::get('property-details/{id}/{type?}', 'property_detail');
                Route::get('internal-property-details/{id}', 'internal_property_detail');
            });

            Route::get('/countries', 'countries');
            Route::get('/states/{id}', 'states');
            Route::get('/cities/{id}', 'cities');
            Route::get('/suburbs/{id}', 'suburbs');
            Route::post('advanced-filter', 'search_filter');

            Route::get('/columns', 'columns');
            Route::get('/top-city-rent-count', 'top_city_rent_count');
            Route::post('/property-map', 'property_map');
        });

        Route::controller(WhatsappController::class)->group(function () {
            Route::post('create-template', 'professional_property_alert');
            Route::get('check-template', 'check_template');
            Route::post('send-template', 'send_template');
            Route::post('message-status', 'message_status');
        });

        Route::controller(DemoDataController::class)->prefix('demo-properties')->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
        });

        Route::controller(FaceDetectionController::class)->prefix('face-detect')->group(function () {
            Route::post('create-session', 'createSession');
            Route::post('check-valid-session', 'getSessionResults');
        });

        Route::middleware(['auth:api'])->group(function () {
            Route::controller(AuthenticationController::class)->group(function () {
                Route::post('logout', 'logout');
            });

            Route::controller(UserController::class)->group(function () {
                Route::get('profile', 'profile');
                Route::post('profile-resend-otp', 'profile_resendOtp');
                Route::post('profile-update', 'profile_update');
                Route::post('profile-verify', 'profile_verify');
                Route::post('change-password', 'change_password');
                Route::post('set-message-schedule-time', 'set_message_schedule_time');
                Route::post('message-alert', 'message_alert');
                Route::post('transcation-history', 'transaction_history');
                Route::post('update-employment', 'update_employment');
            });

            Route::controller(PropertyController::class)->group(function () {
                Route::post('google', 'google');
                Route::post('search-property', 'search_property_v2');
                Route::post('property-needs', 'property_needs');
                Route::post('user-metching-property', 'user_metching_property');
                Route::Post('sent-client-mail', 'sent_client_mail');
                Route::post('tenant_upload_contract', 'tenant_upload_contract');
                Route::get('property-request-all', 'property_request_all');
                Route::get('property-request-data/{id}', 'property_request_data');
                Route::post('property-request/report/status', 'report_status');
            });

            Route::controller(paymentController::class)->group(function () {
                Route::post('subscription', 'subscription');
                Route::post('check-subscription', 'check_subscription');
                Route::post('free-plan/{id}', 'free_plan');
            });

            Route::controller(CalendarController::class)->group(function () {
                Route::get('/calendar', 'index');
            });
        });

        Route::controller(CalendarController::class)->group(function () {
            Route::get('/calendar-details/{id}', 'calendar_details');
        });
    });

    // postman api
    Route::middleware([PostmenAuthenticateApi::class])->group(function () {
        Route::prefix('agency')->name('api_properties.')->group(function () {
            Route::get('/properties', [PostmanAPIController::class, 'index'])->name('properties.index');
            Route::post('/properties', [PostmanAPIController::class, 'store']);
            Route::get('/properties/{id}', [PostmanAPIController::class, 'show']);
            Route::match(['put', 'patch'], '/properties', [PostmanAPIController::class, 'update']);
            Route::delete('/properties/{id}', [PostmanAPIController::class, 'destroy']);
        });
    });
}
