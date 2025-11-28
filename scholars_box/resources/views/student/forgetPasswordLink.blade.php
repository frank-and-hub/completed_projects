@extends('student.layout.app')
@section('title', 'Forgot password')

@section('content')
<main class="login-form cstm-reset-pass-page">
    <div class="cotainer">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Reset Password</div>
                    <div class="card-body">

                        <form id="resetPasswordForm" action="{{ route('reset.password.post') }}" method="POST">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            {{-- Add the email field --}}
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">E-Mail Address</label>
                                <div class="col-md-6">
                                    <input type="email" id="email" class="form-control" name="email" required autofocus>
                                    @if ($errors->has('email'))
                                    <span class="text-danger">{{ $errors->first('email') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                                <div class="col-md-6">
                                    <input type="password" id="password" class="form-control" name="password" required autofocus>
                                    @if ($errors->has('password'))
                                    <span class="text-danger">{{ $errors->first('password') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Confirm Password</label>
                                <div class="col-md-6">
                                    <input type="password" id="password-confirm" class="form-control" name="password_confirmation" required autofocus>
                                    @if ($errors->has('password_confirmation'))
                                    <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6 offset-md-4">
                                <button type="button" id="resetPasswordBtn" class="btn btn-primary">
                                    Reset Password
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function () {
        $('#resetPasswordBtn').on('click', function () {
            // Get password and confirm password values
            var password = $('#password').val();
            var confirmPassword = $('#password-confirm').val();

            // Perform validation
            if (password !== confirmPassword) {
                toastr.error('Password and Confirm Password must match.');
                return;
            }

            // Continue with form submission if validation passes
            var formData = new FormData($('#resetPasswordForm')[0]);

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: '{{ route('reset.password.post') }}',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    // console.log(response);
                    if (response.value == 0) {
                        toastr.error(response.message);
                    } else {
                        toastr.success(response.message);
                        $('#resetPasswordForm')[0].reset();
                    }
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
