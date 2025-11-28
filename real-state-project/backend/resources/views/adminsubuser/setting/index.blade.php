@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Filter Settings'))

@push('custom-css')
    <!-- Include Cropper.js CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">

    <!-- Include Cropper.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endpush
    <style>
    .image_preview {
        position: relative;
        display: inline-block;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        background-size: cover;
        transition: opacity 0.3s ease;
    }

    </style>

<div class="content-wrapper setting-container">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Details</li>
            </ol>
        </nav>
    </div>
    <div class="card mt-3">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="cursor-pointer d-flex align-items-center justify-content-center flex-column"
                        id="image-container">
                        <div class="position-relative" >
                            <!--{{ ($admin->image()->first()?->path) }} -->
                            <img src="{{ $admin->image()->first()?->path && Storage::exists($admin->image()->first()?->path) ? Storage::url($admin->image()->first()?->path) : asset('assets/default_user.png') }}"
                                alt="profile"
                                class="rounded-circle mr-3 profile-img"
                                style="width: 100px; height: 100px; object-fit: cover;">
                            <i class="fa fa-solid fa-pen edit-icon" id="profileImageIcon"></i>
                        </div>
                        <h5 class="my-2">{{ $admin->name }} </h5>
                        <div class="contact-details text-center">
                            <span class="row email d-block mt-1 px-2">
                                <i class="fa fa-envelope fa_dash mr-2"></i>
                                {{ $admin->email }}
                            </span>
                            <span class="row phone d-block mt-1 px-2">
                                <i class="fa fa-phone fa_dash mr-2"></i>
                                {{ $admin->dial_code . ' ' . $admin->phone }}
                            </span>
                            @if ($admin->hasRole('agency'))
                                @if($admin?->agencyRegister)
                                    <span class="location d-block mt-1 px-2">
                                        <i class="fa fa-map-marker fa_dash mr-2"></i>
                                        {{ $admin?->agencyRegister?->street_address . ' ' . $admin?->agencyRegister?->street_address_2 . ' ' . $admin?->agencyRegister?->city_?->name . ' ' . $admin?->agencyRegister?->state_?->name . ' ' . $admin?->agencyRegister?->postal_code . ' ' . $admin?->agencyRegister?->country_?->name }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        $role = Auth::user()->getRoleNames()->first();
    @endphp
    <div class="row mt-3 pl-2 w-100">
        <div class=" col-sm-12 col-md-6 justify-content-center px-0">
            <div class="row property-details-container">

                <div class="col-sm-12 col-lg-6 mb-2">
                    <div class="card p-3 count-card">
                        <a class="d-flex align-items-center">
                            <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center">
                                <i class="fa fa-building text-white" style="font-size: 40px;"></i>
                            </div>
                            <div class="">
                                <h6 class="heading font-regular font-weight-bold mb-1">Listed Properties</h6>
                                <h3 class="font-weight-bold mb-0">{{ $data['totalProperty'] }}</h3>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-6 mb-2">
                    <div class="card p-3 count-card">
                        <a class="d-flex align-items-center">
                            <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center"
                                style="padding: 15px;">
                                <i class="fa fa-building text-white" style="font-size: 30px;"></i>
                                <i class="fa fa-user text-white" style="font-size: 30px;"></i>
                            </div>
                            <div class="">
                                <h6 class="heading font-regular font-weight-bold mb-1">Matched Properties</h6>
                                <h3 class="font-weight-bold mb-0">{{ $data['totalMatchProperties'] }}</h3>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="col-md-12 mt-2">
                                <h5 class="custom-heading-property">Basic Info:-</h5>
                            </div>
                            <div class="row basic-info-container">

                                @if ($admin->hasRole('agency'))
                                    <div class="col-12 basic-info-row row ">
                                        <div class="col-6 info-heading">
                                            Business Name
                                        </div>
                                        <div class="col-6 info-value dimmed-text-color ">
                                            {{ ucwords($admin?->agencyRegister?->business_name??"-") }}
                                        </div>
                                    </div>

                                    <div class="col-12 basic-info-row row ">
                                        <div class="col-6 info-heading">
                                            Message
                                        </div>
                                        <div class="col-6 info-value dimmed-text-color ">
                                            {{ ucwords($admin?->agencyRegister?->message??"-") }}
                                        </div>
                                    </div>
                                @endif

                                @if ($admin->hasRole('agent'))
                                    <div class="col-12 basic-info-row row ">
                                        <div class="col-6 info-heading">
                                            Agency Name
                                        </div>
                                        <div class="col-6 info-value dimmed-text-color ">
                                            {{ $admin->agent_agency?->agencyRegister?->business_name??"-" }}
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12 basic-info-row row ">
                                    <div class="col-6 info-heading">
                                        Country
                                    </div>
                                    <div class="col-6 info-value dimmed-text-color ">
                                        {{ ucwords($admin->country??"-") }}
                                    </div>
                                </div>
                                <div class="col-12 basic-info-row row ">
                                    <div class="col-6 info-heading">
                                        Timezone
                                    </div>
                                    <div class="col-6 info-value dimmed-text-color current_timezone_date">
                                    </div>
                                </div>

                                <div class="col-12 basic-info-row row ">
                                    <div class="col-6 info-heading">
                                        Time
                                    </div>
                                    <div class="col-6 info-value dimmed-text-color current_timezone_time">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class=" col-sm-12 col-md-6 mt-0 px-0">
            <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <div class="col-md-12 mt-2">
                        <h5 class="custom-heading-property">Setting:-</h5>
                    </div>
                    <div class="row basic-info-container">
                        <div class="col-12 basic-info-row row ">
                            <div class="col-6 info-heading">
                                Password
                            </div>
                            <div class="col-6 info-value dimmed-text-color ">
                                <p class="clickable-link mb-0 reset_btn" > Change Password</p>
                            </div>
                        </div>
                        @if ($role != 'agency')
                        <div class="col-12 basic-info-row row ">
                            <div class="col-6 info-heading">
                                WhatsApp Notification
                            </div>
                            <form name="update_admin_whatsapp_notification" class="col-6 info-value dimmed-text-color ">
                                <input type="hidden" name="id" value="{{ $admin->id }}">
                                <span class="switch_text @if(!$admin->is_whatsapp_notification) active-label @endif">Disable</span>
                                <label class="switch ml-2 mt-1">
                                    <input
                                        type="checkbox"
                                        name="statusWhatsAppWeb"
                                        value="1"
                                        id="statusWhatsAppWeb"
                                        data-id="1"
                                        data-datatable="adminsubuser-property"
                                        @if ($admin->is_whatsapp_notification) checked @endif
                                        />
                                    <span class="slider round"></span>
                                </label>
                                <span class="switch_text @if ($admin->is_whatsapp_notification) active-label @endif">Enable</span>
                                <button type="submit" class="btn d-none" id="submitWhatsApp"></button>
                            </form>
                        </div>
                        @endif
                        <div class="col-12 basic-info-row row ">
                            <div class="col-6 info-heading">
                                System Date & Time
                            </div>
                            <div class="col-6 info-value dimmed-text-color utl_timezone_time">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @if ($admin->hasRole('agency'))
            <div class="col-sm-12  mt-2">
                <div class="card">
                    <div class="card-body">
                        <div class="col-md-12 mt-2">
                            <h5 class="custom-heading-property">Business Information :-</h5>
                        </div>
                        <div class="row basic-info-container">
                            <div class="col-12 basic-info-row row ">
                                <div class="col-6 info-heading">
                                    Director / Owner ID Number
                                </div>
                                <div class="col-6 info-value dimmed-text-color ">
                                    {{ $admin?->agencyRegister?->id_number??"-" }}
                                </div>
                            </div>
                            <div class="col-12 basic-info-row row ">
                                <div class="col-6 info-heading">
                                    Company Registration Number
                                </div>
                                <div class="col-6 info-value dimmed-text-color ">
                                    {{ $admin?->agencyRegister?->registration_number??"-" }}
                                </div>
                            </div>
                            <div class="col-12 basic-info-row row ">
                                <div class="col-6 info-heading">
                                    Company VAT Number
                                </div>
                                <div class="col-6 info-value dimmed-text-color ">
                                    {{ $admin?->agencyRegister?->vat_number??"-" }} </div>
                            </div>
                            <div class="col-12 basic-info-row row ">
                                <div class="col-6 info-heading">
                                    Type of Business
                                </div>
                                <div class="col-6 info-value dimmed-text-color ">
                                    {{ $admin?->agencyRegister?->type_of_business??"-" }} </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>

<div class="modal fade" id="reset_password" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form name="reset_password" id="reset_password_form">
                <div class="modal-header plan_name">
                    <div class="row">
                        <div class="col-10">
                            <h5 class="modal-title" id="plan_name">Reset Password</h5>
                        </div>
                        <div class="col-2 " style="padding-right:20px;">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>

                </div>
                <div class="modal-body">
                    @csrf
                    <div class="form-group position-relative">
                        <label for="exampleInputName1"> Old Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="old_pass" id="old_pass"
                            placeholder="***********">
                        <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                    </div>
                    <div class="form-group position-relative">
                        <label for="exampleInputName1"> New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_pass" id="new_pass"
                            placeholder="***********">
                        <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                    </div>
                    <div class="form-group position-relative">
                        <label for="exampleInputName1"> Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="con_pass" id="con_pass"
                            placeholder="***********">
                        <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                    </div>
                </div>
                <div class="alert alert-success success_msg" role="alert"></div>
                <div class="alert alert-danger error_msg" role="alert"></div>
                <div class="modal-footer">
                    {{-- <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button> --}}
                    <button type="submit" class="btn theme_btn_1">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="profileImageModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form name="user_details" id="user_details" enctype="multipart/form-data">
                <div class="modal-header plan_name">
                    <div class="row">
                        <h5 class="modal-title col-md-10" >Profile Details</h5>
                        <button type="button" class="close col-2" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="position-relative text-center w-100 img-container">
                            <img src="{{
                                $admin->image()->first()?->path && Storage::exists($admin->image()->first()?->path)
                                ? Storage::url($admin->image()->first()?->path)
                                : asset('assets/default_user.png')
                                }}"
                                alt="profile"
                                class="rounded-circle mr-3 profile-img image-preview image_preview"
                                style="width: 100px; height: 100px; object-fit: cover;"
                                id="image-preview">
                        </div>

                        <div class="form-group col-md-12 d-none">
                            <label for="imageInput">Profile Image</label>
                            <input type="file" class="form-control" name="profile_image" accept="image/*" id="imageInput">
                        </div>

                        <div class="form-group col-md-12">
                            <label for="profile_user_name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="profile_user_name" placeholder="Enter name" value="{{$admin->name ?? ''}}" >
                        </div>
                    </div>
                </div>
                <div class="alert alert-success success_msg" role="alert"></div>
                <div class="alert alert-danger error_msg" role="alert"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn theme_btn_1" id>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- <div class="modal fade" id="CropperModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg h-100 modal-dialog-scrollable" role="document">
        <div class="modal-content">
                <div class="modal-header plan_name">
                    <div class="row">
                        <h5 class="modal-title col-md-10" >Crop Image</h5>
                        <button type="button" class="close col-2" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body overflow-auto p-4">
                    <div class="row">
                        <div class="position-relative text-center w-100 img-container h-auto text-center ">
                            <img src="{{
                                $admin->image()->first()?->path && Storage::exists($admin->image()->first()?->path)
                                ? Storage::url($admin->image()->first()?->path)
                                : asset('assets/default_user.png')
                                }}"
                                alt="profile"
                                class="rounded-circle mr-3 profile-img image-preview w-25 h-auto"
                                style="max-width: 100%; max-height: fit-content;"
                                id="image-preview"
                                >
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn theme_btn_1" id="saveCropButton" >Save Crop</button>
                </div>
        </div>
    </div>
