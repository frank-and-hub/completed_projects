<div class="card">
    <div class="card-body">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="mb-3">
                <h6 class="cart-title title_for_table">Matched Property list</h6>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="matchedproperty" class="table">
                        <thead>
                            <tr>
                                {{-- <th>Sr.No.</th> --}}
                                <th>Created At</th>
                                <th>Tenant</th>
                                @if (auth()->user()->getRoleNames()->first() == 'agency')
                                    <th>Agent</th>
                                @endif
                                <th>Title</th>
                                <th>Property Type</th>
                                <th>
                                    Action <a href="javascript:void(0)" id="eventScheduleModelCheckedOne"
                                        class="btn d-none" data-toggle="tooltip" class="btn btn-xs" data-placement="top"
                                        data-original-title="Create event"><i class="fa fa-calendar"></i></a>
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
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
                <div class="modal-body">
                    <div>
                        {{-- <p style="font-size:20px;color:rgb(79, 79, 163)" id="">You have Already 2 upcoming event
                            for this matched property.</p> --}}
                    </div>
                    @csrf
                    <select name="SentInternalPropertyUser_id" class="d-none" id="SentInternalPropertyUser_id"
                        ></select>
                    {{-- <input type="hidden" name="SentInternalPropertyUser_id[]" id="SentInternalPropertyUser_id">
                    --}}
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
                            <input type="date" class="form-control" name="date" placeholder="Enter date"
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="time" placeholder="Enter Time"
                                pattern="\d{2}:\d{2}">
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
                    {{-- <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button> --}}
                    <button type="submit" class="btn theme_btn_1">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('custom-script')
    <script type="text/javascript">
        // {{-- role_agency = ("{{ $role == 'agency' ? 1 : 0 }}" == 1) ? true : false; --}}
        $(document).ready(function() {
            $(document).on('click', '.eventschedulemodel', function() {
                $('#event_schedule')[0].reset();
                var dataId = $(this).attr('data-id');
                $('.theme_btn_1').prop('disabled', false);
                $('#SentInternalPropertyUser_id').empty('');
                $('#SentInternalPropertyUser_id').append(`<option value='${dataId}' selected></option>`);
                $('#eventschedulemodel').modal('show');
            });

            var isAgency = "{{ auth()->user()->getRoleNames()->first() }}" === 'agency';

            // Define the columns dynamically based on the role
            var columns = [{
                data: 'created_at',
                name: 'created_at',
                render: function (data, type, row) {
                    return dateF2(data);
                }
            }, {
                data: 'tenant',
                name: 'tenant'
            }];

            if (isAgency) {
                columns.push({
                    data: 'agent',
                    name: 'agent'
                });
            }

            // Add the remaining common columns
            columns.push({
                data: 'title',
                name: 'title'
            }, {
                data: 'property_type',
                name: 'property_type'
            }, {
                data: 'action',
                name: 'action',
                orderable: false
            });

            // Initialize the DataTable
            var adminpropertyreque = $('#matchedproperty').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('adminSubUser.match-property.index') }}",
                columns: columns, // Use the dynamically created columns array
                drawCallback: function(settings) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });

            $.validator.addMethod("lessThanOneMonth", function(value, element) {
                var today = new Date();
                var nextMonth = new Date(today);
                nextMonth.setMonth(today.getMonth() + 1);
                var inputDate = new Date(value);
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
                submitHandler: function(form, e) {
                    e.preventDefault();
                    $('.theme_btn_1').prop('disabled', true);
                    var submitButton = $(form).find('button[type="submit"]');
                    submitButton.prop('disabled', true);
                    $.ajax({
                        url: `{{ route('adminSubUser.calendar.create') }}`,
                        type: "POST",
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.status == 'success') {
                                setTimeout(function() {
                                    $('#eventschedulemodel').modal('hide');
                                }, 500);
                                $("form[name='event_schedule']").find('.serverside_error')
                                    .remove();
                                $('.success_msg').html(response.msg);
                                $('.success_msg').fadeIn();
                                setTimeout(function() {
                                    $('.success_msg').fadeOut();
                                }, 3000);
                                $('#matchedproperty').DataTable().ajax.reload();
                                $('#event_schedule')[0].reset();
                            } else {
                                $("form[name='event_schedule']").find('.serverside_error')
                                    .remove();
                                $('.error_msg').html(response.msg);
                                $('.error_msg').fadeIn();
                                setTimeout(function() {
                                    $('.error_msg').fadeOut();
                                }, 3000);
                            }
                            $('.theme_btn_1').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            handleServerError('event_schedule', xhr.responseJSON.errors);
                            $('.theme_btn_1').prop('disabled', true);
                        }
                    });
                }
            });

            let title_for_table = window.location.pathname.includes('dashboard');

            if (title_for_table !== true) {
                let titles = document.getElementsByClassName('title_for_table');
                for (let title of titles) {
                    title.innerHTML = "";
                }
            }

            $(document).on('change', 'input[name^="properties_"]', function() {
                let ids = [];
                let row = $(this).closest('tr');
                let calendarLink = row.find('a.calendar-link');

                $('input[name^="properties_"]:checked').each(function() {
                    ids.push($(this).data('id'));
                });

                $('input[name^="properties_"]:not(:checked)').each(function() {
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

            $(document).on('click', '#eventScheduleModelCheckedOne', function() {
                let dataId = $(this).attr('data-ids');
                // let idsArray = JSON.parse(dataId);
                $('#event_schedule')[0].reset();
                $('.theme_btn_1').prop('disabled', false);
                $('#SentInternalPropertyUser_id').empty('');
                // idsArray.forEach(function(id) {
                $('#SentInternalPropertyUser_id').append(`<option value="${id}" selected></option>`);
                // });
                $('#eventschedulemodel').modal('show');
            });
        });
    </script>
@endpush
