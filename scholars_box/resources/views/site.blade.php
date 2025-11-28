<!DOCTYPE html>
<html lang="en">

<head>
    <title>Home - Scholarsbox</title>
    <meta name="keywords" content="Scholarsbox" />
    <meta name="description" content="Scholarsbox" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    
    @include('layouts.includes.style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<style>
    .top-right-image {
        background-image: url('{{ asset($data->about_image2) }}');
    }

    .bottom-left-image {
        background-image: url('{{ asset($data->about_image1) }}');
    }
</style>

<body id="home">

    @include('layouts.includes.microheader')

    <!--Partners Logo Start-->
    <div class="main-microsite-logo-one wow fadeInUp" data-wow-delay=".4s">
        <div class="container-fluid">

            <div class="row microsite-slider">
                <div class="col-lg-12">
                    <div class="microsite-box">
                        <img src="{{ asset($data->banner) }}" class="partner-overlay" alt="brand">
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('student.scholarship.calander')
    <section class="main-banner-one" id="loginnew">       
        <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
        <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
        <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
        <div class="banner-one-shape4">
            <img src="{{ asset('images/banner-one-shap4.png') }}" alt="shap">
        </div>
        <div class="banner-mob-one wow fadeIn" data-wow-delay=".5s">
            <img src="{{ asset('images/banner-mob-one.jpg') }}" alt="Banner">
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="banner-content-one">
                        <div class="banner-slide-text">
                            <p class="wow fadeInDown" data-wow-delay=".5s">{{$data->samall_title}}</p>
                        </div>
                        <h1 class="h1-title wow fadeInUp" data-wow-delay=".5s">{{$data->main_title}} </h1>
                        <p class="wow fadeInUp" data-wow-delay=".7s">{!! $data->desc!!}.</p>
                        <!-- <a href="" class="sec-btn-two wow fadeInUp" data-wow-delay=".9s">Get Started</a> -->

                    </div>
                </div>
                <div class="col-lg-6 banner-mob-no-one micro-register">
                    <div class="register-page-form banner-form signinform--disapear">
                        <form id="loginForm" method="post" action="{{ route('Student.doLoginMicro') }}"
                            class="row sign-in-form wow fadeInRight">

                            @csrf

                            <div class="text-center"><a href="{{ url('auth/google') }}" class="btn sec-btn-one w-100"><i
                                        class="fa fa-google"> </i> Sign in with
                                    Google</a></div>
                            <div class="or-text">OR</div>

                            <div class="text-center"><a href="{{ route('Student.otp.login') }}"
                                    class="btn sec-btn-one w-100"> </i> Sign in with
                                    OTP</a></div>
                            <div class="or-text">OR</div>

                            <!-- Form Input -->
                            <div class="col-md-12 mt-3 mb-25">
                                <p class="p-sm input-header">E-mail Address/Mobile Number</p>
                                <input class="form-control email" type="text" name="identifier"
                                    placeholder="example@example.com / 997xxxxxxx" required>
                            </div>
                            <style>
                                .password-container {
                                    position: relative;
                                }

                                .password {
                                    padding-right: 30px;
                                    /* Space for the eye icon */
                                }

                                #password-toggle {
                                    position: absolute;
                                    top: 50%;
                                    right: 10px;
                                    transform: translateY(-50%);
                                    cursor: pointer;
                                }
                            </style>
                            <!-- Form Input -->
                            <div class="col-md-12 mb-25">
                                <p class="p-sm input-header">Password</p>
                                <div class="wrap-input">
                                    <span class="btn-show-pass ico-20"><span
                                            class="flaticon-visibility eye-pass"></span></span>
                                    <div class="password-container">
                                        <input class="form-control password" type="password" name="password"
                                            placeholder="* * * * * * * * *" required>
                                        <i class="fa fa-eye" id="password-toggle"></i>
                                    </div>
                                </div>
                            </div>


                            <!-- Reset Password Link -->
                            <div class="col-md-12 mb-25">
                                <div class="reset-password-link">
                                    <p class="p-sm"><a href="{{ route('Student.forgot.pasword') }}"
                                            class="color--theme">Forgot your password?</a></p>
                                </div>
                            </div>

                            <!-- Form Submit Button -->
                            <div class="col-md-12 mb-25">
                                <button type="submit" id="submitBtn" class="btn sec-btn-one w-100">Log
                                    In</button>
                            </div>

                            <!-- Sign Up Link -->
                            <div class="col-md-12">
                                <p class="create-account text-center">
                                    New user? Join the community! <a href="{{ route('Student.register') }}"
                                        class="color--theme" id="micrositeSignup">Sign up</a>
                                </p>
                            </div>

                        </form>
                    </div>

                    <div class="register-page-form bg-white signupform--disapear">

                        <div class="text-center"><a class="btn sec-btn-one w-100"><i class="fa fa-google"> </i> Sign
                                Up with Google</a></div>
                        <div class="or-text">OR</div>
                        <form name="signupform" class="row sign-up-form">

                            <div id="wizard">

                                <h4></h4>
                                <section>
                                    <div class="row">
                                        <!-- Form Input -->
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">First name</p>
                                            <input class="form-control name" type="text" name="name"
                                                placeholder="John" required>
                                        </div>

                                        <!-- Form Input -->
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">Last name</p>
                                            <input class="form-control name" type="text" name="name"
                                                placeholder="Doe">
                                        </div>

                                        <!-- Form Input -->
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">Email</p>
                                            <input class="form-control name" type="email" name="name"
                                                placeholder="example@example.com">
                                        </div>

                                        <!-- Form Input -->
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">Phone Number</p>
                                            <input class="form-control" type="tel" name="email"
                                                placeholder="7619xxxxxx">
                                        </div>
                                    </div>
                                </section>



                                <h4></h4>
                                <section>
                                    <div class="row">
                                        <!-- Form Input -->
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">Date of Birth</p>
                                            <input class="form-control name" type="date" name="name"
                                                placeholder="Date of Birth">
                                        </div>

                                        <!-- Form Input -->
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">Gender</p>
                                            <select class="form-control">
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>

                                        <!-- Form Input -->
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">State</p>
                                            <select class="form-control">
                                                <option>Gujarat</option>
                                                <option>Karnataka</option>
                                                <option>Tamil Nadu</option>
                                            </select>
                                        </div>
                                    </div>
                                </section>

                                <h4></h4>
                                <section>
                                    <div class="row">
                                        <!-- Form Input -->
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">I am</p>
                                            <select class="form-control">
                                                <option>A School student</option>
                                                <option>Pursuing Bachelors</option>
                                                <option>Pursuing masters</option>
                                                <option>Pursuing PhD.</option>
                                                <option>Pursuing ITIs/Diploma/Polytechnic/Certificate course
                                                </option>
                                                <option>Preparing for competitive exams</option>
                                                <option>Working Professional</option>
                                                <option>Others</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <p class="p-sm input-header">I’m looking for</p>
                                            <select class="form-control">
                                                <option>School Scholarships</option>
                                                <option>Bachelors Scholarships</option>
                                                <option>Master Scholarships</option>
                                                <option>PhD. Scholarships</option>
                                                <option>ITIs/Diploma/Polytechnic/Certificate Scholarships
                                                </option>
                                                <option>Competitive Exams Scholarships</option>
                                                <option>Exchange program scholarships</option>
                                            </select>
                                        </div>
                                    </div>
                                </section>

                                <h4></h4>
                                <section>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="p-sm input-header">Password</p>
                                            <div class="wrap-input">
                                                <span class="btn-show-pass ico-20"><span
                                                        class="flaticon-visibility eye-pass"></span></span>
                                                <input class="form-control password" type="password" name="password"
                                                    placeholder="min 8 characters">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <p class="p-sm input-header">Confirm Password</p>
                                            <div class="wrap-input">
                                                <span class="btn-show-pass ico-20"><span
                                                        class="flaticon-visibility eye-pass"></span></span>
                                                <input class="form-control password" type="password" name="password"
                                                    placeholder="Retype Pasword">
                                            </div>
                                        </div>


                                    </div>
                                </section>
                            </div>
                        </form>

                        <div class="col-md-12">
                            <p class="text-center">
                                Already Have an account? <span class="color--theme signin-trigger">Sign
                                    In</span>
                            </p>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </section>
    <section class="home-fact-section mt-10 mb-10" id="about">
        <div class="overlay"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="wow fadeInRight" data-wow-delay=".4s">
                        <div class="about-title-one">
                            <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one">About Company</h2>
                            </div>
                            <h2 class="h2-title">{{ $data->about_title }}</h2>
                        </div>
                        <p class="descript-one">{{ $data->about_company }}</p>
                        <div class="points-one">
                            <ul>
                                <li>
                                    <div class="check-list">
                                        <img src="{{ asset('images/check.png') }}" alt="check">
                                    </div>
                                    <p>{{ $data->about_listing1 }}</p>
                                </li>
                                <li>
                                    <div class="check-list">
                                        <img src="{{ asset('images/check.png') }}" alt="check">
                                    </div>
                                    <p>{{ $data->about_listing2 }}.</p>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="fact-right-inner-wrap">
                        <div class="fact-inner-box">
                            <div class="fact-content top-left-content">
                                <div class="pattern-overlay circle-patten"></div>
                                <h4>{{$data->circle_listing1}}</h4>
                            </div>
                            <figure class="fact-image top-right-image"></figure>
                            <figure class="fact-image bottom-left-image"></figure>
                            <div class="fact-content bottom-right-content">
                                <div class="pattern-overlay circle-c-patten"></div>
                                <h4>{{$data->circle_listing2}}</h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--About End-->
    <main id="scholarship">
        <section class="scolarship mt-10 mb-10">
            <div class="scolarship-one-shape4">
                <img src="{{ asset('images/banner-one-shap4.png') }}" alt="shap">
            </div>
            <div class="rbt-course-grid-column active-list-view">


                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="scolarship-title-one">

                                <h2 class="h2-title">Scholarships</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-top">

                        <div class="col-md-3" id="stickMe">
                            <aside class="sidebar">
                                <h3 class="sidebar-title">Categories</h3>
                                <ul class="side-list">
                                    <li>
                                        {{-- <a href="#edu1">Education 1</a> --}}
                                        <a class="drop-icon" data-bs-toggle="collapse" href="#education1"
                                            role="button" aria-expanded="true" aria-controls="education1"><i
                                                class="fa fa-chevron-down" aria-hidden="true"></i></a>
                                        <ul class="collapse show" id="education1">
                                            <li class="onclickexpand"><a href="#sub-scholarship">ABOUT THE
                                                    SCHOLARSHIP</a></li>
                                            <li class="onclickexpand"><a href="#sub-sponsor">ABOUT THE SPONSOR</a>
                                            </li>
                                            <li class="onclickexpand"><a href="#sub-who">WHO CAN APPLY?</a></li>
                                            <li class="onclickexpand"><a href="#sub-apply">HOW CAN YOU APPLY?</a></li>
                                            <li class="onclickexpand"><a href="#sub-faq">FAQs</a></li>
                                            <li class="onclickexpand"><a href="#sub-contact">CONTACT DETAILS</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </aside>
                        </div>
                        <!-- Start Single Card  -->
                        <div class="col-md-9 scholar-list">
                            <span class="anchor" id="edu1"></span>
                            {{-- <h2 class="h2-subtitle-one">Education 1</h2> --}}

                            @foreach ($scholarships as $value)
                                <div class="course-grid-3">
                                    <div class="rbt-card variation-01 rbt-hover card-list-2">
                                        <div class="rbt-card-img">
                                            <a href="">
                                                <img src="{{ $value->avatar ? asset($value->avatar) : asset('images/logo.png') }}"
                                                    alt="img">
                                            </a>
                                        </div>
                                        
                                        <div class="rbt-card-body">
                                            <div class="row row-first-block">
                                            @if($value->is_featured == 1)
                            <a href="#"
                                class="scholarship__item-tag_bookmark"><i class="fa fa-bookmark"></i></a>
                                @endif
                                @if($value->is_scholarsip == 1)
                            <a href="#"
                                class="scholarship__item-tag background-grey-cstm" style="margin-left: 10px;">Powered By ScholarsBox</a>
                                @endif

                                @if (isset(auth()->user()->id))
                                                        <span class="heart-btn heart-btn-schid-{{ $value->id }}"
                                                            @if (
                                                                $value->savescholorship &&
                                                                    $value->savescholorship->where('schId', $value->id)->where('userid', auth()->user()->id)->isNotEmpty()) style="color:red;" @endif
                                                            onClick="test({{ $value->id }})">
                                                            <i class="fa fa-heart"></i>
                                                        </span>
                                                    @endif
                                            </div>
                                            <div class="row">
                                                <div class="col-md-11 col-10">
                                                    <h4 class="rbt-card-title"><a
                                                            href="">{{ $value->scholarship_name }}</a></h4>
                                                </div>

                                                <div class="col-md-1 col-2">
                                                    <!-- <div class="rbt-bookmark-btn">
                                                        @if (isset(auth()->user()->id))
                                                        <span class="heart-btn heart-btn-schid-{{ $value->id }}"
                                                            @if (
                                                                $value->savescholorship &&
                                                                    $value->savescholorship->where('schId', $value->id)->where('userid', auth()->user()->id)->isNotEmpty()) style="color:red;" @endif
                                                            onClick="test({{ $value->id }})">
                                                            <i class="fa fa-heart"></i>
                                                        </span>
                                                    @endif
                                                    </div> -->
                                                </div>
                                            </div>
                                            <ul class="meta-inner-content">
                                                <li>
                                                    <i class="fa fa-calendar" title="Published Date"></i>
                                                    Published Date: {{ $value->published_date }}
                                                </li>
                                                <li>
                                                    <i class="fa fa-calendar" title="End Date"></i>
                                                    End Date: {{ $value->end_date }}
                                                </li>
                                            </ul>
                                            <div class="a2a_kit a2a_kit_size_32 a2a_default_style"
                                                id="my_centered_buttons_{{ $value->id }}"
                                                style="display: none; justify-content: center;">
                                                <a class="a2a_button_twitter"
                                                    href="https://twitter.com/intent/tweet?url={{ urlencode(route('Student.scholarship.details', $value->id)) }}&text={{ urlencode($value->scholarship_name) }}"></a>
                                                <a class="a2a_button_whatsapp"
                                                    href="whatsapp://send?text={{ urlencode($value->scholarship_name . ' - ' . route('Student.scholarship.details', $value->id)) }}"></a>
                                                <a class="a2a_button_linkedin"
                                                    href="https://www.linkedin.com/shareArticle?url={{ urlencode(route('Student.scholarship.details', $value->id)) }}&title={{ urlencode($value->scholarship_name) }}"></a>
                                            </div>
                                            <div class="bottom-list">
                                                <a id="sharebutton_{{ $value->id }}"
                                                    class="btn sec-btn-one list-btn"
                                                    onclick="toggleShare({{ $value->id }})">
                                                    <img src="{{ asset('images/share.png') }}"
                                                        style="width:20px; filter:invert(1);">
                                                </a>

                                                @if (isset($value->apply_now))
                                                    @if (isset(auth()->user()->id))
                                                        @if (alreadyApplieadScholarship($value->id))
                                                            <a class="btn sec-btn-one list-btn alert_condication" data-ec=""
                                                                data-limit="" data-check="true">Apply Now</a>
                                                        @else
                                                            @if (
                                                                (int) $value->min_age <= auth()->user()->age &&
                                                                    count(array_intersect(education_req_details(), json_decode($value->education_req))) ===
                                                                        count(json_decode($value->education_req)))
                                                                <a class="btn sec-btn-one list-btn"
                                                                    data-limit="{{ $value->min_age }}" data-bs-toggle="modal"
                                                                    data-bs-target="#applyModal"
                                                                    data-scholarship-id="{{ $value->id }}">Apply Now</a>
                                                            @else
                                                                <?php
                                                                $f = (array) json_decode($value->education_req);
                                                                $msg = '';
                                                                foreach ($f as $k => $v) {
                                                                    if(isset($degree)){
                                                                        $msg .= $degree[$v] . (count($f) != $k + 1 ? ', ' : ' ');

                                                                    }
                                                                    else{
                                                                        $msg .= 'Add Requried Fields';

                                                                    }

                                                                   
                                                                }
                                                                $c = count(array_intersect(education_req_details(), json_decode($value->education_req))) !== count(json_decode($value->education_req));
                                                                ?>
                                                                <a class="btn sec-btn-one list-btn alert_condication"
                                                                    data-ec="{{ $c == true ? $msg : false }}"
                                                                    data-limit="{{ $value->min_age }}" data-check="">Apply
                                                                    Now</a>
                                                            @endif
                                                        @endif
                                                    @else
                                                        <a href="{{ route('Student.login') }}" class="btn sec-btn-one list-btn">Apply Now</a>
                                                    @endif
                                                @endif


                                                <a class="btn sec-btn-one list-btn" id="authorize_button"
                                                    onclick="handleAuthClick()">
                                                    <img src="{{ asset('images/add-calendar-symbol-for-events.png') }}"
                                                        style="width:20px; filter:invert(1);" />
                                                </a>
                                                </a>
                                                <a class="readmore btn sec-btn-two list-btn">Learn More</a>
                                            </div>

                                            <div class="medium-12 small-12 columns smalldesc">
                                                <span class="anchor-sub" id="sub-scholarship"></span>
                                                <h2 class="h2-subtitle-one">About the Scholarship</h2>
                                                <p class="font16 ">{{ $value->short_desc }}</p>
                                                <span class="anchor-sub" id="sub-sponsor"></span>

                                                <h2 class="h2-subtitle-one">About the Sponsor</h2>
                                                <p class="font16 ">{{ $value->sponsor_info }} </p>
                                                <span class="anchor-sub" id="sub-who"></span>

                                                <h2 class="h2-subtitle-one">Who can apply?</h2>
                                                <p class="font16 ">{!! $value->who_can_apply_info !!}. </p>
                                                <span class="anchor-sub" id="sub-apply"></span>

                                                <h2 class="h2-subtitle-one">How can you apply?</h2>
                                                <p class="font16 ">{!! $value->how_to_apply_info !!}. </p>
                                                <span class="anchor-sub" id="sub-faq"></span>

                                                <h2 class="h2-subtitle-one" style="text-transform:capitalize;">FAQs</h2>
                                                <p class="font16 ">{!! $value->faqs !!}. </p>
                                                <span class="anchor-sub" id="sub-contact"></span>

                                                <h2 class="h2-subtitle-one">Contact Details</h2>
                                                <!-- <p class="font16 ">{{ $value->contact_details }}. A</p> -->
                                                <p class="font16 ">No Information Is Provided</p>

                                                <div class="bottom-list">
                                                @if (isset($value->apply_now))
                                                    @if (isset(auth()->user()->id))
                                                        @if (alreadyApplieadScholarship($value->id))
                                                            <a class="btn sec-btn-one list-btn alert_condication" data-ec=""
                                                                data-limit="" data-check="true">Apply Now</a>
                                                        @else
                                                            @if (
                                                                (int) $value->min_age <= auth()->user()->age &&
                                                                    count(array_intersect(education_req_details(), json_decode($value->education_req))) ===
                                                                        count(json_decode($value->education_req)))
                                                                <a class="btn sec-btn-one list-btn"
                                                                    data-limit="{{ $value->min_age }}" data-bs-toggle="modal"
                                                                    data-bs-target="#applyModal"
                                                                    data-scholarship-id="{{ $value->id }}">Apply Now</a>
                                                            @else
                                                                <?php
                                                                $f = (array) json_decode($value->education_req);
                                                                $msg = '';
                                                                foreach ($f as $k => $v) {
                                                                    // $msg .= $degree[$v] . (count($f) != $k + 1 ? ', ' : ' ');
                                                                    if(isset($degree)){
                                                                        $msg .= $degree[$v] . (count($f) != $k + 1 ? ', ' : ' ');

                                                                    }
                                                                    else{
                                                                        $msg .= 'Add Requried Fields';

                                                                    }
                                                                }
                                                                $c = count(array_intersect(education_req_details(), json_decode($value->education_req))) !== count(json_decode($value->education_req));
                                                                ?>
                                                                <a class="btn sec-btn-one list-btn alert_condication"
                                                                    data-ec="{{ $c == true ? $msg : false }}"
                                                                    data-limit="{{ $value->min_age }}" data-check="">Apply
                                                                    Now</a>
                                                            @endif
                                                        @endif
                                                    @else
                                                        <a href="{{ route('Student.login') }}" class="btn sec-btn-one list-btn">Apply Now</a>
                                                    @endif
                                                @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>


                    </div>
                </div>
            </div>

            </div> 
        </section>


    </main>
    @include('student.includes.model')

    @include('layouts.includes.microfooter')



    @include('student.includes.scripts')

    



    <script>
        $(document).ready(function() {
            // Get current URL
            var currentURL = window.location.href;

            // Check if URL contains a specific string
            if (currentURL.indexOf("https://bharatcares.scholarsbox.in/") !== -1) {
                // Change CSS based on URL
                $(".main-banner-one").css("background-color", "#fff");
                $(".banner-slide-text p").css("color", "#0D244D");
                $(".h1-title").css("color", "#0D244D");
                $(".banner-content-one p").css("color", "#0D244D");
                $(".sec-btn-one").css("background-color", "#c90000");
                $(".sec-btn-two").css("background-color", "#c90000");
                $(".color--theme").css("color", "#0D244D");
                $(".subtitle-circle-one").css("background-color", "#0D244D");
                $(".h2-subtitle-one").css("color", "#0D244D");
		$(".points-one p").css("color", "#6B6B6B");
		$(".descript-one").css("color", "#6B6B6B");
                $(".h2-subtitle-one").css("border-bottom", "2px solid #0e224b");
                $(".h2-title").css("color", "#0D244D");
                $(".home-fact-section .fact-right-inner-wrap .fact-inner-box .fact-content h4").css("color",
                    "#0D244D");
                $('.side-list ul li a').attr('style', 'color: #0D244D !important');
                $('.sidebar a').attr('style', 'color: #0D244D !important');
                $(".sec-btn-one::before").css("background-color", "#c90000");
                $(".heart-btn").css("background", "#0D244D");
                $(".footer-copyright-one").css("background-color", "#0e224b");

                $(".sidebar-title").css("color", "#0d244d");

                $(".check-list").css("background-color", "#c90000");
                $(".scolarship").css("background-color", "#C0C0C0");
                $(".main-footer-one").css("background-color", "#c90000");

            } else if (currentURL.indexOf("https://csrbox.scholarsbox.in/") !== -1) {

                $(".main-banner-one").css("background-color", "#fff");
                $(".banner-slide-text p").css("color", "#1B91C9");
                $(".h1-title").css("color", "#1B91C9");
                $(".banner-content-one p").css("color", "#1B91C9");
                // Change CSS based on URL

            } else if (currentURL.indexOf("https://lg.scholarsbox.in/") !== -1) {

                $(".main-banner-one").css("background-color", "#fff");
                $(".banner-slide-text p").css("color", "#6b6b6b");
                $(".h1-title").css("color", "#A50034");
                $(".banner-content-one p").css("color", "#6b6b6b");
                $(".sec-btn-one").css("background-color", "#A50034");
                $(".sec-btn-two").css("background-color", "#6b6b6b");
                $(".subtitle-circle-one").css("background-color", "#A50034");
                $(".h2-subtitle-one").css("color", "#A50034");
                $(".check-list").css("background-color", "#A50034");
                $(".home-fact-section .fact-right-inner-wrap .fact-inner-box .fact-content h4").css("color",
                    "#6b6b6b");
                $(".scolarship").css("background-color", "#ececec");
                $(".scolarship-title-one h2").css("color", "#A50034");
                $(".h2-subtitle-one").css("border-bottom", "2px solid #A50034");
                $(".sidebar-title").css("color", "#A50034");
                $('.side-list ul li a').attr('style', 'color: #A50034 !important');
                $('.sidebar a').attr('style', 'color: #A50034 !important');
                $(".heart-btn").css("background", "#A50034");
                $(".main-footer-one").css("background", "#A50034");
                $(".footer-copyright-one").css("background-color", "#6b6b6b");


                // Change CSS based on URL

            }
            // Add more conditions as needed

            // Alternatively, you can use switch case for better readability
            // switch(currentURL) {
            //     case "example.com/page1":
            //         $("body").css("background-color", "#ff0000");
            //         $("body").css("color", "#ffffff");
            //         break;
            //     case "example.com/page2":
            //         $("body").css("background-color", "#00ff00");
            //         $("body").css("color", "#000000");
            //         break;
            //     // Add more cases as needed
            // }
        });
    </script>
    </script>
