@extends('admin.layout.master')
@section('content')
    @push('custom-style')
        <link rel="stylesheet" href="{{ asset('assets/daterangepicker-master/daterangepicker.css') }}">

        <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    @endpush
    {{-- Breadcrumb --}}
    <x-admin.breadcrumb active="Edit" :breadcrumbs="$breadcrumbs" />

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header bg-lightblue">
                    <h5 class="mb-0 text-primary">Edit Season</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.season.update', $season->id) }}" method="post" class="mt-3">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="season-selectpicker" class="form-label">Name:</label>
                                    {{-- <input type="text" class="form-control" name="name" id="name"> --}}
                                    <select name="season" id="season-selectpicker" data-style="border border-muted"
                                        class="selectpicker form-control" data-container="body" data-width="100%">
                                        <option value="autumn" @selected($season->season == 'autumn')>Autumn</option>
                                        <option value="spring" @selected($season->season == 'spring')>Spring</option>
                                        <option value="summer" @selected($season->season == 'summer')>Summer</option>
                                        <option value="winter" @selected($season->season == 'winter')>Winter</option>


                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-gorup">
                                    <label for="hemishphere-selectpicker" class="form-label">Hemisphere:</label>
                                    <select name="hemisphere" id="hemishphere-selectpicker" data-style="border border-muted"
                                        class="selectpicker form-control" data-container="body" data-width="100%">
                                        <option value="north" @selected($season->hemisphere == 'north')>North</option>
                                        <option value="south" @selected($season->hemisphere == 'south')>South</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" style="position: relative">
                                    <label class="form-label" for="start_date">Start Date:</label>
                                    <input type="text" id="start_date" name="start_date" class="form-control daterange"
                                        value="{{ old('start_date') ?? $season->start_date }}">
                                    <input type="text" stid="season_start_date" name="season_start_date"
                                        value="{{ old('season_start_date') ?? $season->season_start_date }}"
                                        style="visibility: hidden;
                                        position: absolute;
                                        top: 35px;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group" style="position: relative">
                                    <label class="form-label" for="end-date">End Date:</label>
                                    <input type="text" id="end-date" name="end_date" class="form-control daterange"
                                        value="{{ old('end_date') ?? $season->end_date }}">

                                    <input type="text" id="season_end_date" name="season_end_date"
                                        value="{{ old('season_end_date') ?? $season->season_end_date }}"  style="visibility: hidden;
                                        position: absolute;
                                        top: 35px;">
                                </div>
                            </div>

                        </div>

                        <div class="d-row d-flex justify-content-end">
                            <input type="submit" value="Save" class="btn btn-primary">

                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('script')
        <script src="{{ asset('assets/daterangepicker-master/moment.min.js') }}"></script>
        <script src="{{ asset('assets/daterangepicker-master/daterangepicker.js') }}"></script>
        <script>
            var current_year = "{{ \Carbon\Carbon::now()->format('Y') }}";

            $('input[name=start_date]').click(function() {
                $('input[name=season_start_date]').click();
            });

            $('input[name=end_date]').click(function() {
                $('input[name=season_end_date]').click();
            });

            $('input[name=season_start_date],input[name=season_end_date]').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                },
                dateFormat: 'YYYY-MM-DD',
                singleDatePicker: true,
                showDropdowns: false,
            });


            $('input[name=season_start_date]').on('apply.daterangepicker', function(ev, picker) {
                $("input[name=start_date]").val(picker.startDate.format('MMM DD'));
            });

            $('input[name=season_end_date]').on('apply.daterangepicker', function(ev, picker) {
                $("input[name=end_date]").val(picker.startDate.format('MMM DD'));
            });
        </script>
    @endpush
@endsection
