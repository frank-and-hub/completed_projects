@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . $active_page))
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

        .select2-selection--multiple {
            padding-top: 5px !important;
        }

        select {
            border-radius: 3.3rem !important;
        }
    </style>
@endpush
<div class="content-wrapper">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">list</li>
            </ol>
        </nav>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row d-flex justify-content-between align-items-center">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="mb-3">
                    </div>
                </div>
                <div class="text-right d-flex">
                    <div class="text-right">
                        <a href="{{ route('adminSubUser.contract.create') }}" data-toggle="tooltip"
                            data-original-title="Add New" class="btn  btn-danger  mr-2"><i class="fa fa-plus">
                            </i>
                            Add New</a>
                    </div>
                </div>
            </div>
            <h4 class="card-title"></h4>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="ContractTable" class="table">
                            <thead>
                                <tr>
                                    {{-- <th>Sr.No.</th> --}}
                                    <th>Created At</th>
                                    <th>Tenant</th>
                                    <th>Phone</th>
                                    <th>Property</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog"
                aria-labelledby="exampleModal2Label" aria-hidden="true" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form name="property_contracts" id="property_contracts" class="cmxform">
                            <div class="modal-header plan_name">
                                <h5 class="modal-title text-center">Attach Property</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body row">
                                @csrf
                                <div class="col-12 form-group">
                                    <label for="property"> Property</label>
                                    <select id="property" class="form-control property-select2 form-multi-select"
                                        name="property[]">
                                    </select>
                                </div>
                                <div class="col-12 form-group">
                                    <input class="contract_id d-none" name="contract_id" value="" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn theme_btn_1">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="exampleModal4" tabindex="-1" role="dialog"
                aria-labelledby="exampleModal4Label" aria-hidden="true" data-backdrop="static">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div name="property_contracts" id="property_contracts" class="cmxform">
                            <div class="modal-header plan_name">
                                <h5 class="modal-title text-center">Message Sent To Offline Tenants</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body row" id="model_offline_table">

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="exampleModal5" tabindex="-1" role="dialog"
                aria-labelledby="exampleModal5Label" aria-hidden="true" data-backdrop="static">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div name="property_contracts" id="property_contracts" class="cmxform">
                            <div class="modal-header plan_name">
                                <h5 class="modal-title text-center">Message Sent To Tenants</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body row" id="model_table">

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form name="property_tenant" id="property_tenant" class="cmxform">
                            <div class="modal-header plan_name">
                                <h5 class="modal-title text-center">Attach Tenants</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                @csrf

                                <div class="form-group" id="OfflineTenant">
                                    <button type="button" class="btn  mr-2" data-toggle="modal"
                                        data-target="#exampleModal3">Offline Tenant</button>
                                </div>

                                <div class="form-group">
                                    <label for="tenant"> Tenant</label>
                                    <select id="tenant" class="form-control tenant-select2" name="tenant[]">
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input class="d-none contract_id" name="contract_id" value="" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn theme_btn_1">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="exampleModal3" tabindex="-1" role="dialog"
                aria-labelledby="exampleModal3Label" aria-hidden="true" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form name="offline_tenant" id="offline_tenant" class="cmxform">
                            <div class="modal-header plan_name">
                                <h5 class="modal-title text-center">Add Offline Tenants</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                @csrf
                                <div class="form-group">
                                    <label for="country">Country <span class="text-danger">*</span></label>
                                    <select id="country" class="form-control select2" name="country">
                                        <option value="" data-phonecode="" selected disabled>Select country
                                        </option>
                                        @forelse ($countries as $country)
                                            <option data-phonecode="{{ $country->phonecode }}"
                                                value="{{ $country->name }}">{{ ucwords($country->name) }}</option>
                                        @empty
                                            <option value="" data-phonecode="" disabled>No countries Found!
                                            </option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="first_name"> First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label for="last_name"> Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label for="email"> Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" />
                                </div>
                                <div class="col-1 form-group d-none">
                                    <label for="dial_code">Dial Code <span class="text-danger">*</span></label>
                                    <input id="dial_code" class="form-control" name="phonecode" type="tel"
                                        placeholder="+27">
                                </div>
                                <div class="form-group">
                                    <label for="phone"> Contact Number <span class="text-danger">*</span></label>
                                    <input id="phone" type="tel" name="contact_no" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <input class="d-none contract_id" name="contract_id" value="" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn theme_btn_1">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade bd-example-modal-lg modal-dialog-scrollable" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" id="contract_preview">

                    </div>
                </div>
            </div>

            <button type="button" class="btn d-none open_model2" data-toggle="modal"
                data-target="#exampleModal2"></button>
            <button type="button" class="btn d-none open_model" data-toggle="modal"
                data-target="#exampleModal"></button>
            <button type="button" class="btn d-none open_model3" data-toggle="modal"
                data-target="#exampleModal4"></button>
            <button type="button" class="btn d-none open_model4" data-toggle="modal"
                data-target="#exampleModal5"></button>
            <button type="button" class="btn btn-primary d-none" data-toggle="modal" id="model_btn"
                data-target=".bd-example-modal-lg">Large modal</button>
        </div>
    </div>
