@extends('student.layout.app')

@section('title', 'Home - Scholarsbox')

@section('content')
    <!--Banner Start-->
    <section class="main-inner-banner-one">
        <!-- <div class="blur-1">
            <img src="assets/images/Blur_1.png" alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="assets/images/Blur_2.png" alt="bg blur">
        </div> -->
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
                        <h1 class="h1-title">Login With OTP</h1>
                        <!-- <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="index.php">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="contact-us.php">Login With OTP</a>
                                </li>
                            </ul>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->
   
    <!-- LOGIN WITH OTP PAGE
			============================================= -->
			<div id="login" class="bg--scroll login-section division">
				<div class="container">
					<div class="row justify-content-center">


						<!-- REGISTER PAGE WRAPPER -->
						<div class="col-lg-12">
							<div class="r-16 bg--fixed">	
								<div class="row">


									<!-- LOGIN WITH OTP TEXT -->
									<div class="col-md-6 wow fadeInLeft">
                                
										<div class="register-page-txt color--white">

											<!-- Logo -->
											<img class="img-fluid" src="{{asset('images/Scholars Box-Logo-01.png')}}" alt="logo-image">		

											<!-- Title -->
											<h2 class="s-42 w-700 p-0 m-0 h2-title">Enter your</h2>
											<h2 class="s-42 w-700 p-0 m-0 h2-title">Mobile Number to Get OTP</h2>								

										</div>
									</div>	<!-- END FORGET PASSWORD PAGE TEXT -->


									<!-- FORGET PASSWORD FORM -->
									<div class="col-md-6 register-page-wrapper">
                                    @if(session('message'))
                                        <div class="alert alert-error"> 
                                            {{ session('message') }}    
                                        </div>
                                    @endif
										<div class="register-page-form">
											<form method="post" id="mo"> 
                                                @csrf
												<!-- Form Input -->	
												<div class="col-md-12 mt-3">
													<p class="p-sm input-header">Enter Registered Mobile Number</p>
													 <input class="form-control email" type="tel" name="mobileNmber" id="mobileno" pattern="[0-9]{10}" placeholder="997xxxxxxx" required>
												</div>
												<!-- Form Submit Button -->	
												<div class="col-md-12">
													<button type="submit" onclick="sendOtp()" class="btn sec-btn-one w-100">Get OTP</button>
												</div> 

												<!-- Sign Up Link -->	
												<div class="col-md-12">
													<p class="create-account text-center">
                                                    New user? Join the community! <a href="{{route('Student.register')}}" class="color--theme">Sign up</a>
													</p>
												</div>  

											</form> 
										</div>
									</div>	<!-- END LOGIN WITH OTP FORM -->


								</div>  <!-- End row -->
							</div>	<!-- End register-page-wrapper -->
						</div>	<!-- END REGISTER PAGE WRAPPER -->


			 		</div>	   <!-- End row -->	
			 	</div>	   <!-- End container -->		
			</div>	<!-- END LOGIN PAGE -->




		</div>	<!-- END PAGE CONTENT -->

@endsection
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script> 

<script>
    function sendOtp(e) {
        e.preventDefault();
		var obile = $('#mobileno').val();

        $.ajax({
            type: 'POST',
            url: '{{ route('login.otp') }}',
            data: {
				mobile: obile
			},
            success: function (response) { 
            // alert(response); 
                console.log(response);
				if(response.data == 0){
					toastr.error(response.message);
				}

                if(response.data == 1){
                    toastr.error('Mobile No Not Found !!');

                }
                
                $('#mo')[0].reset(); // Reset the form
            },
            error: function (error) {
                console.log(error.responseJSON);

            }
        });
    }


</script>
