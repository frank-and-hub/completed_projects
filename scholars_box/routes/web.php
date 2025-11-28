<?php

use App\Http\Controllers\DataController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Student\ScholarshipController;
use App\Http\Controllers\Student\StudentAuthController; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/register/redirectss', function () {
    return view('student.registerRedirection');    
})->name('Student.register.redirectionsssss');   
 
Route::get('/google-caddddlander', [CommonController::class, 'sendWelcomeEmail'])->name('google-casadaslander');
Route::get('/googdddle-calander', [CommonController::class, 'txt_mail'])->name('dd-calander');
Route::domain('{subdomain}.scholarsbox.in')->group(function () {
    Route::get('/{domain?}', [CommonController::class, 'microSite'])->name('subdomain.home');
    // Add more routes for the subdomain...
});
Route::get('/term/conditions', [CommonController::class, 'term'])->name('subdomainterm');


Route::post('/set-session', [StudentAuthController::class, 'setSession'])->name('set.session');


Route::get('forget-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post'); 
Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');
Route::post('save/join', [CommonController::class, 'savejoinnow'])->name('save.join.now');
Route::post('save/position', [CommonController::class, 'saveposition'])->name('save.position.now');
Route::post('save/contact-us', [CommonController::class, 'contactusmail'])->name('contactus.mail');

Route::post('/save/mobile_numberdd', [StudentController::class, 'saveMobileNumber'])->name('save.mobileNumbersd');
Route::post('/save/mobile_number/otp', [StudentController::class, 'saveMobileNumberOTP'])->name('save.mobileNumberOTP');
Route::get('/delete/joinNow', [StudentController::class, 'deleteJoinNow']);


Route::post('/newsletter', [CommonController::class, 'newslettermail'])->name('newsletter.mail');
Route::get('/google-calander', [CommonController::class, 'googleCalnder'])->name('google-calander');

Route::get('/scholarships', [ScholarshipController::class, 'index'])->name('Student.scholarship.index');
Route::post('/scholarship/filter', [ScholarshipController::class, 'filter'])->name('Student.scholarship.all.filtered');
Route::get('/details/{id}',[ScholarshipController::class, 'detail'])->name('Student.scholarship.details');
Route::get('/login/otp',[CommonController::class, 'loginwithotp'])->name('Student.otp.login');
Route::post('/login/otp',[CommonController::class, 'loginwithotpmobile'])->name('login.otp');
Route::post('/verify/otp',[CommonController::class, 'verfiyotp'])->name('verfiy-otp');
//create a new event


Route::get('/search/data',[CommonController::class, 'searchdata'])->name('search-data');


Route::get('/', [CommonController::class, 'index'])->name('homes');
  
//  Route::get('/{doamin?}', [CommonController::class, 'microSite'])->name('subdomain.home');

    // Add more routes for the subdomain...
// });

   Route::get('blog', [StudentController::class, 'index'])->name('Student.blog.list');
    Route::get('blog/details/{id}', [StudentController::class, 'blogDetails'])->name('Student.blog-details');
    Route::post('blog/search', [CommonController::class, 'searchBlog'])->name('search.blogs');
    
Route::get('/storage/{filename}', function ($filename) {
    $path = storage_path('app/public/' . $filename);

    if (!Storage::disk('public')->exists($filename) || !file_exists($path)) {
        abort(404);
    }

    $file = Storage::disk('public')->get($filename);
    $type = Storage::disk('public')->mimeType($filename);

    return Response::make($file, 200, [
        'Content-Type' => $type,
        'Content-Disposition' => 'inline; filename="' . $filename . '"',
    ]);
})->where('filename', '.*');

Route::get('/faq', [CommonController::class, 'faq'])->name('faq');

Route::view('/mysite', 'sites.site');
Route::get('/otp/redirectd', [StudentController::class, 'showOTPredirect'])->name('Student.otp.redirection');

Route::get('/news-letter', [CommonController::class, 'newsLetter'])->name('news-letter');
Route::get('/study-material', [CommonController::class, 'studyMaterial'])->name('study-material');
Route::get('/calendar', function () {
    return view('student.calendar');
})->name('calendar');

Route::get('/show/user/details/{data}', [ScholarshipController::class, 'getUserData']);

Auth::routes();


Route::get('/contact-us', [StudentController::class, 'contact_us'])->name('contact-us');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/{slug}', [StudentController::class, 'cmsPages'])->name('cmspages');

Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [SocialiteController::class, 'handleCallback']);
Route::get('/csr/about-us', [StudentController::class, 'about_us'])->name('user.about-us');
Route::get('/csr/get-involved', [StudentController::class, 'getInvolved'])->name('get-involved');

Route::get('/site', function () {
     return view('site');
 });
// Import the student routes file
Route::prefix('student')->namespace('Student')->group(base_path('routes/panel/student.php'));
Route::prefix('admin')->namespace('Admin')->group(base_path('routes/panel/admin.php'));


Route::get('/userdata', [DataController::class, 'userdata']);

