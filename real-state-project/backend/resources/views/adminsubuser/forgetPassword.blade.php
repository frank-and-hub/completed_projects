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
</style>
<div class="container-fluid page-body-wrapper full-page-wrapper">
    <div class="content-wrapper d-flex align-items-center auth login_background">
        <div class="row w-100">
            <div class="col-lg-4 mx-auto">
                <div class="auth-form-light text-left p-5">
                    <div class="text-center">
                        <img src="{{ asset('assets/admin/images/logo.png') }}" alt="logo" style="width:100%;">
                    </div>
                    <!-- <h4 class="mt-3">Hello</h4> -->
                    <h6 class="font-weight-light">Forgot Password</h6>
                    <form class="pt-4" id="password_form" name="password_form">
                        @csrf
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control form-control-lg"
                                id="exampleInputEmail1" placeholder="Enter email">
                        </div>
                        <div class="form-group otp_div" style="display: none;">
                            <label>OTP</label>
                            <input type="text" name="otp" class="form-control form-control-lg"
                                id="otp" placeholder="Enter OTP" maxlength="4" pattern="^\d{4}$">
                        </div>
                        <div class="mt-3">
                            <div class="success_msg mt-3"></div>
                            <div class="error_msg text-danger mt-3"></div>
                            <button class="btn btn-block password-btn btn-lg font-weight-medium auth-form-btn">Submit</button>
                        </div>
                    </form>

                    <form class="pt-4" id="forgotpassword_form" name="forgotpassword_form" action="{{route('reset-password', $type_url)}}" method="post" style="display:none">
                        @csrf
                        <input type="text" name="email" id="email_forgot_password" hidden>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" id="password1" name="password" class="form-control form-control-lg"
                                placeholder="Enter password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" id="c_password" name="c_password" class="form-control form-control-lg"
                                id="" placeholder="Enter confirm password" required>
                        </div>
                        <div class="mt-3">
                            <div class="success_msg mt-3"></div>
                            <div class="password-error text-danger mt-3"></div>
                            <button class="btn btn-block forgotpassword-btn btn-lg font-weight-medium auth-form-btn" style="display: none;" id="forgotpassword_form_button">Submit</button>
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
        var url = "{{ route('forgot-password',$type_url) }}";

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
                            $("form[name='password_form']").find('.serverside_error').remove();
                            $('.success_msg').html(response.message);
                            $('.success_msg').fadeIn();
                            setTimeout(function() {
                                $('.success_msg').fadeOut();
                            }, 5000);

                            $('#otp').show();
                            $('.otp_div').css('display', 'block');

                            if(response.data && response.data.verification){
                                $('#email_forgot_password').val(response.data.verification);
                                $('#password_form').hide();
                                $('#forgotpassword_form').show();

                            }

                        } else {
                            $("form[name='password_form']").find('.serverside_error').remove();
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

        $('#c_password').on('input', function() {
            const password = $('#password1').val();
            const confirmPassword = $(this).val();
            const errorDiv = $('.password-error');

            if (confirmPassword !== password) {
                $('#forgotpassword_form_button').hide();

                errorDiv.show(); // Show error message
                errorDiv.text('Passwords do not match.');

            } else if(password.length < 8){

                errorDiv.show(); // Show error message
                errorDiv.text('Password must be at least 8 characters long.');

            }else {

                $('#forgotpassword_form_button').show();
                errorDiv.hide(); // Hide error message
            }
        });

        $('#password1').on('input', function() {
            const confirmPassword = $('#c_password').val();
            const password = $(this).val();
            const errorDiv = $('.password-error');

            if(confirmPassword){
                if (confirmPassword !== password) {
                    $('#forgotpassword_form_button').hide();
                    errorDiv.show(); // Show error message
                    errorDiv.text('Passwords do not match.');

                } else if(password.length < 8){

                    errorDiv.show(); // Show error message
                    errorDiv.text('Password must be at least 8 characters long.');

                }else {

                    $('#forgotpassword_form_button').show();
                    errorDiv.hide(); // Hide error message
                }
            }

        });
    });
</script>
@endpush
@endsection
