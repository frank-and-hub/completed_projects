@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Filter Settings'))
<style>
.profile-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #fff;
  border-radius: 10px;
  padding: 20px 30px;
  width: 100%;
}

.profile-details {
  display: flex;
  align-items: center;
  gap: 20px;
}

.profile-img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
}

.card_profile .info {
  display: flex;
  flex-direction: column;
}

.card_profile .name {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 10px;
}

.card_profile .role {
  display: inline-block;
  background-color: #f30051;
  color: #ffff;
  font-size: 12px;
  font-weight: bold;
  border-radius: 20px;
  padding: 5px 10px;
  margin-right: 10px;
}

.location,
.email,
.phone {
  font-size: 15px;
  color: #464e5f;
  /* margin: 3px 5px; */
  font-weight: 600;
}

.card_profile .stats {
  /* display: flex; */
  /* gap: 30px; */
  justify-content: right;
}

.card_profile .stat {
  text-align: left;
  border: 2px dotted #e4e6ef;
  padding: 10px 20px;
  border-radius: 5px;
}

.stat-number {
  font-size: 25px;
  font-weight: 900;
  color: #000;
  margin-bottom: 10px;
}

.card_profile .stat-label {
  font-size: 15px;
  color: #464e5f;
  font-weight: 600;
}

.profile_info {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}

.container_profile {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  margin-top: 20px;
}

.card_profile_form {
  background-color: white;
  border-radius: 8px;
  padding: 20px;
  width: 49%;
}

.card_profile_form h3 {
  color: #e91e63;
  margin-bottom: 15px;
  font-size: 18px;
}

.card_profile_form .form-group {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 15px;
}

.card_profile_form .form-control {
  flex: 1;
  display: flex;
  flex-direction: column;
  border: none;
  padding: 0px;
  margin: 0px 0px 15px 0px;
}

.card_profile_form .form-control label {
  font-weight: bold;
  margin-bottom: 15px;
  font-size: 14px;
}

.card_profile_form .form-control label span {
  color: #e91e63;
}

