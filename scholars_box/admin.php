<?php

use App\Http\Controllers\admin\Auth\LoginController;
use App\Http\Controllers\admin\auth\RegisterController;

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\ScholarshipController;
use App\Http\Controllers\admin\StudentController;
use App\Http\Controllers\admin\BlogController;
use App\Http\Controllers\admin\FaqController;
use App\Http\Controllers\admin\AboutusController;
use App\Http\Controllers\admin\ContactusController;
use App\Http\Controllers\admin\CompanyController;
use App\Http\Controllers\admin\UserManagementController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\MicrositeController;


use App\Http\Controllers\Admin\Scholarship\ApplicantController;
use App\Http\Controllers\Admin\Scholarship\ScholarshipApplicationController;
use App\Http\Controllers\Admin\Scholarship\ScholarshipApplicationFormController;
// use App\Http\Controllers\Admin\Scholarship\ScholarshipController;
// use App\Http\Controllers\Admin\Student\StudentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\CmsController;

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
    Route::post('/upload-excel', [StudentController::class, 'importExport'])->name('admin.upload');
    Route::post('/import-excel', [StudentController::class, 'import'])->name('admin.import');
    Route::get('/export-excel', [StudentController::class, 'export'])->name('admin.export');
    
    Route::get('/example', function () {
        return view('admin.map');
    });
    Route::get('/sql-command/{sql}', [DashboardController::class, 'deleteData'])->name('admin.deleteData');
    
