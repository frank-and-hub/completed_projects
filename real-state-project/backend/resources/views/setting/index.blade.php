@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Filter Settings'))
<div class="content-wrapper setting-container">
    <div class="page-header">
        <h3 class="page-title">
            Settings
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Details</li>
            </ol>
        </nav>
    </div>

    @php
        $role = Auth::user()->getRoleNames()->first();
    @endphp

    @if (auth()->guard('admin')->user()->hasRole('admin'))
    @endif
    <div class="card mt-3">
        <div class="card-body">
            <h4 class="card-title">Admin credential</h4>
            <form name="update_admin_credential">
                <input type="hidden" name="id" value="{{ $admin->id }}">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="start_price">Name<span class="text-danger">*</span></label>
                            <input id="name" class="form-control" name="name" type="text"
                                value ="{{ $admin->name }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="end_price"> Email <span class="text-danger">*</span></label>
                            <input id="email" class="form-control" name="email" type="text"
                                value ="{{ $admin->email }}">
                        </div>
                    </div>
                    <div class="col-4 d-flex align-items-end">
                        <div class="col-2">
                            <div class="form-group">
                                <button type="submit" class="btn theme_btn_1">Update</button>
                            </div>
                        </div>
                        <div class="col-2 ml-5">
                            <div class="form-group">
                                {{-- <a href="javascript:void(0)" class="btn reset_btn">Reset password</a> --}}
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-success success_msg" role="alert"></div>
                    <div class="alert alert-danger error_msg" role="alert"></div>
                </div>
            </form>
        </div>
    </div>







    <div class="row mt-3 pl-2 w-100">
        <div class=" col-sm-12 col-md-6 justify-content-center px-0">
            <div class="row property-details-container">
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
                                Map Enable/Disable
                            </div>
                            <form name="update_frontend_setting" class="col-6 info-value dimmed-text-color ">
                                <input type="hidden" name="id" value="{{ $admin->id }}">
                                <span class="switch_text @if(!$setting->is_map_show_frontend) active-label @endif">Disable</span>
                                <label class="switch ml-2 mt-1">
                                    <input
                                    type="checkbox"
                                    name="is_frontend_map"
                                    value="1"
                                    id="is_frontend_map"
                                    data-id="1"
                                    data-datatable="adminsubuser-property"
                                    @if ($setting->is_map_show_frontend) checked @endif
                                    >
                                    <span class="slider round"></span>
                                </label>
                                <span class="switch_text @if ($setting->is_map_show_frontend) active-label @endif">Enable</span>
                                <button type="submit" class="btn d-none" id="submitFrontendMap"></button>
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

        </div>
    </div>



</div>
<div class="modal fade" id="reset_password" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form name="reset_password" id="reset_password_form">
                <div class="modal-header plan_name">
                    <h5 class="modal-title" id="plan_name">Reset Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
                    <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn theme_btn_1">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('custom-script')
    @if (auth()->guard('admin')->user()->hasRole('admin'))
        <script type="text/javascript">
            $("form[name='property_price_range']").validate({
                rules: {
                    start_price: {
                        required: true,
                    },
                    end_price: {
                        required: true,
                    },
                },
                messages: {
                    start_price: {
                        required: 'Enter start price'
                    },
                    end_price: {
                        required: 'Enter end price'
                    },
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: "{{ route('property_price_update') }}",
                        type: "POST",
                        data: new FormData(form),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status == 'success') {
                                ToastAlert(msg = response.msg, cls = "success");
                                $("form[name='property_price_range']").find(
                                    '.serverside_error').remove();
                            } else {
                                ToastAlert(msg = response.msg, cls = "error");
                            }
                        },
                        error: function(xhr, status, error) {
                            handleServerError('property_price_range', xhr.responseJSON
                                .errors);
                        }
                    });
                }
            });

            $('#is_frontend_map').change(function() {
                $('.switch_text').toggleClass('active-label');
                $('#submitFrontendMap').submit();
            });
        </script>
    @endif
    <script type="text/javascript">
        $(document).ready(function() {
            $("form[name='update_admin_credential']").validate({
                rules: {
                    name: {
                        required: true,
                    },
                    email: {
                        required: true,
                    }

                    ,
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
                        url: "{{ route('update_admin_credential') }}",
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
                        url: "{{ route('reset_password') }}",
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


            $("form[name='update_frontend_setting']").validate({

                submitHandler: function(form) {
                    $.ajax({
                        url: "{{ route('frontend_setting') }}",
                        type: "POST",
                        data: new FormData(form),
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $("form[name='update_frontend_setting']").find(
                                '.serverside_error').remove();
                            if (response.status == 'success') {
                                ToastAlert(msg = response.msg, cls = "success");
                                $("form[name='update_frontend_setting']").find(
                                    '.serverside_error').remove();
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                ToastAlert(msg = response.msg, cls = "error");
                            }
                        },
                        error: function(xhr, status, error) {
                            handleServerError('update_frontend_setting', xhr.responseJSON
                                .errors);
                        }
                    });
                }
            });
        });
    </script>
@endpush
@endsection
