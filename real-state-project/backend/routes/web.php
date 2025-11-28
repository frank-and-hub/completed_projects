<?php

use App\Http\Controllers\adminsubuser\Agency\AgentController;
use App\Http\Controllers\adminsubuser\CalendarControlller;
use App\Http\Controllers\adminsubuser\ContractController;
use App\Http\Controllers\adminsubuser\ExternalPropertController;
use App\Http\Controllers\adminsubuser\HomeController as AdminsubuserHomeController;
use App\Http\Controllers\adminsubuser\MatchPropertyController;
use App\Http\Controllers\adminsubuser\PlanController as AdminSubUserPlanController;
use App\Http\Controllers\adminsubuser\SettingController as AdminsubuserSettingController;
use App\Http\Controllers\adminUser\PropertyController as AdminUserPropertyController;
use App\Http\Controllers\adminsubuser\ContractRecordController;
use App\Http\Controllers\admin\AdminContractRecordController;
use App\Http\Controllers\admin\ExternalPropertyController;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\AgencyController;
use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\CalendarControlller as AdminCalendarControlller;
use App\Http\Controllers\admin\ExternalPropertyUserController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\PlanController;
use App\Http\Controllers\admin\PropertyController;
use App\Http\Controllers\admin\PropertyNeedApiUser;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\SubmittedProperty;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\api\ApiController;
use App\Http\Controllers\api\ContactusController;
use App\Http\Controllers\CommonController;
use App\Http\Middleware\AdminSubUserMiddleware;
use App\Http\Middleware\AuthAdminMiddleware;
use App\Http\Middleware\PreventBackHistory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

// Route::get('/test', [ApiController::class, 'test_function'])->name('test');

// Route::get('/emailtemplate', [CommonController::class, 'emailtemplate'])->name('emailtemplate');

Route::group(['prefix' => 'dynamic-contract'], function () {
    Route::controller(ContractController::class)->group(function () {
        Route::get('tenant/{url_id}/{id}', 'view_pdf')->name('view_pdf');
    });
});

