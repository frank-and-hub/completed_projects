@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Need | User'))
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
                <li class="breadcrumb-item"><a href="#">{{ucwords($title)}}</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{ route('property-need-api-user.index') }}">List</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    @if (!isset($api))
                        Add
                    @else
                        View
                    @endif
                </li>
            </ol>
        </nav>
    </div>
    <div class="row grid-margin">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        @if (!isset($api))
                            Add
                        @else
                            View
                        @endif API
                    </h4>
                    <form class="cmxform" id="add_api_user" name="add_api_user" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="form-group col-6">
                                <label for="agencies">Agency @if (!isset($api))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <select id="agency" class="form-control select2" data-coreui-search="true"
                                    data-live-search="true" name="agency">
                                    <option value="" disabled>Select Agency</option>
                                    @forelse ($agencies as $key => $val)
                                        <option value="{{ $key }}"
                                            {{ isset($api) ? ($api->agency_id == $key ? 'selected' : '') : '' }}>
                                            {{ $val }}</option>
                                    @empty
                                        <option value="">No agency found!</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label for="country">Country @if(!isset($api))<span class="text-danger">*</span>@endif</label>
                                <select id="country" class="form-control select2 form-multi-select"
                                    data-coreui-search="true" data-live-search="true" name="country">
                                    <option value="" data-phonecode="" selected disabled>Select country</option>
                                    @forelse ($countries as $country)
                                        <option data-phonecode="{{ $country->phonecode }}"
                                            {{ isset($api) ? ($api?->country == $country->name ? 'selected' : '') : '' }}
                                            value="{{ $country->name }}">
                                            {{ ucwords($country->name) }}</option>
                                    @empty
                                        <option value="" data-phonecode="" disabled>No countries Found!</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="col-6 form-group">
                                <label for="first_name">User Name @if(!isset($api))<span class="text-danger">*</span>@endif</label>
                                <input id="first_name" class="form-control" name="name" type="text"
                                    value="{{ isset($api) ? $api?->name : '' }}">
                            </div>
                            {{--
                            <div class="col-6 form-group">
                                <label for="email">Email @if(!isset($api))<span class="text-danger">*</span>@endif</label>
                                <input id="email" class="form-control" name="email" type="email"
                                    value="{{ isset($api) ? $api?->email : '' }}">
                            </div>

                            <div class="col-1 form-group d-none">
                                <label for="dial_code">Dial Code <span class="text-danger">*</span></label>
                                <input id="dial_code" class="form-control" name="dial_code" type="tel"
                                    value="{{ isset($api) ? $api?->dial_code : '' }}" placeholder="+27">
                            </div>
                            <div class="col-6 form-group">
                                <label for="contact">Contact Number @if(!isset($api))<span class="text-danger">*</span>@endif</label>
                                <input id="phone" name="contact" class="form-control"
                                    type=@if (!isset($api)) "tel" @else "text" @endif
                                    value="{{ isset($api) ? $api?->contact : '' }}">
                            </div>
                            --}}
                            <div class="col-6 form-group">
                                <label> Enter your Password @if(!isset($api))<span class="text-danger">*</span>@endif</label>
                                <input type="text" class="form-control" name="password" id="password_edit_agency"
                                    placeholder="Enter Password" value="{{ isset($api) ? $api?->password : '' }}">
                                @if (!isset($api))
                                <i class="auto-password fa_dash fa fa-key" data-toggle="tooltip" data-original-title="Generate Password"
                                id="autoFillButton"></i>
                                @endif
                            </div>
                        </div>
                        <div class="alert alert-success  success_msg"></div>
                        <div class="alert alert-danger error_msg"></div>
                        @if (!isset($api))
                            <button type="submit" class="btn btn-primary mr-2">Create</button>
                        @endif
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
            // var input = document.querySelector("#phone");
            // var iti = window.intlTelInput(input, {
            //     utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
            //     initialCountry: "auto",
            //     geoIpLookup: function(success, failure) {
            //         $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            //             var countryCode = (resp && resp.country) ? resp.country : "us";
            //             success(countryCode);
            //         });
            //     },
            //     autoPlaceholder: "aggressive",
            //     separateDialCode: true,
            //     preferredCountries: ["us", "gb", "in"],
            // });

            $("form[name='add_api_user']").validate({
                rules: {
                    name: {
                        required: true,
                    },
                    agency: {
                        required: true,
                    },
                    password: {
                        required: true
                    },
                    // contact: {
                    //     required: false
                    // },
                    // email: {
                    //     required: false
                    // },
                    country: {
                        required: true
                    }
                },
                submitHandler: function(form, e) {
                    e.preventDefault();
                    // var fullPhoneNumber = iti.getSelectedCountryData();
                    // $('#dial_code').val(fullPhoneNumber.dialCode);
                    // if (!iti.isValidNumber()) {
                    //     $('.error_msg').html('Please enter a valid phone number');
                    //     $('.error_msg').fadeIn();
                    //     setTimeout(function() {
                    //         $('.error_msg').fadeOut();
                    //     }, 5000);
                    //     return false;
                    // }

                    $.ajax({
                        url: "{{ route('property-need-api-user.store') }}",
                        type: "POST",
                        data: new FormData(form),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status == true) {
                                $("form[name='add_api_user']").find('.error_msg').remove();
                                $('.success_msg').html(response.message);
                                $('.success_msg').fadeIn();
                                setTimeout(function() {
                                    $('.success_msg').fadeOut();
                                }, 5000);
                                $('#add_api_user')[0].reset();
                                window.location.href =
                                    "{{ route('property-need-api-user.index') }}"
                            } else {
                                $("form[name='add_api_user']").find('.error_msg').remove();
                                $('.error_msg').html(response.message);
                                $('.error_msg').fadeIn();
                                setTimeout(function() {
                                    $('.error_msg').fadeOut();
                                }, 5000);
                            }
                            window.location.href =
                                "{{ route('property-need-api-user.index') }}"
                        },
                        error: function(xhr, status, error) {
                            handleServerError('add_api_user', xhr.responseJSON.errors);
                        }
                    });
                }
            });
            $('#autoFillButton').on('click', function() {
                var autoPassword = generateString(12);
                $('#password_edit_agency').val(autoPassword);
            });

        });


        function generateString(passwordLength) {
                var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$';
                var password = '';
                for (var i = 0; i < passwordLength; i++) {
                    var randomIndex = Math.floor(Math.random() * characters.length);
                    password += characters[randomIndex];
                }
                return password;
            }

        $('#country').select2();
        $('#agency').select2();
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
        @if (isset($api))
            disableAllInputsInForm('add_api_user');
        @endif
    </script>
@endpush
@endsection