</div> --}}

</div>
@push('custom-script')
<script type="text/javascript">
const uploadProfileUrl = `{{ route('adminSubUser.setting.uploadProfile') }}`;
$(document).ready(function() {

    let cropper;
    let zoomLevel = 1;

    $(document).on('click', '#profileImageIcon', function () {
        $('#profileImageModel').modal('show');
    });

    $(".image_preview").each(function() {
        // Store the original image source
        var originalSrc = $(this).attr("src");
        $(".image_preview").hover(
            function() {
                $(this).css({
                    "cursor": "pointer",
                    "opacity": "0.4"
                });
                $(this).attr("src", "https://www.pngmart.com/files/23/Edit-Icon-PNG-Isolated-HD.png");
            },
            function() {
                $(this).css({
                    "opacity": "1",
                    "cursor": "auto",
                });
                $(this).attr("src", originalSrc);
            });
        });

    $("form[name='update_admin_credential']").validate({
        rules: {
            name: {
                required: true,
            },
            email: {
                required: true,
            }
        },
        messages: {
            name: {
                required: 'Enter name'
            },
            email: {
                required: 'Enter email'
            },
        },
        submitHandler: function(form) {
            $.ajax({
                url: "{{ route('adminSubUser.setting.update_admin_credential') }}",
                type: "POST",
                data: new FormData(form),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status == 'success') {
                        ToastAlert(msg = response.msg, cls = "success");
                        $("form[name='update_admin_credential']").find(
                            '.serverside_error').remove();
                        location.reload();
                    } else {
                        ToastAlert(msg = response.msg, cls = "error");
                    }
                },
                error: function(xhr, status, error) {
                    handleServerError('update_admin_credential', xhr.responseJSON
                        .errors);
                }
            });
        }
    });

    $("form[name='reset_password']").validate({
        rules: {
            old_pass: {
                required: true,
            },
            new_pass: {
                required: true,
            },
            con_pass: {
                required: true,
            }

            ,
        },
        messages: {
            old_pass: {
                required: 'Enter old password'
            },
            new_pass: {
                required: 'Enter new password'
            },
            con_pass: {
                required: 'Enter confirm password'
            },
        },
        submitHandler: function(form) {
            $.ajax({
                url: "{{ route('adminSubUser.setting.reset_password') }}",
                type: "POST",
                data: new FormData(form),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status == 'success') {
                        $("form[name='reset_password']").find('.serverside_error')
                            .remove();
                        $('#reset_password').modal('hide')
                        $('#reset_password_form')[0].reset();
                        ToastAlert(msg = response.msg, cls = "success");

                    } else {
                        ToastAlert(msg = response.msg, cls = "error");
                    }
                },
                error: function(xhr, status, error) {
                    handleServerError('reset_password', xhr.responseJSON.errors);
                }
            });
        }
    });

    $(document).on('click', '.reset_btn', function() {
        $('#reset_password').modal('show')
    })

    $('#statusWhatsAppWeb').change(function() {
        status_whats_app_web_check();
        $('.switch_text').toggleClass('active-label');
        $('#submitWhatsApp').submit();
    });

    status_whats_app_web_check();

    function status_whats_app_web_check() {
        value = $('#statusWhatsAppWeb').prop('checked');
        if (value) {
            $('.slot_type').css('display', '');
            $('.slot_whatsweb').css('display', '');
        } else {
            $('.slot_type').css('display', 'none');
            $('.slot_whatsweb').css('display', 'none');
        }
    }

    $('#is_slot').change(function() {
        is_slot();
    });

    is_slot();

    function is_slot() {
        value = $('#is_slot').val();
        if (value == 'all_time') {
            $('.slot_whatsweb').css('display', 'none');
        } else {
            $('.slot_whatsweb').css('display', '');
        }
    }

    $('input.timepicker').timepicker({});

    $("form[name='update_admin_whatsapp_notification']").validate({

        submitHandler: function(form) {
            $.ajax({
                url: "{{ route('adminSubUser.setting.whatsApp_notification') }}",
                type: "POST",
                data: new FormData(form),
                processData: false,
                contentType: false,
                success: function(response) {
                    $("form[name='update_admin_whatsapp_notification']").find(
                        '.serverside_error').remove();
                    if (response.status == 'success') {
                        ToastAlert(msg = response.msg, cls = "success");
                        $("form[name='update_admin_whatsapp_notification']").find(
                            '.serverside_error').remove();
                        // location.reload();
                    } else {
                        ToastAlert(msg = response.msg, cls = "error");
                    }
                },
                error: function(xhr, status, error) {
                    handleServerError('update_admin_whatsapp_notification', xhr
                        .responseJSON
                        .errors);
                }
            });
        }
    });

    // {{--
    $('#profileImageIcon').on('click', function() {
        $('#imageInput').click();
    });
    // --}}

     $('#image-preview').on('click', function() {
         $('#imageInput').click();
     });

    $('#imageInput').change(function(event) {
        // {{-- $('#profileImageModel').modal('hide'); --}}
        // {{-- $('#CropperModel').modal('show'); --}}
        var file = event.target.files[0];
        if (file) {
            // {{-- loadImage(event);--}}
            uploadImage(file);
        }
    });

    $("form[name='user_details']").validate({
        rules: {
            name: {
                required: true
            },
        },
        submitHandler: function (form, e) {
            e.preventDefault();
            $('.theme_btn_1').prop('disabled', true);
            var submitButton = $(form).find('button[type="submit"]');
            submitButton.prop('disabled', true);
            $.ajax({
                url: "{{ route('adminSubUser.setting.update_admin_credential') }}",
                type: "POST",
                data: $(form).serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        setTimeout(function () {
                            $('#profileImageModel').modal('hide');
                        }, 500);
                        $("form[name='user_details']").find('.serverside_error')
                            .remove();
                        $('.success_msg').html(response.msg);
                        $('.success_msg').fadeIn();
                        setTimeout(function () {
                            $('.success_msg').fadeOut();
                        }, 3000);
                        $('#matchedproperty').DataTable().ajax.reload();
                        $('#user_details')[0].reset();
                        // $('#saveCropButton').click();
                        window.location.reload();
                    } else {
                        $("form[name='user_details']").find('.serverside_error')
                            .remove();
                        $('.error_msg').html(response.msg);
                        $('.error_msg').fadeIn();
                        setTimeout(function () {
                            $('.error_msg').fadeOut();
                        }, 3000);
                    }
                    $('input[name=search]').keyup();
                    $('.theme_btn_1').prop('disabled', false);
                },
                error: function (xhr, status, error) {
                    handleServerError('user_details', xhr.responseJSON.errors);
                    $('.theme_btn_1').prop('disabled', true);
                }
            });
        }
    });

    function uploadImage(file){
        var formData = new FormData();
        formData.append('profile_image', file);
        $.ajax({
            url: uploadProfileUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var newImageURL = URL.createObjectURL(file);
                $('.profile-img').each(function () {
                    $(this).attr('src', response.path);
                });
            },
            error: function() {
                console.error('Upload failed');
            }
        });
    }

    function loadImage(event) {
        let image = document.getElementById('image-preview');
        if (!image) {
            console.error('Image preview element not found');
            return;
        }

        const file = event.target.files[0];
        image.src = URL.createObjectURL(file);

        if (typeof Cropper === 'undefined') {
            console.error('Cropper.js is not loaded');
            return;
        }

        image.onload = function () {
            if (window.cropper) {
                window.cropper.destroy();
            }
            window.cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                movable: true,
                zoomable: true,
                scalable: true,
                cropBoxResizable: true,
                dragMode: "move",
                background: false,
                autoCropArea: 0.7,
                viewBox: "circle",
                ready: function () {
                    this.cropper.cropBox.querySelector(
                    ".cropper-view-box"
                    ).style.borderRadius = "50%";
                },
                crop: function(event) {
                    console.info(event.detail.x, event.detail.y, event.detail.width, event.detail.height);
                }
            });
            zoomInOut();
        }
    }

    function getCroppedImage() {
        if (!window.cropper) {
            console.error('Cropper not initialized');
            return null;
        }
        const canvas = window.cropper.getCroppedCanvas({
            width: 300,
            height: 300,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            console.error('Failed to get cropped canvas');
            return null;
        }

        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                const file = new File([blob], 'profile_image.jpg', { type: 'image/jpeg' });
                resolve(file);
            }, 'image/jpeg', 0.95);
        });
    }

    function saveCroppedImage() {
        const croppedImage = getCroppedImage().then((blob) => {
            if (blob) {
                // Update preview with cropped image
                const previewUrl = URL.createObjectURL(blob);
                $('#image-preview').attr('src', previewUrl);

                // Upload the cropped image
                uploadImage(blob);

                // Optional: Clean up
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                $('#CropperModel').modal('hide');
                $('#profileImageModel').modal('show');
            }
        }).catch((error) => {
            console.error('Error processing cropped image:', error);
        });

        if (croppedImage) {
            fetch(croppedImage)
                .then(res => res.blob())
                .then(blob => uploadImage(blob));
        }
    }

    $('#saveCropButton').click(function() {
        saveCroppedImage();
    });

    const body = document.body;

    function zoomInOut() {
        zoomLevel = 1.01;
        body.style.transform = `scale(${zoomLevel})`;
        setTimeout(() => {
            zoomLevel = 1;
            body.style.transform = `scale(${zoomLevel})`;
        }, 300);
    }
});
</script>
@endpush
@endsection