Route::prefix('admin')->group(function () {
    Route::middleware(['guest:admin', PreventBackHistory::class])->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::get('/', 'login')->name('login');
            Route::post('/loginprocess', 'loginprocess')->name('loginprocess');
        });
    });
    Route::middleware([AuthAdminMiddleware::class, 'auth:admin', 'role:admin', PreventBackHistory::class])->group(function () {
        // Route::middleware(['role:agent', 'guard:admin'], function () {
        Route::controller(HomeController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/properties-location', 'map')->name('map');
            Route::get('/total-revenue', 'total_revenue')->name('total_revenue');
            Route::get('/total-property', 'total_property')->name('total_property');
        });
        Route::controller(AuthController::class)->group(function () {
            Route::get('/logout', 'logout')->name('logout')->withoutMiddleware(['role:admin', AuthAdminMiddleware::class]);
        });
        Route::prefix('tenants')->group(function () {
            Route::controller(UserController::class)->group(function () {
                Route::get('/', 'index')->name('user_list');
                Route::post('/change-status', 'change_update')->name('change_update');
                Route::post('/user-delete', 'user_delete')->name('user_delete');
                Route::get('/view/{id}', 'view')->name('user_view');
                Route::get('/user-subscription/{id}', 'user_subscription')->name('user_subscription');
                Route::get('/user-property-request/{id}', 'user_property_request')->name('user_property_request');
            });
        });
        Route::prefix('Enquiry')->group(function () {
            Route::controller(ContactusController::class)->group(function () {
                Route::get('/', 'index')->name('enquiry_list');
                Route::post('email-update', 'email_update')->name('email_update');
            });
        });
        // Route::prefix('features')->group(function () {
        //     Route::controller(BestFeaturesController::class)->group(function () {
        //         Route::get('/', 'index')->name('features_list');
        //         Route::get('/add', 'add_features')->name('add_features');
        //         Route::get('/edit/{id}', 'edit_features')->name('edit_features');
        //         Route::post('/insert', 'insert')->name('insert_features');
        //         Route::post('/update', 'update')->name('update_features');
        //         Route::post('/delete', 'delete')->name('delete_features');
        //     });
        // });
        Route::prefix('plans')->group(function () {
            Route::controller(PlanController::class)->group(function () {
                Route::get('/', 'index')->name('plan_list');
                Route::get('/add', 'add_plan')->name('add_plan');
                Route::post('/edit', 'edit_plan')->name('edit_plan');
                Route::post('/insert', 'insertorupdate')->name('insert_plan');
                Route::post('/update', 'update')->name('update_plan');
            });
        });

        Route::resource('property-need-api-user', PropertyNeedApiUser::class);

        Route::prefix('setting')->group(function () {
            Route::controller(SettingController::class)->group(function () {
                Route::get('/', 'index')->name('setting');
                Route::post('property-price-update', 'property_price_update')->name('property_price_update');
                Route::post('update-admin-credential', 'update_admin_credential')->name('update_admin_credential');
                Route::post('reset-password', 'reset_password')->name('reset_password');
                Route::post('frontend-setting', 'frontend_setting')->name('frontend_setting');
                Route::post('/uploadProfile', 'uploadProfile')->name('uploadProfile');
            });
        });

        Route::prefix('submitted-property-needs')->group(function () {
            Route::controller(SubmittedProperty::class)->group(function () {
                Route::get('/', 'index')->name('submitted_property');
                Route::get('/view/{id}', 'view')->name('submitted_property_view');
                Route::get('/metching', 'metching_property')->name('metching_property');
            });
        });

        // Route::prefix('client-property')->group(function () {
        Route::controller(PropertyController::class)->group(function () {
            Route::get('/client-property', 'index')->name('property_data');
            Route::get('/property-list/{id?}', 'property_list')->name('property.list');
            Route::post('/dataTable/{id?}', 'dataTable')->name('property.dataTable');
            Route::get('/property-list/view/{id}', 'property_view')->name('property.view');
            Route::get('/property/external-view/{id}', 'external_property_view')->name('property.view-external');
        });

        Route::controller(ExternalPropertyController::class)->group(function () {
            Route::get('/partner-property-list', 'index')->name('api_property.list');
        });
        // });

        Route::prefix('admin-type')->name('admin_user.')->group(function () {

            Route::controller(AdminController::class)->group(function () {

                Route::get('/{admin_user}', 'listing')->name('role_type_admin_list');
                Route::post('/change-status/{admin_user}', 'change_update')->name('change_update');
                Route::post('/request-verification', 'request_verification')->name('request_verification');

                Route::get('/view/{admin}', 'admin_user_view')->name('role_type_view');
                Route::post('/delete/{id}', 'admin_user_delete')->name('role_type_delete');
                Route::get('/match-property/list/{id?}', 'match_property_list')->name('match_property_list');
                Route::get('/match-property/view/{id?}', 'match_property_view')->name('match_property_view');

                Route::get('subscribe/list', 'subscribe_list')->name('subscribe_list');
            });

            Route::controller(AgencyController::class)->prefix('/agency')->name('agency.')->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');

                Route::get('/edit/{admin}', 'edit')->name('edit');
                Route::post('/update/{admin}', 'update')->name('update');
            });
        });

        Route::controller(AdminCalendarControlller::class)->name('calendar.')->prefix('/calendar')->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
        });

        Route::resource('external_property_users', ExternalPropertyUserController::class);
        Route::controller(ExternalPropertyUserController::class)->name('external_property_users.')->prefix('/external_property_user')->group(function () {
            Route::get('/status', 'status')->name('status');
        });

        Route::resource('contract_records', AdminContractRecordController::class);
    });
});

// private landlord , agency and agent
Route::prefix('{type}')->group(function () {
    Route::middleware(['guest:admin', PreventBackHistory::class])->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::get('/login', 'sub_login')->name('sub_login');
            Route::post('/login', 'sub_loginprocess')->name('sub_loginprocess');

            Route::match(['get', 'post'], '/forgot-password', 'forgot_password')->name('forgot-password');
            Route::post('/reset_password', 'reset_password')->name('reset-password');
        });
    });
})->whereIn('type', ['privatelandlord', 'agency', 'agent']);