</body>

</html>
<script async src="https://static.addtoany.com/menu/page.js"></script>
<script>
    function toggleShare(scholarshipId) {
        // Get the share div corresponding to the clicked share button
        const shareDiv = document.getElementById('my_centered_buttons_' + scholarshipId);

        // Toggle the display state of the share div
        if (shareDiv.style.display === 'block') {
            shareDiv.style.display = 'none'; // Hide the share div if it's currently visible
        } else {
            shareDiv.style.display = 'block'; // Show the share div if it's currently hidden
        }
    }
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    function test(schid) {
        // Get the CSRF token from the meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: 'POST',
            url: "{{ route('Student.save.scholorship') }}",
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                scholarshipId: schid
            },
            success: function(response) {
                // Display toastr popup on success
                toastr.success(response.message);
                if (response.data == 1) {
                    $('.heart-btn-schid-' + schid).css('color', '#A2CC3B');
                } else {
                    $('.heart-btn-schid-' + schid).css('color', 'white');
                }
            },
            error: function(error) {
                console.error('Error saving scholarship', error);
                // Display toastr popup on error
                toastr.error('Error saving scholarship');
            }
        });
    }
</script>
<script>
    function sendEmail() {
        var formData = $('#emailForm').serialize();

        $.ajax({
            type: 'POST',
            url: '{{ route('newsletter.mail') }}',
            data: formData,
            success: function (response) {
                toastr.success(response.message);
                $('#emailForm')[0].reset(); // Reset the form
            },
            error: function (error) {
                console.log(error.responseJSON);
                toastr.error('Email Address Not found !!');

            }
        });
    }

    $('#emailForm').submit(function (event) {
        event.preventDefault(); // Prevent the default form submission
        sendEmail();
    });

  
    document.getElementById('micrositeSignup').addEventListener('click', function(event) {
  event.preventDefault(); // Prevent default link behavior
  
  // Get company name
  var companyName = "{{ $companyForSignUp }}";
  var micrositeFlag = 1; // Set microsite flag
  
  // Make AJAX request to set session
  $.ajax({
    type: "POST",
    url: "{{ route('set.session') }}",
    data: {
      companyName: companyName,
      Microsite: micrositeFlag
    },
    success: function(response) {
      console.log("Session values set successfully");
      // Optionally, redirect user after successful session setting
      window.location.href = "{{ route('Student.register') }}";
    },
    error: function(xhr, status, error) {
      console.error("Error setting session values:", error);
    }
  });
});




</script>
