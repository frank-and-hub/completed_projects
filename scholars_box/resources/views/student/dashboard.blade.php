@extends('student.layout.app')
@section('title', 'Scholarship - Student Dashboard')
@section('content')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


    <?php
    
    $state = \App\Models\CountryData\State::where('status', 'active')->orderBy('name', 'asc')->get();
    
    $district = \App\Models\CountryData\District::whereStatus('active')->orderBy('name', 'asc')->get();
    ?>
    <script type="text/javascript">
        document.getElementById('permanent_state').addEventListener('change', function() {
            const permanent_state = this.options[this.selectedIndex].getAttribute('data-val');
            document.getElementById('permanent_district').selectedIndex = '';
            document.querySelectorAll('#permanent_district option').forEach(function(option) {
                if (option.getAttribute('data-state') === permanent_state) {
                    option.disabled = false;
                    option.style.visibility = 'visible';
                    option.style.display = 'block';
                } else {
                    option.disabled = true;
                    option.style.visibility = 'hidden';
                    option.style.display = 'none';
                }
            });
        });
        document.getElementById('current_state').addEventListener('change', function() {
            const current_state = this.options[this.selectedIndex].getAttribute('data-val');
            document.getElementById('current_district').selectedIndex = '';
            document.querySelectorAll('#current_district option').forEach(function(option) {
                if (option.getAttribute('data-state') === current_state) {
                    option.disabled = false;
                    option.style.visibility = 'visible';
                    option.style.display = 'block';
                } else {
                    option.disabled = true;
                    option.style.visibility = 'hidden';
                    option.style.display = 'none';
                }
            });
        });
    </script>
    {{-- <script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=6587b475bbc2e400122e679e&product=inline-share-buttons&source=platform" async="async"></script> --}}
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/cropper/4.1.0/cropper.min.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/filepond/dist/filepond.css">
    <link rel="stylesheet" type="text/css"
        href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css">

    <style>
        /* image uploader */
        .photo-crop-container {
            position: relative;
        }

        .cropper-view-box {
            border-radius: 50%;
        }

        .btnDiv {
            margin-bottom: 10px;
        }

        .btnDiv .btn-primary {
            background-color: #1b91c9;
            border-color: #1b91c9;
        }

        .photo-crop-container:before {
            content: '';
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            position: absolute;
            height: 0px;
            width: 100%;
            z-index: 9;
            background-color: #f5f5f5;
            vertical-align: middle;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            opacity: 0;
            top: 80px;
            -webkit-transition: all linear 0.3s 0.1s;
            -o-transition: all linear 0.3s 0.1s;
            transition: all linear 0.3s 0.1s;
        }

        .photo-crop-container.show-loader:before {
            content: 'Cropping...';
            opacity: 1;
            height: calc(100% - 80px);
        }

        .photo-crop-container {
            position: relative;
            overflow: hidden;
        }

        .photo-crop-container img {
            display: block;
            max-width: 100%;
            width: 100%;
            height: 100%;
        }

        .photo-crop-container .crop-preview-cont {
            overflow: hidden;
            -webkit-transition: all linear 0.2s;
            -o-transition: all linear 0.2s;
            transition: all linear 0.2s;
            -webkit-transform: translateY(0%);
            -ms-transform: translateY(0%);
            transform: translateY(0%);
            opacity: 1;
            height: 100%;
            text-align: center;
            display: none;
        }

        .photo-crop-container .crop-preview-cont #crop_img {

            z-index: 1;
            color: #fff;
            text-align: center;
            margin-top: 10px;
            cursor: pointer;
            background-color: #1b91c9;
            padding: 10px;
        }

        .photo-crop-container.show-result .crop-preview-cont .img_container {
            max-width: 400px;
        }

        .photo-crop-container.show-result .crop-preview-cont {
            -webkit-transform: translateY(10%);
            -ms-transform: translateY(10%);
            transform: translateY(10%);
            opacity: 0;
            height: 0;
        }

        .photo-crop-container #user_cropped_img {
            -webkit-transition: all linear 0.2s 2s;
            -o-transition: all linear 0.2s 2s;
            transition: all linear 0.2s 2s;
            -webkit-transform: translateY(-10%);
            -ms-transform: translateY(-10%);
            transform: translateY(-10%);
            opacity: 0;
            position: absolute;
        }

        .photo-crop-container.show-result #user_cropped_img {
            -webkit-transform: translateY(0%);
            -ms-transform: translateY(0%);
            transform: translateY(0%);
            opacity: 1;
            position: relative;
        }

        .photo-crop-container #user_cropped_img img {
            max-width: 300px;
        }

        .photo-crop-container #user_cropped_img img {
            -webkit-transition: all cubic-bezier(0.22, 0.61, 0.36, 1) 0.2s 2.3s;
            -o-transition: all cubic-bezier(0.22, 0.61, 0.36, 1) 0.2s 2.3s;
            transition: all cubic-bezier(0.22, 0.61, 0.36, 1) 0.2s 2.3s;
            -webkit-transform: translateY(-10%);
            -ms-transform: translateY(-10%);
            transform: translateY(-10%);
            opacity: 0;
            scroll-behavior: smooth;
        }

        .photo-crop-container.show-result #user_cropped_img img {
            -webkit-transform: translateY(0%);
            -ms-transform: translateY(0%);
            transform: translateY(0%);
            opacity: 1;
            border-radius: 50%;
        }

        .cropper-face {
            border-radius: 50%;
        }

        @media only screen and (max-width: 575px) {
            .photo-crop-container #user_cropped_img img {
                max-width: 100%;
            }
        }


        /* Suggested Scholarship CSS */
        .cstm-suggested-row-css {
            margin-top: 30px;
        }

        .suggested-schols-main {
            width: 100%;
        }

        .sugg-schol-card {
            padding: 10px;
            box-shadow: 0px 0px 5px 1px #c0c0c0 !important;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .sugg-schol-card .row {
            align-items: center;
        }

        .sugg-schol-image img {
            width: 100%;
            height: 100px;
            object-fit: cover;
        }

        .sugg-schol-card-inner h4 {
            font-size: 14px;
            font-weight: 600;
            color: #1a91c9;
        }

        .sugg-schol-lastdate .icon {
            color: #1a91c9;
            font-size: 6px;
        }

        .sugg-schol-lastdate .text {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            color: #777777;
            font-family: 'Roboto';
        }

        .sugg-schol-card .row .col-md-4 {
            padding-right: 0px;
        }

        .sugg-schol-lastdate .date {
            font-size: 12px;
            font-weight: 600;
            color: #161616;
        }

        .sugg-schol-learnmore .readmore {
            float: left;
            margin-top: 5px;
            font-size: 12px;
            padding: 2px 5px;
        }

        .suggested-scholarships-main-heading h3 {
            font-size: 16px;
            font-weight: 500;
            text-transform: uppercase;
            text-align: left;
            color: #161616;
            margin-bottom: 20px;
            margin-top: 15px;
        }

        @media only screen and (max-width: 767px) {
            .sugg-schol-card .row .col-md-4 {
                width: 30%;
            }

            .sugg-schol-card .row .col-md-8 {
                width: 70%;
            }

            .sugg-schol-card-inner h4 {
                margin-bottom: 0px;
            }
        }
    </style>


    <!--Banner Start-->
    <section class="main-inner-banner-one innerpage-banner">
        <div class="blur-1">
            <img src="{{ asset('images/Blur_1.png') }}" alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="{{ asset('images/Blur_2.png') }}" alt="bg blur">
        </div>
        <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
        <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
        <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
        <div class="banner-one-shape4">
            <img src="{{ asset('images/banner-inner-shape-one.png') }}" alt="shap">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">Student Dashboard</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{ url('/') }}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Student Dashboard</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <!-- all-scholarship -->
    <section class="mt-10 mb-10 stu-dashboard">
        <div class="container">
            <div class="row">

                <div class="col-xl-3 col-lg-4 order-1 order-lg-0">
                    <div class="profile-sidebar">
                        <div class="widget-profile pro-widget-content">
                            @include('student.profile_img')
                        </div>
                        <div class="dashboard-widget">
                            <nav class="dashboard-menu">
                                @include('student.sidebar')
                            </nav>
                        </div>
                    </div>
                    <!--SUGGESTED SCHOLARSHIP SECTION STARTS-->
                    <div class="row scholarship__list-wrap row-cols-1 cstm-suggested-row-css">
                        <div class="col">
                            <div class="scholarship__item-two shine__animate-item">

                                <div class="suggested-schols-main">
                                    <div class="suggested-scholarships-main-heading">
                                        <h3>Suggested Scholarships</h3>
                                    </div>
                                    @if (isset($suggested_scholoarships) && count($suggested_scholoarships) > 0)
                                        @foreach ($suggested_scholoarships as $value)
                                            <div class="sugg-schol-inner">
                                                <div class="sugg-schol-card">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="sugg-schol-image">
                                                                <img src="{{ $value->avatar ? asset($value->avatar) : asset('images/logo.png') }}"
                                                                    alt="" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="sugg-schol-card-inner">
                                                                <div class="sugg-schol-heading">
                                                                    <h4>{{ $value->scholarship_name }}</h4>
                                                                </div>
                                                                {{-- <div class="sugg-schol-ydate">
                                                                    <span class="icon"><i class="fa fa-circle"></i></span>
                                                                    <span class="text">End Date:</span>
                                                                    <span class="udate">{{ $value->end_date }}</span>
                                                                </div> --}}
                                                                <div class="sugg-schol-learnmore">
                                                                    <a href="{{ route('Student.scholarship.details', $value->slug) }}" class="readmore btn sec-btn-two list-btn">Learn More</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p>Not Found !!</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8 order-2">
                    <div class="card profile-main">
                        <div class="card-body pt-0">

                            <nav class="user-tabs mb-4">
                                <ul class="nav nav-tabs nav-tabs-bottom nav-justified">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#pat_appointments" data-bs-toggle="tab">Profile Details</a>
                                    </li>
                                </ul>
                            </nav>
                            <div class="tab-content pt-0">

                                <div id="pat_appointments" class="tab-pane fade show active">
                                    <div class="">
                                        <div class="card-body">
                                            <div class="profile-accordion-one" id="accordionExample">
                                                <div class="accordion one">
                                                    <div class="accordion-item one">
                                                        <h3 class="accordion-header h3-title" id="headingOne">
                                                            <button class="accordion-button one" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"> Student's Personal Details
                                                                <span class="icon">
                                                                    <i class="fa fa-angle-left" aria-hidden="true"></i>
                                                                </span>
                                                            </button>
                                                        </h3>
                                                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne">
                                                            <div class="accordion-body">
                                                                <div class="student-personal-form">
                                                                    <form id="userPersonalDetailUpdateForm" onsubmit="event.preventDefault();">
                                                                        @csrf
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>First Name*</label>
                                                                                    <input type="text" name="first_name" class="form-input-one" value="{{ $user->first_name }}" placeholder="First Name" required />
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Last Name*</label>
                                                                                    <input type="text" name="last_name" class="form-input-one" value="{{ $user->last_name }}" placeholder="Last Name" required />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Email Id*</label>
                                                                                    <input type="email" name="email" value="{{ $user->email }}" class="form-input-one" placeholder="Email Address" required />
                                                                                    @if ($user->email_verified == 1)
                                                                                        <i class="fa-solid fa-check"></i>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Mobile Number*</label>
                                                                                    <input type="text" name="phone_number" value="{{ $user->phone_number }}" class="form-input-one digitsOnly" placeholder="Phone No." maxlength="12" readonly required />
                                                                                    @if ($user->mobile_verified == 1)
                                                                                        <i class="fa-solid fa-check"></i>
                                                                                    @endif
                                                                                </div>

                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Date of Birth*</label>
                                                                                    <input type="date" name="date_of_birth" class="date form-input-one" value="{{ $user->date_of_birth }}" placeholder="Date of Birth" max="{{ date('Y-m-d') }}" required />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Whatsapp Number (Optional)</label>
                                                                                    <input type="text" name="whatsapp_number" value="{{ $user->whatsapp_number }}" class="form-input-one digitsOnly" placeholder="Whatsapp Number" maxlength="12">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Gender*</label>
                                                                                    <select name="gender" class="form-input-one" required >
                                                                                        <option value="">Select Gender</option>
                                                                                        <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}> Male</option>
                                                                                        <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}> Female</option>
                                                                                        <option value="other" {{ $user->gender === 'other' ? 'selected' : '' }}> Other</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Aadhar Card Number (Optional)</label>
                                                                                    <input type="text" name="aadhar_card_number" value="{{ $user->aadhar_card_number }}" class="form-input-one digitsOnly" placeholder="Aadhar Card Number" />
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>
                                                                                        <input id="is_minority" name="is_minority" type="checkbox" {{ optional($user->student)->is_minority === 1 ? 'checked' : '' }} />If you belong to minority
                                                                                    </label>
                                                                                    <div id="minority_group_div" style="{{ optional($user->student)->is_minority ? 'display:block;' : 'display:none;' }}">
                                                                                        <select name="minority_group" class="form-input-one">
                                                                                            <option value=""> Select Minority group</option>
                                                                                            <option value="muslim" {{ strtolower(optional($user->student)->minority_group) === 'muslim' ? 'selected' : '' }}> Muslim</option>
                                                                                            <option value="sikh" {{ strtolower(optional($user->student)->minority_group) === 'sikh' ? 'selected' : '' }}> Sikh</option>
                                                                                            <option value="christian" {{ strtolower(optional($user->student)->minority_group) === 'christian' ? 'selected' : '' }}> Christian</option>
                                                                                            <option value="buddhist" {{ strtolower(optional($user->student)->minority_group) === 'buddhist' ? 'selected' : '' }}> Buddhist</option>
                                                                                            <option value="jain" {{ strtolower(optional($user->student)->minority_group) === 'jain' ? 'selected' : '' }}> Jain</option>
                                                                                            <option value="zoroastrians" {{ strtolower(optional($user->student)->minority_group) === 'zoroastrians' ? 'selected' : '' }}> Zoroastrians</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <script type="text/javascript">
                                                                                    const isMinorityCheckbox = document.getElementById("is_minority");
                                                                                    const minorityGroupDiv = document.getElementById("minority_group_div");

                                                                                    if (isMinorityCheckbox.checked) {
                                                                                        minorityGroupDiv.style.display = "block";
                                                                                    } else {
                                                                                        minorityGroupDiv.style.display = "none";
                                                                                    }

                                                                                    isMinorityCheckbox.addEventListener("change", function() {
                                                                                        if (this.checked) {
                                                                                            minorityGroupDiv.style.display = "block";
                                                                                        } else {
                                                                                            minorityGroupDiv.style.display = "none";
                                                                                        }
                                                                                    });

                                                                                    $(document).ready(function() {
                                                                                        $('#studentCategory').on("change", function() {
                                                                                            setcategory();
                                                                                        });

                                                                                        function setcategory() {
                                                                                            var category = $('#studentCategory').val();
                                                                                            if (category == "other reservation") {
                                                                                                $('#otherReservationShow').show();
                                                                                            } else {
                                                                                                $('#otherReservationShow').hide();
                                                                                            }
                                                                                        }
                                                                                        setcategory();
                                                                                    });
                                                                                </script>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Category</label>
                                                                                    <select name="category" id="studentCategory" class="form-input-one">
                                                                                        <option value="">Select Category</option>
                                                                                        <option value="general" {{ strtolower(optional($user->student)->category) === 'general' ? 'selected' : '' }}> General</option>
                                                                                        <option value="obc c" {{ strtolower(optional($user->student)->category) === 'obc c' ? 'selected' : '' }}> OBC C</option>
                                                                                        <option value="obc nc" {{ strtolower(optional($user->student)->category) === 'obc nc' ? 'selected' : '' }}> OBC NC</option>
                                                                                        <option value="sc" {{ strtolower(optional($user->student)->category) === 'sc' ? 'selected' : '' }}> SC</option>
                                                                                        <option value="st" {{ strtolower(optional($user->student)->category) === 'st' ? 'selected' : '' }}> ST</option>
                                                                                        <option value="other reservation" {{ strtolower(optional($user->student)->category) === 'other reservation' ? 'selected' : '' }}> Other Reservation</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" id="otherReservationShow">
                                                                                <div class="form-box-one">
                                                                                    <label>Please Specify (Other Reservation)</label>
                                                                                    <input type="text" name="other_reservation" class="form-input-one" value="{{ optional($user->student)->other_reservation }}" placeholder="" />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>
                                                                                        <input id="is_pwd_category" name="is_pwd_category" type="checkbox" {{ optional($user->student)->is_pwd_category ? 'checked' : '' }} />If you belong to PwD Category
                                                                                    </label>
                                                                                    <div id="pwd_percentage_div" style="display:none;">
                                                                                        <input name="pwd_percentage" type="range" min="1" class="form-control-range w-100" id="formControlRange" onInput="$('#rangeval').html($(this).val())" value="{{ optional($user->student)->pwd_percentage ?? 50 }}">
                                                                                        <span id="rangeval">{{ optional($user->student)->pwd_percentage ?? 50 }}</span><span>%</span>
                                                                                        <span>(Select Percentage of Disability)</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <script type="text/javascript">
                                                                                const isPWDCheckbox = document.getElementById("is_pwd_category");
                                                                                const pwdPercentageDiv = document.getElementById("pwd_percentage_div");

                                                                                if (isPWDCheckbox.checked) {
                                                                                    pwdPercentageDiv.style.display = "block";
                                                                                } else {
                                                                                    pwdPercentageDiv.style.display = "none";
                                                                                }

                                                                                isPWDCheckbox.addEventListener("change", function() {
                                                                                    if (this.checked) {
                                                                                        pwdPercentageDiv.style.display = "block";
                                                                                    } else {
                                                                                        pwdPercentageDiv.style.display = "none";
                                                                                    }
                                                                                });
                                                                            </script>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>
                                                                                        <input id="is_army_veteran_category" name="is_army_veteran_category" type="checkbox" {{ optional($user->student)->is_army_veteran_category ? 'checked' : '' }} /> If you/your family belong to army veteran category.
                                                                                    </label>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>I am *</label>
                                                                                    <select name="user_type" id="user_type" autocomplete="off" class="form-input-one" required >
                                                                                        <option value="">Please Select</option>
                                                                                        <option {{ auth()->user()->user_type == 'A School student' ? 'selected' : '' }} value="A School student">A School student</option>
                                                                                        <option {{ auth()->user()->user_type == 'Pursuing Bachelors' ? 'selected' : '' }} value="Pursuing Bachelors"> Pursuing Bachelors</option>
                                                                                        <option {{ auth()->user()->user_type == 'Pursuing masters' ? 'selected' : '' }} value="Pursuing masters"> Pursuing masters</option>
                                                                                        <option {{ auth()->user()->user_type == 'Pursuing PhD' ? 'selected' : '' }} value="Pursuing PhD"> Pursuing PhD</option>
                                                                                        <option {{ auth()->user()->user_type == 'Pursuing ITIs/Diploma/Polytechnic/Certificate course' ? 'selected' : '' }} value="Pursuing ITIs/Diploma/Polytechnic/Certificate course"> Pursuing ITIs/Diploma/Polytechnic/Certificate course</option>
                                                                                        <option {{ auth()->user()->user_type == 'Preparing for competitive exams' ? 'selected' : '' }} value="Preparing for competitive exams"> Preparing for competitive exams</option>
                                                                                        <option {{ auth()->user()->user_type == 'Working Professional' ? 'selected' : '' }} value="Working Professional"> Working Professional</option>
                                                                                        <option {{ auth()->user()->user_type == 'Others' ? 'selected' : '' }} value="Others">Others</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>I’m looking for  *</label>
                                                                                    <select name="looking_for" id="looking_for" autocomplete="off" class="form-input-one" required>
                                                                                        <option value="">Please Select</option>
                                                                                        @foreach (['School Scholarships', 'Bachelors Scholarships', 'Master Scholarships', 'PhD. Scholarships', 'ITIs/Diploma/Polytechnic/Certificate Scholarships', 'Competitive Exams scholarships', 'Exchange program scholarships'] as $k => $v)
                                                                                            <option  value="{{ $v }}"  {{ auth()->user()->looking_for == $v ? 'selected' : '' }}>  {{ $v }} </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <script type="text/javascript">
                                                                                const isArmyVeteranCheckbox = document.getElementById("is_army_veteran_category");
                                                                                const armyVeteranDataDiv = document.getElementById("army_veteran_data_div");

                                                                                if (isArmyVeteranCheckbox.checked) {
                                                                                    armyVeteranDataDiv.style.display = "block";
                                                                                } else {
                                                                                    armyVeteranDataDiv.style.display = "none";
                                                                                }

                                                                                isArmyVeteranCheckbox.addEventListener("change", function() {
                                                                                    if (this.checked) {
                                                                                        armyVeteranDataDiv.style.display = "block";
                                                                                    } else {
                                                                                        armyVeteranDataDiv.style.display = "none";
                                                                                    }
                                                                                });
                                                                            </script>
                                                                            <div class="col-12">
                                                                                <div class="form-box-one mb-0">
                                                                                    <button type="button" id="updateBtn"
                                                                                        class="sec-btn-one"><span>Update</span></button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>

                                                    {{-- 2 --}}
                                                    <div class="accordion-item one">
                                                        <h3 class="accordion-header h3-title" id="headingTwo">
                                                            <button class="accordion-button one collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                                aria-expanded="false" aria-controls="collapseTwo">
                                                                Education Details<span class="icon"><i
                                                                        class="fa fa-angle-left"
                                                                        aria-hidden="true"></i></span>
                                                            </button>
                                                        </h3>
                                                        <div id="collapseTwo" class="accordion-collapse collapse"
                                                            aria-labelledby="headingTwo">
                                                            <div class="accordion-body">
                                                                <div class="student-personal-form">

                                                                    <div id="dynamic_forms">
                                                                        <!-- Dynamic forms will be appended here -->
                                                                    </div>

                                                                    <form id="userEducationDetailUpdateFormBoard"
                                                                        action="#" methof="POST">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                            </div>

                                                                            <div class="accordion-item one">
                                                                                <h3 class="accordion-header h3-title"
                                                                                    id="headingInsideOne">
                                                                                    <button
                                                                                        class="accordion-button one collapsed"
                                                                                        type="button"
                                                                                        data-bs-toggle="collapse"
                                                                                        data-bs-target="#collapseInsideOne"
                                                                                        aria-expanded="false"
                                                                                        aria-controls="collapseInsideOne">
                                                                                        Add Education<span
                                                                                            class="icon"><i
                                                                                                class="fa fa-angle-left"
                                                                                                aria-hidden="true"></i></span>
                                                                                    </button>
                                                                                </h3>
                                                                                <div id="collapseInsideOne"
                                                                                    class="accordion-collapse collapse"
                                                                                    aria-labelledby="headingInsideOne"
                                                                                    data-bs-parent="#headingInsideOne">
                                                                                    <div class="accordion-body row">
                                                                                        <div class="col-md-12">

                                                                                            <div class="row">

                                                                                                <div class="col-md-6">
                                                                                                    <div
                                                                                                        class="form-box-one">
                                                                                                        <label>Degree*</label>
                                                                                                        <select required
                                                                                                            name="level"
                                                                                                            class="form-input-one educationLevel">
                                                                                                            <option
                                                                                                                value="">
                                                                                                                Select
                                                                                                                Degree
                                                                                                            </option>
                                                                                                            @foreach (\App\Models\EducationDetail::DEGREES as $key => $value)
                                                                                                                <option
                                                                                                                    value="{{ $key }}">
                                                                                                                    {{ $value }}
                                                                                                                </option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="form-box-one">
                                                                                                    <label>
                                                                                                        <input name="is_education_pursuing" type="checkbox" id="is_graduation_pursuing"> If Currently pursuing
                                                                                                    </label>
                                                                                                </div>

                                                                                                <div class="col-md-6 otherLevelInput"
                                                                                                    style="display: none;">
                                                                                                    <div
                                                                                                        class="form-box-one">
                                                                                                        <label>Custom Degree*</label>
                                                                                                        <input type="text" name="other_level" class="form-input-one" placeholder="Other Level" value="" required />
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-6">
                                                                                                    <div class="form-box-one">
                                                                                                        <label>Institute/University/School*</label>
                                                                                                
                                                                                                        <?php
                                                                                                        $institutes = \App\Models\EducationDetail::INSTITUTES;
                                                                                                
                                                                                                        $otherInstitute = 'Other';
                                                                                                        $otherKey = array_search($otherInstitute, $institutes);
                                                                                                
                                                                                                        if ($otherKey !== false) {
                                                                                                            unset($institutes[$otherKey]);
                                                                                                        }
                                                                                                
                                                                                                        array_multisort(array_values($institutes), SORT_ASC, $institutes);
                                                                                                
                                                                                                        // Prepend "Other" to the beginning of the list
                                                                                                        if ($otherKey !== false) {
                                                                                                            $institutes = [$otherKey => $otherInstitute] + $institutes;
                                                                                                        }
                                                                                                        ?>
                                                                                                
                                                                                                        <select name="education_institute" id="education_institute" class="form-input-one" required>
                                                                                                            <option value="">Select Institute</option>
                                                                                                            @foreach ($institutes as $key => $value)
                                                                                                                <option value="{{ $key }}" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') && optional($user->student)->educationDetails->firstWhere('level', 'education')->institute_name == $key ? 'selected' : '' }}> {{ $value }}</option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>
                                                                                                
                                                                                                <script>
                                                                                                    document.getElementById('education_institute').addEventListener('change', function() {
                                                                                                        var educationInstitute = this.value;
                                                                                                        var otherUniversityElement = document.getElementById('other_university');
                                                                                                        if (educationInstitute == 'Other') {
                                                                                                            otherUniversityElement.classList.remove('d-none');
                                                                                                        } else {
                                                                                                            otherUniversityElement.classList.add('d-none');
                                                                                                        }
                                                                                                    });
                                                                                                </script>
                                                                                                <div class="col-md-6 d-none"
                                                                                                    id="other_university">
                                                                                                    <div
                                                                                                        class="form-box-one">
                                                                                                        <label>Other Institute/University/School</label>
                                                                                                        <input required type="text" name="education_institute_other" class="form-input-one" placeholder="Institute/University" value="{{ optional($user->student)->educationDetails->firstWhere('level', 'education')->education_institute_other ?? '' }}" />
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-6">
                                                                                                    <div
                                                                                                        class="form-box-one">
                                                                                                        <label>Type of Institute*</label>
                                                                                                        <select name="education_institute_type" class="form-input-one" required >
                                                                                                            <option value=""> Select Type of Institute
                                                                                                            </option>
                                                                                                            <option value="government_aided" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') === 'government_aided' ? 'selected' : '' }}> Government Aided</option>
                                                                                                            <option value="private_institute" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') === 'private_institute' ? 'selected' : '' }}> Private Institute</option>
                                                                                                            <option value="public_aided" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') === 'public_aided' ? 'selected' : '' }}> Public Aided</option>
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-6">
                                                                                                    <div class="form-box-one">
                                                                                                        <label for="education_state">State*</label>
                                                                                                        <?php
                                                                                                        $state = \App\Models\CountryData\State::whereStatus('active')->orderBy('name', 'asc')->get();
                                                                                                        $district = \App\Models\CountryData\District::whereStatus('active')->orderBy('name', 'asc')->get(); // Added orderBy here
                                                                                                        ?>
                                                                                                        <select name="state_id" id="education_state" class="form-input-one" required>
                                                                                                            <option value="" class="" data-val="">Select Education State</option>
                                                                                                            @foreach ($state as $s)
                                                                                                                <option value="{{ $s->name }}" data-val="{{ $s->id }}" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') && optional($user->student)->educationDetails->firstWhere('level', 'education')->state_id == $s->id ? 'selected' : '' }}>
                                                                                                                    {{ $s->name }}
                                                                                                                </option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>
                                                                                                
                                                                                                <div class="col-md-6">
                                                                                                    <div class="form-box-one">
                                                                                                        <label for="education_district">District*</label>
                                                                                                        <select name="education_institute_district" id="education_district" class="form-input-one" required>
                                                                                                            <option value="" class="" data-state="">Select Education District</option>
                                                                                                            @foreach ($district as $d)
                                                                                                                <option value="{{ $d->name }}" data-state="{{ $d->state_id }}" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') && optional($user->student)->educationDetails->firstWhere('level', 'education')->district_id == $d->id ? 'selected' : '' }}>
                                                                                                                    {{ $d->name }}
                                                                                                                </option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>
                                                                                                

                                                                                                <div class="col-md-6">
                                                                                                    <div class="form-box-one">
                                                                                                        <label>Course Name*</label>
                                                                                                        <?php
                                                                                                        $sortedCourses = \App\Models\EducationDetail::COURCE;
                                                                                                        
                                                                                                        $otherCourse = 'Others';
                                                                                                        $otherKey = array_search($otherCourse, $sortedCourses);
                                                                                                
                                                                                                        if ($otherKey !== false) {
                                                                                                            unset($sortedCourses[$otherKey]);
                                                                                                        }
                                                                                                
                                                                                                        asort($sortedCourses);
                                                                                                
                                                                                                        if ($otherKey !== false) {
                                                                                                            $sortedCourses = [$otherKey => $otherCourse] + $sortedCourses;
                                                                                                        }
                                                                                                        ?>
                                                                                                        <select name="education_course_name" id="education_course_name" class="form-input-one" required>
                                                                                                            <option value="">Select Course Name</option>
                                                                                                            @foreach ($sortedCourses as $key => $value)
                                                                                                                <option value="{{ $key }}" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') ? (optional($user->student)->educationDetails->firstWhere('level', 'education')->course_name == $key ? 'selected' : '') : '' }}>
                                                                                                                    {{ $value }}
                                                                                                                </option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>
                                                                                                

                                                                                                <div class="col-md-6 d-none" id="other_course">
                                                                                                    <div class="form-box-one">
                                                                                                        <label>Other Education Course</label>
                                                                                                        <input required type="text" name="education_course_other" class="form-input-one" placeholder="Other Course" value="{{ optional($user->student)->educationDetails->firstWhere('level', 'education')->education_course_other ?? '' }}" />
                                                                                                    </div>
                                                                                                </div>
                                                                                                <script>
                                                                                                    document.getElementById('education_course_name').addEventListener('change', function() {
                                                                                                        var educationInstitute = this.value;
                                                                                                        var otherCourseElement = document.getElementById('other_course');
                                                                                                        if (educationInstitute == 'Others') {
                                                                                                            otherCourseElement.classList.remove('d-none');
                                                                                                        } else {
                                                                                                            otherCourseElement.classList.add('d-none');
                                                                                                        }
                                                                                                    });
                                                                                                </script>
                                                                                                <div class="col-md-6">
                                                                                                    <div class="form-box-one">
                                                                                                        <label>Specialisation*</label>
                                                                                                        <input type="text" name="education_specialisation" class="form-input-one" placeholder="Specialisation" value="{{ optional($user->student)->educationDetails->firstWhere('level', 'education')->specialisation ?? '' }}" required />
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-6">
                                                                                                    <div
                                                                                                        class="form-box-one">
                                                                                                        <label>Grading System*</label>
                                                                                                        <select name="education_grade_type" class="form-input-one" required >
                                                                                                            <option value=""> Select Grading System</option>
                                                                                                            <option value="10_point_grading_system_cgpa" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') === '10_point_grading_system_cgpa' ? 'selected' : '' }}> 10 point grading system CGPA</option>
                                                                                                            <option value="%_marks_out_of_100" {{ optional($user->student)->educationDetails->firstWhere('level', 'education') === '%_marks_out_of_100' ? 'selected' : '' }}> % marks out of 100</option>
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-6">
                                                                                                    <div
                                                                                                        class="form-box-one">
                                                                                                        <label>Percentage scored/CGPA *</label>
                                                                                                        <input type="text" name="education_grade" class="form-input-one" placeholder="Percentage scored/CGPA" oninput="validateNumericInput(this)" value="{{ optional($user->student)->educationDetails->firstWhere('level', 'education')->grade ?? '' }}" required />
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-6">
                                                                                                    <div class="form-box-one">
                                                                                                        <label>From *</label>
                                                                                                        <input id="education_start_date" type="date" name="education_start_date" class="form-input-one date" placeholder="From" value="{{ optional($user->student)->educationDetails->firstWhere('level', 'education')->start_date ?? '' }}" max="{{ date('Y-m-d') }}" required />
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-6">
                                                                                                    <div class="form-box-one" id="graduationEndDateContainer">
                                                                                                        <label>To *</label>
                                                                                                        <input type="date" id="education_end_date" name="education_end_date" class="date form-input-one" placeholder="To" value="{{ optional($user->student)->educationDetails->firstWhere('level', 'education')->end_date ?? '' }}">
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-12">
                                                                                                    <div class="form-box-one mb-0">
                                                                                                        <button type="submit" class="sec-btn-one"><span>Update</span></button>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- 2nd --}}

                                                    <div class="accordion-item one">
                                                        <h3 class="accordion-header h3-title" id="headingFive">
                                                            <button class="accordion-button one collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive"> Education Documents<span class="icon"><i  class="fa fa-angle-left"  aria-hidden="true"></i></span></button>
                                                        </h3>
                                                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive">
                                                            <div class="accordion-body">
                                                                <div class="student-personal-form">
                                                                    <form id="userDocumentUpdateForm" enctype="multipart/form-data">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Select a Document *</label>
                                                                                    <select name="document_type" class="form-input-one" required >
                                                                                        <option value="">Select Document Type</option>
                                                                                        @foreach (\App\Models\Document::$documentTypes as $key => $value)
                                                                                            <option value="{{ $key }}"> {{ $value }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Upload Your Document*</label>
                                                                                    <input type="file" name="document" class="" required />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-12" id="otherInput" style="display:none">
                                                                                <div class="form-box-one">
                                                                                    <label for="otherText">Other Document Name*</label>
                                                                                    <input class="form-input-one" type="text" name="other_document_name">
                                                                                </div>
                                                                            </div>

                                                                            <div id="documentList">
                                                                                <!-- Document list will be displayed here -->
                                                                            </div>

                                                                            <div class="col-12">
                                                                                <div class="form-box-one mb-0">
                                                                                    <button type="submit" class="sec-btn-one"><span>Update</span></button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- 3 --}}
                                                    <div class="accordion-item one">
                                                        <h3 class="accordion-header h3-title" id="headingThree">
                                                            <button class="accordion-button one collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> Work Experience (Optional)<span class="icon"><i class="fa fa-angle-left" aria-hidden="true"></i></span></button>
                                                        </h3>
                                                        <div id="collapseThree" class="accordion-collapse collapse"
                                                            aria-labelledby="headingThree">
                                                            <div class="accordion-body">
                                                                <div class="student-personal-form">

                                                                    <div id="dynamic_forms_work_experience">
                                                                        <!-- Dynamic forms will be appended here -->
                                                                    </div>

                                                                    <form id="userWorkDetailUpdateForm">
                                                                        <div class="accordion-item one">
                                                                            <h3 class="accordion-header h3-title" id="headingInsideTwo">
                                                                                <button class="accordion-button one collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInsideTwo" aria-expanded="false" aria-controls="collapseInsideTwo"> Add Work Experience<span class="icon"><i class="fa fa-angle-left" aria-hidden="true"></i></span></button>
                                                                            </h3>
                                                                            <div id="collapseInsideTwo" class="accordion-collapse collapse" aria-labelledby="headingInsideTwo" data-bs-parent="#headingInsideTwo">
                                                                                <div class="accordion-body row">
                                                                                    <div class="col-md-12">
                                                                                        <div class="row">
                                                                                            <div class="col-md-12 mb-3">
                                                                                                <h6>Total Experience (includes paid/unpaid internships, immersion programs, NCC/NSS etc.)</h6>
                                                                                            </div>

                                                                                            <div class="col-md-6">
                                                                                                <div class="form-box-one">
                                                                                                    <label>Employment Type *</label>
                                                                                                    <select name="employment_type" class="form-input-one" required >
                                                                                                        <option value=""> Select Employment Type</option>
                                                                                                        <option value="full_time"> Full time</option>
                                                                                                        <option value="internship"> Internship</option>
                                                                                                        <option value="part_ttime"> Part Time</option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-box-one">
                                                                                                    <label>Company Name*</label>
                                                                                                    <input type="text" value="" name="company_name" class="form-input-one" placeholder="" required />
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-box-one">
                                                                                                    <label>Designation*</label>
                                                                                                    <input type="text" value="" name="designation" class="form-input-one" placeholder="" required /> 
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-box-one">
                                                                                                    <label>Joining Date*</label>
                                                                                                    <input type="date" value="" name="joining_date" id="joining_date" class="date form-input-one" placeholder="" required />
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-box-one">
                                                                                                    <label>Working Presently
                                                                                                    </label>
                                                                                                    <select name="working_currently" onchange="toggleWorkedTill(this)" class="form-input-one">
                                                                                                        <option value=""> Select Working Presently</option>
                                                                                                        <option value="1"> Yes</option>
                                                                                                        <option value="0"> No</option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-6" id="workedTillContainer2" style="display: none;">
                                                                                                <div class="form-box-one">
                                                                                                    <label>Worked Till*</label>
                                                                                                    <input type="date" name="end_date" id="end_date" value="" class="date form-input-one" placeholder="" required />
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-box-one">
                                                                                                    <label>Job
                                                                                                        Profile*</label>
                                                                                                    <input type="text"
                                                                                                        name="job_role"
                                                                                                        value=""
                                                                                                        class="form-input-one"
                                                                                                        placeholder=""
                                                                                                        required />
                                                                                                </div>
                                                                                            </div>


                                                                                            <div class="col-12">
                                                                                                <div
                                                                                                    class="form-box-one mb-0">
                                                                                                    <button type="submit"
                                                                                                        class="sec-btn-one"><span>ADD</span></button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- 4 --}}
                                                    <div class="accordion-item one">
                                                        <h3 class="accordion-header h3-title" id="headingFour">
                                                            <button class="accordion-button one collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour"> Family Member Details (Optional)<span class="icon"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                                                            </button>
                                                        </h3>
                                                        <div id="collapseFour" class="accordion-collapse collapse"
                                                            aria-labelledby="headingFour">
                                                            <div class="accordion-body">
                                                                <div class="student-personal-form">
                                                                    <form id="userFamilyDetailUpdateForm">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Relationship with the Principal Guardian *</label>
                                                                                    <select name="guardian_name"
                                                                                        class="form-input-one" required >
                                                                                        <option value="">Select
                                                                                            Relationship with the
                                                                                            Principal Guardian</option>
                                                                                        <option value="Father"
                                                                                            {{ optional($user->student->guardianDetails)->name === 'Father' ? 'selected' : '' }}>
                                                                                            Father</option>
                                                                                        <option value="Mother"
                                                                                            {{ optional($user->student->guardianDetails)->name === 'Mother' ? 'selected' : '' }}>
                                                                                            Mother</option>
                                                                                        <option value="Others"
                                                                                            {{ optional($user->student->guardianDetails)->name === 'Others' ? 'selected' : '' }}>
                                                                                            Others</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Principal Guardian Name*</label>
                                                                                    <input type="text" value="{{ optional($user->student->guardianDetails)->relationship }}" name="guardian_relationship" class="form-input-one" placeholder="" required />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Occupation of the Principal Guardian*</label>
                                                                                    <input type="text" value="{{ optional($user->student->guardianDetails)->occupation }}" name="guardian_occupation" class="form-input-one" placeholder="" required />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Mobile No. of the Principal Guardian*</label>
                                                                                    <input type="text" value="{{ optional($user->student->guardianDetails)->phone_number }}" name="guardian_phone_number" class="form-input-one digitsOnly" placeholder="Phone No." maxlength="12" required />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>No of siblings *</label>
                                                                                    <select name="number_of_siblings"
                                                                                        class="form-input-one" required >
                                                                                        <option value="">Select No of siblings</option>
                                                                                        <option value="only_child"
                                                                                            {{ optional($user->student->guardianDetails)->number_of_siblings === 'only_child' ? 'selected' : '' }}>
                                                                                            Only Child</option>
                                                                                        <option value="1"
                                                                                            {{ optional($user->student->guardianDetails)->number_of_siblings === '1' ? 'selected' : '' }}>
                                                                                            1</option>
                                                                                        <option value="2"
                                                                                            {{ optional($user->student->guardianDetails)->number_of_siblings === '2' ? 'selected' : '' }}>
                                                                                            2</option>
                                                                                        <option value="3"
                                                                                            {{ optional($user->student->guardianDetails)->number_of_siblings === '3' ? 'selected' : '' }}>
                                                                                            3</option>
                                                                                        <option value="4"
                                                                                            {{ optional($user->student->guardianDetails)->number_of_siblings === '4' ? 'selected' : '' }}>
                                                                                            4</option>
                                                                                        <option value="5"
                                                                                            {{ optional($user->student->guardianDetails)->number_of_siblings === '5' ? 'selected' : '' }}>
                                                                                            5</option>
                                                                                        <option value="6"
                                                                                            {{ optional($user->student->guardianDetails)->number_of_siblings === '6' ? 'selected' : '' }}>
                                                                                            6</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Family's Annual Income (in INR) *</label>
                                                                                    <div class="input-group w-100">
                                                                                        <input type="text" value="{{ optional($user->student->guardianDetails)->annual_income }}" name="annual_income" class="form-input-one income w-50 digitsOnly" placeholder="Annual Income" required />
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <style>
                                                                                .input-group {
                                                                                    display: flex;
                                                                                }

                                                                                .income {
                                                                                    width: 60% !important;
                                                                                }

                                                                                .form-input-one,
                                                                                .form-select-one {
                                                                                    margin: 0;
                                                                                    padding: 10px;
                                                                                    border: 1px solid #ccc;
                                                                                    border-radius: 3px 0 0 3px;
                                                                                    /* rounded corners on the left side */
                                                                                }

                                                                                .form-select-one {
                                                                                    border-left: 0;
                                                                                    /* remove the left border */
                                                                                    border-radius: 0 3px 3px 0;
                                                                                    /* rounded corners on the right side */
                                                                                }
                                                                            </style>



                                                                            <div class="col-md-12">
                                                                                <h6 class="center-heading">Current Address</h6>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>House Type *</label>
                                                                                    <select name="current_house_type" class="form-input-one" required >
                                                                                        <option value="">Select House Type</option>
                                                                                        <option
                                                                                            value="self_family_owned_katcha_house"
                                                                                            {{ optional($user->student->addressDetails->firstWhere('type', 'current'))->house_type === 'self_family_owned_katcha_house' ? 'selected' : '' }}>
                                                                                            Self/Family Owned Katcha
                                                                                            House
                                                                                            (Mud House, Tin Shed)
                                                                                        </option>
                                                                                        <option
                                                                                            value="self_family_owned_pakka_house"
                                                                                            {{ optional($user->student->addressDetails->firstWhere('type', 'current'))->house_type === 'self_family_owned_pakka_house' ? 'selected' : '' }}>
                                                                                            Self/Family Owned Pakka
                                                                                            House
                                                                                        </option>
                                                                                        <option value="rented_katcha_house"
                                                                                            {{ optional($user->student->addressDetails->firstWhere('type', 'current'))->house_type === 'rented_katcha_house' ? 'selected' : '' }}>
                                                                                            Rented Katcha (Mud House/Tin
                                                                                            Shed)</option>
                                                                                        <option value="rented_pakka_house"
                                                                                            {{ optional($user->student->addressDetails->firstWhere('type', 'current'))->house_type === 'rented_pakka_house' ? 'selected' : '' }}>
                                                                                            Rented Pakka House</option>
                                                                                    </select>

                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Address *</label>
                                                                                    <textarea name="current_address" class="form-input-one" placeholder="Address" required >{!! optional($user->student->addressDetails->firstWhere('type', 'current'))->address !!}</textarea>
                                                                                </div>
                                                                            </div>
                                                                            {{--
                                                                            <script>
                                                                                @if(optional($user->student->addressDetails->firstWhere('type', 'current'))->state)
                                                                                    document.addEventListener('DOMContentLoaded', function() {
                                                                                        var permanentState = document.getElementById('current_state');
                                                                                        if (permanentState) {
                                                                                            var event = new Event('change');
                                                                                            permanentState.dispatchEvent(event);
                                                                                        }
                                                                                    });
                                                                                @endif
                                                                            </script>
                                                                            --}}
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>State *</label>
                                                                                    <?php
                                                                                    $state = \App\Models\CountryData\State::whereStatus('active')->orderBy('name', 'asc')->get();
                                                                                    $district = \App\Models\CountryData\District::whereStatus('active')->orderBy('name', 'asc')->get();
                                                                                    ?>
                                                                                    <select class="form-input-one current_state" name="current_state" id="current_state" required>
                                                                                        <option value="" class="" data-val="">Select Current State</option>
                                                                                        @foreach ($state as $s)
                                                                                            <option value="{{ $s->name }}" data-val="{{ $s->id }}" {{ optional($user->student->addressDetails->firstWhere('type', 'current'))->state == $s->name ? 'selected' : '' }}>
                                                                                                {{ $s->name }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>District *</label>
                                                                                    <select id="current_district" name="current_district" class="form-input-one current_district" required>
                                                                                        <option value="" data-state="">Select Current District</option>
                                                                                        @foreach ($district as $d)
                                                                                            <option value="{{ $d->name }}" data-state="{{ $d->state_id }}" data-val="{{ $d->id }}" {{ optional($user->student->addressDetails->firstWhere('type', 'current'))->district == $d->id ? 'selected' : '' }}>
                                                                                                {{ $d->name }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Pincode *</label>
                                                                                    <input type="text" value="{{ optional($user->student->addressDetails->firstWhere('type', 'current'))->pincode }}" name="current_pincode" class="form-input-one digitsOnly" placeholder="Pincode" maxlength="6" required />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <div class="form-box-one">
                                                                                    <label>
                                                                                        <input type="checkbox" id="is_pm_same_as_current" name="is_pm_same_as_current" checked="{{ optional($user->student)->is_pm_same_as_current ? 'checked' : '' }}" value="{{ optional($user->student)->is_pm_same_as_current ? '1' : '0' }}" /> If same as Current address
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <h6 class="center-heading">Permanent Address</h6>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>House Type *</label>
                                                                                    <select name="permanent_house_type"
                                                                                        class="form-input-one" required >
                                                                                        <option value="">Select House Type</option>
                                                                                        <option value="self_family_owned_katcha_house" {{ optional($user->student->addressDetails->firstWhere('type', 'permanent'))->house_type === 'self_family_owned_katcha_house' ? 'selected' : '' }}> Self/Family Owned Katcha House (Mud House, Tin Shed)</option>
                                                                                        <option value="self_family_owned_pakka_house" {{ optional($user->student->addressDetails->firstWhere('type', 'permanent'))->house_type === 'self_family_owned_pakka_house' ? 'selected' : '' }}> Self/Family Owned Pakka House</option>
                                                                                        <option value="rented_katcha_house" {{ optional($user->student->addressDetails->firstWhere('type', 'permanent'))->house_type === 'rented_katcha_house' ? 'selected' : '' }}> Rented Katcha (Mud House/Tin Shed)</option>
                                                                                        <option value="rented_pakka_house" {{ optional($user->student->addressDetails->firstWhere('type', 'permanent'))->house_type === 'rented_pakka_house' ? 'selected' : '' }}> Rented Pakka House</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Address *</label>
                                                                                    <textarea name="permanent_address" class="form-input-one" placeholder="Address" required >{{ optional($user->student->addressDetails->firstWhere('type', 'permanent'))->address }}</textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>State *</label>
                                                                                    <select class="form-input-one permanent_state" name="permanent_state" id="permanent_state" required >
                                                                                        <option value="" class="" data-val=""> Select Permanent State</option>
                                                                                        @foreach ($state as $s)
                                                                                            <option value="{{ $s->name }}" data-state="{{ $s->state_id }}" data-val="{{ $s->id }}" {{ optional($user->student->addressDetails->firstWhere('type', 'permanent'))->state == $s->name ? 'selected' : '' }}> {{ $s->name }} </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>District *</label>
                                                                                    <select id="permanent_district" name="permanent_district" class="form-input-one permanent_district" required >
                                                                                        <option value="0" data-state="">Select Permanent District</option>
                                                                                        @foreach ($district as $s)
                                                                                            <option value="{{ $s->name }}" data-state="{{ $s->state_id }}" data-val="{{ $s->id }}" {{ optional($user->student->addressDetails->firstWhere('type', 'permanent'))->district == $s->id ? 'selected' : '' }}> {{ $s->name }} </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Pincode *</label>
                                                                                    <input type="text" value="{{ optional($user->student->addressDetails->firstWhere('type', 'permanent'))->pincode }}" name="permanent_pincode" class="form-input-one digitsOnly" placeholder="Pincode" maxlength="6" required />
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="form-box-one">
                                                                                    <label>Current Citizenship *</label>
                                                                                    <input type="text" value="{{ optional($user->student)->current_citizenship == 1 ? 'Indian' : '' }}" name="current_citizenship" class="form-input-one" placeholder="Citizenship" required />
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-12">
                                                                                <div class="form-box-one mb-0">
                                                                                    <button type="submit" class="sec-btn-one"><span>Update</span></button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <!-- all-scholarship-end -->
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" data-backdrop="false"
        aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">Change Profile Photo</h5>
                    <button type="button" id="closeProfileModalButton" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="photo-crop-container">
                        <input type="file" class="upload-photo" name="filepond" />
                        <div class="crop-preview-cont">
                            <p>Drag photo to reposition the focal point of your image.</p>
                            <div class="btnDiv">
                                <button type="button" id="zoomInBtn1" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">
                                    <span class="docs-tooltip" data-toggle="tooltip" title="cropper.zoom(0.1)">
                                        <span class="fa fa-search-plus"></span>
                                    </span>
                                </button>
                                <button type="button" id="zoomOutBtn1" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">
                                    <span class="docs-tooltip" data-toggle="tooltip" title="cropper.zoom(-0.1)">
                                        <span class="fa fa-search-minus"></span>
                                    </span>
                                </button>
                            </div>
                            <div class="img_container"></div>
                            <div id="crop_img">Confirm</div>
                        </div>
                        <div id="user_cropped_img"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel"></h5>
                    <button type="button" id="closeModalButton" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="documentImage" src="" style="max-width: 100%;">
                </div>
            </div>
        </div>
    </div>
    <!-- Onload Popup Modal -->
    <div class="modal fade" id="onloadModal" tabindex="-1" role="dialog" aria-labelledby="onloadModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="onloadModalLabel">Verify Mobile & Email</h5>
                </div>
                <div class="modal-body">
                    <form id="onloadForm" action="{{ route('Student.verified.otp') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="inputField">Enter Mobile Number</label>
                            <input type="text" class="form-control" name="phone_number" id="inputFieldMobileNumber" value="{{ auth()->user()->phone_number ?? '' }}" readonly required />
                            <label for="inputField">Enter OTP sent on Mobile</label>
                            <input type="text" class="form-control" name="mobile_verified" id="inputFieldMobile" required />
                            <br>
                            <label for="inputField">Enter Email</label>
                            <input type="text" class="form-control" name="email" id="inputFieldEmailId" value="{{ auth()->user()->email ?? '' }}" readonly required />
                            <label for="inputField">Enter OTP sent on Email</label>
                            <input type="text" class="form-control" name="email_verified" id="inputFieldEmail" required >
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="onloadModalMobile" tabindex="-1" role="dialog" aria-labelledby="onloadModalLabel"
        aria-hidden="true" @if ($errors->any()) style="display: block;" @endif>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="onloadModalLabel">Verify Mobile</h5>
                </div>
                <div class="modal-body">
                    <form id="onloadForms" action="{{ route('save.mobileNumbersd') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="inputFieldMobileNumbers">Enter Mobile Number</label>
                            <input type="text" class="form-control" name="phone_number" id="inputFieldMobileNumbers" pattern="\d{10}" required title="Please enter a valid 10-digit mobile number." />
                            @if ($errors->has('phone_number'))
                                <div class="invalid-feedback" style="display: block;">
                                    {{ $errors->first('phone_number') }}
                                </div>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="onloadModalMobileUpdate" tabindex="-1" role="dialog" aria-labelledby="onloadModalUpdateLabel" aria-hidden="true" @if ($errors->any()) style="display: block;" @endif>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="onloadModalUpdateLabel">Verify Mobile</h5>
                </div>
                <div class="modal-body">
                    <form id="onloadForms" action="{{ route('save.mobileNumberOTP') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="inputFieldMobileNumbers">Enter Mobile Number</label>
                            <input type="text" class="form-control" name="phone_number" value="{{ auth()->user()->phone_number }}" id="inputFieldMobileNumbersOTP" pattern="\d{10}" required title="Please enter a valid 10-digit mobile number." />
                            @if ($errors->has('phone_number'))
                                <div class="invalid-feedback" style="display: block;">
                                    {{ $errors->first('phone_number') }}
                                </div>
                            @endif
                            <label for="inputFieldMobileNumbers">Enter OTP</label>
                            <input type="text" class="form-control" name="otp" id="inputFieldMobieOtp" required title="Please Enter OTP." />
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var closeProfileModalButton = document.getElementById('closeProfileModalButton');
            var profileModal = document.getElementById('profileModal');

            closeProfileModalButton.addEventListener('click', function() {
                var modalInstance = bootstrap.Modal.getInstance(profileModal);
                if (modalInstance) {
                    modalInstance.hide();
                } else {
                    modalInstance = new bootstrap.Modal(profileModal);
                    modalInstance.hide();
                }
            });
        });
    </script>



    <script type="text/javascript">
        document.addEventListener('change', function(event) {
            if (event.target.name === 'is_education_pursuing') {
                var education_end_date = document.getElementById('education_end_date');
                education_end_date.disabled = event.target.checked;
                if (event.target.checked) {
                    education_end_date.value = '';
                }
            }
        });

        $(document).ready(function() {
            $('#userPersonalDetailUpdateForm input[name="email"]').removeAttr('readonly');
            $('#userPersonalDetailUpdateForm input[name="phone_number"]').removeAttr('readonly');
            @if(optional($user->student->addressDetails->firstWhere('type', 'current'))->state)
            $(document).ready(function(){
                $('#current_state').trigger('change');
            });
            @endif
            @if(optional($user->student->addressDetails->firstWhere('type', 'permanent'))->state)
            $(document).ready(function(){
                $('#permanent_state').trigger('change');
            });
            @endif
            $('#current_state').on('change', function(e) {
                e.preventDefault();
                var state_id = $(this).val();
                $.post("{{ route('Student.district') }}", {
                        stateId: state_id
                    })
                    .done(function(response) {
                        $('#current_district').find('option').remove();
                        $('#current_district').append(`<option value="" data-id="" >Select Current District</option>`);
                        $.each(response, function(index, value) {
                            $("#current_district").append(`<option value='${value.id}' data-val='${value.id}' data-state='${value.state_id}' >${value.name}</option>`);
                        });
                    })
                    .fail(function(xhr, status, error) {
                        console.error('Error occurred:', error);
                    });
            });
            $('#permanent_state').on('change', function(e) {
                e.preventDefault();
                var state_id = $(this).val();
                $.post("{{ route('Student.district') }}", {
                        stateId: state_id
                    })
                    .done(function(response) {
                        $('#permanent_district').find('option').remove();
                        $('#permanent_district').append(`<option value="" data-val="" >Select Permanent District</option>`);
                        $.each(response, function(index, value) {
                            $("#permanent_district").append(`<option value='${value.id}' data-state='${value.state_id}' >${value.name}</option>`);
                        });
                    })
                    .fail(function(xhr, status, error) {
                        console.error('Error occurred:', error);
                    });
            });
            document.getElementById('education_state').addEventListener('change', function() {
                const education_state = this.options[this.selectedIndex].getAttribute('data-val');
                document.getElementById('education_district').selectedIndex = 0;
                document.querySelectorAll('#education_district option').forEach(function(option) {
                    if (option.getAttribute('data-state') === education_state) {
                        option.disabled = false;
                        option.style.visibility = 'visible';
                        option.style.display = 'block';
                    } else {
                        option.disabled = true;
                        option.style.visibility = 'hidden';
                        option.style.display = 'none';
                    }
                });
            });
            $('#education_start_date').on('change', function() {
                var education_start_date = new Date($(this).val());
                $('#education_end_date').prop('min', $(this).val());
                var education_end_date = new Date($('#education_end_date').val());
                if (education_end_date < education_start_date) {
                    $('#education_end_date').val('');
                }
            });

            $('#education_end_date').on('change', function() {
                var education_start_date = new Date($('#education_start_date').val());
                var education_end_date = new Date($(this).val());
                if (education_end_date < education_start_date) {
                    $(this).val($('#education_start_date').val());
                }
            });
            $('#joining_date').on('change', function() {
                var joining_date = new Date($(this).val());
                $('#end_date').prop('min', $(this).val());
                var end_date = new Date($('#end_date').val());
                if (end_date < joining_date) {
                    $('#end_date').val('');
                }
            });

            $('#end_date').on('change', function() {
                var joining_date = new Date($('#joining_date').val());
                var end_date = new Date($(this).val());
                if (end_date < joining_date) {
                    $(this).val($('#joining_date').val());
                }
            });
        });
    </script>



    <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-validate-size/dist/filepond-plugin-image-validate-size.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropper/4.1.0/cropper.min.js"></script>

    <script type="text/javascript">
        $.fn.filepond.registerPlugin(
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType,
            FilePondPluginImageValidateSize,
            FilePondPluginFileEncode
        );

        FilePond.create(
            document.querySelector('.upload-photo'), {
                labelIdle: '<div class="uploading-frame">Drag your photo here or <span class="filepond--label-action fontDarkOrange"> Click to upload </span></div>',
                checkValidity: true,
                dropValidation: true,
                acceptedFileTypes: ['image/png', 'image/jpeg'],
                imageValidateSizeMinWidth: 200,
                imageValidateSizeMinHeight: 200,
                maxFileSize: '5MB',
                labelMaxFileSize: 'Maximum file size allowed is {filesize}',
                labelFileProcessing: 'Generating file for cropping',
                labelFileProcessingComplete: 'Click to upload new Image.',
                server: {
                    process: function(fieldName, file, metadata, load, error, progress, abort) {
                        load();
                    },
                    fetch: null,
                    revert: null
                }
            });

        const pond = document.querySelector('.filepond--root');
        var photo_crop_container = $('.photo-crop-container');
        var crop_preview_cont = photo_crop_container.find('.crop-preview-cont');
        var filepond_img_Container = $('.img_container')
        var photo_preview_container = $('#previewProfile');
        var img_cropping = '';

        if (pond) {
            pond.addEventListener('FilePond:processfile', function(e, file) {
                crop_preview_cont.slideDown('slow');
                const image = new Image();
                image.src = URL.createObjectURL(e.detail.file.file);
                filepond_img_Container.append(image);
                img_cropping = filepond_img_Container.find('img');
                img_cropping.attr('src', image.src);

                img_cropping.cropper({
                    viewMode: 2,
                    dragMode: 'move',
                    aspectRatio: 1 / 1,
                    guides: false,
                    cropBoxResizable: true,
                    zoomable: true,
                    zoomOnWheel: true,
                    minCropBoxWidth: 200,
                    minCropBoxHeight: 200,
                });
                var cropper = img_cropping.data('cropper');

                var zoomInBtn = document.getElementById('zoomInBtn1');
                var zoomOutBtn = document.getElementById('zoomOutBtn1');

                zoomInBtn.addEventListener('click', function() {
                    cropper.zoom(0.1); 
                });

                zoomOutBtn.addEventListener('click', function() {
                    cropper.zoom(-0.1); 
                });
                var cropped_img = '';
                $('#crop_img').on('click', function(ev) {
                    $('html,body').animate({
                            scrollTop: $(".photo-crop-container").offset().top - 80
                        },
                        'slow');
                    photo_crop_container.addClass('show-loader show-result');
                    cropped_img = img_cropping.cropper('getCroppedCanvas', {
                        width: 500,
                        height: 500,
                        imageSmoothingEnabled: false,
                        imageSmoothingQuality: 'high',
                    }).toDataURL('image/jpeg/png/svg/jpg');

                    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    photo_preview_container.html('').append('<img src=""/>');

                    var formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('avatar', dataURItoBlob(cropped_img));
                    sendImageToServer(formData);

                    photo_preview_container.find('img').attr('src', cropped_img);
                    setTimeout(function() {
                        photo_crop_container.removeClass('show-loader');
                    }, 1800);
                });
            });

            pond.addEventListener('FilePond:removefile', function(e) {
                setTimeout(function() {
                    photo_crop_container.removeClass('show-result');
                }, 1000);
                crop_preview_cont.slideUp();
                img_cropping.cropper('destroy').html('');
                photo_preview_container.html('');
                filepond_img_Container.html('');
            });
        }
    </script>
    <script type="text/javascript">
        function validateNumericInput(input) {
            input.value = input.value.replace(/[^0-9.]/g, '');

            const parts = input.value.split('.');
            if (parts.length > 2) {
                input.value = parts[0] + '.' + parts.slice(1).join('');
            }
        }
    </script>

    <script type="text/javascript">
        function toggleWorkedTill(req) {
            var workedTillContainer = document.getElementById("workedTillContainer");
            var workedTillContainer2 = document.getElementById("workedTillContainer2");
            if (req.value == '1') {
                workedTillContainer2.style.display = "none";
                workedTillContainer.style.display = "none";
            } else {
                workedTillContainer2.style.display = "block";
                workedTillContainer.style.display = "block";
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#graduationEndDateContainer').hide();
            $('#is_graduation_pursuing').change(function() {
                $('#graduationEndDateContainer').toggle(!this.checked);
            });
            $('#is_graduation_pursuing').change();
        });
    </script>

    <script>
        const container = document.getElementById("profileModalLabel");
        const modal = new bootstrap.Modal(container);

        document.getElementById("closeApplyModalButton").addEventListener("click", function() {
            modal.hide();
        });
    </script>

    @if (auth()->user()->phone_number == '' || auth()->user()->phone_number == null)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                
                var onloadModal = new bootstrap.Modal(document.getElementById('onloadModalMobile'), {
                    backdrop: 'static', 
                    keyboard: false 
                });
                <?php if(!in_array(auth()->user()->id,[316])) { ?>
                onloadModal.show();
                <?php } ?>
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            });
        </script>
    @endif






@endsection
