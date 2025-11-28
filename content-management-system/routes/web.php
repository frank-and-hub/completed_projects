<?php

use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Cache;
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

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/terms-&-conditions', [WebController::class, 'terms_and_conditions'])->name('terms_and_conditions');
Route::get('/privacy-policy', [WebController::class, 'privacy_policy'])->name('privacy-policy');
Route::get('/delete-account', [WebController::class, 'delete_account'])->name('delete-account');
Route::get('/request-response', [WebController::class, 'thank_you'])->name('thank-you');
Route::post('/delete-account/save', [WebController::class, 'save'])->name('delete-account-save');
// Route::get('/mail',function(){

//     return view('Mail.verify_email');
// });


Route::get('/test', function () {
    $cache_get = Cache::get('link');
    return $cache_get;
})->name('test');

// Route::get('/cities', function () {
//     return view('admin.cities.index');
// })->name('cities');
// Route::get('/cities-categories', function () {
//     return view('admin.cityCategory.index');
// })->name('cities.cat');
// Route::get('/cities-categories-cat', function () {
//     return view('admin.cityCategory.create');
// })->name('cities.cat.c');

// Route::get('/cities/edit', function () {
//     return view('admin.cities.edit');
// })->name('cities.edit');

Route::get('/share', function () {
    $cache =   Cache::put('link', 'hello');
    return $cache;
})->name('share');