.card_profile_form .form-control input,
.form-control select {
  padding: 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.card_profile_form .btn-reset {
  background-color: #fdd6dd;
  border: none;
  color: #e91e63;
  padding: 10px 15px;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  margin-left: auto;
}

.card_profile_form .btn-reset:hover {
  background-color: #e91e63;
  color: white;
}

.card_profile_form .toggle-group {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.card_profile_form .toggle-group input[type="radio"] {
  display: none;
}

.card_profile_form .toggle-group label {
  cursor: pointer;
  font-weight: bold;
  color: #aaa;
}

.card_profile_form .toggle-group input:checked + label {
  color: #e91e63;
}

.card_profile_form .time-group {
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.switch {
  position: relative;
  display: inline-block;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.switch_flex {
  display: flex;
  align-items: center;
  flex-direction: row !important;
}

.switch_flex .switch_text {
  margin-right: 10px;
}

.switch_flex .switch {
  margin: 0 10px 0 0px !important;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

input:checked + .slider {
  background-color: #e91e63;
}

input:focus + .slider {
  box-shadow: 0 0 1px #e91e63;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

.fa_dash {
  color: #adb2ca !important;
}

.info-item {
  padding: 10px 0;
  border-bottom: 1px solid #ddd;
}

.info-label {
  font-weight: bold;
  color: #555;
  flex: 1;
}

.info-value {
  text-align: right;
  color: #333;
  flex: 1;
}

.card-title {
  font-size: 18px;
  font-weight: bold;
  color: #333;
}

.template-demo {
  padding-top: 10px;
}

@media (max-width: 768px) {
  .container_profile {
    flex-direction: column;
  }

  .card_profile_form {
    width: 100%;
  }

  .stats {
    flex-wrap: wrap;
  }

  .stat {
    margin-bottom: 10px;
    flex: 1 1 100%;
  }
}

</style>

<div class="content-wrapper">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Details</li>
            </ol>
        </nav>
    </div>

    <div class="row mt-2">
        <div class="col-12">

            <div class="card_profile">
                <div class="card-body_profile">
                    <div class="profile-card">
                        <div class="row w-100">
                            <!-- Profile Details -->
                            <div class="col-md-6 col-12 mb-0">
                                <div class="profile-details d-flex align-items-center h-100">
                                    <div class="cursor-pointer" id="image-container">
                                        <img src="{{ $admin->image()->first()?->path && Storage::exists($admin->image()->first()?->path) ? Storage::url($admin->image()->first()?->path) : asset('assets/default_user.png') }}"
                                            alt="profile" class="rounded-circle mr-3 profile-img"
                                            style="width: 80px; height: 80px; object-fit: cover;" id="profileImage">
                                        <input type="file" id="imageInput" accept="image/*" class="d-none">
                                    </div>
                                    <div class="info">
                                        <span class="name d-block font-weight-bold">{{ $admin->name }}</span>
                                        <div class="profile_info">
                                            <div class="landlordProfile d-flex align-items-center px-2">
                                                <i class="fa fa-user fa_dash mr-2" aria-hidden="true"></i>
                                                <span class="role">{{ $admin->designation() }}</span>
                                            </div>
                                            @if ($admin->hasRole('agency'))
                                                <span class="location d-block mt-1 px-2">
                                                    <i class="fa fa-map-marker fa_dash mr-2"></i>
                                                    {{ $admin->agencyRegister?->street_address . ' ' . $admin->agencyRegister?->street_address_2 . ' ' . $admin->agencyRegister?->city_?->name . ' ' . $admin->agencyRegister?->state_?->name . ',' . $admin->agencyRegister?->postal_code . ' ' . $admin->agencyRegister?->country_?->name }}
                                                </span>
                                            @endif
                                            <span class="email d-block mt-1 px-2">
                                                <i class="fa fa-envelope fa_dash mr-2"></i>
                                                {{ $admin->email }}
                                            </span>
                                            <span class="phone d-block mt-1 px-2">
                                                <i class="fa fa-phone fa_dash mr-2"></i>
                                                {{ $admin->dial_code . ' ' . $admin->phone }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Section -->
                            <div class="col-md-6 col-sm-12">
                                <div class="col-md-12 d-flex">
                                    <div class="stat col-md-4 col-sm-6 ml-2 mb-0">
                                        <div class="stat-number font-weight-bold">{{ $data['totalProperty'] }}</div>
                                        <div class="stat-label">Listed Property</div>
                                    </div>
                                    <div class="stat col-md-4 col-sm-6 ml-2 mb-0">
                                        <div class="stat-number font-weight-bold">{{ $data['totalMatchProperties'] }}
                                        </div>
                                        <div class="stat-label">Matched Property</div>
                                    </div>
                                    <div class="stat col-md-4 col-sm-6 ml-2 mb-0">
                                        <div class="stat-number font-weight-bold">Time</div>
                                        <div class="stat-label" style="overflow:hidden;" id="timezone_time"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container_profile">
                <!-- Admin Credential Card -->
                <div class="card_profile_form">
                    <h3>Admin Credential</h3>
                    <form name="update_admin_credential">
                        <input type="hidden" name="id" value="{{ $admin->id }}">
                        <div class="form-group">
                            <div class="form-control">
                                <label for="start_price">Name<span class="text-danger">*</span></label>
                                <input id="name" class="form-control" name="name" type="text"
                                    value ="{{ $admin->name }}">
                            </div>
                        </div>
                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                        <button type="submit" class="btn theme_btn_1">Update</button>
                        <a href="javascript:void(0)" class="btn reset_btn">Reset password</a>
                    </form>
                </div>
                @php
                    $role = Auth::user()->getRoleNames()->first();
                @endphp
                <!-- WhatsApp Notification Card -->
                @if ($role != 'agency')
                    <div class="card_profile_form">
                        <h3>WhatsApp Notification</h3>
                        <div class="">
                            <form name="update_admin_whatsapp_notification">
                                <input type="hidden" name="id" value="{{ $admin->id }}">
                                <div class="time-group">
                                    <div class="form-control">
                                        <label for="slot">Notification<span>*</span></label>
                                        <div class="switch_flex">
                                            <span class="switch_text">disable</span>
                                            <label class="switch ml-2 mt-1">
                                                <input type="checkbox" name="status_whatsweb" value="1"
                                                    id="status_whatsweb" data-id = "1"
                                                    data-datatable = "adminsubuser-property"
                                                    @if ($admin->is_whatsapp_notification) checked @endif>
                                                <span class="slider round"></span>
                                            </label>
                                            <span class="switch_text">enable</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn theme_btn_1">Update</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card mt-3">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-6">
                <div class="card-body">
                    <h4 class="card-title"><b>Basic Information</b></h4>
                    <div class="template-demo">
                        <div class="info-item d-flex justify-content-between">
                            <span class="info-label">Country</span>
                            <span class="info-value">{{ ucwords($admin->country) }}</span>
                        </div>

                        @if ($admin->hasRole('agency'))
                            <div class="info-item d-flex justify-content-between">
                                <span class="info-label">Business Name</span>
                                <span class="info-value">{{ ucwords($admin?->agencyRegister?->business_name) }}</span>
                            </div>
                            <div class="info-item d-flex justify-content-between">
                                <span class="info-label">Message</span>
                                <span class="info-value">{{ ucwords($admin?->agencyRegister?->message) }}</span>
                            </div>
                        @endif

                        @if ($admin->hasRole('agent'))
                            <div class="info-item d-flex justify-content-between">
                                <span class="info-label">Agency Name</span>
                                <span
                                    class="info-value">{{ $admin->agent_agency?->agencyRegister?->business_name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Business Information -->
            @if ($admin->hasRole('agency'))
                <div class="col-6">
                    <div class="card-body">
                        <h4 class="card-title"><b>Business Information</b></h4>
                        <div class="template-demo">
                            <div class="info-item d-flex justify-content-between">
                                <span class="info-label">Director / Owner ID Number</span>
                                <span class="info-value">{{ $admin?->agencyRegister?->id_number }}</span>
                            </div>
                            <div class="info-item d-flex justify-content-between">
                                <span class="info-label">Company Registration Number</span>
                                <span class="info-value">{{ $admin?->agencyRegister?->registration_number }}</span>
                            </div>
                            <div class="info-item d-flex justify-content-between">
                                <span class="info-label">Company VAT Number</span>
                                <span class="info-value">{{ $admin?->agencyRegister?->vat_number }}</span>
                            </div>
                            <div class="info-item d-flex justify-content-between">
                                <span class="info-label">Type of Business</span>
                                <span
                                    class="info-value">{{ ucwords($admin?->agencyRegister?->type_of_business) }}</span>
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
            },
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
            $('.switch_text').first().addClass('active-label');
            $('.switch_text').last().removeClass('active-label');
        } else {
            $('.switch_text').first().removeClass('active-label');
            $('.switch_text').last().addClass('active-label');
        }
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

    $('#profileImageIcon').on('click', function() {
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