</div>
@endsection
@push('custom-script')
<script type="text/javascript">
    $(document).ready(function() {
        "use strict";

        const request_type = `{{ $active_page == 'agency' ? true : false }}`;
        const STATUS_UPDATE_ROUTE = `{{ route('adminSubUser.contract.status') }}`;
        const UPDATE_PROPERTY_CONTRACT = `{{ route('adminSubUser.contract.update_contracts_property') }}`;
        let GET_TENANTS = `{{ route('adminSubUser.contract.get_tenants') }}`;
        let GET_PROPERTIES = `{{ route('adminSubUser.contract.get_properties') }}`;
        const UPDATE_CONTRACTS_TENANT = `{{ route('adminSubUser.contract.update_contracts_tenants') }}`;
        const OFFLINE_TENANT = `{{ route('adminSubUser.contract.offline_tenants') }}`;
        let GET_OFFLINE_TENANTS_LIST = `{{ route('adminSubUser.contract.offline_tenants_list') }}`;
        let GET_TENANTS_LIST = `{{ route('adminSubUser.contract.tenants_list') }}`;
        const GET_STATUS_UPDATE = `{{ route('adminSubUser.contract.change_contract_status') }}`;
        let UPDATE_NEW_CONTRACT = `{{ route('adminSubUser.contract.update_new_contract') }}`;
        const GET_SELECT_TENANT = `{{ route('adminSubUser.contract.get_selected_tenants') }}`;
        const GET_SELECT_PROPERTY = `{{ route('adminSubUser.contract.get_selected_properties') }}`;

        var table = $('#ContractTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: window.location.href,
            columns: [
            {
                data: 'created_at',
                name: 'created_at',
                orderable: true,
                render: function (data, type, row) {
                    return dateF2(data);
                }
            },{
                data: 'tenant',
                name: 'tenant',
                orderable: false
            }, {
                data: 'phone',
                name: 'phone',
                orderable: false
            }, {
                data: 'property',
                name: 'property',
                orderable: false
            }, {
                data: 'status',
                name: 'status',
                orderable: false
            },{
                data: 'action',
                name: 'action',
                orderable: false
            }],
            drawCallback: function(settings, json) {
                $('[data-toggle=tooltip]').tooltip();
            }
        });

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
        //
        $(document).on('click', '.product_update', function() {
            const contractId = $(this).data('id');
            $('.contract_id').val(contractId);
            const GET_API_PROPERTIES = `${GET_PROPERTIES}/${contractId}`;
            $('.open_model2').click();
            $('.property-select2').select2({
                ajax: {
                    url: GET_API_PROPERTIES,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        // let selectedTenantIds = data?.selectedIds || [];
                        // $('.property-select2').val(selectedTenantIds).trigger('change');
                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                placeholder: 'Search for a property',
                multiple: false,
                allowClear: true
            });

            $.post(GET_SELECT_PROPERTY, {
                contract_id: contractId
            }, function(res) {
                const selected = $('#property');
                selected.empty();
                let html = '';
                const propertyArray = Object.values(res.property);
                propertyArray.forEach(function(val, index) {
                    html +=
                        `<option value="${val.property_id}" selected> ${val.title}</option>`;
                });
                selected.html(html);
            }, 'JSON');
        });
        // offline_tenant form submissions
        $("#offline_tenant").validate({
            rules: {
                first_name: {
                    required: true
                },
                last_name: {
                    required: true
                },
                email: {
                    required: true,
                    email_rule: true
                },
                contact_no: {
                    required: true
                },
                contract_id: {
                    required: true
                },
                country: {
                    required: true
                }
            },
            messages: {
                first_name: {
                    required: 'Please select first name'
                },
                last_name: {
                    required: 'Please select last name'
                },
                email: {
                    required: 'Please select email'
                },
                contact_no: {
                    required: 'Please select contact no'
                },
                contract_id: {
                    required: 'contract is required',
                },
                country: {
                    required: 'Select contract',
                }
            },
            submitHandler: function(form, event) {
                event.preventDefault();
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
                const formDataSerialized = $(form).serialize();
                $.post(OFFLINE_TENANT, formDataSerialized,
                    function(res) {
                        $("form[name='offline_tenant']").find('.serverside_error').remove();
                        if (res.status === 'success') {
                            // Handle success
                            $("form[name='offline_tenant']").find('.serverside_error')
                                .remove();
                            $('.success_msg').html(res.msg).fadeIn();
                            setTimeout(function() {
                                $('.success_msg').fadeOut();
                            }, 5000);
                            $('#offline_tenant')[0].reset();
                            $('.modal').modal('hide');
                            table.draw();
                        } else {
                            $("form[name='offline_tenant']").find('.serverside_error')
                                .remove();
                            $('.error_msg').html(res.msg).fadeIn();
                            setTimeout(function() {
                                $('.error_msg').fadeOut();
                            }, 5000);
                        }
                    }, 'json').fail(function(xhr, status, error) {
                    handleServerError('offline_tenant', xhr.responseJSON ? xhr
                        .responseJSON.errors : []);
                });

            }
        });
        // show offline_tenant list into model
        $(document).on('click', '.offline_tenants_list', function() {
            const contractId = $(this).data('id');
            const url_api = `${GET_OFFLINE_TENANTS_LIST}/${contractId}`;
            $('.open_model3').click();
            $.get(url_api, {
                contract_id: contractId,
            }, function(res) {
                $('#model_offline_table').html(res?.html);
            }, 'json').fail(function(xhr, status, error) {
                console.error('Error fetching contract details:', error);
            });
        });
        // show tenant data into list
        $(document).on('click', '.tenants_list', function() {
            const contractId = $(this).data('id');
            const url_api = `${GET_TENANTS_LIST}/${contractId}`;
            $('.open_model4').click();
            $.get(url_api, {
                contract_id: contractId,
            }, function(res) {
                $('#model_table').html(res?.html);
            }, 'json').fail(function(xhr, status, error) {
                console.error('Error fetching contract details:', error);
            });
        });
        // update tenant details
        $(document).on('click', '.tenant_update', function() {
            const contractId = $(this).data('id');
            const offlineTenantId = $(this).data('offline');
            $('.contract_id').val(contractId);
            const GET_API_TENANTS = `${GET_TENANTS}/${contractId}`;
            $('.open_model').click();
            if(offlineTenantId !== ''){
                $('#OfflineTenant').css('display','none');
            }
            $('.tenant-select2').select2({
                ajax: {
                    url: GET_API_TENANTS,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        //
                        //
                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                placeholder: 'Search for a tenant',
                multiple: false,
                allowClear: true
            });
            $.post(GET_SELECT_TENANT, {
                contract_id: contractId
            }, function(res) {
                const selected = $('#tenant');
                selected.empty();
                let html = '';
                const tenantsArray = Object.values(res.tenants);
                tenantsArray.forEach(function(val, index) {
                    html +=
                        `<option value="${val.tenant_id}" selected> ${val.name}</option>`;
                });
                selected.html(html);
                // let selectedTenantIds = res?.selectedIds || [];
                // $('.tenant-select2').val(selectedTenantIds).trigger('change');
            }, 'JSON');
        });
        //property_contracts form submission
        $("#property_contracts").validate({
            rules: {
                property: {
                    required: true
                },
                contract_id: {
                    required: true,
                },
            },
            messages: {
                property: {
                    required: 'Please select Properties'
                },
                contract_id: {
                    required: 'contract is required',
                },
            },
            submitHandler: function(form, event) {
                event.preventDefault();
                const formDataSerialized = $(form).serialize();
                $.post(UPDATE_PROPERTY_CONTRACT, formDataSerialized,
                    function(res) {
                        $("form[name='property_contracts']").find('.serverside_error').remove();
                        if (res.status === 'success') {
                            // Handle success
                            $("form[name='property_contracts']").find('.serverside_error')
                                .remove();
                            $('.success_msg').html(res.msg).fadeIn();
                            setTimeout(function() {
                                $('.success_msg').fadeOut();
                            }, 5000);
                            $("#property_contracts")[0].reset();
                            $('.modal').modal('hide');
                            table.draw();
                        } else {
                            // Handle error
                            $("form[name='property_contracts']").find('.serverside_error')
                                .remove();
                            $('.error_msg').html(res.msg).fadeIn();
                            setTimeout(function() {
                                $('.error_msg').fadeOut();
                            }, 5000);
                        }
                    }, 'json').fail(function(xhr, status, error) {
                    handleServerError('property_contracts', xhr.responseJSON ? xhr
                        .responseJSON.errors : []);
                });
            }
        });
        // default form key press
        $('form').on('keypress', function(e) {
            if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });
        // property_tenant form
        $("#property_tenant").validate({
            rules: {
                tenant: {
                    required: true
                },
                contract_id: {
                    required: true,
                },
            },
            messages: {
                tenant: {
                    required: 'Please select Tenants'
                },
                contract_id: {
                    required: 'contract is required',
                },
            },
            submitHandler: function(form, event) {
                event.preventDefault();
                const formDataSerialized = $(form).serialize();
                $.post(UPDATE_CONTRACTS_TENANT, formDataSerialized,
                    function(
                        res) {
                        $("form[name='property_tenant']").find('.serverside_error').remove();
                        if (res.status === 'success') {
                            // Handle success
                            $("form[name='property_tenant']").find('.serverside_error')
                                .remove();
                            $('.success_msg').html(res.msg).fadeIn();
                            setTimeout(function() {
                                $('.success_msg').fadeOut();
                            }, 5000);
                            $('#property_tenant')[0].reset();
                            $('.modal').modal('hide');
                            table.draw();
                        } else {
                            // Handle error
                            $("form[name='property_tenant']").find('.serverside_error')
                                .remove();
                            $('.error_msg').html(res.msg).fadeIn();
                            setTimeout(function() {
                                $('.error_msg').fadeOut();
                            }, 5000);
                        }
                    }, 'json').fail(function(xhr, status, error) {
                    handleServerError('property_tenant', xhr.responseJSON ? xhr
                        .responseJSON.errors : []);
                });

            }
        });
        // tenant-select2 work
        $('#property, #tenant').on('select2:select', function(e) {
            const result = e.params.data;
            $('.select2-search__field, .select2-search select2-search--inline').css('width', '100%');
        });
        // default select 2 css
        $('.select2-search__field, .select2-search select2-search--inline').css('width', '100%');
        // tenant properties on select
        $('#property, #tenant').on('select2:open', function() {
            $('.select2-search__field, .select2-search select2-search--inline').css('width', '100%');
        });
        // tenant properties on select
        $('#property, #tenant').select2({
            width: '100%',
            allowClear: true
        });
        // auto click on select
        $('.select2').off('click');

        $(document).on('change', 'input[name=updated_contract]', function() {
            const id = $(this).data('input-id');
            const file = $(this)[0].files[0];

            if (file) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('file', file);

                // Send the FormData via an AJAX request
                $.ajax({
                    url: UPDATE_NEW_CONTRACT,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire(
                            'Contract Updated successfully'
                        );
                        $('.close').click();
                    },
                    error: function(error) {
                        console.error('Error uploading file:', error);
                        Swal.fire(
                            'Something went wrong ', res.msg, 'error'
                        );
                    }
                });
            } else {
                console.info('No file selected');
            }

        });

        $(document).on('change', '.change_contract_status', function() {
            status = $(this).val();
            const id = $(this).data('id');

            $.post(GET_STATUS_UPDATE, {
                'id': id,
                'status': status
            }, function(res) {
                Swal.fire(
                    'Status Updated ', res.msg, 'success'
                );
                $('.close').click();
            }, 'JSON').fail(function() {
                Swal.fire(
                    'Something went wrong ', res.msg, 'error'
                );
            });
        });
    });
</script>
@endpush
