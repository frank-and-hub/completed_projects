@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . $active_page))
    @push('custom-css')
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

            #copyBtn{
                float: right;
                cursor: pointer;
                margin-right: 10px;
                margin-top: -32px;
                color: #F30051;
                border: none;
            }
        </style>
    @endpush
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item"><a href="#">Details</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ isset($external_property) ? (!isset($view) ? 'Edit' : 'View') : 'Add' }}
                    </li>
                </ol>
            </nav>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ $title }}
                        {{-- <div class="float-right pointer">
                            <a href="#">
                                <img src="{{ asset('/assets/admin/icon/postman-icon-svgrepo-com.svg') }}" style="width:2rem" />
                            </a>
                        </div> --}}
                        </h4>
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="change_status">Status </label>
                                <select id="change_status" class="form-control select2 form-select" data-coreui-search="true"
                                    data-live-search="true" name="status">
                                    <option value="" selected disabled>Select Status</option>
                                    <option value="1" @selected($status == '1') >Allowed</option>
                                    <option value="0" @selected($status == '0') >Decline</option>
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label for="copyInput">Link </label>
                                <input id="copyInput" autocomplete="off" class="form-control" type="text" readonly
                                    value={{ route('api_properties.properties.index') }} />
                                    <i class="fa fa-copy" data-toggle="tooltip" id="copyBtn"
                                            data-original-title="Copy Link"  ></i>
                            </div>
                        </div>
                        <form class="cmxform" id="add_external_property_user" name="add_external_property_user"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="form-group col-6">
                                    <label for="name">User Name </label>
                                    <input id="name" autocomplete="off" class="form-control" type="text" rows="20"
                                        name="name" value="{{ $external_property?->name ?? '' }}"
                                        placeholder="Enter name" />
                                </div>

                                <div class="form-group col-6">
                                    <label for="Password">Password </label>
                                    <input id="Password" autocomplete="off" class="form-control" type="text" rows="20"
                                        name="password" placeholder="Enter Password"
                                        value="{{ isset($view) ? $external_property->password_text : '' }}" />
                                    @if (!isset($view))
                                        <i class="auto-password fa_dash fa fa-key" data-toggle="tooltip"
                                            data-original-title="Generate Password" id="autoFillButton"></i>
                                    @endif
                                </div>
                                <div class="form-group col-12">
                                    <label for="api_key">Api Key </label>
                                    <textarea id="api_key" autocomplete="off" class="form-control" type="text" rows="10"
                                        name="api_key"
                                        placeholder="Enter api key mumber">{{ $external_property?->api_key ?? '' }}</textarea>
                                    @if (!isset($view))
                                        <i class="auto-password fa_dash fa fa-key" data-toggle="tooltip"
                                            data-original-title="Generate Api Key" id="autoFillApiKey"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="alert alert-success success_msg" role="alert"></div>
                            <div class="alert alert-danger error_msg" role="alert"></div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('custom-script')
        <script type="text/javascript">
            $(document).ready(function () {
                $('#country').select2({
                    placeholder: 'Search for a country',
                    allowClear: true
                });

                $('#agencies').on('change', function () {
                    $('.country_required').toggleClass('d-none');

                    var countryInput = $('#country');
                    if (this.value) {
                        countryInput.attr('required', true);
                    } else {
                        countryInput.removeAttr('required');
                    }
                });

                $('#agencies').select2({
                    placeholder: 'Search for a agencies',
                    allowClear: true
                });

                $('.select2-search__field').css('width', '100%');

                $('#autoFillButton').on('click', function () {
                    var autoPassword = generateString(12);
                    $('#Password').val(autoPassword);
                });

                $('#autoFillApiKey').on('click', function () {
                    var autoPassword = generateString(50);
                    $('#api_key').val(autoPassword);
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

               $('#copyBtn').on('click', function () {
                    var input = $('#copyInput');
                    input.select();
                    input[0].setSelectionRange(0, 99999); // For mobile devices
                    console.info(input.val());
                    try {
                        document.execCommand("copy");
                    } catch (err) {
                        console.error("Failed to copy text", err);
                    }
                });

                const statusUrl = `{{ route('adminSubUser.property.agency_status', ['id' => Auth::user()->id]) }}`;

                $('#change_status').change(function () {
                    var value = $(this).val();
                    console.log(value);

                    $.post(statusUrl, {
                        status: value,
                        _token: '{{ csrf_token() }}'
                    }, function (response) {
                        if (response.status === 'success') {
                            $('.success_msg').text(response.msg).show();
                            $('.error_msg').hide();
                        } else {
                            $('.error_msg').text(response.msg).show();
                            $('.success_msg').hide();
                        }
                    }).fail(function (xhr) {
                        $('.error_msg').text(xhr.responseJSON.msg).show();
                        $('.success_msg').hide();
                    });
                });

            });
            @if (isset($view))
                disableAllInputsInForm('add_external_property_user');
            @endif
        </script>
    @endpush
@endsection
@section('custom-css')
<link rel="stylesheet" href="{{ asset('assets/css/intlTelInput.css') }}">
