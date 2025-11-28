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
    </style>
@endpush
<div class="content-wrapper">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                <li class="breadcrumb-item"><a href=" {{ route('external_property_users.index') }}">List</a></li>
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
                    <h4 class="card-title">{{ isset($external_property) ? 'Update' : 'Create' }} {{ $title }}
                    </h4>
                    <form class="cmxform" id="add_external_property_user" name="add_external_property_user"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="form-group col-6">
                                <label for="agencies">Agency @if (!isset($external_property))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <select id="agencies" class="form-control form-select" data-coreui-search="true"
                                    data-live-search="true" name="agencies[]" multiple>
                                    <option value="" disabled>Select Agency</option>
                                    @forelse ($agencies as $key => $val)
                                        <option value="{{ $key }}"
                                            {{ isset($external_property) ? ($external_property->has_agencies && in_array($key, $external_property->agencies_ids) ? 'selected' : '') : '' }}>
                                            {{ $val }}</option>
                                    @empty
                                        <option value="">No agency found!</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="form-group col-6 ">
                                <label for="country">Country @if (!isset($external_property))
                                        <span class="text-danger country_required">*</span>
                                    @endif
                                </label>
                                <select id="country" class="form-control form-select" data-coreui-search="true"
                                    data-live-search="true" name="country">
                                    <option value="">Select Country</option>
                                    @forelse ($countries as $country)
                                        <option value="{{ $country }}"
                                            {{ isset($external_property) ? ($external_property?->country == $country ? 'selected' : '') : '' }}>
                                            {{ $country }}</option>
                                    @empty
                                        <option value="">No country found!</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label for="name">User Name @if (!isset($external_property))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input id="name" autocomplete="off" class="form-control" type="text"
                                    rows="20" name="name" value="{{ $external_property?->name ?? '' }}"
                                    placeholder="Enter name" />
                            </div>

                            <div class="form-group col-6 d-none">
                                <label for="email">Email @if (!isset($external_property))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input id="email" autocomplete="off" class="form-control" type="email"
                                    rows="20" name="email" value="{{ $external_property?->email ?? '' }}"
                                    placeholder="Enter email address" />
                            </div>

                            <div class="form-group col-6 d-none">
                                <label for="phone">Phone @if (!isset($external_property))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input id="phone" autocomplete="off" class="form-control" type="text"
                                    rows="20" name="phone" value="{{ $external_property?->phone ?? '' }}"
                                    placeholder="Enter phone mumber" />
                            </div>


                            <div class="form-group col-6">
                                <label for="Password">Password @if (!isset($external_property))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input id="Password" autocomplete="off" class="form-control" type="text"
                                    rows="20" name="password" placeholder="Enter Password"
                                    value="{{ isset($view) ? $external_property->password_text : '' }}" />
                                @if (!isset($view))
                                    <i class="auto-password fa_dash fa fa-key" data-toggle="tooltip" data-original-title="Generate Password"
                                        id="autoFillButton"></i>
                                @endif
                            </div>
                            <div class="form-group col-12 @if (!isset($external_property)) d-none @endif">
                                <label for="api_key">Api Key @if (!isset($external_property))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <textarea id="api_key" autocomplete="off" class="form-control" type="text" rows="10" name="api_key"
                                    placeholder="Enter api key mumber">{{ $external_property?->api_key ?? '' }}</textarea>
                                @if (!isset($view))
                                    <i class="auto-password fa_dash fa fa-key" data-toggle="tooltip" data-original-title="Generate Api Key"
                                        id="autoFillApiKey"></i>
                                @endif
                            </div>
                        </div>
                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                        @if (!isset($view))
                            <input type="submit" name="submit"
                                value="{{ isset($external_property) ? 'Update' : 'Create' }}"
                                class="btn btn-primary mr-2" />
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('custom-script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#country').select2({
                placeholder: 'Search for a country',
                allowClear: true
            });

            $('#agencies').on('change', function() {
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

            $('#autoFillButton').on('click', function() {
                var autoPassword = generateString(12);
                $('#Password').val(autoPassword);
            });

            $('#autoFillApiKey').on('click', function() {
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

            const ADD_NEW_EXTERNAL_PROPERTY_USER =
                `{{ isset($external_property) ? route('external_property_users.update', ['external_property_user' => $external_property->id]) : route('external_property_users.store') }}`;
            const METHOD = `{{ isset($external_property) ? 'PUT' : 'POST' }}`;

            $('#add_external_property_user').validate({
                rules: {
                    name: {
                        required: true,
                    },
                    email: {
                        required: false,
                    },
                    password: {
                        required: true,
                    },
                    agencies: {
                        required: false,
                    },
                    country: {
                        required: function() {
                            const agencies = $('#agencies').val();
                            return (agencies == '' ? true : false);
                        }
                    },
                    phone: {
                        required: false,
                    },
                    api_key: {
                        required: false,
                    }
                },
                submitHandler: function(form) {

                    const formData = new FormData();
                    let formDataSerialized = $('#add_external_property_user').serialize();

                    $.ajax({
                        url: ADD_NEW_EXTERNAL_PROPERTY_USER,
                        type: METHOD,
                        data: formDataSerialized,
                        processData: false,
                        success: function(response) {
                            if (response.status == 'success') {
                                $("form[name='add_external_property_user']").find(
                                    '.serverside_error').remove();
                                $('.success_msg').html(response.msg);
                                $('.success_msg').fadeIn();
                                setTimeout(function() {
                                    $('.success_msg').fadeOut();
                                }, 5000);
                                $('#add_external_property_user')[0].reset();
                                window.location.href =
                                    "{{ route('external_property_users.index') }}"
                            } else {
                                $("form[name='add_external_property_user']").find(
                                    '.serverside_error').remove();
                                $('.error_msg').html(response.msg);
                                $('.error_msg').fadeIn();
                                setTimeout(function() {
                                    $('.error_msg').fadeOut();
                                }, 5000);
                            }
                        },
                        error: function(xhr, status, error) {
                            handleServerError('add_external_property_user', xhr.responseJSON
                                .errors);
                        }
                    });
                }
            });
        });
        @if (isset($view))
            disableAllInputsInForm('add_external_property_user');
        @endif
    </script>
@endpush
@endsection
