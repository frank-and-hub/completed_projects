@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Calendar'))
    @push('custom-css')
        {{--
        <script src="{{ asset('assets/admin/calender/fullcalendar.main.css') }}"></script> --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css" />
        <style>
            .wrapper {
                display: grid;
                grid-template-columns: 1fr 190px;
                grid-template-rows: 1fr;
                grid-column-gap: 0px;
                grid-row-gap: 0px;
            }

            .filter {
                grid-area: 1 / 2 / 2 / 3;
                margin: 6em 0 0 1em;
            }

            .event_filter_wrapper {
                margin: 0.5em 0;
            }

            #calendar {
                grid-area: 1 / 1 / 2 / 2;
                width: 100%
            }

            .festival .fc-time {
                display: none;
            }

            .fc-day-grid-event .fc-content {
                white-space: normal;
            }

            a.fc-event-past {
                background-color: grey !important;
                border: none;
            }

            a.fc-event.hidden {
                display: none;
            }

            .fc-button {
                color: black !important;
                background-color: #f0f0f0;
                border: 1px solid transparent;
            }

            .fc-button:hover {
                color: white !important;
                background-color: #007bff;
                border-color: #007bff;
            }

            .fc-toolbar-chunk {
                border: none !important
            }

            .fc-customToday-button {
                color: white !important;
                background-color: #F30051 !important
            }
        </style>
    @endpush
    <div class="content-wrapper">
        <div class="page-header">
            {{-- <h3 class="page-title"> Calendar</h3> --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Calendar</a></li>
                    <li class="breadcrumb-item active" aria-current="page">list</li>
                </ol>
            </nav>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class='remove_header'>
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row d-flex justify-content-between align-items-center">
                            <div class="mb-3">
                                <h4 class="card-title"></h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="calendarTable" class="table">
                                        <thead>
                                            <tr>
                                                {{-- <th>Sr.No.</th> --}}
                                                {{-- <th>Created At</th> --}}
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Title</th>
                                                <th>Address</th>
                                                <th>Description</th>
                                                <th>Tenant</th>
                                                <th>Agent/Landlord</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input name="date" value="" class="d-none" id="selectedDate" />
        </div>
    </div>
    @push('custom-script')
        <script src="{{ asset('assets/admin/calender/moment.min.js') }}"></script>
        <script src="{{ asset('assets/admin/calender/main.min.js') }}"></script>
        <script src="{{ asset('assets/admin/calender/moment.main.global.min.js') }}"></script>
        <script src="{{ asset('assets/admin/calender/icalendar.main.global.min.js') }}"></script>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {

                const ajax_url = `{{ route('calendar.index') }}`;
                let AJAX_URL_DATA_TABLE = ``;

                const filter = '';
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    headerToolbar: {
                        start: 'prev,next,customToday',
                        center: 'title',
                        end: '',
                    },
                    customButtons: {
                        customToday: {
                            text: 'Today',
                            click: function () {
                                calendar.gotoDate(new Date());
                            },
                            classNames: ['custom'],
                            class: 'hello'
                        }
                    },
                    timeZone: 'Europe/Berlin',
                    weekNumbers: true,
                    initialView: 'dayGridMonth',
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true,
                    },
                    views: {
                        dayGridWeek: {
                            titleFormat: '{DD.{MM.}}YYYY',
                        },
                        listWeek: {
                            titleFormat: '{DD.{MM.}}YYYY',
                        },
                    },
                    eventSources: [{
                        url: `{{ route('calendar.data') }}`,
                        method: 'GET',
                        extraParams: {
                            filter: filter,
                        },
                        failure: function (jqXHR, textStatus, errorThrown) {
                            console.error('Error fetching events:', textStatus, errorThrown);
                        }
                    }],
                    eventDataTransform: function (eventData) {
                        var startDateUTC = new Date(eventData.start);

                        eventData.start = startDateUTC.getFullYear() + '-' +
                            ('0' + (startDateUTC.getMonth() + 1)).slice(-2) + '-' +
                            ('0' + startDateUTC.getDate()).slice(-2) + ' ' +
                            ('0' + startDateUTC.getHours()).slice(-2) + ':' +
                            ('0' + startDateUTC.getMinutes()).slice(-2) + ':' +
                            ('0' + startDateUTC.getSeconds()).slice(-2);
                        return eventData;
                    },
                    eventClassNames: function (info) {
                        var result = true;
                        var states = [];
                        var kinds = [];

                        $("input[name='event_filter_sel']:checked").each(function () {
                            if ($(this).data('type') == 'state') {
                                states.push($(this).val());
                            } else if ($(this).data('type') == 'kind') {
                                kinds.push($(this).val());
                            }
                        });

                        if (states.length) {
                            result = result && states.indexOf(info.event.extendedProps.state) >= 0;
                        }

                        if (kinds.length) {
                            result = (result && kinds.indexOf(info.event.extendedProps.kind) >= 0) || info
                                .event.extendedProps.kind == 'holiday';
                        }
                        if (info.event.extendedProps.kind === 'meeting') {
                            result = 'meeting-event'; // Add a custom class for meetings
                        } else if (info.event.extendedProps.kind === 'appointment') {
                            result = 'appointment-event'; // Add a custom class for appointments
                        } else if (info.event.extendedProps.kind === 'holiday') {
                            result = 'holiday-event'; // Add a custom class for holidays
                        } else if (info.event.extendedProps.kind === 'concert') {
                            result = 'concert-event';
                        } else {
                            result = 'hidden';
                        }
                        return result;
                    },
                    windowResize: function (view) {
                        var current_view = view.type;
                        var expected_view =
                            $(window).width() > 800 ? 'dayGridMonth' : 'listWeek';
                        if (current_view !== expected_view) {
                            calendar.changeView(expected_view);
                        }
                    },
                    dateClick: function (info) {
                        const selectedDate = info.dateStr;
                        $('#selectedDate').val(selectedDate);
                        calendarTable.draw();
                        $(".fc-day").removeClass("highlighted");
                        $(info.dayEl).addClass("highlighted");
                    },
                });
                calendar.render();
                if ($(window).width() < 800) {
                    calendar.changeView('listWeek');
                }
                $('input[class=event_filter]').change(function () {
                    calendar.render();
                });

                function fetchEventsForDate(date) {
                    $.ajax({
                        url: `{{ route('adminSubUser.calendar.data') }}`,
                        method: 'GET',
                        data: {
                            date: date,
                            filter: filter
                        },
                        success: function (data) {

                            showEventPopup(data);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.error('Error fetching events:', textStatus, errorThrown);
                        }
                    });
                }

                function showEventPopup(events) {
                    let popupContent = '<h3>Events for this day:</h3>';
                    if (events.length === 0) {
                        popupContent += '<p>No events for this day.</p>';
                    } else {
                        events.forEach(function (event) {
                            popupContent +=
                                `<p><strong>${event.title}</strong><br>${event.start} - ${event.end}</p>`;
                        });
                    }


                    const popup = $('<div id="eventPopup" class="popup"><div class="popup-content"></div></div>');
                    popup.find('.popup-content').html(popupContent);
                    $('body').append(popup);


                    $('#eventPopup').show();


                    $(document).on('click', '#eventPopup', function (e) {
                        if (!$(e.target).closest('.popup-content').length) {
                            $('#eventPopup').remove();
                        }
                    });
                }

                $('input[class=event_filter]').change(function () {
                    calendar.render();
                });
                $('.fc-header-toolbar').css('margin', '1em');

                var calendarTable = $('#calendarTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ajax_url,
                        data: function (d) {
                            d.selectedDate = $('#selectedDate').val();
                        }
                    },
                    columns: [
                        {
                            data: 'event_datetime',
                            name: 'event_datetime',
                            render: function (data, type, row) {
                                return dateF2(data);
                            }
                        }, {
                            data: 'event_datetime',
                            name: 'event_datetime',
                            render: function (data, type, row) {
                                return timeF(data);
                            }
                        }, {
                            data: 'title',
                            name: 'title',
                            render: function (data, type, row, meta) {
                                return data ? (data.length > 30 ?
                                    `<span class="short-text">${data.substring(0, 30)}...</span>
                                                <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                                                <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                                    data) : '';
                            }
                        }, {
                            data: 'address',
                            name: 'address',
                            render: function (data, type, row, meta) {
                                return data ? (data.length > 30 ?
                                    `<span class="short-text">${data.substring(0, 30)}...</span>
                                                <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                                                <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                                    data) : '';
                            }
                        }, {
                            data: 'description',
                            name: 'description',
                            render: function (data, type, row, meta) {
                                return data ? (data.length > 30 ?
                                    `<span class="short-text">${data.substring(0, 30)}...</span>
                                                <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                                                <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                                    data) : '';
                            }
                        }, {
                            data: 'tenant',
                            name: 'tenant'
                        }, {
                            data: 'agent',
                            name: 'agent'
                        }, {
                            data: 'status',
                            name: 'status'
                        }],
                    drawCallback: function (settings, json) {

                        $('#calendarTable').off('click', '.view-more').on('click', '.view-more',
                            function () {
                                var $shortText = $(this).siblings('.short-text');
                                var $fullText = $(this).siblings('.full-text');

                                if ($shortText.is(':visible')) {
                                    $shortText.hide();
                                    $fullText.show();
                                    $(this).text('View Less');
                                } else {
                                    $shortText.show();
                                    $fullText.hide();
                                    $(this).text('View More');
                                }
                            });
                        $('[data-toggle=tooltip]').tooltip();
                    }
                });
            });
        </script>
    @endpush
@endsection
