@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
    <div class="content-wrapper ">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Properties</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="property-header">
                    <div class="property-count" style="flex: 1">
                        Property <span class="badge bg-secondary" id="dataCount">{{count($dataTable)}}</span>
                    </div>
                    <div class="header-right-section">
{{--
                        @if ($user->selected_agency)
                            <button class="btn btn-add font-weight-bold" onclick="window.location.href='{{ $user->selected_agency_api_status != 1 ? route('adminSubUser.property.agency_status', ['id' => auth()->id()]) : 'javascript:void(0)' }}'">
                                <i class="fas fa-map me-1"></i> Allowed Property
                            </button>
                        @endif 
                        --}}

                        <button class="btn btn-add add_property font-weight-bold">
                            <i class="fas fa-plus me-1"></i> Add Property
                        </button>

                        {{-- <div class="input-group align-items-center" style="border: 1px solid #DEDEDE;border-radius: 3rem;">
                            <input type="search" class="form-control" name="search" placeholder="Search for property..." style="border:none;"/>
                            <span class="search-icon px-2"><i class="fas fa-search"></i></span>
                        </div> --}}

                        <div class="input-group align-items-center" style="border: 1px solid #DEDEDE; border-radius: 3rem;">
                            <input type="search" class="form-control" id="searchInput" name="search" placeholder="Search..." style="border:none;" />
                            <span class="search-icon px-2" id="searchIcon" style="cursor:pointer;">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 my-4" id="propertyContainer">
        </div>

        <div class="modal fade" id="time_slot_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form name="time_slot_form" id="time_slot_form">
                        <div class="modal-header plan_name">
                            <div class="row">
                                <h5 class="modal-title col-md-10" id="plan_name">Event Schedule</h5>
                                <button type="button" class="close col-2" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <div class="modal-body pb-0">
                            <div>
                            </div>
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="start_day_of_week">From Week Days<span class="text-danger">*</span></label>
                                    <select class="form-control" name="start_day_of_week" id="start_day_of_week">
                                        <option value="" selected>Select Week Days</option>
                                        @forelse (nameOfWeeks() as $key => $week)
                                            <option value="{{ $key }}">{{ $week }}</option>
                                        @empty
                                            <option value="Monday">No Week F0und !</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="end_day_of_week">To Week Days <span class="text-danger">*</span></label>
                                    <select class="form-control" name="end_day_of_week" id="end_day_of_week">
                                        <option value="" selected>Select Week Days</option>
                                        @forelse (nameOfWeeks() as $key => $week)
                                            <option value="{{ $key }}">{{ $week }}</option>
                                        @empty
                                            <option value="Monday">No Week F0und !</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                    <select class="form-control" name="start_time" id="start_time">
                                        <option value="" selected>Select Time</option>
                                        @forelse (hoursTimeSlots() as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @empty
                                            <option value="Monday">No Week F0und !</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="end_time">End Time <span class="text-danger">*</span></label>
                                    <select class="form-control" name="end_time" id="end_time">
                                        <option value="" selected>Select Time</option>
                                        @forelse (hoursTimeSlots() as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @empty
                                            <option value="Monday">No Week F0und !</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                        <input type="hidden" name="property_id" id="property_id" value="">
                        <input type="hidden" name="time_slot_route" id="time_slot_route" value="">
                        <div class="modal-footer">
                            <button type="submit" class="btn theme_btn_1">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('custom-script')
        <script type="text/javascript">
            $(document).on('click', '.add_property', function (e) {
                e.preventDefault(); // Prevent the default link action

                $.ajax({
                    url: "{{ route('adminSubUser.check_plan_is_exists') }}",
                    type: "get",
                    success: function (response) {
                        if (response.status === 'success') {
                            subscription = response.data.is_plan_running;
                            if (subscription == 1) {
                                window.location.href = "{{ route('adminSubUser.property.add') }}";
                            } else {
                                subscriptionAdminModel();
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                    }
                });
            });

            STATUS_UPDATE_ROUTE = `{{ route('adminSubUser.property.status') }}`;
            STATUS_UPDATE_ROUTE_CONTRACT = `{{ route('adminSubUser.property.status_contract') }}`;
            DATA_TABLE_PROPERTIES = `{{ route('adminSubUser.property.dataTable') }}`;

            const nameOfWeeks = {!! json_encode(nameOfWeeks()) !!};

            $('input[name=search]').on('keyup', function () {
                var searchQuery = $('input[name=search]').val();
                var encodedSearchQuery = encodeURIComponent(searchQuery);
                $.post(DATA_TABLE_PROPERTIES,
                    { search: encodedSearchQuery },
                    function (response) {
                        generatePropertyCards(response?.data);
                        $('#dataCount').html(response?.data.length);
                    }, 'json'
                ).fail(function (xhr, status, error) {
                    console.error('Request failed. Status:', xhr.status, 'Error:', error);
                });
            });

            $('input[name=search]').keyup();

            function generatePropertyCards(properties) {
                const container = document.getElementById("propertyContainer");
                let html = "";
                if(properties.length === 0){
                    html += `<div class="col-12 text-center w-25 align-middle" ><img src="{{asset('/assets/admin/images/no_data_found.svg')}}" style="width: 30rem;"/></div>`;
                }else{
                    properties.forEach((property) => {

                        const address = propertyAddress(suburb = property?.suburb, town = property?.town, province = property?.province, country = property?.country),
                              price = R_price(property?.financials?.price),
                              mainImage = findMainImage(property?.media) ? `{!!Storage::url('${findMainImage(property?.media)}')!!}`: null,
                              detail_web_route = `${window.location.href}/view/${property.id}`,
                              edit_web_route = `${window.location.href}/edit/${property.id}`,
                              hasContractOrAlreadySend = (property.sent_properties.length > 0) || (property.contract.length > 0),
                              statusChecked = property.status ? 'checked' : '',
                              matchedPropertyUrl = `{{route('adminSubUser.match-property.index')}}`,
                              demyImage = `{{asset("/assets/admin/images/header_banner.png")}}`,
                              ui_web_route = `{{url('property-detail?property_id=${property?.id}&updateKey=internal')}}`,
                              isTimeSlot = property?.property_time_slot ?? property.status,
                              time_slot_route = `${window.location.href}/update-time-zone/${property.id}`;
                              matchedTenantsCount = findGroupUser(property?.sent_properties);

                        html += `<div class="col-sm-1 col-md-4 col-lg-4 mb-3">
                                    <div class="property-card">
                                        <div class="property-image">
                                            <img
                                            loading="lazy"
                                            src="${mainImage ?? demyImage}" alt="${truncateTitle(property?.title, 100)}"
                                            onerror="this.onerror=null; this.src='${demyImage}';" loading="lazy"
                                            >
                                            <span class="property-check-box property-status text-capitalize bg-${property.status?`success`:`secondary`}">
                                                ${property.status?`active`:`inactive`}
                                            </span>
                                            <div class="dropdown more-options">
                                                <span class="btn" style="padding:0.5rem;" type="button" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i>
                                                <span class="caret"></span></span>
                                                <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                                                    <li role="presentation" class="dropdown-menu-item menu-divider">
                                                        <label for="${property?.id}_id" class="d-flex justify-content-start align-items-center w-100">
                                                            <input type="checkbox" ${statusChecked} id="${property?.id}_id" class="d-none ${property?.contract.length > 0 ? `changeContractProperty` : `changeStatusProperty`}" data-id="${property?.id}" />
                                                            <i class="menu-option-icon fa mr-2 fa-solid fa-toggle-${property.status ? "on" : "off"}"></i>
                                                            <p class="text-capitalize mb-0 font-weight-bold">
                                                                ${!property.status?`active`:`inactive`}
                                                            </p>
                                                        </label>
                                                    </li>

                                                    ${!hasContractOrAlreadySend ? `<li role="presentation" class="dropdown-menu-item menu-divider">
                                                        <a href="${edit_web_route}" class="d-flex justify-content-start align-items-center w-100" >
                                                            <i class="fa fa-edit edit-icon p-2" style="font-size:14px; "></i>
                                                             <p class="text-capitalize mb-0 font-weight-bold">
                                                                Edit
                                                            </p>
                                                        </a>
                                                    </li>`:``}

                                                    ${isTimeSlot ? `<li role="presentation"
                                                    class="dropdown-menu-item menu-divider update_time_slot"
                                                    data-href="${time_slot_route}"
                                                    data-property_id="${property.id}"
                                                    data-end_day_of_week="${property?.property_time_slot?.end_day_of_week}"
                                                    data-start_day_of_week="${property?.property_time_slot?.start_day_of_week}"
                                                    data-end_time="${property?.property_time_slot?.end_time}"
                                                    data-start_time="${property?.property_time_slot?.start_time}"
                                                    >
                                                        <a href="#" class="d-flex justify-content-start align-items-center w-100" >
                                                            <i class="fa fa-clock edit-icon p-2" style="font-size:14px; "></i>
                                                             <p class="text-capitalize mb-0 font-weight-bold">
                                                                Time Slot
                                                            </p>
                                                        </a>
                                                    </li>`:``}

                                                    <li role="presentation" class="dropdown-menu-item menu-divider">
                                                       <a href="${ui_web_route}" target="_blank" class="d-flex justify-content-start align-items-center w-100" >
                                                            <i class="fa fa-image edit-icon p-2" style="font-size:14px; "></i>
                                                             <p class="text-capitalize mb-0 font-weight-bold">
                                                                View
                                                            </p>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="property-details">
                                            <span class="property-type">${property?.propertyType}</span>
                                            <h5 class="property-title text-capitalize py-1 text-truncate">${property?.title}</h5>
                                            <p class="property-location mt-1 text-capitalize"><i class="fas fa-map-marker-alt"></i> ${address ?? ''}</p>
                                            <div class="property-features">
                                                <span class="feature"><i class="fas fa-bed"></i> ${property?.bedrooms} Bedroom</span>
                                                <span class="feature"><i class="fas fa-bath"></i> ${property?.bathrooms} Bathroom</span>
                                            </div>
                                            <div class="property-matched">
                                                <a href="${matchedPropertyUrl+`?property_id=`+(matchedTenantsCount > 0 ? property?.id:`no-match`)}" class="property-location"><i class="fas fa-building"></i> ${matchedTenantsCount > 0 ? matchedTenantsCount : `No`} Tenants Matched</a>
                                            </div>
                                            <div class="property-price mt-2">
                                                <span class="price">${price}</span>
                                                <a href="${detail_web_route}" class="read-more font-weight-bold">View Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                    });
                }
                container.innerHTML = html;
            }

            $(document).on('change', '.changeStatusProperty', function () {
                var $this = $(this);
                var previousStatus = $this.prop('checked');
                var dataStatus = ($this.is(':checked')) ? 'unblock' : 'block';
                var dataId = $this.data('id');
                var dataTable = $this.data('datatable');
                Swal.fire({
                    title: `Are you sure?`,
                    text: 'You want to ' + dataStatus + ' Property!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    confirmButtonText: 'Yes, ' + dataStatus + ' it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        statusAjaxCall(STATUS_UPDATE_ROUTE, dataId, dataStatus, dataTable);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        $this.prop('checked', (previousStatus == false));
                    }
                });
            });

            $(document).on('click', '.update_time_slot', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                var id = $(this).data('property_id');
                let end_day_of_week = $(this).data('end_day_of_week');
                let start_day_of_week = $(this).data('start_day_of_week');
                let end_time = $(this).data('end_time');
                let start_time = $(this).data('start_time');
// console.log(nameOfWeeks)
                if (url) {
                    $('#time_slot_model').modal('show');
                    $('#time_slot_route').val(url);
                    $('#property_id').val(id);
                    $('select[name="end_day_of_week"]').val(nameOfWeeks.indexOf(end_day_of_week));
                    $('select[name="start_day_of_week"]').val(nameOfWeeks.indexOf(start_day_of_week));
                    $('select[name="end_time"]').val(end_time);
                    $('select[name="start_time"]').val(start_time);
                }
            });

            // Custom method: Start time should be before end time
            $.validator.addMethod("startBeforeEndTime", function (value, element) {
                var startTime = $('select[name="start_time"]').val();
                var endTime = $('select[name="end_time"]').val();
                if (!startTime || !endTime) return true; // Let required rule handle empty
                return startTime < endTime;
            }, "Start time must be before end time");

            // Custom method: Start and end day of week should not be the same
            $.validator.addMethod("differentDays", function (value, element) {
                var startDay = $('select[name="start_day_of_week"]').val();
                var endDay = $('select[name="end_day_of_week"]').val();
                if (!startDay || !endDay) return true; // Let required rule handle empty
                return startDay !== endDay;
            }, "Start and end day of week cannot be the same");

            $('select[name="start_time"]').on('change', function () {
                $('select[name="end_time"]').val('');
            });

            $('select[name="start_day_of_week"]').on('change', function () {
                $('select[name="end_day_of_week"]').val('');
            });

            $("form[name='time_slot_form']").validate({
                rules: {
                    start_time: {
                        required: true,
                        startBeforeEndTime: true,
                    },
                    end_time: {
                        required: true,
                        startBeforeEndTime: true,
                    },
                    start_day_of_week: {
                        required: true,
                        differentDays: true
                    },
                    end_day_of_week: {
                        required: true,
                        differentDays: true
                    },
                },
                messages: {
                    start_time: {
                        required: 'Select start time',
                        startBeforeEndTime: 'Start time must be before end time'
                    },
                    end_time: {
                        required: 'Select end time'
                    },
                    start_day_of_week: {
                        required: 'Select start day of week',
                        differentDays: 'Start and end day of week cannot be the same'
                    },
                    end_day_of_week: {
                        required: 'Select end day of week',
                        differentDays: 'Start and end day of week cannot be the same'
                    },
                },
                submitHandler: function (form) {
                    const url = $('#time_slot_route').val();
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: new FormData(form),
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response.status == 'success') {
                                $("form[name='time_slot_form']").find('.serverside_error').remove();
                                $('.success_msg').html(response.msg);
                                $('.success_msg').fadeIn();
                                setTimeout(function () {
                                    $('.success_msg').fadeOut();
                                }, 5000);
                                $('#time_slot_form')[0].reset();

                                Swal.fire({
                                    title: "Property Timeslot Updated Created!",
                                    icon: "success",
                                    draggable: true,
                                    showConfirmButton: true,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                    //     window.location.href = "{{ route('adminSubUser.property.index') }}";
                                        $('#time_slot_model').modal('hide');
                                    }
                                });
                            } else {
                                $("form[name='time_slot_form']").find('.serverside_error').remove();
                                $('.error_msg').html(response.msg);
                                $('.error_msg').fadeIn();
                                setTimeout(function () {
                                    $('.error_msg').fadeOut();
                                }, 5000);
                            }
                        },
                        error: function (xhr, status, error) {
                            handleServerError('time_slot_form', xhr.responseJSON
                                .errors);
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Something went wrong!",
                                draggable: true,
                            });
                        }
                    });
                }
            });

            $(document).on('change', '.changeContractProperty', function () {
                var $this = $(this);
                var previousStatus = $this.prop('checked');
                var dataStatus = ($this.is(':checked')) ? 'unblock' : 'block';
                var dataId = $this.data('id');
                var dataTable = $this.data('datatable');
                Swal.fire({
                    title: `Are you sure?`,
                    text: `You want to ${dataStatus} Property! this property has a contract attached to it.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    confirmButtonText: 'Yes, ' + dataStatus + ' it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        statusAjaxCall(STATUS_UPDATE_ROUTE_CONTRACT, dataId, dataStatus, dataTable);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        $this.prop('checked', (previousStatus == false));
                    }
                });
            });

            function statusAjaxCall(url, dataId, dataStatus, dataTable) {
                $.post(url, {
                    'dataId': dataId,
                    'datastatus': dataStatus
                }, function (response) {
                    if (response.status) {
                        if (response.type == 1) {
                            Swal.fire(
                                'Unblock!', response.msg, 'success'
                            );
                        } else {
                            Swal.fire(
                                'Block', response.msg, 'success'
                            );
                        }
                    } else {
                        Swal.fire(
                            'Oops !', response.msg, 'error'
                        );
                        if (previousStatus == false) {
                            $this.prop('checked', true);
                        } else {
                            $this.prop('checked', false);
                        }
                    }
                    $('input[name=search]').keyup();
                    $('#' + dataTable).DataTable().ajax.reload();
                }, 'JSON').fail(function (xhr, status, error) {
                    Swal.fire(
                        'Error',
                        'Status process encountered an error. Your file is safe :)',
                        'error'
                    );
                });
            }
        </script>
    @endpush
@endsection
