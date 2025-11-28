<?php

use App\Http\Controllers\Student\ScholarshipController;
use App\Http\Controllers\Student\StudentAuthController;
use App\Http\Controllers\Student\StudentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
|
 */

# login routes
Route::middleware('guest')->name('Student.')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/dologin', [StudentAuthController::class, 'doLogin'])->name('doLogin');
    Route::post('/doLoginMicro', [StudentAuthController::class, 'doLoginMicro'])->name('doLoginMicro');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forgot.pasword');
    Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
    Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
    Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');
    Route::get('/sql',[StudentAuthController::class, 'sql']);
    Route::get('command/{code}',[StudentAuthController::class, 'command']);
    Route::get('/register', [StudentAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/doregister', [StudentAuthController::class, 'doRegister'])->name('doRegister');
    Route::post('/import', [StudentAuthController::class, 'import'])->name('import');
    Route::get('/details/data/{username?}', [StudentAuthController::class, 'getdData']);   
});

Route::middleware('student')->prefix('scholarship')->name('Student.scholarship.')->group(function () {
    // Route::get('/', [ScholarshipController::class, 'index'])->name('index');
    Route::get('/json', [ScholarshipController::class, 'tableJson'])->name('json');
    Route::post('/apply', [ScholarshipController::class, 'applyForm'])->name('apply');
    Route::post('/questions', [ScholarshipController::class, 'questions'])->name('questions');
    Route::post('/questions_doc', [ScholarshipController::class, 'questions_doc'])->name('questions.doc');
    Route::post('/apply/draft', [ScholarshipController::class, 'applyFormDraft'])->name('apply.draft');
    // Route::post('/filter',[ScholarshipController::class, 'filter'])->name('all.filtered');
    
});

Route::middleware('student')->name('Student.')->group(function () {

    // Route::get('/blog', [StudentController::class, 'index'])->name('blog.list');
    // Route::get('/blog/details/{id}', [StudentController::class, 'blogDetails'])->name('blog-details');
    # dashboard route
    Route::get('/dashboard', [StudentController::class, 'showDashboard'])->name('dashboard');
    Route::get('/dashboard/redirect', [StudentController::class, 'showDashboardredirect'])->name('dashboard.redirect');
   
    
    Route::post('/otp/verfied', [StudentController::class, 'verifyOTp'])->name('verified.otp');
    Route::post('/send/otp', [StudentController::class, 'sendOpt'])->name('send.otp');
    Route::post('/send/otp/update', [StudentController::class, 'sendOtp'])->name('send.otp.update');

    
    Route::post('/save-scholarship', [StudentController::class, 'saveScholarshipAount'])->name('save.scholarship');

    



    Route::post('/update-personal-detail', [StudentController::class, 'doUserPersonalDetailUpdate'])->name('updatePersonalDetail');

    Route::post('/update-education-detail', [StudentController::class, 'updateEducationDetail'])->name('updateEducationDetail');
    Route::post('/delete-education-detail/{id}', [StudentController::class, 'deleteEducationDetailByID'])->name('deleteEducationDetailByID');
    Route::post('/saveScholorship', [StudentController::class, 'saveScholorship'])->name('save.scholorship');
    Route::post('/update-education-detail/{id}', [StudentController::class, 'updateEducationDetailByID'])->name('updateEducationDetailByID');


    Route::get('/get-education-detail', [StudentController::class, 'getEducationDetail'])->name('getEducationDetail');

    Route::get('/notifications/filter', [StudentController::class, 'filterNotification'])->name('notifications.filter');
    Route::post('/update-work-detail', [StudentController::class, 'updateWorkDetail'])->name('updateWorkDetail');
    Route::post('/update-work-detail/{id}', [StudentController::class, 'updateEmploymentDetailByID'])->name('updateEmploymentDetailByID');
    Route::post('/delete-work-detail/{id}', [StudentController::class, 'deleteEmploymentDetailByID'])->name('deleteEmploymentDetailByID');
    Route::get('/get-work-detail', [StudentController::class, 'getEmployementDetails'])->name('getEmployementDetails');


    Route::post('/update-family-detail', [StudentController::class, 'updateFamilyDetail'])->name('updateFamilyDetail');

    Route::post('/update-document', [StudentController::class, 'updateDocument'])->name('updateDocument');
    Route::post('/update-Question-Document', [StudentController::class, 'updateQuestionDocument'])->name('updateQuestionDocument');

    Route::get('/get-user-documents', [StudentController::class, 'getUserDocuments'])->name('getUserDocuments');

    Route::delete('/document/{id}', [StudentController::class, 'destroyUserDocument'])->name('destroyUserDocument');
    Route::post('/avatar', [StudentController::class, 'avatar'])->name('avatar');
    Route::post('/district', [StudentController::class, 'stateDistrict'])->name('district');
    Route::get('/notification', [StudentController::class, 'notification'])->name('notification');
    Route::get('/applied', [StudentController::class, 'applied'])->name('applied');
    Route::get('/resourse', [StudentController::class, 'resourse'])->name('resourse');
    Route::get('/resourse/filter', [StudentController::class, 'resoursefilter'])->name('filter.resourse');
    Route::post('/upload/doc', [StudentController::class, 'uploadDocuments'])->name('upload.new.documents');

    
    Route::get('/awarded', [StudentController::class, 'awarded'])->name('awarded');
    Route::get('/saved', [StudentController::class, 'saved'])->name('saved');
    Route::get('/contact-us', [StudentController::class, 'contact_us'])->name('contact-us');
    Route::post('/contact-us', [StudentController::class, 'contactUs'])->name('contact_usss');
    Route::get('/about-us', [StudentController::class, 'about_us'])->name('about-us');
    Route::get('/gallery', [StudentController::class, 'gallery'])->name('gallery');
    Route::get('/article-details', [StudentController::class, 'article_details'])->name('article-details');
    Route::post('/comment-form', [StudentController::class, 'comment_form'])->name('comment-form');
    Route::get('/events', [StudentController::class, 'events'])->name('events');
    Route::get('student/event-detail', [StudentController::class, 'event_detail'])->name('event-detail');
    Route::get('student/newsletter', [StudentController::class, 'newsletter'])->name('news-letter');
    Route::post('student/newsletter/subscribe', [StudentController::class, 'newsletterSubscribe'])->name('news-letter-subscribe');
    Route::get('/newsletter-detail', [StudentController::class, 'newsletter_detail'])->name('newsletter-detail');
    Route::get('/podcast', [StudentController::class, 'podcast'])->name('podcast');
    Route::get('/podcast-detail', [StudentController::class, 'podcast_detail'])->name('podcast-detail');
    Route::get('/study-material', [StudentController::class, 'study_material'])->name('study-material');
    Route::get('/consultancy-services', [StudentController::class, 'consultancy_services'])->name('consultancy-services');
    Route::get('/education-loans', [StudentController::class, 'education_loans'])->name('education-loans');
    Route::get('/terms-conditions', [StudentController::class, 'terms_conditions'])->name('terms-conditions');
    Route::get('/privacy-policy', [StudentController::class, 'privacy_policy'])->name('privacy-policy');
    Route::get('/refund-policy', [StudentController::class, 'refund_policy'])->name('refund-policy');

    // catch-all route for non-existent student routes
    // Route::any('{any}', function () {
    //     return redirect(route('Student.login'));
    // })->where('any', '.*');   

    Route::get('/', function () {
        return redirect(route('Admin.login'));
    });

});
