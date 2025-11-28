@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
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

            .img-thumb {
                max-height: 75px;
                border: 2px solid none;
                border-radius: 3px;
                padding: 1px;
                cursor: pointer;
            }

            .img-thumb-wrapper {
                display: inline-block;
                margin: 10px 10px 0 0;
            }

            .remove {
                display: block;
                background: #444;
                border: 1px solid none;
                color: white;
                text-align: center;
                cursor: pointer;
            }

            .remove:hover {
                background: white;
                color: black;
            }

            /* Flex container for the buttons */
            .button-group {
                display: flex;
                gap: 5px;
            }

            /* Radio button styling */
            .button-group input[type="radio"] {
                display: none;
            }

            /* Label styling to look like buttons */
            .button-group label {
                display: inline-block;
                padding: 10px 20px;
                border: 1px solid #ddd;
                border-radius: 10px;
                cursor: pointer;
                font-size: 16px;
                text-align: center;
                transition: background-color 0.3s, color 0.3s;
            }

            /* Selected button style */
            .button-group input[type="radio"]:checked+label {
                background-color: #d92a55;
                color: white;
                border-color: #d92a55;
            }

            .inputWithIcon {
                position: relative;
            }

            .inputWithIcon input {
                padding-left: 2.5rem;
            }

            .inputWithIcon img {
                position: absolute;
                left: 20px;
                top: 65%;
                transform: translateY(-50%);
                color: #666;
                pointer-events: none;
            }
        </style>
    @endpush
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Properties</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('adminSubUser.property.index') }}">List</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol>
            </nav>
        </div>
        <form class="cmxform" id="add_property" name="add_property" enctype="multipart/form-data">
            @csrf
            <div class="row grid-margin">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Basic Information</h4>
                            <div class="row">
                                @if ($agent)
                                    <div class="col-4 form-group">
                                        <label for="agent">Agent <span class="text-danger">*</span></label>
                                        <select class="form-control" id="agent" name="agent">
                                            <option value="">Select Agent</option>
                                            @foreach ($agent as $agency_agent)
                                                <option value="{{ $agency_agent->id }}">{{ $agency_agent->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="{{ $agent ? 'col-4' : 'col-6' }} form-group">
                                    <label for="landSize">Land Size( m²)</label>
                                    <input id="landSize" class="form-control number_with_decimal" name="landSize"
                                        type="text">
                                </div>
                                <div class="{{ $agent ? 'col-4' : 'col-6' }} form-group">
                                    <label for="buildingSize">Building Size( m)</label>
                                    <input id="buildingSize" class="form-control number_with_decimal" name="buildingSize"
                                        type="text">
                                </div>
                                <div class="col-12 form-group">
                                    <label for="title">Title<span class="text-danger">*</span></label>
                                    <input id="title" class="form-control check-lorem" name="title" type="text">
                                </div>
                                <div class="col-6 form-group">
                                    <label for="propertyType">Property type <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="propertyType" name="propertyType">
                                        <option value="">Select type</option>
                                        @foreach(propertyTypes() as $propertyType)
                                            <option value="{{ $propertyType }}">{{ $propertyType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 form-group">
                                    <label for="propertyStatus">Property Status <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="propertyStatus" name="propertyStatus">
                                        <option value="Rental Monthly">Rental Monthly</option>
                                    </select>
                                </div>
                                <div class="col-6 form-group">
                                    <label for="bedroom"> Number of bedroom <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="bedroom" name="bedroom">
                                        <option value="">Select bedroom</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5+">5+</option>
                                    </select>
                                </div>
                                <div class="col-6 form-group">
                                    <label for="bathroom"> Number of bathroom <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="bathroom" name="bathroom">
                                        <option value="">Select bathroom</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5+">5+</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label> Property Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="" class="form-control check-lorem" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="card-title">Location & Address</h4>
                            <div class="row">
                                <div class="form-group col-4">
                                    <label for="country">Country <span class="text-danger">*</span></label>
                                    <select class="form-control countries-select2 map-address" id="country"
                                        name="country"></select>
                                </div>
                                <div class="form-group col-4">
                                    <label for="province">State / Province <span class="text-danger">*</span></label>
                                    <select class="form-control state-select2 map-address" id="state" name="state"
                                        disabled></select>
                                </div>
                                <div class="form-group col-4">
                                    <label for="city">City <span class="text-danger">*</span></label>
                                    <select class="form-control city-select2 map-address" id="city" name="city"
                                        disabled></select>
                                </div>
                                <div class="form-group col-4">
                                    <label for="suburb">Suburb <span class="text-danger">*</span></label>
                                    <select class="form-control suburb-select2 map-address" id="suburb" name="suburb"
                                        disabled></select>

                                    {{-- <label for="suburb">Suburb Name <span class="text-danger">*</span></label>
                                    <input id="suburb" class="form-control" name="suburb" type="text"> --}}
                                </div>
                                <div class="form-group col-4">
                                    <label for="streetNumber">Street Number <span class="text-danger">*</span></label>
                                    <input id="streetNumber" class="form-control map-address" name="streetNumber"
                                        type="text">
                                </div>
                                <div class="form-group col-4">
                                    <label for="streetName">Street Name <span class="text-danger">*</span></label>
                                    <input id="streetName" class="form-control map-address" name="streetName" type="text">
                                </div>
                                <div class="form-group col-6">
                                    <label for="unitNumber">Unit Number </label>
                                    <input id="unitNumber" class="form-control map-address" name="unitNumber" type="text">
                                </div>
                                <div class="form-group col-6">
                                    <label for="complexName">Complex Name </label>
                                    <input id="complexName" class="form-control check-lorem map-address" name="complexName"
                                        type="text">
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="card-title">Financials
                                <span id="financial_currency" class="currency"></span>
                            </h4>
                            <div class="row">
                                {{-- <div class="col-4 form-group">
                                    <label for="currency">Currency <span class="text-danger">*</span></label>
                                    <input id="currency" class="form-control" name="currency" type="text">
                                </div> --}}
                                <div class="col-4 form-group">
                                    <label for="price"> Price<span class="text-danger">*</span></label>
                                    <input id="price" class="form-control number_with_decimal" name="price" type="text">
                                </div>
                                <div class="col-4 form-group">
                                    <label for="ratesAndTaxes">Rates And Taxes</label>
                                    <input id="ratesAndTaxes" class="form-control number_with_decimal" name="ratesAndTaxes"
                                        type="text">
                                </div>
                                <div class="col-4 form-group">
                                    <label for="levy">Levy</label>
                                    <input id="levy" class="form-control number_with_decimal" name="levy" type="text">
                                </div>
                                <div class="col-4 form-group">
                                    <label for="depositRequired">Deposit Required<span class="text-danger">*</span></label>
                                    <input id="depositRequired" class="form-control number_with_decimal"
                                        name="depositRequired" type="text">
                                </div>
                                <div class="col-4 form-group">
                                    <label for="leasePeriod">Lease Period (Months)<span class="text-danger">*</span></label>
                                    <input id="leasePeriod" class="form-control number_with_decimal" name="leasePeriod"
                                        type="text">
                                </div>
                                {{-- <div class="col-4 form-group">
                                    <label for="priceUnit">Price Unit<span class="text-danger">*</span></label>
                                    <input id="priceUnit" class="form-control" name="priceUnit" type="text">
                                </div> --}}
                                <div class="col-4 form-group mt-4">
                                    <div class="form-check form-check-flat form-check-primary">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="isReduced">
                                            Is Reduced
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body rental_property_feature">
                            <h4 class="card-title" id="toggleCollapse">Rental Property Features
                                <span id="rental_property_feature_toggleCollapse"></span>
                            </h4>
                            <div class="mt-4">
                                @foreach ($checkbox_columns as $key => $checkbox_column_)
                                    <div class="col-lg-12">
                                        <div class="accordion border-0" id="accordion-{{ $key }}-{{ $key }}" role="tablist">
                                            <div class="" role="tab" id="heading-{{ $key }}">
                                                <h5 class="">
                                                    <a data-toggle="collapse" href="#collapse-{{ $key }}-{{ $key }}"
                                                        aria-expanded="false" aria-controls="collapse-{{ $key }}-{{ $key }}"
                                                        class="row justify-content-between " onclick="toggleIcon(this)">
                                                        {{ ucwords(checkBoxTextUpadte([$key])) }} :-
                                                        <i class="fa fa-chevron-circle-down view-icon"
                                                            style="color:#FF5E6D;"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                        </div>
                                        <div id="collapse-{{ $key }}-{{ $key }}" class="collapse" role="tabpanel"
                                            aria-labelledby="heading-{{ $key }}-{{ $key }}"
                                            data-parent="#accordion-{{ $key }}-{{ $key }}">
                                            <input class="form-control" name="search_{{ $key }}" id="searchInput_{{$key}}"
                                                placeholder="Search Features... " />
                                            <div class="accordion accordion-bordered row border-0 shadow-none"
                                                id="accordion-{{ $key }}" role="tablist">
                                                @foreach ($checkbox_column_ as $k => $checkbox_column)
                                                    <div class="card m-0 border-0 col-lg-4 ">
                                                        <label class="card-header border-0 py-2" role="tab"
                                                            id="heading-{{ $k }}-{{ $key }}">
                                                            <h6 class="font-weight-bold">
                                                                {{ checkBoxTextUpadte([$k]) }}
                                                            </h6>
                                                        </label>
                                                        <div role="tabpanel" data-parent="#accordion-{{ $key }}">
                                                            <div class="card-body pb-2">
                                                                <div class="row p-0">
                                                                    <div class="col-md-12 m-0">
                                                                        <div class="form-group">
                                                                            @foreach ($checkbox_column as $checkbox_colum)
                                                                                <div class="form-check form-check-primary">
                                                                                    <label class="form-check-label">
                                                                                        <input type="checkbox"
                                                                                            class="form-check-input opacity-100"
                                                                                            name="{{ $k }}[{{ $checkbox_colum[0] }}]">
                                                                                        <span
                                                                                            class="searchedHtml">{{ checkBoxTextUpadte($checkbox_colum) }}</span>
                                                                                    </label>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <script type="text/javascript">
                                        document.getElementById('searchInput_{{$key}}').addEventListener('input', function () {
                                            var searchQuery = this.value.trim().toLowerCase();
                                            var formChecks = document.querySelectorAll('#accordion-{{ $key }} .form-check-primary');
                                            var cardHeaders = document.querySelectorAll('#accordion-{{ $key }} .card-header');
                                            if (searchQuery === '') {
                                                formChecks.forEach(function (label) {
                                                    label.style.display = '';
                                                });
                                                cardHeaders.forEach(function (header) {
                                                    var card = header.closest('.card');
                                                    header.style.display = '';
                                                    card.style.display = '';
                                                });
                                            } else {
                                                formChecks.forEach(function (label) {
                                                    var spanText = label.querySelector('.searchedHtml').textContent.trim().toLowerCase();
                                                    var card = label.closest('.card');
                                                    var cardHeader = card.querySelector('.card-header');
                                                    label.style.display = (spanText.includes(searchQuery)) ? 'block' : 'none';
                                                });

                                                cardHeaders.forEach(function (cardHeader) {
                                                    var card = cardHeader.closest('.card');
                                                    var formChecksInCard = card.querySelectorAll('.form-check-primary');
                                                    var visibleCheckboxes = Array.from(formChecksInCard).some(function (check) {
                                                        return check.style.display !== 'none';
                                                    });
                                                    card.style.display = (!visibleCheckboxes) ? 'none' : 'block';
                                                });
                                            }
                                        });
                                    </script>
                                @endforeach

                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="card-title">Property Images</h4>
                            <div class="row">
                                <div class="col-12 form-group">
                                    <label for="ismain_image"> Upload main Image <span class="text-danger">*</span></label>
                                    <input id="ismain_image" class="form-control dropify" name="ismain_image" type="file">
                                </div>
                                <div class="col-12 form-group">
                                    <label for="ismain">Upload All Images<span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col">
                                            <input type="file" id="files" name="files[]" multiple class="form-control" />
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="alert alert-success success_msg" role="alert"></div>
                            <div class="alert alert-danger error_msg" role="alert"></div>
                            <button type="submit" class="btn btn-primary mr-2">Submit</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Map Location</h4>
                            <div class="row">

                                <div class="col-12 form-group mt-4 d-none">
                                    <input id="pac-input" class="controls" type="text" placeholder="Search Box" />
                                </div>

                                <div class="col-12 form-group">
                                    <div id="map"></div>
                                </div>

                                <div class="col-12 form-group inputWithIcon">
                                    <label for="latitude">Latitude <span class="text-danger">*</span></label>
                                    <input id="latitude" class="form-control" name="latitude" type="text"
                                        placeholder="Latitude" readonly>
                                    <img src="{{ asset('assets/admin/images/lat.png') }}" class="toggle-password"
                                        style="top: 72px;" />
                                </div>

                                <div class="col-12 form-group inputWithIcon">
                                    <label for="longitude">Longitude <span class="text-danger">*</span></label>
                                    <input id="longitude" class="form-control" name="longitude" type="text"
                                        placeholder="Longitude" readonly>
                                    <img src="{{ asset('assets/admin/images/long.png') }}" class="toggle-password"
                                        style="top: 72px;" />
                                    {{-- <i class="fa fa-map-marker-alt" aria-hidden="true"></i> --}}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                    <div class="modal-footer">
                        <button type="submit" class="btn theme_btn_1">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('custom-script')
    <script src="https://www.dukelearntoprogram.com/course1/common/js/image/SimpleImage.js"></script>
    <script src="{{ asset('assets/admin/js/search_drag_marker_map.js') }}"></script>

    <script type="text/javascript">
        function upload() {
            $('.imgcanvas').css('display', 'inline-block')
            var imgcanvas = document.getElementById("features_img_canv1");
            var fileinput = document.getElementById("features_img");
            var image = new SimpleImage(fileinput);
            image.drawTo(imgcanvas);
        }

        function toggleIcon(anchor) {
            // Find the icon inside the clicked <a> element
            const icon = anchor.querySelector('.view-icon');

            // Toggle between the "down" and "up" chevron classes
            if (icon.classList.contains('fa-chevron-circle-down')) {
                icon.classList.remove('fa-chevron-circle-down');
                icon.classList.add('fa-chevron-circle-up');
            } else {
                icon.classList.remove('fa-chevron-circle-up');
                icon.classList.add('fa-chevron-circle-down');
            }
        }

        $(document).ready(function () {
            if (window.File && window.FileList && window.FileReader) {
                $("#files").on("change", function (e) {
                    var files = e.target.files,
                        filesLength = files.length;
                    for (var i = 0; i < filesLength; i++) {
                        var f = files[i]
                        var fileReader = new FileReader();
                        fileReader.onload = (function (e) {
                            var file = e.target;
                            $("<div class=\"img-thumb-wrapper card shadow\">" +
                                "<img class=\"img-thumb\" src=\"" + e.target.result +
                                "\" data-original-title=\"" + file.name + "\"/>" +
                                "<br/><span class=\"remove\">Remove</span>" +
                                "</div>").insertAfter("#files");
                            $(".remove").click(function () {
                                $(this).parent(".img-thumb-wrapper").remove();
                            });

                        });
                        fileReader.readAsDataURL(f);
                    }
                });
            } else {
                alert("Your browser doesn't support to File API")
            }

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
                    $('#time_slot_model').modal('hide');
                    const startTime = $(form).find('[name="start_time"]').val();
                    const endTime = $(form).find('[name="end_time"]').val();
                    const startDay = $(form).find('[name="start_day_of_week"]').val();
                    const endDay = $(form).find('[name="end_day_of_week"]').val();

                    // 2. Inject values into hidden inputs in the second form
                    const $secondForm = $("form[name='add_property']");

                    // Create hidden fields if they don't exist
                    if ($secondForm.find('[name="start_time"]').length === 0) {
                        $secondForm.append('<input type="hidden" name="start_time">');
                    }
                    if ($secondForm.find('[name="end_time"]').length === 0) {
                        $secondForm.append('<input type="hidden" name="end_time">');
                    }
                    if ($secondForm.find('[name="start_day_of_week"]').length === 0) {
                        $secondForm.append('<input type="hidden" name="start_day_of_week">');
                    }
                    if ($secondForm.find('[name="end_day_of_week"]').length === 0) {
                        $secondForm.append('<input type="hidden" name="end_day_of_week">');
                    }

                    // 3. Set the values
                    $secondForm.find('[name="start_time"]').val(startTime);
                    $secondForm.find('[name="end_time"]').val(endTime);
                    $secondForm.find('[name="start_day_of_week"]').val(startDay);
                    $secondForm.find('[name="end_day_of_week"]').val(endDay);

                    // 4. Submit the second form
                    $secondForm.submit();
                }
            });

            $("form[name='add_property']").validate({
                rules: {
                    agent: {
                        required: true
                    },
                    title: {
                        required: true
                    },
                    // buildingSize: {
                    //     required: true
                    // },
                    // landSize: {
                    //     required: true
                    // },
                    propertyType: {
                        required: true
                    },
                    propertyStatus: {
                        required: true
                    },
                    bedroom: {
                        required: true
                    },
                    bathroom: {
                        required: true
                    },
                    description: {
                        required: true
                    },
                    country: {
                        required: true
                    },
                    state: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                    streetNumber: {
                        required: true
                    },
                    streetName: {
                        required: true
                    },
                    suburb: {
                        required: true
                    },
                    unitNumber: {
                        required: false
                    },
                    complexName: {
                        required: false
                    },
                    // currency: {
                    //     required: true
                    // },
                    price: {
                        required: true
                    },
                    // ratesAndTaxes: {
                    //     required: true
                    // },
                    // levy: {
                    //     required: true
                    // },
                    depositRequired: {
                        required: true
                    },
                    leasePeriod: {
                        required: true
                    },
                    // priceUnit: {
                    //     required: true
                    // },
                    ismain_image: {
                        required: true
                    },
                    files: {
                        required: true
                    },
                    latitude: {
                        required: true
                    },
                    longitude: {
                        required: true
                    }
                },
                messages: {
                    agent: {
                        required: 'Select Agent'
                    },
                    title: {
                        required: 'Enter title'
                    },
                    // buildingSize: {
                    //     required: 'Enter building size'
                    // },
                    // landSize: {
                    //     required: 'Enter land size'
                    // },
                    propertyType: {
                        required: 'Select property type'
                    },
                    propertyStatus: {
                        required: 'Select property status'
                    },
                    bedroom: {
                        required: 'Select bedroom'
                    },
                    bathroom: {
                        required: 'Select bedroom'
                    },
                    description: {
                        required: 'Enter description'
                    },
                    country: {
                        required: 'Select country'
                    },
                    state: {
                        required: 'Select state'
                    },
                    city: {
                        required: 'Select city'
                    },
                    streetNumber: {
                        required: 'Enter street number'
                    },
                    streetName: {
                        required: 'Enter street name'
                    },
                    suburb: {
                        required: 'Enter suburb name'
                    },
                    unitNumber: {
                        required: 'Enter unit number'
                    },
                    complexName: {
                        required: 'Enter complex name'
                    },
                    // currency: {
                    //     required: 'Enter currency'
                    // },
                    price: {
                        required: 'Enter price'
                    },
                    // ratesAndTaxes: {
                    //     required: 'Enter rates and taxes'
                    // },
                    // levy: {
                    //     required: 'Enter levy'
                    // },
                    depositRequired: {
                        required: 'Enter deposit required'
                    },
                    leasePeriod: {
                        required: 'Enter lease period'
                    },
                    // priceUnit: {
                    //     required: 'Enter price unit'
                    // },
                    ismain_image: {
                        required: 'Select property main image'
                    },
                    files: {
                        required: 'Select property all images'
                    },
                    latitude: {
                        required: "Kindly drag the marker to the property location on the map."
                    },
                    longitude: {
                        required: "Kindly drag the marker to the property location on the map."
                    },
                },
                submitHandler: function (form) {
                    if(!$('#time_slot_model').validate().numberOfInvalids() > 0) {
                        $('#time_slot_model').modal('show');
                        return false;
                    }
                    const $loremFields = $(this).find('.check-lorem');
                    let hasLorem = false;

                    $loremFields.each(function () {
                        if (isLoremIpsum($(this).val())) {
                            hasLorem = true;
                            return false; // break the loop
                        }
                    });

                    if (hasLorem) {
                        e.preventDefault();
                        alert('Please replace Lorem Ipsum text with real content before submitting.');
                        return false;
                    }

                    $.ajax({
                        url: "{{ route('adminSubUser.property.insert_property') }}",
                        type: "POST",
                        data: new FormData(form),
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response.status == 'success') {
                                $("form[name='add_property']").find(
                                    '.serverside_error').remove();
                                $('.success_msg').html(response.msg);
                                $('.success_msg').fadeIn();
                                setTimeout(function () {
                                    $('.success_msg').fadeOut();
                                }, 5000);
                                $('#add_property')[0].reset();

                                Swal.fire({
                                    title: "Property Successfully Created!",
                                    icon: "success",
                                    draggable: true,
                                    showConfirmButton: true,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "{{ route('adminSubUser.property.index') }}";
                                    }
                                });

                            } else {
                                $("form[name='add_property']").find(
                                    '.serverside_error').remove();
                                $('.error_msg').html(response.msg);
                                $('.error_msg').fadeIn();
                                setTimeout(function () {
                                    $('.error_msg').fadeOut();
                                }, 5000);
                            }
                        },
                        error: function (xhr, status, error) {
                            handleServerError('add_property', xhr.responseJSON
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

            $('.countries-select2').on('select2:select', function (e) {
                result = e.params.data;
                $('#financial_currency').html("(In " + result.currency + "(" + result.currency_symbol + "))")
            });

            $('form').on('keypress', function (e) {
                if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                }
            });

            $('.map-address').on('change', function () {
                let fullAddress = '';

                $('.map-address').each(function () {
                    let part = $(this).is('select')
                        ? $(this).find('option:selected').text()
                        : $(this).val();

                    if (part && part.trim() !== '') {
                        fullAddress += part.trim() + ', ';
                    }
                });

                fullAddress = fullAddress.replace(/,\s*$/, '');
                $('#pac-input').val(fullAddress);

                if (geocoder) {
                    geocoder.geocode({ address: fullAddress }, (results, status) => {
                        if (status === "OK") {
                            const location = results[0].geometry.location;
                            draggableMarker.position = location;
                            map.setCenter(location);
                            $('#latitude').val(location.lat());
                            $('#longitude').val(location.lng());
                        } else {
                            console.warn("Geocode failed:", status);
                        }
                    });
                }
            });


        });
    </script>
@endpush
