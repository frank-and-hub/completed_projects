<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    EmailController,
    HomeController,
    LoginController,
    UserController,
    AdminController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

Route::get('/', function () {
    return redirect('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
/** gmail controller */
Route::resource('emails', EmailController::class);

Route::get('emails/list', [EmailController::class, 'send_list'])->name('emails.send.list');
Route::group(['prefix' => 'user'], function () {
    Route::post('list', [UserController::class, 'list'])->name('user.list');
    Route::get('show_list', [UserController::class, 'show_list'])->name('user.show_list');
    Route::post('report_list', [UserController::class, 'showList'])->name('user.show.list');
    Route::post('getEmailCount', [UserController::class, 'getEmailCount'])->name('user.count.emails');
    Route::post('report_list_csv', [UserController::class, 'showListCSV'])->name('user.show.list.csv');
    Route::post('user_filter', [UserController::class, 'user_filter'])->name('user.filter');
    Route::post('delete', [UserController::class, 'delete'])->name('user.delete');
    Route::post('all_update', [UserController::class, 'all_update'])->name('user.all.update');
});
Route::resource('user', LoginController::class);
Route::resource('users', UserController::class);
Route::resource('admins', AdminController::class);

Route::post('env', [EmailController::class, 'updateEnv'])->name('email.env');
Route::post('sendEmail', [EmailController::class, 'sendEmail'])->name('user.send.email');
Route::get('clear', function (Request $request) {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    DB::statement('TRUNCATE TABLE records');
    DB::statement('TRUNCATE TABLE emails');
    DB::statement('TRUNCATE TABLE logins');
    DB::statement('TRUNCATE TABLE users');
    DB::statement('TRUNCATE TABLE jobs');
    DB::statement('TRUNCATE TABLE failed_jobs');
    // return $request->getHost();
});

Route::get('session', function () {
    pd(session()->all());
});

