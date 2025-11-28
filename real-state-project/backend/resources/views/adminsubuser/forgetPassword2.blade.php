@extends('layout.base')
@section('master')
@section('title', __('PocketProperty | Forgot Password'))

<style>
    body,
    html {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .login-background {
        background: #fff;
        font-family: 'Poppins', sans-serif;
    }

    .content-wrapper {
        height: 100vh;
        overflow-y: auto;
        /* Allows scrolling for smaller screens */
    }

    .property-content {
        position: absolute;
        top: 60px;
        left: 60px;
        color: white;
        z-index: 2;
    }

    .property-content h1 {
        font-size: 2.5rem;
        margin: 0;
    }

    .property-content h2 {
        font-size: 1.8rem;
        margin-top: 10px;
    }

    .login-half-bg {
        position: relative;
        overflow: hidden;
        height: 100vh;
    }

    .login-half-bg img {
        /* object-fit: cover; */
        height: 100%;
        width: 100%;
    }

    /* Responsive Styles */
    @media only screen and (max-width: 991px) {
        .left-side {
            display: none !important;
            /* Hides image and content on smaller devices */
        }

        .property-content {
            left: 20px;
            text-align: left;
        }

        .property-content h1 {
            font-size: 2rem;
        }

        .property-content h2 {
            font-size: 1.5rem;
        }
    }

    @media only screen and (max-width: 768px) {
        .property-content h1 {
            font-size: 1.8rem;
        }

        .property-content h2 {
            font-size: 1.2rem;
        }
    }

    @media only screen and (max-width: 576px) {
        .property-content {
            left: 10px;
        }

        .property-content h1 {
            font-size: 1.5rem;
        }

        .property-content h2 {
            font-size: 1rem;
        }
    }

    @media only screen and (max-width: 375px) {
        .property-content h1 {
            font-size: 1.2rem;
        }

        .property-content h2 {
            font-size: 0.9rem;
        }
    }

    @media only screen and (max-width: 320px) {
        .property-content h1 {
            font-size: 1rem;
        }

        .property-content h2 {
            font-size: 0.8rem;
        }
    }
</style>
<div class="container-fluid page-body-wrapper full-page-wrapper">
    <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg" style="background-color:white">
        <div class="row flex-grow login-background">
            <div class="col-lg-6 login-half-bg d-flex flex-row position-relative left-side">
                <img src="{{ asset('assets/admin/images/login.png') }}" alt="Building Image">
                <div class="property-content">
                    <h1>List Your Property,</h1>
                    <h2>Reach Your Perfect Tenant.</h2>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center justify-content-center right-side">
                <div class="auth-form-transparent text-left p-3">
                    <div class="brand-logo text-center">
                        <img src="{{ asset('assets/admin/images/logo.png') }}" alt="logo" style="width:100%;">
                    </div>
                    <h4>Welcome back!</h4>
                    <h6 class="font-weight-light">Forgot Password!</h6>
                    <form class="pt-4" id="password_form" name="password_form">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend bg-transparent">
                                    <span class="input-group-text bg-transparent border-right-0">
                                        <i class="fa fa-user fa_dash"></i>
                                    </span>
                                </div>
                                <input type="email" name="email" class="form-control form-control-lg border-left-0"
                                    id="email" placeholder="Email">
                            </div>
                        </div>
                        <div class="form-group otp_div" style="display: none;">
                            <label for="otp">OTP</label>
                            <div class="input-group">
                                <div class="input-group-prepend bg-transparent">
                                    <span class="input-group-text bg-transparent border-right-0">
                                        <i class="fa fa-lock fa_dash"></i>
                                    </span>
                                </div>
                                <input type="text" name="otp" class="form-control form-control-lg border-left-0"
                                    id="otp" placeholder="Enter OTP" maxlength="4" pattern="^\d{4}$">
                            </div>
                        </div>
                        <p style="color:red" class="error_msg">Invalid Login</p>
                        <p style="color:green" class="success_msg">Login Successfully</p>

                        <div class="my-2 d-flex justify-content-between align-items-center">
                        </div>
                        <div class="my-3">
                            <button
                                class="btn btn-block login-btn btn-lg font-weight-medium auth-form-btn">Submit</button>
                        </div>
                    </form>



                    <form class="pt-4" id="forgotpassword_form" name="forgotpassword_form"
                        action="{{ route('reset-password', $type_url) }}" method="post" style="display: none;">
                        @csrf

                        <input type="text" name="otp" id="otp_reset" value="1234" hidden>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend bg-transparent">
                                    <span class="input-group-text bg-transparent border-right-0">
                                        <i class="fa fa-user fa_dash"></i>
                                    </span>
                                </div>
                                <input type="email" name="email" class="form-control form-control-lg border-left-0"
                                    id="email_forgot_password" placeholder="Email" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend bg-transparent">
                                    <span class="input-group-text bg-transparent border-right-0">
                                        <i class="fa fa-user text-primary"></i>
                                    </span>
                                </div>
                                <input type="password" id="password1" name="password"
                                    class="form-control form-control-lg border-left-0" placeholder="Enter Password"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="c_password">Confirm Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend bg-transparent">
                                    <span class="input-group-text bg-transparent border-right-0">
                                        <i class="fa fa-user text-primary"></i>
                                    </span>
                                </div>
                                <input type="password" id="c_password" name="c_password"
                                    class="form-control form-control-lg border-left-0"
                                    placeholder="Enter confirm password" required>
                            </div>
                        </div>
                        <p style="color:red" class="error_msg">Invalid Login</p>
                        <p style="color:green" class="success_msg">Login Successfully</p>

                        <div class="my-2 d-flex justify-content-between align-items-center">
                        </div>
                        <div class="my-3">
                            <button class="btn btn-block login-btn btn-lg font-weight-medium auth-form-btn"
                                style="display: none;" id="forgotpassword_form_button">Submit</button>
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
            var url = "{{ route('forgot-password', $type_url) }}";

            var resrt_password = "{{ route('reset-password', $type_url) }}";

            rule = {
                email: {
                    required: true,
                    email_rule: true,
                },
                otp: {
                    required: true,
                    // email_rule: true,
                },
            };

            message = {
                email: {
                    required: 'Please enter your email',
                    email_rule: "Please Enter Valid Format",
                },
                otp: {
                    required: 'Please enter otp',
                    // email_rule: "Please Enter Valid Format",
                }
            };


            $('#password_form').validate({
                rules: rule,
                messages: message,
                submitHandler: function(form) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.status == true) {
                                $("form[name='password_form']").find('.serverside_error')
                                    .remove();
                                $('.success_msg').html(response.message);
                                $('.success_msg').fadeIn();
                                setTimeout(function() {
                                    $('.success_msg').fadeOut();
                                }, 5000);

                                $('#otp').show();
                                $('.otp_div').css('display', 'block');

                                if (response.data && response.data.verification) {
                                    $('#email_forgot_password').val(response.data
                                        .verification);
                                    $('#otp_reset').val(response.data.otp);
                                    $('#password_form').hide();
                                    $('#forgotpassword_form').show();


                                }

                            } else {
                                $("form[name='password_form']").find('.serverside_error')
                                    .remove();
                                $('.error_msg').html(response.message);
                                $('.error_msg').fadeIn();
                                setTimeout(function() {
                                    $('.error_msg').fadeOut();
                                }, 5000);
                            }
                        },
                        error: function(xhr, status, error) {
                            handleServerError('password_form', xhr.responseJSON.errors);
                        }
                    });
                }
            });

            $('#forgotpassword_form').validate({
                rules: rule,
                messages: message,
                submitHandler: function(form) {
                    $('.error_msg').html("");
                    $.ajax({
                        url: resrt_password,
                        type: "POST",
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.status == true) {
                                $("form[name='forgotpassword_form']").find('.error_msg')
                                    .remove();
                                $('.success_msg').html(response.message);
                                $('.success_msg').fadeIn();
                                setTimeout(function() {
                                    $('.success_msg').fadeOut();
                                }, 5000);

                                setTimeout(function() {
                                    location.replace(
                                        "{{ route('sub_loginprocess', $type_url) }}"
                                    );
                                    // window.location.reload(1);
                                }, 1000);

                            } else if (response.status == 2) {
                                $("form[name='password_form']").find('.error_msg').remove();

                                $('.error_msg').html(response.message);
                                $('.error_msg').fadeIn();

                                setTimeout(function() {
                                    $('.error_msg').fadeOut();
                                }, 5000);

                                // location.reload();

                            } else {
                                $("form[name='password_form']").find('.error_msg').remove();
                                $('.error_msg').html(response.message);
                                $('.error_msg').fadeIn();
                                setTimeout(function() {
                                    $('.error_msg').fadeOut();
                                }, 5000);
                            }
                        },
                        error: function(xhr, status, error) {
                            handleServerError('password_form', xhr.responseJSON.errors);
                        }
                    });
                }
            });
        })
    </script>


    <script type="text/javascript">
        $(document).ready(function() {
            $('#c_password').on('input', function() {
                const password = $('#password1').val();
                const confirmPassword = $(this).val();
                const errorDiv = $('.error_msg');

                if (confirmPassword !== password) {
                    $('#forgotpassword_form_button').hide();

                    errorDiv.show(); // Show error message
                    errorDiv.text('Passwords do not match.');

                } else if (password.length < 8) {

                    errorDiv.show(); // Show error message
                    errorDiv.text('Password must be at least 8 characters long.');

                } else {

                    $('#forgotpassword_form_button').show();
                    errorDiv.hide(); // Hide error message
                }
            });

            $('#password1').on('input', function() {
                const confirmPassword = $('#c_password').val();
                const password = $(this).val();
                const errorDiv = $('.password-error');

                if (confirmPassword) {
                    if (confirmPassword !== password) {
                        $('#forgotpassword_form_button').hide();
                        errorDiv.show(); // Show error message
                        errorDiv.text('Passwords do not match.');

                    } else if (password.length < 8) {

                        errorDiv.show(); // Show error message
                        errorDiv.text('Password must be at least 8 characters long.');

                    } else {

                        $('#forgotpassword_form_button').show();
                        errorDiv.hide(); // Hide error message
                    }
                }

            });
        });
    </script>
@endpush
@endsection
