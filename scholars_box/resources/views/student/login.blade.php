@extends('student.layout.app')
@section('title', 'Login - Scholarsbox')
@section('content')
<div>

    <!--Banner Start-->
    <section class="main-inner-banner-one">
        <div class="blur-1">
            <img src="{{ asset('images/Blur_1.png') }}" alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="{{ asset('images/Blur_2.png') }}" alt="bg blur">
        </div>
        <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
        <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
        <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
        <div class="banner-one-shape4">
            <img src="{{ asset('images/banner-inner-shape-one.png') }}" alt="shap">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">Login</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="{{ route('Student.login') }}">Login</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
    <!-- LOGIN PAGE
   ============================================= -->
    <div id="login" class="bg--scroll login-section division">
        <div class="container">
            <div class="row justify-content-center">


                <!-- REGISTER PAGE WRAPPER -->
                <div class="col-lg-12">
                    <div class="r-16 bg--fixed">
                        <div class="row">
    <?php
    $loginpagedata = DB::table('loginpage')->first();
    ?>

                            <!-- LOGIN PAGE TEXT -->
                            <div class="col-md-6 wow fadeInLeft">
                                <div class="register-page-txt color--white">

                                    <!-- Logo -->
                                    <img class="img-fluid" src="{{ asset('images/Scholars Box-Logo-01 new.png') }}" alt="logo-image">

                                    <!-- Logo if someone signs in from Micorsite -->
                                    <!-- <div class="logoifmicor">
                                        <img class="img-fluid" src="{{ asset('images/HMIF LOGO-1.jpg') }}" alt="logo-image" style="max-height:65px;">
                                    </div> -->

                                    <!-- Title -->
                                    <h2 class="s-42 w-700 p-0 m-0 h2-title">{{ucwords($loginpagedata->title)}}</h2>
                                    <!--<h2 class="s-42 w-700 p-0 m-0 h2-title">to ScholarsBox</h2>-->

                                    <!-- Text -->
                                    <p class="p-md mt-2">{{$loginpagedata->description??null}}
                                    </p>

                                </div>
                            </div> <!-- END LOGIN PAGE TEXT -->


                            <!-- LOGIN FORM -->
                            <div class="col-md-6 register-page-wrapper">
                                <div class="register-page-form">
                                    <form id="loginForm" method="post" action="{{ route('Student.doLogin') }}"
                                        class="row sign-in-form wow fadeInRight">

                                        @csrf

                                        <div class="text-center"><a href="{{ url('auth/google') }}"
                                                class="btn sec-btn-one w-100"><i class="fa fa-google"> </i> Sign in with
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
                                                    padding-right: 30px; /* Space for the eye icon */
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
                                                        class="flaticon-visibility eye-pass"></span></span><div class="password-container">
                                                <input class="form-control password" type="password" name="password"
                                                    placeholder="* * * * * * * * *" required>
                                                    <i class="fa fa-eye" id="password-toggle"></i>
                                                    </div>
                                            </div>
                                        </div>
                                        

                                        <!-- Reset Password Link -->
                                        <div class="col-md-12 mb-25">
                                            <div class="reset-password-link">
                                                <p class="p-sm"><a href="{{route('forget.password.get')}}"
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
                                                    class="color--theme" id="withoutMicroSite">Sign up</a>
                                            </p>
                                        </div>

                                    </form>
                                </div>
                            </div> <!-- END LOGIN FORM -->


                        </div> <!-- End row -->
                    </div> <!-- End register-page-wrapper -->
                </div> <!-- END REGISTER PAGE WRAPPER -->


            </div> <!-- End row -->
        </div> <!-- End container -->
    </div> <!-- END LOGIN PAGE -->

</div> <!-- END PAGE CONTENT -->

<script>
    document.getElementById('withoutMicroSite').addEventListener('click', function(event) {
  event.preventDefault(); // Prevent default link behavior
  
  // Get company name
  var companyName = "ScholarsBox";
  var micrositeFlag = 0; // Set microsite flag
  
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
@endsection