@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Filter Settings'))
<style>

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
                        <div class="position-relative">
                            <img src="{{ $admin->image()->first()?->path && Storage::exists($admin->image()->first()?->path) ? Storage::url($admin->image()->first()?->path) : asset('assets/default_user.png') }}"
                                alt="profile" class="rounded-circle mr-3 profile-img"
                                style="width: 100px; height: 100px; object-fit: cover;" id="profileImage">
                            <input type="file" id="imageInput" accept="image/*" class="d-none">
                            <i class="fa fa-solid fa-pen edit-icon"></i>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row mt-3 pl-2">
        <div class=" col-sm-12 col-md-6 justify-content-center">
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
                                        {{ $admin->agent_agency?->agencyRegister?->business_name }}
                                    </div>
                                </div>
                                @endif


                                <div class="col-12 basic-info-row row ">
                                    <div class="col-6 info-heading">
                                        Country
                                    </div>
                                    <div class="col-6 info-value dimmed-text-color ">
                                        {{ ucwords($admin->country) }}
                                    </div>
                                </div>
                                <div class="col-12 basic-info-row row ">
                                    <div class="col-6 info-heading">
                                        Timezone
                                    </div>
                                    <div class="col-6 info-value dimmed-text-color ">
                                        Africa / Johannesburg
                                    </div>
                                </div>

                                <div class="col-12 basic-info-row row ">
                                    <div class="col-6 info-heading">
                                        Time
                                    </div>
                                    <div class="col-6 info-value dimmed-text-color ">
                                        09:17:33 AM
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>



            </div>
        </div>

        <div class="col-6  row">
            <div class="col-sm-12 ">
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
                                    <p class="clickable-link mb-0"> Change Password
                                    </p>
                                </div>
                            </div>
                            <div class="col-12 basic-info-row row ">
                                <div class="col-6 info-heading">
                                    WhatsApp Notification

                                </div>
                                <div class="col-6 info-value dimmed-text-color ">
                                    <span class="switch_text active-label">Disable</span>

                                    <label class="switch ml-2 mt-1">
                                        <input type="checkbox" name="status_whatsweb" value="1" id="status_whatsweb"
                                            data-id="1" data-datatable="adminsubuser-property" />
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="switch_text">Enable</span>

                                </div>

                            </div>

                            <div class="col-12 basic-info-row row ">
                                <div class="col-6 info-heading">
                                    Time
                                </div>
                                <div class="col-6 info-value dimmed-text-color ">
                                    09:17:33 AM
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
                            <h5 class="custom-heading-property">Business Information
                                :-</h5>
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


    <div class="row mt-3">
        <!-- <div class="col-sm-12 col-md-6">
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
                                {{ $admin->agent_agency?->agencyRegister?->business_name }}
                            </div>
                        </div>
                        @endif


                        <div class="col-12 basic-info-row row ">
                            <div class="col-6 info-heading">
                                Country
                            </div>
                            <div class="col-6 info-value dimmed-text-color ">
                                {{ ucwords($admin->country) }}
                            </div>
                        </div>
                        <div class="col-12 basic-info-row row ">
                            <div class="col-6 info-heading">
                                Timezone
                            </div>
                            <div class="col-6 info-value dimmed-text-color ">
                                Africa / Johannesburg
                            </div>
                        </div>

                        <div class="col-12 basic-info-row row ">
                            <div class="col-6 info-heading">
                                Time
                            </div>
                            <div class="col-6 info-value dimmed-text-color ">
                                09:17:33 AM
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div> -->


        <!-- @if ($admin->hasRole('agency'))

        <div class="col-sm-12 col-md-6 mt-2">
            <div class="card">
                <div class="card-body">

                    <div class="col-md-12 mt-2">
                        <h5 class="custom-heading-property">Business Information
                            :-</h5>
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
        @endif -->

    </div>

</div>




</div>

@push('custom-script')
<script type="text/javascript">
const uploadProfileUrl = `{{ route('adminSubUser.setting.uploadProfile') }}`;
$(document).ready(function() {

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

    $('#status_whatsweb').change(function() {
        status_whatsweb_check();
    });
    status_whatsweb_check();

    function status_whatsweb_check() {
        value = $('#status_whatsweb').prop('checked');
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
                        location.reload();
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

    $('#profileImage').on('click', function() {
        $('#imageInput').click();
    });

    $('#imageInput').change(function(event) {
        var file = event.target.files[0];
        if (file) {
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
                    $('#profileImage').attr('src', response.path);
                },
                error: function() {
                    console.error('Upload failed');
                }
            });
        }
    });
});
</script>
@endpush
@endsection
