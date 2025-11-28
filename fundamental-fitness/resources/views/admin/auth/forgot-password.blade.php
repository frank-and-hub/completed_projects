@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Forgot Password')

<div class="row g-0 login-wrapper">
    <div class="col-lg-6 login-left">
        <div class="text-center loginLeftImg"></div>
    </div>
    <div class="col-lg-6  col-md-12 login-right">
        <div class="loginRightImg">
            <div class="login-container">
                <div class="text-center mb-5"><img src="{{ asset('assets/images/logo-login.svg') }}"></div>
                <form method="post" action="{{ route('admin.sendResetToken') }}">
                    @csrf
                    <div class="whiteBg">
                        <h4>Forgot Password</h4>
                        <p>Please enter your email address, you will receive a link to create a new password via email
                        </p>
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Email<span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" placeholder="Please enter your email"
                                    name="email" value="">
                            </div>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="btn-block">
                            <button type="submit" class="btn btn-lg btn-primary w-100">
                                Send
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.login') }}" class="forgotPass ms-auto"> Back to Login </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
