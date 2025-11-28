@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Agency'))
@push('custom-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>

    <style>
        .iti {
            display: block;
        }

        .auto-password {
            float: right;
            cursor: pointer;
            margin-right: 10px;
            margin-top: -32px;
            color: #F30051;
        }
    </style>
@endpush
<div class="content-wrapper">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Agents</a></li>
                <li class="breadcrumb-item"><a href="{{ route('adminSubUser.agent.index') }}">list </a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <div class="row grid-margin">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title"></h4>
                    <form class="cmxform" id="add_agency" name="add_agency" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-6 form-group">
                                <label for="first_name">Name<span class="text-danger">*</span></label>
                                <input id="first_name" class="form-control" name="name" type="text"
                                    value="{{ $agent->name }}">
                            </div>

                            <div class="col-6 form-group">
                                <label for="email">Email* <span class="text-danger">*</span></label>
                                <input id="email" class="form-control" name="email" type="email"
                                    value="{{ $agent->email }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-1 form-group d-none">
                                <label for="dial_code">Dial Code* <span class="text-danger">*</span></label>
                                <input id="dial_code" class="form-control" name="dial_code" type="tel"
                                    placeholder="+27" value="{{ $agent->dial_code }}">
                            </div>
                            <div class="col-6 form-group">
                                <label for="contact">Contact Number* <span class="text-danger">*</span></label>
                                <input id="phone" name="contact" class="form-control" type="tel"
                                    value="{{ $agent->phone }}">
                            </div>
                            <div class="col-6 form-group">
                                <label> Enter your Password <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="password" id="password_edit_agency"
                                    placeholder="Enter Password">
                                <i class="auto-password fa fa-key" id="autoFillButton"></i>

                            </div>
                        </div>

                        <div class="alert succountry_codecess_msg success_msg"></div>
                        <div class="alert alert-danger error_msg"></div>

                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('custom-script')
    <script src="https://www.dukelearntoprogram.com/course1/common/js/image/SimpleImage.js"></script>
    <script type="text/javascript">
        function upload() {
            // $('.school_img').css('display', 'none')
            $('.imgcanvas').css('display', 'inline-block')
            var imgcanvas = document.getElementById("features_img_canv1");
            var fileinput = document.getElementById("features_img");
            var image = new SimpleImage(fileinput);
            image.drawTo(imgcanvas);
        }

        $(document).ready(function() {

            // Initialize intl-tel-input on the input field
            var input = document.querySelector("#phone");
            var iti = window.intlTelInput(input, {
                // Set options (e.g., auto search, allow dropdown)
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
                initialCountry: "auto",
                geoIpLookup: function(success, failure) {
                    $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                        // var countryCode = (resp && resp.country) ? resp.country : "us";
                        iti.setNumber("{{ '+' . $agent->dial_code . $agent->phone }}");
                    });
                },
                autoPlaceholder: "aggressive",
                separateDialCode: true,
                preferredCountries: ["us", "gb", "in"], // Add preferred countries
            });

            iti.setNumber("{{ '+' . $agent->dial_code . $agent->phone }}");


            $("form[name='add_agency']").validate({
                rules: {
                    name: {
                        required: true,
                    },

                    // password: {
                    //     required: true
                    // },

                    contact: {
                        required: true
                    },
                    email: {
                        required: true
                    }
                },
                submitHandler: function(form, e) {
                    e.preventDefault();
                    var fullPhoneNumber = iti.getSelectedCountryData();
                    $('#dial_code').val(fullPhoneNumber.dialCode);
                    if (!iti.isValidNumber()) {
                        $('.error_msg').html('Please enter a valid phone number');
                        $('.error_msg').fadeIn();
                        setTimeout(function() {
                            $('.error_msg').fadeOut();
                        }, 5000);
                        return false;
                    }

                    $.ajax({
                        url: "{{ route('adminSubUser.agent.update', $agent->id) }}",
                        type: "POST",
                        data: new FormData(form),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status == true) {
                                $("form[name='add_agency']").find('.serverside_error')
                                    .remove();
                                $('.success_msg').html(response.message);
                                $('.success_msg').fadeIn();
                                setTimeout(function() {
                                    $('.success_msg').fadeOut();
                                }, 5000);
                                $('#add_agency')[0].reset();
                                window.location.href =
                                    "{{ route('adminSubUser.agent.index') }}";
                            } else {
                                $("form[name='add_agency']").find('.serverside_error')
                                    .remove();
                                $('.error_msg').html(response.message);
                                $('.error_msg').fadeIn();
                                setTimeout(function() {
                                    $('.error_msg').fadeOut();
                                }, 5000);
                            }
                        },
                        error: function(xhr, status, error) {
                            handleServerError('add_agency', xhr.responseJSON.errors);
                        }
                    });
                }
            });

            $('#autoFillButton').on('click', function() {
                // Automatically generate and fill a password
                var autoPassword = generatePassword();
                $('#password_edit_agency').val(autoPassword); // Set the password input field value
            });

        });
        // Function to generate a random password
        function generatePassword() {
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$';
            var passwordLength = 12;
            var password = '';
            for (var i = 0; i < passwordLength; i++) {
                var randomIndex = Math.floor(Math.random() * characters.length);
                password += characters[randomIndex];
            }
            return password;
        }
    </script>
@endpush
@endsection
