@extends('layout.base')
@section('master')
@section('title', __('PocketProperty | Login'))
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

    /* .content-wrapper {
        height: 100vh;
        overflow-y: auto;
        Allows scrolling for smaller screens
    } */

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
        /* height: 100vh; */
    }

    .login-half-bg img {
        /* object-fit: cover; */
        height: 100%;
        width: 100%;
        /* max-height: 700px; */
    }

    .toggle-password {
        top: 48px;
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

<div class="full-page-wrapper">
    <div class="d-flex align-items-stretch auth auth-img-bg">
        <div class="row flex-grow login-background">
            <!-- Left Side (Image and Text) -->
            <div class="col-lg-6 login-half-bg d-flex flex-row position-relative left-side">
                <img src="{{ asset('assets/admin/images/login.png') }}" alt="Building Image">
                <div class="property-content">
                    <h1>List Your Property,</h1>
                    <h2>Reach Your Perfect Tenant.</h2>
                </div>
            </div>
            <!-- Right Side (Form) -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center right-side">
                <div class="auth-form-transparent text-left p-3">
                    <div class="brand-logo text-center">
                        <img src="{{ asset('assets/admin/images/logo.png') }}" alt="logo" style="width:100%;">
                    </div>

                    <h4 class="text-center">Welcome back!</h4>
                    <h6 class="font-weight-light text-center">Happy to see you again!</h6>


                    <form class="pt-4" id="login_form" name="login_form">
                        <div class="form-group">
                            <h5 class="text-center">
                                {{ $type_url === 'privatelandlord' ? 'Landlord' : ucwords($type_url) }} Login</h5>
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
                        {{-- <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control form-control-lg"
                                id="exampleInputPassword1" placeholder="Password">
                            <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                        </div> --}}
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group position-relative">
                                <div class="input-group-prepend bg-transparent">
                                    <span class="input-group-text bg-transparent border-right-0">
                                        <i class="fa fa-lock fa_dash"></i>
                                    </span>
                                </div>
                                <input type="password" name="password"
                                    class="form-control form-control-lg border-left-0" id="Password"
                                    placeholder="Password">
                                <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                            </div>
                        </div>
                        <p style="color:red" class="error_msg">Invalid Login</p>
                        <p style="color:green" class="success_msg">Login Successfully</p>

                        <div class="my-2 d-flex justify-content-between text-right">
                            <a href="{{ route('forgot-password', $type_url) }}" class="auth-link text-black">Forgot
                                password?</a>
                        </div>
                        <div class="my-3">
                            <button class="btn btn-block login-btn btn-lg font-weight-medium auth-form-btn">SIGN
                                IN</button>
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
            var url =
                "{{ in_array($type_url ?? '', ['privatelandlord', 'agency', 'agent']) ? route('sub_loginprocess', $type_url) : route('loginprocess') }}"

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
                                $("form[name='login_form']").find('.serverside_error')
                                    .remove();
                                $('.success_msg').html(response.message);
                                $('.success_msg').fadeIn();
                                setTimeout(function() {
                                    $('.success_msg').fadeOut();
                                }, 5000);
                                var dashboard_url =
                                    "{{ in_array($type_url ?? '', ['privatelandlord', 'agency', 'agent']) ? route('adminSubUser.dashboard') : route('dashboard') }}";
                                window.location.href =
                                    dashboard_url; //"{{ route('dashboard') }}"
                            } else {
                                $("form[name='login_form']").find('.serverside_error')
                                    .remove();
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
