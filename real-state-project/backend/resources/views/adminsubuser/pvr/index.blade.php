@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Calendar'))
@push('custom-css')

@endpush
<div class="content-wrapper">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Calendar</a></li>
                <li class="breadcrumb-item active" aria-current="page">List</li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-lg-12 mt-3">
            <div class="card">
                <div class="card-body">
                    <div class="row d-flex justify-content-center align-items-center">
                        <div class="row justify-content-around segment-controller ">
                            <label class="px-4 py-2 mx-0 px-0 border-0   rounded text-center ">
                                Upcoming
                                <input type="radio" id="upcoming" name="type" value="upcoming"
                                    class="d-none btn-clicked" checked />
                            </label>
                            <label class="px-4 py-2 mx-1 px-0 border-0   rounded text-center">
                                Completed
                                <input type="radio" id="completed" name="type" value="completed" class="d-none " />
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table id="pvrTable" class="table">
                                    <thead>
                                        <tr>
                                            <th>Property Name</th>
                                            <th>Property Type</th>
                                            <th>Tenant Name</th>
                                            <th>Request Time & Date</th>
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
    </div>
</div>
@push('custom-script')
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const ajax_url = `{{ route('adminSubUser.calendar.pvr_index') }}`;
    let AJAX_URL_DATA_TABLE = ``;
    const filter = '';

    var pvrTable = $('#pvrTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: ajax_url,
            data: function(d) {
                d.selectedDate = $('#selectedDate').val();
                d.selectedType = $('input[name=type]:checked').val();
            }
        },
        columns: [{
            data: 'property',
            name: 'property',
            searchable: true,
        }, {
            data: 'property_type',
            name: 'property_type',
            searchable: true,
        }, {
            data: 'tenant',
            name: 'tenant',
            searchable: true,
        }, {
            data: 'event_datetime',
            name: 'event_datetime',
            render: function(date) {
                return dateF2(date);
            }
        }, {
            data: 'status',
            name: 'status'
        }],
        drawCallback: function(settings, json) {
            $('#pvrTable').off('click', '.view-more').on('click', '.view-more',
                function() {
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

    $('#resetBtn').on('click', function() {
        $('#selectedDate').val('');
        pvrTable.draw();
    });

    // $('input[name=type]').click(function() {
    //     pvrTable.draw();
    //     $('input[name=type]').removeClass('btn-clicked');
    //     $(this).addClass('btn-clicked');

    //     var $label = $(this).closest('label');

    //     // $('label').not($label).css({
    //     //     'background-color': 'transparent',
    //     //     'color': 'black'
    //     // });

    //     $label.toggleClass('inactive-segment');

    //     // $label.css({
    //     //     'background-color': '#d71d1d',
    //     //     'color': 'white',
    //     //     'padding': '.8rem',
    //     //     'border-radius': '13px'
    //     // });
    // });


    $('input[name=type]').click(function() {
        pvrTable.draw();

        // Reset all buttons
        $('input[name=type]').removeClass('btn-clicked')
            .closest('label')
            .removeClass('inactive-segment');

        // Add classes to clicked button
        $(this).addClass('btn-clicked');

        var $label = $(this).closest('label');
        $label.addClass('inactive-segment');
    });
    $('input[name=type]:checked').trigger('click');

});
</script>
@endpush
@endsection