Route::name('adminSubUser.')->group(function () {
    Route::middleware([AdminSubUserMiddleware::class, 'auth:admin', 'role:privatelandlord|agency|agent', PreventBackHistory::class])->group(function () {
        Route::controller(AdminsubuserHomeController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/total-revenue', 'total_revenue')->name('total_revenue');
            Route::get('/total-property', 'total_property')->name('total_property');
        });

        Route::controller(AdminUserPropertyController::class)->name('property.')->prefix('/property')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/dataTable', 'dataTable')->name('dataTable');
            Route::get('/add', 'add')->name('add');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/insert', 'insert')->name('insert_property');
            Route::post('/update/{id}', 'update')->name('update');
            Route::post('/update-time-zone/{id}', 'update_time_zone')->name('update_time_zone');
            Route::get('/view/{id}', 'view')->name('view');
            Route::post('/delete', 'delete')->name('delete');
            Route::post('/status', 'status')->name('status');
            Route::post('/status_contract', 'status_contract')->name('status_contract');
            Route::post('/agency_status/{id}', 'agency_status')->name('agency_status');
        });

        Route::controller(MatchPropertyController::class)->name('match-property.')->prefix('/matched-tenants')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/dataTable', 'dataTable')->name('dataTable');
            Route::get('/view/{id?}', 'view')->name('view');
        });

        Route::controller(CalendarControlller::class)->name('calendar.')->prefix('/calendar')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/create', 'create')->name('create');
            Route::post('/note', 'createNote')->name('note');
            Route::get('/data', 'data')->name('data');
            Route::post('/history', 'history_dataTable')->name('history_dataTable');
        });

        Route::controller(CalendarControlller::class)->name('calendar.')->prefix('/property_viewing_request')->group(function () {
            Route::get('/', 'pvr_index')->name('pvr_index');
        });
        ///////////////////////////   Agent Route ////////////////////////////////
        Route::controller(AgentController::class)->name('agent.')->prefix('/agent')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::get('/view/{id}', 'show')->name('view');
            Route::post('/update/{id}', 'update')->name('update');
            Route::post('/status', 'status')->name('status');
            Route::get('agent-property-list/{id?}', 'agent_property_list')->name('properties.list');
            Route::get('datatable/model', 'datatable_model')->name('datatable.model');
        });

        Route::prefix('setting')->name('setting.')->group(function () {
            Route::controller(AdminsubuserSettingController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('update-admin-credential', 'update_admin_credential')->name('update_admin_credential');
                Route::post('reset-password', 'reset_password')->name('reset_password');
                Route::post('/whatsApp_notification', 'whatsApp_notification')->name('whatsApp_notification');
                Route::post('/uploadProfile', 'uploadProfile')->name('uploadProfile');
            });
        });

        Route::controller(AdminSubUserPlanController::class)->group(function () {
            Route::get('subscribe/list', 'subscribe_list')->name('subscribe_list');
            Route::post('subscribe', 'subscribe')->name('subscribe');
            Route::get('/check-plan-exists', 'checkPlan')->name('check_plan_is_exists');
            Route::get('/payfast_success', 'payfast_success')->name('payfast_success');
            Route::get('/payfast_cancel', 'payfast_cancel')->name('payfast_cancel');
        });

        Route::resource('contract', ContractController::class);
        Route::resource('contract_records', ContractRecordController::class);

        Route::controller(ContractController::class)->prefix('contract')->name('contract.')->group(function () {
            Route::post('/status', 'status')->name('status');
            Route::get('/properties/{id?}', 'get_properties')->name('get_properties');
            Route::post('/selected_properties', 'get_selected_properties')->name('get_selected_properties');
            Route::get('/get/all_properties', 'get_all_properties')->name('get_all_properties');
            Route::post('/update_contracts', 'update_contracts_property')->name('update_contracts_property');
            Route::get('/tenants/{id?}', 'get_tenants')->name('get_tenants');
            Route::post('/get_tenants', 'get_selected_tenants')->name('get_selected_tenants');
            Route::post('/update_contracts_tenants', 'update_contracts_tenants')->name('update_contracts_tenants');
            Route::post('/offline_tenants', 'offline_tenants')->name('offline_tenants');
            Route::get('/offline_tenants_list/{id?}', 'offline_tenants_list')->name('offline_tenants_list');
            Route::get('/tenants_list/{id?}', 'tenants_list')->name('tenants_list');
            Route::post('/update_new_contract/{id?}', 'uploadAgencyContract')->name('update_new_contract');
            Route::post('/change_contract_status', 'change_contract_status')->name('change_contract_status');
        });

        Route::controller(ExternalPropertController::class)->name('external_property.')->prefix('/external_property')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/status', 'status')->name('status');
        });
    });
});

///// common route

Route::controller(CommonController::class)->prefix('/common')->name('common.')->group(function () {
    Route::get('/country', 'country')->name('country');
    Route::get('/state', 'state')->name('state');
    Route::get('/city', 'city')->name('city');
    Route::get('/suburb', 'suburb')->name('suburb');

    Route::post('/property-lat-lng', 'property_lat_lng')->name('property-lat-lng');
});

$LIVE = config('services.live');
if ($LIVE == 1) {
    Route::get('/', function () {
        return File::get(public_path() . '/index.html');
    });
    Route::get('/{any}', function () {
        return File::get(public_path() . '/404.html');
    })->where('any', '.*');
} else {
    Route::get('/', function () {
        return view('comingsoon');
    });
}
