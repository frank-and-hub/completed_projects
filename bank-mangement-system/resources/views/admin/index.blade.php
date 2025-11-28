@extends('layout')
@section('css')

@stop
@section('content')
<section id="header" class="backg backg-one" style="background-image: linear-gradient(0deg, #{{$set->gradient1}} 0%, #{{$set->gradient2}} 100%);">
<div class="circle-shape"><img src="{{url('/')}}/asset/img/shape/shape-circle.svg" alt="circle"></div>

<div class="container backg">
    <div class="backg-content-wrap">
		<div class="row align-items-center">
            <div class="col-lg-4">
            </div>
            <div class="col-lg-4">
            <div class="navbar-logo">
                <a href="{{url('/')}}/login" class="logo">
                    <img src="{{url('/')}}/asset/images/sbmfa_logo.jpg" alt="logo" class="main-logo">
                </a>
            </div>
            </div>
            <div class="col-lg-4">
            </div>
        </div>
		
        <div class="row align-items-center">
           {{-- <div class="col-lg-6 z100">
                --}}{{--<div class="backg-content">
                    <span class="discount wow soneFadeUp" data-wosw-delay="0.3s">Welcome back,</span>
                    <h1 class="backg-title wow soneFadeUp" data-wow-delay="0.5s">
                    Sign in to continue
                    </h1>                 

                </div>--}}{{--
                <!-- /.backg-content -->
            </div>--}}
            <!-- /.col-lg-6 -->

            <div class="col-lg-4"></div>
				<div class="col-lg-4">
				<div class="wow soneFadeLeft sign-in-box">
				  <form action="{{route('submitAdminlogin')}}" method="post" class="contact-form" data-saasone="contact-froms" id="login-form">
					  @csrf
					  <input type="text" name="username" id="username" placeholder="Username" class="username login_branch">
					  <input type="password" name="password" placeholder="Password" class="password login_password">
					  <div class="text-left">

					  </div>
					  <div class="text-right">
					  		<input type="hidden" name="loginstatus" id="loginstatus">
						  <button type="submit" class="sone-btn">Sign In</button>
					  </div>
				  </form>
				</div>
                <!-- /.promo-mockup -->
				
				<div class="wow soneFadeLeft varification-box" style="display: none;">
					<form action="javascript:void(0);" method="post" data-saasone="contact-froms" id="varification-form">
						@csrf
						<input type="text" name="username" placeholder="Username" class="branch n_login_branch">
						<input type="password" name="password" placeholder="Password" class="password n_login_password">
						<input type="text" name="otp" placeholder="OTP" class="otp">
						<input type="hidden" name="pNumber" class="pNumber">
						<input type="hidden" name="uId" class="uId">
						<a href="javascript:void(0);" name="resendotp" id="resendotp">Resend OTP</a>
						<div class="text-right varification-button">
							<button type="submit" class="sone-btn">Verify</button>
						</div>
					</form>
				</div>
				
				
            </div>
			<div class="col-lg-4"></div>
            <!-- /.col-lg-6 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.backg-content-wrap -->
</div>
<!-- /.container -->
</section>

@stop

@section('script')
@include('admin.script')
@stop