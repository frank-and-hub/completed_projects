@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Login')

<div class="row g-0 login-wrapper">
    <div class="col-lg-6 login-left">
        <div class="text-center loginLeftImg"></div>
    </div>
    <div class="col-lg-6  col-md-12 login-right">
        <div class="loginRightImg">
            <div class="login-container">
                <div class="text-center mb-5"><img src="{{ asset('assets/images/logo-login.svg') }}"></div>
                <form method="post" action="{{route('admin.loginAuth')}}">
                    @csrf
                    <div class="whiteBg">
                        <h4>Sign In</h4>
                        @if (session('status'))
                        <div class="alert alert-success text-center mt-3">
                            {{ session('status') }}
                        </div>
                        @endif
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Email<span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" placeholder="Please enter your email" name="email" value="">
                            </div>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Password<span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg" placeholder="Please enter your password" name="password" value="">
                            </div>
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-space-between">
                                <div class="form-check">
                                    <label class="form-check-label" for="flexCheckDefault">
                                    </label>
                                </div>

                                <a href="{{ route('admin.forgotPassword') }}" class="forgotPass ms-auto">Forgot Password?</a>

                            </div>
                        </div>
                        <button type="submit" class="btn btn-lg btn-primary w-100">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
