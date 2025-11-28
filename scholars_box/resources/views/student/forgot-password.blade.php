  @extends('student.layout.app')
@section('title', 'Forgot password')

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
                        <h1 class="h1-title">Forget Password</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="index.php">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="{{route('Student.forgot.pasword')}}">Forget Password</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <!-- FORGET PASSWORD PAGE
			============================================= -->
			<div id="login" class="bg--scroll login-section division">
				<div class="container">
					<div class="row justify-content-center">


						<!-- REGISTER PAGE WRAPPER -->
						<div class="col-lg-12">
							<div class="r-16 bg--fixed">	
								<div class="row">


									<!-- FORGET PASSWORD PAGE TEXT -->
									<div class="col-md-6 wow fadeInLeft forget-pass-cstm-css">
										<div class="register-page-txt color--white">

											<!-- Logo -->
											<img class="img-fluid" src="{{asset('images/Scholars Box-Logo-01 new.png')}}" alt="logo-image">		

											<!-- Title -->
											<h2 class="s-42 w-700 p-0 m-0 h2-title">Confirm your</h2>
											<h2 class="s-42 w-700 p-0 m-0 h2-title">Email</h2>								

										</div>
									</div>	<!-- END FORGET PASSWORD PAGE TEXT -->


									<!-- FORGET PASSWORD FORM -->
									<div class="col-md-6 register-page-wrapper">
										<div class="register-page-form">
										<form id="forgetPasswordForm" action="{{ route('forget.password.post') }}" method="POST">
    @csrf
    <!-- Form Input -->
    <div class="col-md-12 mt-3">
        <p class="p-sm input-header">Enter E-mail Address</p>
        <input class="form-control email" type="email" name="email" placeholder="example@example.com" required>
    </div>
    <!-- Form Submit Button -->
    <div class="col-md-12">
        <button type="button" id="sendResetLink" class="btn sec-btn-one w-100">Send Password Reset Link</button>
    </div>
    <!-- Sign Up Link -->
    <div class="col-md-12">
        <p class="create-account text-center">
            New user? Join the community! <a href="{{ route('Student.register') }}" class="color--theme">Sign up</a>
        </p>
    </div>
</form>
 
										</div>
									</div>	<!-- END FORGET PASSWORD FORM -->


								</div>  <!-- End row -->
							</div>	<!-- End register-page-wrapper -->
						</div>	<!-- END REGISTER PAGE WRAPPER -->


			 		</div>	   <!-- End row -->	
			 	</div>	   <!-- End container -->		
			</div>	
			<!-- END LOGIN PAGE -->
			<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

			<script>
    $(document).ready(function () {
        $('#sendResetLink').on('click', function () {
            // Get form data
            var formData = $('#forgetPasswordForm').serialize();

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: '{{ route('forget.password.post') }}',
                data: formData,
                success: function (response) {
					if(response.value == 0){
						toastr.error(response.message);
					}else{
						toastr.success(response.message);
					}
					console.log(response.success);
                    
                $('#forgetPasswordForm')[0].reset(); // Reset the form
                },
                error: function (error) {
                    // Handle errors (you may display an error message to the user)
                    console.log(error.responseJSON);
                }
            });
        });
    });
</script>

    @endsection
