@extends('layout.base')
@section('master')
@section('title', __('PocketProperty | Login'))
<style>
    .login_background {
        /* background-image: url("{{ asset('assets/admin/images/header_banner.png') }}");
        background-size: cover; */
        background: #F30051;
        position: relative;
    }

    .login_background::after {
        content: "";
        position: absolute;
        bottom: 0;
        right: 0;
        background-image: url("{{ asset('assets/admin/images/header_banner.png') }}");
        width: 50%;
        height: 100%;
        z-index: 0;
        background-repeat: no-repeat;
        background-size: 100%;
        background-position: bottom right;
    }

    .login_background .row {
        z-index: 1;
    }

    .forgot_password_div {
        text-align: right;
        margin: 10px 0 0 0;
    }
</style>
<div class="container-fluid page-body-wrapper full-page-wrapper">
    <div class="content-wrapper d-flex align-items-center auth login_background">
        <div class="row w-100">
            <div class="col-lg-4 mx-auto">
                <div class="auth-form-light text-left p-5">
                    <div class="text-center">
                        <img src="{{ asset('assets/admin/images/logo.png') }}" alt="logo" style="width:100%;">
                    </div>
                    <h4 class="mt-3">Hello {{ $title }}!</h4>
                    <h6 class="font-weight-light">Sign in to continue.</h6>
                    <form class="pt-4" id="login_form" name="login_form">
                        @csrf
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control form-control-lg border-0"
                                id="exampleInputEmail1" placeholder="Enter email">
                        </div>
                        <div class="form-group position-relative">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control form-control-lg border-0"
                                id="exampleInputPassword1" placeholder="Password">
                            <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                        </div>
                        <div class="mt-3">
                            <div class="success_msg mt-3"></div>
                            <div class="error_msg text-danger mt-3"></div>
                            <button class="btn btn-block login-btn btn-lg font-weight-medium auth-form-btn">SIGN
                                IN</button>
                            @if (isset($type_url))
                                <div class="forgot_password_div">
                                    <a href="{{ route('forgot-password', $type_url) }}" class="link-primary">Forgot
                                        Password</a>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('custom-script')
    <script type="text/javascript">
        $(document).ready(function() {
            var url = "{{ in_array($type_url ?? '', ['privatelandlord', 'agency', 'agent']) ? route('sub_loginprocess', $type_url) : route('loginprocess') }}";

            $('#login_form').validate({
                rules: {
                    email: {
                        required: true,
                        email_rule: true,
                    },
                    password: {
                        required: true
                    },
                },
                messages: {
                    email: {
                        required: 'Please enter your email',
                        email_rule: "Please Enter Valid Format",
                    },
                    password: {
                        required: 'Please enter your password'
                    },
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.status == 'success') {
                                $("form[name='login_form']").find('.serverside_error').remove();
                                $('.success_msg').html(response.message);
                                $('.success_msg').fadeIn();
                                setTimeout(function() {
                                    $('.success_msg').fadeOut();
                                }, 5000);
                                var dashboard_url ="{{ in_array($type_url ?? '', ['privatelandlord', 'agency', 'agent']) ? route('adminSubUser.dashboard') : route('dashboard') }}";
                                window.location.href = dashboard_url; //"{{ route('dashboard') }}"
                            } else {
                                $("form[name='login_form']").find('.serverside_error').remove();
                                $('.error_msg').html(response.message);
                                $('.error_msg').fadeIn();
                                setTimeout(function() {
                                    $('.error_msg').fadeOut();
                                }, 5000);
                            }
                        },
                        error: function(xhr, status, error) {
                            handleServerError('login_form', xhr.responseJSON.errors);
                        }
                    });
                }
            });
        })
    </script>
@endpush
@endsection