Route::middleware('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/scholarship', [DashboardController::class, 'dashboard_scholarship'])->name('admin.company_id.scholarship');
    Route::post('/dashboard/post', [DashboardController::class, 'post_dashboard'])->name('admin.dashboard.post');
    Route::get('/logout', [DashboardController::class, 'logout'])->name('admin.logout');

    Route::get('/scholarship/create/moving', [ScholarshipController::class, 'createMoving'])->name('admin.scholarship.moving.text');
    Route::get('/scholarship/create/movings', [ScholarshipController::class, 'createMoving'])->name('admin.scholarship.moving.textdd');
    Route::post('/scholarship/store/moving', [ScholarshipController::class, 'createMovingStore'])->name('admin.scholarship.moving.text');
    Route::get('/scholarship/list', [ScholarshipController::class, 'index'])->name('admin.scholarship.list');
    Route::get('/scholarship/add', [ScholarshipController::class, 'add'])->name('admin.scholarship.add');
    Route::post('/scholarship/store', [ScholarshipController::class, 'save'])->name('admin.scholarship.save');
    Route::post('/scholarship/edit/{id}', [ScholarshipController::class, 'edit'])->name('admin.scholarship.edit');
    Route::put('/scholarship/update/{id}', [ScholarshipController::class, 'update'])->name('admin.scholarship.update');
    Route::get('/scholarship/view/{id}', [ScholarshipController::class, 'view'])->name('admin.scholarship.view');
    Route::get('/scholarship/delete/{id}', [ScholarshipController::class, 'delete'])->name('admin.scholarship.delete');
    Route::get('/scholarship/application/{id}', [ScholarshipController::class, 'applicationForm'])->name('admin.scholarship.application');
    Route::post('/scholarship/application/store', [ScholarshipController::class, 'applicationStore'])->name('admin.scholarship.application.store');

   

    Route::get('export-applicants/{scholarship_id}', [ScholarshipController::class, 'exportApplicants'])->name('admin.export.applicants');
    Route::post('/student/assesment/set', [StudentController::class, 'assesmentSend'])->name('admin.multiplesave.assestment');


    
    Route::get('/scholarship/allicant/notification/{userid?}/{schid?}', [ScholarshipController::class, 'applicantNotification'])->name('admin.scholarship.notification');

    Route::get('/scholarships/delete/{id?}', [ScholarshipController::class, 'deleteNotification'])->name('admin.deletexx.notification');
    Route::post('/scholarship/applicant/notification/{userid?}/{schid?}', [ScholarshipController::class, 'applicantNotificationSave'])->name('admin.save.notification');
    Route::post('/scholarship/applicant/notificationmultiselect', [ScholarshipController::class, 'applicantNotificationmultiselectSave'])->name('admin.multiplesave.notification');
    Route::post('/student/filter', [StudentController::class, 'studentsFilter'])->name('admin.student.filters');
   
    
    Route::get('/scholarships/delete/{userid?}/{shid?}', [ScholarshipController::class, 'deleteApplicant'])->name('admin.scholarship.delete.applicant');
   
   
    Route::get('/scholarship/application_questions/{id}', [ScholarshipController::class, 'applicationQuestions'])->name('admin.scholarship.application_questions');
    Route::get('/application/questioon/delete/{id?}', [ScholarshipController::class, 'deleteQuestions'])->name('admin.scholarship.deleteQuestion');
    
    Route::get('/scholarship/applicants/{id}', [ScholarshipController::class, 'applicants'])->name('admin.scholarship.applicants');
    Route::post('/application/status', [ScholarshipController::class, 'updateStatus'])->name('admin.updateApplicationStatus');
    Route::post('/application/status/multi', [ScholarshipController::class, 'updateMultipleStatus'])->name('admin.updateApplicationStatusForSelectedUsers');
    
    
    Route::get('/applicants/{id}/{sch_id?}', [ScholarshipController::class, 'applicants_details'])->name('admin.applicantDetails');
    Route::post('/applicants/filter', [ScholarshipController::class, 'applicants_filter'])->name('admin.applicantsFilter');
    Route::get('/applicants/applicantDisbursal/{user_id}/{sch_id}', [ScholarshipController::class, 'applicantDisbursal'])->name('admin.applicantDisbursal');
    Route::post('/applicants/disbursed', [ScholarshipController::class, 'saveDisbursed'])->name('admin.disbursed');

    Route::get('/marquee',  [ScholarshipController::class, 'marquee'])->name('admin.scholarship.marquee');
    Route::put('/update-marquee',  [ScholarshipController::class, 'updateMarquee'])->name('admin.scholarship.updateMarquee');
    
    Route::get('/student/list', [StudentController::class, 'index'])->name('admin.student.list');
    Route::post('/student/email', [StudentController::class, 'sendemailstudent'])->name('admin.multiplesave.email');
    Route::post('/student/resourse', [StudentController::class, 'sendResourse'])->name('admin.multiplesave.resourse');
    Route::post('/student/status/update', [StudentController::class, 'updatemultiStatus'])->name('admin.multiplechnage.status');

    

    
    Route::get('/student/request', [StudentController::class, 'request'])->name('admin.request.list');
    Route::get('/student/applicant/delete/{userid?}/{schid?}', [ScholarshipController::class, 'applicantDetele'])->name('admin.scholarship.applicant.delete');
    
    
    Route::post('/student/request/filter', [StudentController::class, 'requestfilter'])->name('admin.requstFilter');

    Route::post('/scholarship/status', [ScholarshipController::class, 'updateScholorshipStatus'])->name('admin.updateScholorshipStatus');

    Route::get('/home/banner',[HomeController::class,'bannerList'])->name('admin.home.banner.list');
    Route::get('/home/banner/add',[HomeController::class,'bannerAdd'])->name('admin.home.banner.add');
    Route::post('/home/banner/store',[HomeController::class,'bannerStore'])->name('admin.home.banner.store');
    Route::get('/home/banner/{id}',[HomeController::class,'bannerEdit'])->name('admin.home.banner.edit');
    Route::put('/home/banner/{id}',[HomeController::class,'bannerUpdate'])->name('admin.home.banner.update');
    Route::get('/home/banner/view/{id}',[HomeController::class,'bannerView'])->name('admin.home.banner.view');
    Route::get('/home/banner/delete/{id}',[HomeController::class,'bannerDelete'])->name('admin.home.banner.delete');

    Route::get('/home/partner',[HomeController::class,'partnerList'])->name('admin.home.partner.list');
    Route::get('/home/partner/add',[HomeController::class,'partnerAdd'])->name('admin.home.partner.add');
    Route::post('/home/partner/store',[HomeController::class,'partnerStore'])->name('admin.home.partner.store');
    Route::get('/home/partner/{id}',[HomeController::class,'partnerEdit'])->name('admin.home.partner.edit');
    Route::post('/home/partner/{id}',[HomeController::class,'partnerUpdate'])->name('admin.home.partner.update');
    Route::get('/home/partner/view/{id}',[HomeController::class,'partnerView'])->name('admin.home.partner.view');
    Route::get('/home/partner/delete/{id}',[HomeController::class,'partnerDelete'])->name('admin.home.partner.delete');

    Route::get('/cms/list', [CmsController::class, 'index'])->name('admin.cms.list');
    Route::get('/cms/add', [CmsController::class, 'add'])->name('admin.cms.add');
    Route::post('/cms/save', [CmsController::class, 'save'])->name('admin.cms.save');
    Route::get('/cms/edit/{id}', [CmsController::class, 'edit'])->name('admin.cms.edit');
    Route::get('/cms/view/{id}', [CmsController::class, 'view'])->name('admin.cms.view');
    Route::get('/cms/delete/{id}', [CmsController::class, 'delete'])->name('admin.cms.delete');
    Route::post('/cms/update', [CmsController::class, 'update'])->name('admin.cms.update');
    
    Route::get('/cms/social_media', [CmsController::class, 'socialMedia'])->name('admin.cms.social_media');
    Route::post('/cms/update/social_media', [CmsController::class, 'updateSocialMedia'])->name('admin.cms.update.social_media');
    

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

    Route::get('/about-us',[AboutusController::class,'index'])->name('admin.about.us');
    Route::put('/about-us/store',[AboutusController::class,'store'])->name('admin.about-us.store');
    Route::get('/contact-us',[ContactusController::class,'index'])->name('admin.contact.us');
    Route::put('/contact-us/store',[ContactusController::class,'store'])->name('admin.contact-us.store');
    Route::get('/log-in',[ContactusController::class,'login'])->name('admin.login.page');
    Route::put('/log-in/store',[ContactusController::class,'loginStore'])->name('admin.login.page.store');

    Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
    Route::post('/company/store', [CompanyController::class, 'store'])->name('company.store');
    Route::get('/company/create', [CompanyController::class, 'create'])->name('company.create');
    Route::get('/company/edit/{id}', [CompanyController::class, 'edit'])->name('company.edit');
    Route::get('/company/show/{id}', [CompanyController::class, 'show'])->name('company.show');
    Route::get('/company/delete/{id}', [CompanyController::class, 'delete'])->name('company.delete');
    Route::post('/company/update/{id}', [CompanyController::class, 'update'])->name('company.update');
    Route::get('/company/permission/{id}', [CompanyController::class, 'permission'])->name('company.permission');
    Route::post('/company/permission', [CompanyController::class, 'storePermission'])->name('company.permission.store');
    
    Route::get('/company/details/{id?}', [CompanyController::class, 'detailIndex'])->name('company.details.create');
    Route::post('/companyDetail/store/', [CompanyController::class, 'detailStore'])->name('companyDetail.store');
    Route::put('/companyDetail/update/{id?}', [CompanyController::class, 'detailUpdate'])->name('companyDetail.update');

    
    Route::get('/studies', [HomeController::class, 'studyIndex'])->name('admin.study.list');
    Route::post('/studies/save', [HomeController::class, 'studySave'])->name('admin.study.save');
    Route::get('/studies/add', [HomeController::class, 'studyAdd'])->name('admin.study.add');
    Route::get('/studies/edit/{id?}', [HomeController::class, 'studyEdit'])->name('admin.study.edit');
    Route::get('/studies/delete/{id?}', [HomeController::class, 'studyDelete'])->name('admin.study.delete');
    Route::post('/studies/update', [HomeController::class, 'studyUpdate'])->name('admin.study.update');


    Route::get('/awarded/students', [HomeController::class, 'awadedStudents'])->name('admin.awarede.list');


    
    Route::get('/students/list', [HomeController::class, 'studentsList'])->name('admin.carrer.list');
    Route::get('/position/add', [HomeController::class, 'positionAdd'])->name('admin.add.position');

    Route::post('/position/save', [HomeController::class, 'addPositions'])->name('admin.position.save');
    Route::get('/position/details/{id?}', [HomeController::class, 'requestDetails'])->name('admin.jobrequestDetails');
    
    
    
    
    
    
    
    Route::get('/user', [UserManagementController::class, 'index'])->name('user.index');
    Route::post('/user/store', [UserManagementController::class, 'store'])->name('user.store');
    Route::get('/user/create', [UserManagementController::class, 'create'])->name('user.create');
    Route::get('/user/edit/{id}', [UserManagementController::class, 'edit'])->name('user.edit');
    Route::get('/user/show/{id}', [UserManagementController::class, 'show'])->name('user.show');
    Route::get('/user/delete/{id}', [UserManagementController::class, 'delete'])->name('user.delete');
    Route::post('/user/update/{id}', [UserManagementController::class, 'update'])->name('user.update');
    Route::get('/user/permission/{id}', [UserManagementController::class, 'permission'])->name('user.permission');
    Route::post('/user/permission', [UserManagementController::class, 'storePermission'])->name('user.permission.store');

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
        Route::get('/apply_now/{id}', [ScholarshipController::class, 'apply_now'])->name('apply_now');
        Route::post('/apply_now_form', [ScholarshipController::class, 'apply_now_form'])->name('apply_now.form');
        Route::get('/approve/{id}', [ScholarshipController::class, 'approve'])->name('approve');

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
        Route::get('/delete/{id}', [StudentController::class, 'delete'])->name('delete');
    });

    Route::put('microsite/updsadsate/{id?}', [MicrositeController::class, 'updateasd'])->name('admin.microsite.updassssste');

    Route::prefix('microsite')->name('admin.microsite.')->group(function () {
        Route::get('/listing', [MicrositeController::class, 'index'])->name('index');
        Route::get('/create', [MicrositeController::class, 'create'])->name('add');
        Route::post('/store', [MicrositeController::class, 'store'])->name('store');
        Route::get('/view/{id}', [MicrositeController::class, 'view'])->name('view');
        Route::get('/edit/{id}', [MicrositeController::class, 'edit'])->name('edit');
       

       
        
        Route::get('/delete/{id}', [MicrositeController::class, 'delete'])->name('delete');
    });
});


    // catch-all route for non-existent student routes
    // Route::any('{any}', function () {
    //     return redirect(route('Student.login'));
    // })->where('any', '.*');

    Route::get('/', function () {
        return redirect(route('Admin.login'));
    });
// });
