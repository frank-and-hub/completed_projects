@extends('student.layout.app')

@section('title', 'Home - Scholarsbox')

@section('content')
  <!--Banner Start-->
  <section class="main-inner-banner-one">
    <div class="blur-1">
        <img src="assets/images/Blur_1.png" alt="bg blur">
    </div>
    <div class="blur-2">
        <img src="assets/images/Blur_2.png" alt="bg blur">
    </div>
    <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
    <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
    <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
    <div class="banner-one-shape4">
        <img src="assets/images/banner-inner-shape-one.png" alt="shap">
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrum-title-one wow fadeInDown">
                    <h1 class="h1-title">Confirm OTP</h1>
                    <!-- <div class="breadcrum-one">
                        <ul>
                            <li>
                                <a href="index.php">Home</a>
                            </li>
                            <li>
                                <i class="fa fa-chevron-right"></i>
                            </li>
                            <li>
                                <a href="contact-us.php">Confirm Password</a>
                            </li>
                        </ul>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</section>
<!--Banner End-->

<!-- CONFIRM PASSWORD PAGE
        ============================================= -->
        <div id="login" class="bg--scroll login-section division">
            <div class="container">
                <div class="row justify-content-center">


                    <!-- REGISTER PAGE WRAPPER -->
                    <div class="col-lg-12">
                        <div class="r-16 bg--fixed">	
                            <div class="row">


                                <!-- CONFIRM PASSWORD PAGE TEXT -->
                                <div class="col-md-6 wow fadeInLeft">
                                    <div class="register-page-txt color--white">

                                        <!-- Logo -->
                                        <img class="img-fluid" src="{{asset('images/Scholars Box-Logo-01.png')}}" alt="logo-image">		

                                        <!-- Title -->
                                        <h2 class="s-42 w-700 p-0 m-0 h2-title">Confirm OTP</h2>
                                        <!-- <h2 class="s-42 w-700 p-0 m-0 h2-title">New Password</h2>									 -->

                                    </div>
                                </div>	<!-- END CONFIRM OTP PAGE TEXT -->


                                <!-- CONFIRM PASSWORD FORM -->
                                <div class="col-md-6 register-page-wrapper">
                                    <div class="register-page-form">
                                        <form action="{{route('verfiy-otp')}}" method="post">
                                            @csrf
                                            <div class="col-md-12 mt-3">
                                                <p class="p-sm input-header">Enter The OTP Received</p>
                                                <input class="form-control email" type="text" name="oto" placeholder="* * * * * * * * *"> 
                                            </div>
                                            <input type="hidden" name="mobile_numer" value="{{$user->phone_number}}">
                                            <!-- Form Submit Button -->	
                                            <div class="col-md-12">
                                                <button type="submit" class="btn sec-btn-one w-100">Login</button>
                                            </div> 

                                            <!-- Sign Up Link -->	
                                            <div class="col-md-12">
                                                <p class="create-account text-center">
                                                New user? Join the community! <a href="{{route('Student.register')}}" class="color--theme">Sign up</a>
                                                </p>
                                            </div>  

                                        </form> 
                                    </div>
                                </div>	<!-- END CONFIRM PASSWORD FORM -->


                            </div>  <!-- End row -->
                        </div>	<!-- End register-page-wrapper -->
                    </div>	<!-- END REGISTER PAGE WRAPPER -->


                 </div>	   <!-- End row -->	
             </div>	   <!-- End container -->		
        </div>	<!-- END CONFIRM PASSWORD PAGE -->




    </div>	<!-- END PAGE CONTENT -->

@endsection
