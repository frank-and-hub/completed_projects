@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
    @push('custom-css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <style>
            .icon-size{
                margin-left: 0.5rem;
                margin-right: 0.5rem;
            }
        </style>
    @endpush
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="agent-header mb-1">
                    <div class="agent-count row align-items-center w-40" style="flex: 1">
                        <h4 class="mb-0 mr-2">Tenant</h4> <span class="badge bg-secondary"
                            id="dataCount">{{count($dataTable)}}</span>
                    </div>
                    <div class="header-right-section w-60 d-flex">
                        <div class="col-md-2 col-sm-2 col-xl-3 col-12">
                            <button id="eventScheduleModelCheckedOne"
                                class="btn action-buttons add-agent-btn mx-auto d-none text-center" data-toggle="tooltip"
                                data-placement="top" style="padding:1rem"
                                data-original-title="Create event">
                                <i class="fa fa-regular fa-calendar-plus"></i>
                                <p class="mb-0 d-none d-xl-block">Create event</p>
                            </button>
                        </div>
                        <div class="col-md-6 col-xl-5 col-12">
                            <select class="form-control select2 status-dropdown ml-3 text-truncate" name="properties"
                                id="properties_dropDown">
                                <option class="text-capitalize" value="">All Properties</option>
                                @forelse ($properties as $key => $property)
                                    <option class="text-capitalize text-truncate" value="{{$key}}">{{$property}}</option>
                                @empty
                                    <option disabled>No Property Found !</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-4 col-xl-4 col-12">
                            <div class="input-group align-items-center w-100" style="border: 1px solid #DEDEDE; border-radius: 3rem; padding:5px">
                                <input type="search" class="form-control" id="searchInput" name="search" placeholder="Search..." style="border:none;" />
                                <span class="search-icon px-2" id="searchIcon" style="cursor:pointer;">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 my-4" id="matchedPropertyContainer">
        </div>
    </div>

    <div class="modal fade" id="eventschedulemodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="event_schedule" id="event_schedule">
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
                        <select name="SentInternalPropertyUser_id[]" class="d-none" id="SentInternalPropertyUser_id"
                            multiple></select>
                        <input type="text" class="d-none" name="d" value=''>
                        <input type="text" class="d-none" name="t" value=''>

                        <input type="text" class="d-none" name="defaultDate" value=''>
                        <input type="text" class="d-none" name="defaultTime" value=''>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" placeholder="Enter title">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Map/ Metting Link</label>
                                <input type="url" class="form-control" name="link"
                                    placeholder="Enter Map or any metting link">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date" id="date" placeholder="Enter date"
                                    min="{!!date('d-m-Y')!!}" value={!!date('d-m-Y')!!}>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="time" id="time" placeholder="Enter Time"
                                    value={!!date('H:i')!!}>
                            </div>

                            <div class="form-group col-md-12">
                                <label for="">Description</label>
                                <textarea name="description" class="form-control" id="" cols="15" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-success success_msg" role="alert"></div>
                    <div class="alert alert-danger error_msg" role="alert"></div>
                    <div class="modal-footer">
                        <button type="submit" class="btn theme_btn_1">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="makeNotesmodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="make_notes" id="make_notes">
                    <div class="modal-header plan_name">
                        <div class="row">
                            <h5 class="modal-title col-md-10" id="plan_name">Notes for this tenant (if you have any)</h5>
                            <button type="button" class="close col-2" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body pb-0">
                        <div>
                            <input name="sipu_id" class="d-none" id="sipu_id" value="" type="text">
                        </div>
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for=""></label>
                                <textarea name="description" class="form-control" id="sipu_note"
                                    style="height: 150px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-success success_msg" role="alert"></div>
                    <div class="alert alert-danger error_msg" role="alert"></div>
                    <div class="modal-footer">
                        <button type="submit" class="btn theme_btn_1">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header plan_name">
                    <div class="row">
                        <h5 class="modal-title col-md-11" id="plan_name">Property Viewing History</h5>
                        <button type="button" class="close col-1" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body" id="eventHistory">
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade bd-example-modal-md modal-dialog-scrollable" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="contract_preview">
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-primary d-none" data-toggle="modal" id="model_btn"
        data-target=".bd-example-modal-md">Large modal</button>

    @push('custom-script')
        <script type="text/javascript">
            $(document).ready(function () {
                DATA_TABLE_MATCHED_PROPERTIES = `{{ route('adminSubUser.match-property.dataTable') }}`;
                DATA_TABLE_USER_EVENT_HISTORY = `{{ route('adminSubUser.calendar.history_dataTable') }}`;

                var urlParams = new URLSearchParams(window.location.search);
                var propertyId = urlParams.get('property_id');
                if (propertyId) {
                    if (propertyId === 'no-match') {
                        $('#properties_dropDown').append(`<option value="no-data" disabled selected>Select A Property First</option>`).change();
                    } else {
                        $('#properties_dropDown').val(propertyId).change();
                    }
                }

                $(document).on('click', '.eventschedulemodel', function () {
                    $('#event_schedule')[0].reset();
                    let property = $('#properties_dropDown').val();
                    if (!property) {
                        Swal.fire('Please select a property first.');
                        return false;
                    }
                    const dataId = $(this).data('id');
                    $('.theme_btn_1').prop('disabled', false);
                    $('#SentInternalPropertyUser_id').empty('');
                    $('#SentInternalPropertyUser_id').append(`<option value='${dataId}' selected></option>`);
                    $('#eventschedulemodel').modal('show');
                });

                $(document).on('click', '.notesModel', function () {
                    $('#make_notes')[0].reset();
                    let property = $('#properties_dropDown').val();
                    if (!property) {
                        Swal.fire('Please select a property first.');
                        return false;
                    }
                    const dataId = $(this).data('id');
                    const dataNote = $(this).data('note');
                    $('.theme_btn_1').prop('disabled', false);
                    const $input = $('#sipu_id');
                    const $sipu_input_note = $('#sipu_note');
                    $input.val(dataId);
                    $sipu_input_note.val(dataNote);
                    $('#makeNotesmodel').modal('show');
                });

                $.validator.addMethod("lessThanOneMonth", function (value, element) {
                    var today = new Date();

                    // Create a date object for the next month
                    var nextMonth = new Date(today);
                    nextMonth.setMonth(today.getMonth() + 1);

                    // Convert the input value from d-m-Y format to mm/dd/yyyy
                    var dateParts = value.split('-'); // Split the date by dash (assuming the format is d-m-Y)
                    // Create a new date object in mm/dd/yyyy format
                    var inputDate = new Date(dateParts[1] + '/' + dateParts[0] + '/' + dateParts[2]);

                    // Check if input date is less than or equal to next month
                    return inputDate <= nextMonth;
                });

                $("form[name='event_schedule']").validate({
                    rules: {
                        title: {
                            required: true
                        },
                        date: {
                            required: true,
                            lessThanOneMonth: true
                        },
                        time: {
                            required: true
                        },
                    },
                    messages: {
                        date: {
                            lessThanOneMonth: "The date must be within one month from today."
                        }
                    },
                    submitHandler: function (form, e) {
                        e.preventDefault();

                        var localDate = $("input[name='date']").val();
                        var localTime = $("input[name='time']").val();

                        const demoDate = utcTimeConversion(localDate, localTime);
                        console.log(localDate, localTime, demoDate);

                        $("input[name='defaultDate']").val(demoDate.date);
                        $("input[name='defaultTime']").val(demoDate.time);

                        $("input[name='d']").val(demoDate.default_date);
                        $("input[name='t']").val(demoDate.default_time);

                        $('.theme_btn_1').prop('disabled', true);
                        var submitButton = $(form).find('button[type="submit"]');
                        submitButton.prop('disabled', true);
                        $.ajax({
                            url: `{{ route('adminSubUser.calendar.create') }}`,
                            type: "POST",
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.status == 'success') {
                                    setTimeout(function () {
                                        $('#eventschedulemodel').modal('hide');
                                    }, 500);
                                    $("form[name='event_schedule']").find('.serverside_error')
                                        .remove();
                                    $('.success_msg').html(response.msg);
                                    $('.success_msg').fadeIn();
                                    setTimeout(function () {
                                        $('.success_msg').fadeOut();
                                    }, 3000);
                                    $('#matchedproperty').DataTable().ajax.reload();
                                    $('#event_schedule')[0].reset();
                                } else {
                                    $("form[name='event_schedule']").find('.serverside_error')
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
                                handleServerError('event_schedule', xhr.responseJSON.errors);
                                $('.theme_btn_1').prop('disabled', true);
                            }
                        });
                    }
                });

                $("form[name='make_notes']").validate({
                    rules: {
                        sipu_id: {
                            required: true
                        },
                        description: {
                            required: true
                        }
                    },
                    messages: {
                        description: {
                            required: "please enter this tenant note"
                        }
                    },
                    submitHandler: function (form, e) {
                        e.preventDefault();

                        $('.theme_btn_1').prop('disabled', true);
                        var submitButton = $(form).find('button[type="submit"]');
                        submitButton.prop('disabled', true);
                        $.ajax({
                            url: `{{ route('adminSubUser.calendar.note') }}`,
                            type: "POST",
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.status == 'success') {
                                    setTimeout(function () {
                                        $('#makeNotesmodel').modal('hide');
                                    }, 500);
                                    $("form[name='make_notes']").find('.serverside_error')
                                        .remove();
                                    $('.success_msg').html(response.msg);
                                    $('.success_msg').fadeIn();
                                    setTimeout(function () {
                                        $('.success_msg').fadeOut();
                                    }, 3000);
                                    $('#matchedproperty').DataTable().ajax.reload();
                                    $('#make_notes')[0].reset();
                                } else {
                                    $("form[name='make_notes']").find('.serverside_error')
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
                                handleServerError('make_notes', xhr.responseJSON.errors);
                                $('.theme_btn_1').prop('disabled', true);
                            }
                        });
                    }
                });

                // $(document).on('change',function(){
                flatpickr("#date", { dateFormat: "d-m-Y", minDate: "today", maxDate: new Date().fp_incr(30), enableTime: false });
                flatpickr("#time", { enableTime: true, noCalendar: true, dateFormat: "H:i ", minDate: "now" });
                // })

                $(document).on('change', 'input[name^="properties_"]', function () {
                    let ids = [];
                    let row = $(this).closest('.agent-card');
                    let calendarLink = row.find('button.eventschedulemodel');

                    $('input[name^="properties_"]:checked').each(function () {
                        ids.push($(this).data('id'));
                    });

                    if ($('input[name^="properties_"]:checked').length > 5) {
                        Swal.fire('You can\'t check more than 5 properties.');
                        $(this).prop('checked', false);
                        return false;
                    }

                    $('input[name^="properties_"]:not(:checked)').each(function () {
                        const index = ids.indexOf($(this).data('id'));
                        if (index > -1) {
                            ids.splice(index, 1);
                        }
                    });

                    if ($(this).prop('checked')) {
                        calendarLink.hide();
                    } else {
                        calendarLink.show();
                    }

                    $('#eventScheduleModelCheckedOne').attr('data-ids', JSON.stringify(ids));
                    if (ids.length > 0) {
                        $('#eventScheduleModelCheckedOne').removeClass('d-none');
                    } else {
                        $('#eventScheduleModelCheckedOne').addClass('d-none');
                    }
                });

                $(document).on('click', '#eventScheduleModelCheckedOne', function () {
                    let dataId = $(this).attr('data-ids');
                    let idsArray = JSON.parse(dataId);
                    $('#event_schedule')[0].reset();
                    $('.theme_btn_1').prop('disabled', false);
                    $('#SentInternalPropertyUser_id').empty('');
                    idsArray.forEach(function (id) {
                        $('#SentInternalPropertyUser_id').append(`<option value="${id}" selected></option>`);
                    });
                    $('#eventschedulemodel').modal('show');
                });

                $('input[name=search]').on('keyup', function () {
                    var searchQuery = $('input[name=search]').val();
                    var propertyQuery = $('#properties_dropDown').val();
                    var encodedSearchQuery = encodeURIComponent(searchQuery);
                    var encodedPropertyQuery = encodeURIComponent(propertyQuery);
                    $.post(DATA_TABLE_MATCHED_PROPERTIES,
                        {
                            search: encodedSearchQuery,
                            property: encodedPropertyQuery
                        },
                        function (response) {
                            generateMatchedTenantCards(response?.data);
                            $('#dataCount').html(response?.data.length);
                        }, 'json'
                    ).fail(function (xhr, status, error) {
                        console.error('Request failed. Status:', xhr.status, 'Error:', error);
                    });
                });

                $('input[name=search]').keyup();

                $('#properties_dropDown').on('change', function () {
                    if ($(this).val() !== 'no-data') {
                        $(this).find('option[value="no-data"]').remove();
                    }
                    $('input[name=search]').keyup();
                });

                $(document).on('click', '.event_history', function () {
                    const id = $(this).data('id');
                    $.post(DATA_TABLE_USER_EVENT_HISTORY,
                        { id: encodeURIComponent(id) },
                        function (response) {
                            createEventHistoryTable(response.data);
                        }, 'json'
                    ).fail(function (xhr, status, error) {
                        console.error('Request failed. Status:', xhr.status, 'Error:', error);
                    });
                });
            });

            function generateMatchedTenantCards(properties) {
                const container = document.getElementById("matchedPropertyContainer");
                if (!container) return; // Early return if container doesn't exist

                // Predefine constants outside the loop
                const noDataImage = "{{ asset('/assets/admin/images/no_data_found.svg') }}";
                const defaultImage = "{{ asset('assets/default_user.png') }}";
                const downloadDecryptPdfRoute = "{{ route('download_and_decrypt_Pdf') }}";
                const exiredTime = "{!! creditReportencodedbase64() !!}";
                const isAgencyUser = "{{ !auth()->guard('admin')->user()->hasRole('agency') }}";

                // Generate HTML using template literals and array methods
                const html = properties.length === 0
                    ? `<div class="col-12 text-center w-25 align-middle"><img src="${noDataImage}" style="width: 30rem;" alt="No data found" loading="lazy"></div>`
                    : properties.map(data => {

                        const userStatus = data?.status === 1,
                            sentProperty = data?.sent_internal_properties?.[0] || {},
                            creditReportStatus = sentProperty.credit_reports_status ?? 'unapproved',
                            allowedAdminIds = data?.sent_internal_properties?.map(prop => prop.admin_id) || [],
                            propertyCount = data?.passing?.length ?? 0,
                            mainImage = data?.image ? `{!! Storage::url('${data.image}') !!}` : defaultImage,
                            userEvent = data?.calendar2?.[0]?.event_datetime ? dateF2(data.calendar2[0].event_datetime) : null,
                            totalEventCount = data?.calendar2?.filter(item => allowedAdminIds.includes(item?.admin_id)).length || 0,
                            eventType = findEventType(data?.calendar2?.[0]?.event_datetime),
                            creditReportRoute = `${downloadDecryptPdfRoute}/${exiredTime}/${data?.credit_report?.id}`,
                            eventColor = eventType === 2 ? '#17a2b8' : eventType === 1 ? '#d4a017' : '#28a745',
                            makeNotes = true,
                            userEmployment = data.user_employment ?? null,
                            employementArray = {
                                'employed' : 'Employed',
                                'contract' : 'Contract',
                                'self_employed' : 'Self Employed',
                                'student' : 'Student',
                                'retired' : 'Retired',
                                'unemployed' : 'Unemployed',
                            };

                        return `
                                                    <div class="col-sm-12 col-md-4 col-lg-3 mb-3">
                                                        <div class="agent-card">
                                                            ${!userEvent ? '<div class="new-tag"><p class="mb-0">New</p></div>' : ''}
                                                            ${userStatus ? `
                                                                <div class="user-check-box">
                                                                    <input class="cursor-pointer" type="checkbox" name="properties_[]" data-id="${sentProperty.id}" id="properties_${sentProperty.id}">
                                                                </div>` : ''}
                                                            <img src="${mainImage}" alt="${data?.name || 'Tenant'}" class="agent-image" onerror="this.src='${defaultImage}';" loading="lazy">
                                                            <h5 class="agent-name text-capitalize text-truncate pb-1">${data?.name || 'Unknown'}</h5>
                                                            <p class="agent-location text-capitalize pb-1 text-truncate">
                                                                <i class="fas fa-map-marker-alt"></i> ${data?.country || 'N/A'}
                                                            </p>
                                                            <p class="mb-1 pb-1">${data?.country_code || ''}${data?.phone || 'N/A'}</p>
                                                            <p class="agent-property font-weight-bold" style="color:${eventColor}">
                                                                ${userEvent || 'Booking not scheduled!'}
                                                            </p>
                                                            ${totalEventCount > 0 ? `
                                                                <p class="btn agent-property p-0 event_history property-matched" data-id="${data?.id}" data-toggle="modal" data-target="#exampleModalCenter" title="View Event History" role="button">
                                                                    <a class="property-location">Total Event Booked: ${totalEventCount}</a>
                                                                </p>` : ''}
                                                            ${creditReportStatus !== 'unapproved' && data?.credit_report?.credit_report_pdf ? `
                                                                <p class="agent-property" style="color:#5294e2" title="Credit Report" role="button" onclick="previewFile('${creditReportRoute}')">
                                                                    <u class="pointer font-weight-bold">View Credit Report</u>
                                                                </p>` : ''}
                                                            ${userEmployment ? `
                                                            <p class="agent-property font-weight-bold">
                                                                <i class="fas fa-users icon-size"></i> Number of Residents:
                                                                 <span class="font-weight-normal" >${userEmployment?.live_with}</span>
                                                            </p>
                                                            <p class="agent-property font-weight-bold">
                                                                <i class="fas fa-user-tie icon-size"></i> Employement Status:
                                                                 <span class="font-weight-normal" >${employementArray[userEmployment?.emplyee_type] ?? 'N/A'}</span>
                                                            </p>
                                                                ` : ``}
                                                            <div class="action-buttons btn-sz">
                                                                ${userStatus ? `
                                                                    <button class="action-btn message calendar-link eventschedulemodel" data-id="${sentProperty.id}" title="Calendar">
                                                                        <i class="fas fa-calendar"></i>
                                                                    </button>` : '<p style="color:red;">Tenant is Blocked</p>'}
                                                                    ${sentProperty && makeNotes ? `
                                                                    <button class="action-btn message calendar-link notesModel" data-id="${sentProperty.id}" data-note="${sentProperty.notes}" title="Notes">
                                                                        <i class="fas fa-sticky-note"></i>
                                                                    </button>` : '<p style="color:red;">Tenant is Blocked</p>'}
                                                            </div>
                                                        </div>
                                                    </div>`;
                    }).join('');

                container.innerHTML = html;
            }

            function createEventHistoryTable(events) {
                const container = document.getElementById("eventHistory");
                let html = "";
                if (events.length === 0) {
                    html += `<div class="col-12 text-center">No events data available in table</div>`;
                } else {
                    html += `<table class="table table-hover table-responsive{-sm|-md|-lg|-xl}"><th>Property</th><th>Date & Time</th><th>Title</th><th>Description</th><th>Status</th></tr>`;
                    events.forEach((data) => {
                        html += `<tr>`;
                        html += `<td class="text-capitalize text-truncate">${data?.property?.title}</td>`;
                        html += `<td>${dateF2(data?.event_datetime)}</td>`;
                        html += `<td class="text-capitalize text-truncate">${data?.title}</td>`;
                        html += `<td class="text-capitalize text-truncate">${data?.description ?? `N/A`}</td>`;
                        html += `<td class="text-capitalize">${statusBtn(data?.status, data?.event_datetime)}</td>`;
                        html += `</tr>`;
                    });
                    html += `<table>`;
                }
                container.innerHTML = html;
            }

        </script>
    @endpush

@endsection
