@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}
    <x-admin.breadcrumb active="Locations" />

    {{-- DataTable --}}
    <div style="position: relative">

        <x-admin.datatable id="location-table" title="Locations" loaderID='dt-loader'>

            <x-slot:other>
                <div class="row pl-4 mt-3">
                    <div class="col-12">
                        <div class="form-inline">
                            <label for="type" class="form-label">Display:</label>
                            <select class="selectpicker ml-2" id="dorpDownFilter"
                                data-style="border border-muted" data-show-subtext='true' title="All Locations">
                                <option value="" selected class="d-none"></option>
                                <optgroup label="Home Page Locations" data-max-options="1">
                                    <option value="active_location" >Active Locations</option>
                                    <option value="inactive_location">Inactive Locations</option>
                                </optgroup>
                                 <optgroup label="SEO Locations" data-max-options="1">
                                    <option value="greater_than_ten_parks">Active Locations (10 + parks) </option>
                                    <option value="less_then_ten_parks">Inactive Locations (less then 10 parks) </option>
                                </optgroup>
                            </select>
                            <button class="btn btn-primary ml-2" id="ApplyBtn" disabled>Apply</button>
                            <button class="btn btn-danger ml-2" disabled id="ResetAllSelectedBtn">Reset</button>
                        </div>
                    </div>
                </div>
            </x-slot:other>
            <x-slot:headings>
                <th>City</th>
                <th>State</th>
                <th>Country</th>
                <th>Action</th>
            </x-slot:headings>
        </x-admin.datatable>
    </div>

    @push('script')
        <script type="text/javascript">
            var uRL = `{{ route('admin.locations.index') }}`;
        </script>
        <script src="{{ asset('assets/js/dt/location-dt.js') }}"></script>
    @endpush
@endsection
