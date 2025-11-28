@extends('student.layout.app')
@section('title', 'Register - Scholarsbox')
@section('content')
<?php
$state = \App\Models\CountryData\State::whereStatus('active')->get(); 
$district = \App\Models\CountryData\District::whereStatus('active')->get(); 
?>

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
                        <h1 class="h1-title">Sign Up</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="{{ route('Student.register') }}">Sign Up</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <!-- SIGN UP PAGE
   ============================================= -->
    <div id="signup" class="bg--scroll login-section division">
        <div class="container">
            <div class="row justify-content-center">


                <!-- REGISTER PAGE WRAPPER -->
                <div class="col-lg-12">
                    <div class=" r-16 bg--fixed">
                        <div class="row">

                            <!-- SIGN UP PAGE TEXT -->
                            <div class="col-md-5">
                                <div class="register-page-txt color--white">

                                    <!-- Logo -->
                                    <img class="img-fluid" src="{{ asset('images/Scholars Box-Logo-01-crop.png') }}" alt="logo-image">
                                    <!-- Section ID -->
                                    <!-- <span class="section-id">Start for free</span> -->

                                    <!-- Title -->
                                    <h2 class="s-42 w-700 p-0 m-0 h2-title">Create</h2>
                                    <h2 class="s-42 w-700 p-0 m-0 h2-title">an account</h2>

                                    <!-- Text -->
                                    <div class="txt-block left-column wow fadeInRight">


                                        <!-- CONTENT BOX #1 -->
                                        <div class="cbox-2 process-step mt-5 wow fadeInRight">

                                            <!-- Icon -->
                                            <div class="ico-wrap">
                                                <div class="cbox-2-ico bg--theme color--white">1</div>
                                                <span class="cbox-2-line"></span>
                                            </div>

                                            <!-- Text -->
                                            <div class="cbox-2-txt">
                                                <h5 class="s-22 w-700">Sign Up</h5>
                                                <p>Fill in a short form or use an existing Google Account to create an account
                                                </p>
                                            </div>

                                        </div> <!-- END CONTENT BOX #1 -->


                                        <!-- CONTENT BOX #2 -->
                                        <div class="cbox-2 process-step wow fadeInRight">

                                            <!-- Icon -->
                                            <div class="ico-wrap">
                                                <div class="cbox-2-ico bg--theme color--white">2</div>
                                                <span class="cbox-2-line"></span>
                                            </div>

                                            <!-- Text -->
                                            <div class="cbox-2-txt">
                                                <h5 class="s-22 w-700">Login</h5>
                                                <p>Log into your account and easily apply for scholarships that are best suited to you
                                                </p>
                                            </div>

                                        </div> <!-- END CONTENT BOX #2 -->
                                    </div>

                                </div>
                            </div> <!-- END SIGN UP PAGE TEXT -->

                            <!-- SIGN UP FORM -->
                            <div class="col-md-7 bg-white register-page-wrapper">

                                <div class="register-page-form">

                                    <div class="text-center"><a href="{{ url('auth/google') }}"
                                            class="btn sec-btn-one w-100"><i class="fa fa-google">
                                            </i>
                                            Sign Up with Google</a></div>
                                    <div class="or-text">OR</div>
                                    <form name="signupform" method="post" action="{{ route('Student.doRegister') }}"
                                        class="row sign-up-form wow fadeInLeft">
                                        @csrf

                                        <div id="wizard">

                                            <h4></h4>
                                            <section>
                                                <div class="row">
                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">First name</p>
                                                        <input class="form-control name" type="text"
                                                            name="first_name" placeholder="Rohan" required autocomplete="off">
                                                    </div>

                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">Last name</p>
                                                        <input class="form-control name" type="text" name="last_name"
                                                            placeholder="Mehra" autocomplete="off">
                                                    </div>

                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">Email</p>
                                                        <input class="form-control name" type="email" name="email"
                                                            placeholder="example@example.com" autocomplete="off">
                                                    </div>

                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">Mobile Number</p>
                                                        <input class="form-control" type="tel" name="phone_number"
                                                            placeholder="7619xxxxxx" autocomplete="off">
                                                    </div>
                                                </div>
                                            </section>



                                            <h4></h4>
                                            <section>
                                                <div class="row">
                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                    <p class="p-sm input-header">Date of Birth</p>
                                                    <input class="form-control name" type="date" name="date_of_birth" placeholder="Date of Birth" style="padding:20px" max="<?php echo date('Y-m-d'); ?>">
                                                </div>


                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">Gender</p>
                                                        <select name="gender" class="form-control" autocomplete="off">
                                                            <option value="">Select Gender</option>
                                                            <option value="male" >Male</option>
                                                            <option value="female" >Female</option>
                                                            <option value="other"> Other</option>
                                                        </select>
                                                    </div>

                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">State</p>
                                                        <select name="state" class="form-control" id="stateLogin" autocomplete="off">
                                                            <option value="" class="" data-val="">Select State</option>
                                                            @foreach($state as $k => $val)
                                                            <option value="{{$val->name}}" data-val="{{(int)$val->id}}">{{$val->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">District</p>
                                                        <select name="district" class="form-control" id="districtlogin" autocomplete="off">
                                                            <option value="0" class="" data-state="">Select District</option> 
                                                            @foreach($district as $k => $val)
                                                            <option value="{{$val->name}}" class="" data-state="{{(int)$val->state_id}}">{{$val->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </section>
                                            <script>
                                            document.getElementById('stateLogin').addEventListener('change', function() {
                                                const state = this.options[this.selectedIndex].getAttribute('data-val');
                                                document.getElementById('districtlogin').selectedIndex = 0;
                                                document.querySelectorAll('#districtlogin option').forEach(function(option) {
                                                    if (option.getAttribute('data-state') === state) {
                                                        option.disabled = false;
                                                        option.style.visibility = 'visible'; 
                                                        option.style.display = 'block';
                                                    } else {
                                                        option.disabled = true;
                                                        option.style.visibility = 'hidden';
                                                        option.style.display = 'none';
                                                    }
                                                });
                                            });
                                            </script>
                                            <h4></h4>
                                            <section>
                                                <div class="row">
                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">I am</p>
                                                        <select name="user_type" class="form-control" autocomplete="off">
                                                            <option value="">Please Select</option>
                                                            <option>A School student</option>
                                                            <option>Pursuing Bachelors</option>
                                                            <option>Pursuing masters</option>
                                                            <option>Pursuing PhD.</option>
                                                            <option>Pursuing ITIs/Diploma/Polytechnic/Certificate course</option>
                                                            <option>Preparing for competitive exams</option>
                                                            <option>Working Professional</option>
                                                            <option>Others</option>
                                                        </select>
                                                    </div>

                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">I’m looking for</p>
                                                        <select name="looking_for" class="form-control" autocomplete="off">
                                                            <option value="">Please Select</option>
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
                                            <style>
                                                .password-container {
                                                    position: relative;
                                                }
                                        
                                                .password {
                                                    padding-right: 30px; /* Space for the eye icon */
                                                }
                                        
                                                #password-toggle2 {
                                                    position: absolute;
                                                    top: 50%;
                                                    right: 10px;
                                                    transform: translateY(-50%);
                                                    cursor: pointer;
                                                }
                                            </style>
                                            <section>
                                                <div class="row">
                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">Password</p>
                                                        <div class="wrap-input">
                                                            <span class="btn-show-pass ico-20"><span
                                                                    class="flaticon-visibility eye-pass"></span></span>
                                                            <input name="password" class="form-control password"
                                                                type="password" placeholder="min 8 characters"
                                                                required>
                                                        </div>
                                                    </div>

                                                    <!-- Form Input -->
                                                    <div class="col-md-6 mb-25">
                                                        <p class="p-sm input-header">Confirm Password</p>
                                                        <div class="wrap-input">
                                                            <span class="btn-show-pass ico-20"><span
                                                                    class="flaticon-visibility eye-pass"></span></span>
                                                                    <!--<div class="password-container">-->
                                                            <input class="form-control password" type="text"
                                                                name="confirm_password" placeholder="Retype Password"
                                                                required>
                                                                <!--<i class="fa fa-eye" id="password-toggle2"></i>-->
                                                            <!--</div>-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>
                                    </form>


                                </div>
                            </div> <!-- END SIGN UP FORM -->



                        </div> <!-- End row -->
                    </div> <!-- End register-page-wrapper -->
                </div> <!-- END REGISTER PAGE WRAPPER -->


            </div> <!-- End row -->
        </div> <!-- End container -->
    </div> <!-- END SIGN UP PAGE -->


</div> <!-- END PAGE CONTENT -->
@endsection