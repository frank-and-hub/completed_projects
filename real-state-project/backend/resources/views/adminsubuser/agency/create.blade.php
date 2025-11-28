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
        <h3 class="page-title">
            Add Agency
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Agencies</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add</li>
            </ol>
        </nav>
    </div>
    <form class="cmxform" id="add_agency" name="add_agency" enctype="multipart/form-data">
        @csrf
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Basic Information</h4>

                        <div class="form-group">
                            <label for="business">Business Name <span class="text-danger">*</span></label>
                            <input id="business" class="form-control" name="business_name" type="text">
                        </div>
                        <div class="row">
                            <div class="col-6 form-group">
                                <label for="first_name">First Name<span class="text-danger">*</span></label>
                                <input id="first_name" class="form-control" name="first_name" type="text">
                            </div>

                            <div class="col-6  form-group">
                                <label for="last_name">Last Name<span class="text-danger">*</span></label>
                                <input id="last_name" class="form-control" name="last_name" type="text" required>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-1 form-group d-none">
                                <label for="dial_code">Dial Code* <span class="text-danger">*</span></label>
                                <input id="dial_code" class="form-control" name="dial_code" type="tel"
                                    placeholder="+27">
                            </div>
                            <div class="col-4 form-group">
                                <label for="contact">Contact Number <span class="text-danger">*</span></label>
                                <input id="phone" name="contact" class="form-control" type="tel">
                            </div>
                            <div class="col-4 form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input id="email" class="form-control" name="email" type="email">
                            </div>
                            <div class="col-4 form-group">
                                <label> Enter your Password <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="password" id="password_edit_agency"
                                    placeholder="Enter Password">
                                <i class="auto-password fa fa-key" id="autoFillButton"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label> Message <span class="text-danger">*</span></label>
                            <textarea name="description" id="" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-body">
                        <h4 class="card-title">Business Information</h4>
                        <div class="row">
                            <div class="col-4 form-group">
                                <label for="owner_id">Director / Owner ID number <span
                                        class="text-danger">*</span></label>
                                <input id="owner_id" class="form-control" name="owner_id" type="text">
                            </div>
                            <div class="col-4 form-group">
                                <label for="registration_number">Company registration number <span
                                        class="text-danger">*</span></label>
                                <input id="registration_number" class="form-control" name="registration_number"
                                    type="text">
                            </div>
                            <div class="col-4 form-group">
                                <label for="vat_number">Company VAT number <span class="text-danger">*</span></label>
                                <input id="vat_number" class="form-control" name="vat_number" type="text">
                            </div>
                            <div class="form-group col-6">
                                <label for="message">Type of Business <span class="text-danger">*</span></label>
                                <select name="business_type" id="business_types" class="form-control">
                                    <option disabled selected>Please select</option>
                                    @foreach ($business_types as $business_type)
                                        <option value="{{ $business_type }}">{{ ucwords($business_type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-5">
                    <div class="card-body">
                        <h4 class="card-title">Address</h4>
                        <!-- <label for="address">Address* <span class="text-danger">*</span></label> -->
                        <div class="row">
                            <div class="form-group col-4">
                                <label for="country">Country <span class="text-danger">*</span></label>
                                <select class="form-control countries-select2" id="country"
                                    name="country"></select>
                            </div>
                            <div class="form-group col-4">
                                <label for="province">State / Province <span class="text-danger">*</span></label>
                                <select class="form-control state-select2" id="state" name="state"
                                    disabled></select>
                            </div>
                            <div class="form-group col-4">
                                <label for="city">City <span class="text-danger">*</span></label>
                                <select class="form-control city-select2" id="city" name="city"
                                    disabled></select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-12">
                                <label for="street_address">Street Address <span class="text-danger">*</span></label>
                                <input id="street_address" class="form-control" name="street_address"
                                    type="text">
                            </div>
                            <div class="form-group col-12">
                                <label for="street_address_2">Street Address Line 2</label>
                                <input id="street_address_2" class="form-control" name="street_address_2"
                                    type="text">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-4">
                                <label for="postal">Postal / Zip Code <span class="text-danger">*</span></label>
                                <input id="postal" class="form-control" name="postal" type="text">
                            </div>
                        </div>

                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
                        var countryCode = (resp && resp.country) ? resp.country : "us";
                        success(countryCode);
                    });
                },
                autoPlaceholder: "aggressive",
                separateDialCode: true,
                preferredCountries: ["us", "gb", "in"], // Add preferred countries
            });


            $("form[name='add_agency']").validate({
                rules: {
                    first_name: {
                        required: true,
                    },
                    last_name: {
                        required: true
                    },
                    password: {
                        required: true
                    },
                    business_name: {
                        required: true
                    },
                    contact: {
                        required: true
                    },
                    email: {
                        required: true
                    },
                    owner_id: {
                        required: true
                    },
                    registration_number: {
                        required: true
                    },
                    vat_number: {
                        required: true
                    },
                    street_address: {
                        required: true
                    },
                    // street_address_2: {
                    //     required: true
                    // },
                    city: {
                        required: true
                    },
                    province: {
                        required: true
                    },

                    postal: {
                        required: true
                    },
                    country: {
                        required: true
                    },
                    business_type: {
                        required: true,
                        maxWords: 500
                    },
                    description: {
                        required: true
                    }
                },
                submitHandler: function(form, e) {
                    e.preventDefault();
                    var fullPhoneNumber = iti.getSelectedCountryData();
                    $('#dial_code').val(fullPhoneNumber.dialCode);
                    if (!iti.isValidNumber()) {
                        alert("Please enter a valid phone number");
                        // e.preventDefault();  // Stop form submission if the phone number is invalid
                        return false;
                    }

                    $.ajax({
                        url: "{{ route('admin_user.agency.store') }}",
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
                                    "{{ route('admin_user.role_type_admin_list', 'agency') }}"
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
