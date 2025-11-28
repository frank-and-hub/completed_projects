<?php

use App\Http\Controllers\admin\Auth\LoginController;
use App\Http\Controllers\admin\auth\RegisterController;

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\ScholarshipController;
use App\Http\Controllers\admin\StudentController;
use App\Http\Controllers\admin\BlogController;
use App\Http\Controllers\admin\FaqController;

use App\Http\Controllers\Admin\Scholarship\ApplicantController;
use App\Http\Controllers\Admin\Scholarship\ScholarshipApplicationController;
use App\Http\Controllers\Admin\Scholarship\ScholarshipApplicationFormController;
// use App\Http\Controllers\Admin\Scholarship\ScholarshipController;
// use App\Http\Controllers\Admin\Student\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
 */

# login routes
Route::middleware('guest')->name('admin.')->group(function () {
    // Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/register', [LoginController::class, 'register'])->name('register');
    Route::get('/login', function () {
        return view('admin/auth/login');
    });
    Route::get('/register', function () {
        return view('admin/auth/register');
    });
    
     
});

// Route::name('admin.')->group(function () {
    # dashboard route
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/logout', [DashboardController::class, 'logout'])->name('admin.logout');


    Route::get('/scholarship/list', [ScholarshipController::class, 'index'])->name('admin.scholarship.list');
    Route::get('/scholarship/add', [ScholarshipController::class, 'add'])->name('admin.scholarship.add');
    Route::post('/scholarship/store', [ScholarshipController::class, 'save'])->name('admin.scholarship.save');
    Route::post('/scholarship/edit/{id}', [ScholarshipController::class, 'edit'])->name('admin.scholarship.edit');
    Route::put('/scholarship/update/{id}', [ScholarshipController::class, 'update'])->name('admin.scholarship.update');
    Route::get('/scholarship/view/{id}', [ScholarshipController::class, 'view'])->name('admin.scholarship.view');
    Route::get('/scholarship/delete/{id}', [ScholarshipController::class, 'delete'])->name('admin.scholarship.delete');
    Route::get('/scholarship/application/{id}', [ScholarshipController::class, 'applicationForm'])->name('admin.scholarship.application');
    Route::post('/scholarship/application/store', [ScholarshipController::class, 'applicationStore'])->name('admin.scholarship.application.store');

    Route::get('/scholarship/application_questions/{id}', [ScholarshipController::class, 'applicationQuestions'])->name('admin.scholarship.application_questions');
    

    
    
    Route::get('/student/list', [StudentController::class, 'index'])->name('admin.student.list');




    Route::get('/blog/list', [BlogController::class, 'index'])->name('admin.blog.list');
    Route::post('/blog/save', [BlogController::class, 'store'])->name('admin.blog.save');
    Route::get('/blog/add', [BlogController::class, 'add'])->name('admin.blog.add');
    Route::get('/blog/edit/{id}', [BlogController::class, 'edit'])->name('admin.blog.edit');
    Route::get('/blog/view/{id}', [BlogController::class, 'view'])->name('admin.blog.view');
    Route::get('/blog/delete/{id}', [BlogController::class, 'delete'])->name('admin.blog.delete');
    Route::put('/blog/update/{id}', [BlogController::class, 'update'])->name('admin.blog.update');
    
    Route::get('/faq/list', [FaqController::class, 'index'])->name('admin.faq.list');
    Route::get('/faq/edit/{id}', [FaqController::class, 'edit'])->name('admin.faq.edit');
    Route::get('/faq/view/{id}', [FaqController::class, 'view'])->name('admin.faq.view');
    Route::get('/faq/delete/{id}', [FaqController::class, 'delete'])->name('admin.faq.delete');
    Route::get('/faq/add', [FaqController::class, 'add'])->name('admin.faq.add');
    Route::put('/faq/update/{id}', [FaqController::class, 'update'])->name('admin.faq.update');
    Route::post('/faq/store', [FaqController::class, 'store'])->name('admin.faq.store');

    


    // Main scholarship routes and application-form as a sub-route
    Route::prefix('scholarship')->name('scholarship.')->group(function () {
        // Scholarship routes
        Route::get('/', [ScholarshipController::class, 'index'])->name('index');
        Route::get('/json', [ScholarshipController::class, 'tableJson'])->name('json');
        Route::get('/show/{id}', [ScholarshipController::class, 'view'])->name('view');
        Route::get('/edit/{id}', [ScholarshipController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [ScholarshipController::class, 'update'])->name('update');
        // Route::get('/add', [ScholarshipController::class, 'add'])->name('add');
        Route::post('/create', [ScholarshipController::class, 'create'])->name('create');
        Route::delete('/delete/{id}', [ScholarshipController::class, 'delete'])->name('delete');

        Route::get('/update-status',  [ScholarshipController::class, 'updateStatus'])->name('updateStatus');

        // Additional scholarship-related routes
        Route::post('/add-contact-persons/{id}', [ScholarshipController::class, 'addContactPersons'])->name('addContactPersons');
        Route::post('/add-locations/{id}', [ScholarshipController::class, 'addLocations'])->name('addLocations');
        Route::post('/add-educations/{id}', [ScholarshipController::class, 'addEducations'])->name('addEducations');
        Route::post('/add-details/{id}', [ScholarshipController::class, 'addDetails'])->name('addDetails');


        // Nested application-form routes
        Route::prefix('application-form')->name('application-form.')->group(function () {
            Route::get('/', [ScholarshipApplicationFormController::class, 'index'])->name('index');
            Route::get('/json', [ScholarshipApplicationFormController::class, 'tableJson'])->name('json');
            Route::get('/show/{id}', [ScholarshipApplicationFormController::class, 'view'])->name('view');
            Route::get('/edit/{id}', [ScholarshipApplicationFormController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [ScholarshipApplicationFormController::class, 'update'])->name('update');
            Route::get('/add/{id}', [ScholarshipApplicationFormController::class, 'add'])->name('add');
            Route::post('/create', [ScholarshipApplicationFormController::class, 'create'])->name('create');
            Route::delete('/delete/{id}', [ScholarshipApplicationFormController::class, 'delete'])->name('delete');
        });


        Route::prefix('applicants')->name('applicants.')->group(function () {
            Route::get('/{scholarship_id}', [ApplicantController::class, 'index'])->name('index');
            Route::get('/json/{scholarship_id}', [ApplicantController::class, 'tableJson'])->name('json');
            Route::get('/detail/{id}', [ApplicantController::class, 'detail'])->name('detail');
            Route::get('/document-verificationjson/{id}', [ApplicantController::class, 'documentVerificationJson'])->name('documentVerificationJson');
        });
    });

    Route::prefix('scholarship-application')->name('scholarshipApplication.')->group(function () {
        Route::get('/', [ScholarshipApplicationController::class, 'index'])->name('index');
        Route::get('/json', [ScholarshipApplicationController::class, 'tableJson'])->name('json');
        Route::get('/detail/{id}', [ScholarshipApplicationController::class, 'detail'])->name('detail');
        Route::get('/document-verificationjson/{id}', [ScholarshipApplicationController::class, 'documentVerificationJson'])->name('documentVerificationJson');
    });






    // Student routes
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/json', [StudentController::class, 'tableJson'])->name('json');
        Route::get('/show/{id}', [StudentController::class, 'view'])->name('view');
        Route::get('/edit/{id}', [StudentController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [StudentController::class, 'update'])->name('update');
        Route::get('/add', [StudentController::class, 'add'])->name('add');
        Route::post('/create', [StudentController::class, 'create'])->name('create');
        Route::delete('/delete/{id}', [StudentController::class, 'delete'])->name('delete');
    });


    // catch-all route for non-existent student routes
    // Route::any('{any}', function () {
    //     return redirect(route('Student.login'));
    // })->where('any', '.*');

    Route::get('/', function () {
        return redirect(route('Admin.login'));
    });
// });
