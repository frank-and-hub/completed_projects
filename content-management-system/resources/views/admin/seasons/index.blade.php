@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->
@section('content')
 {{-- Breadcrumb --}}
 <x-admin.breadcrumb active="Seasons" :breadcrumbs="$breadcrumbs" />
 <x-admin.datatable id="season-tbl" title="Seasons" loaderID="dt-loader">
    <x-slot:custom_headings>
        <div class="form-inline">
            <label for="hemisphere" class="form-label">Select Hemisphere:</label>
            <select name="hemisphere" id="hemisphere" class="ml-2 selectpicker" data-width="190px" data-style="border border-muted" data-show-subtext='true'>
                <option value="north" selected="">
                    North
                </option>
                <option value="south">
                    South
                </option>
            </select>
        </div>
    </x-slot:custom_headings>

    <x-slot:headings>
        <th>Season</th>
        <th>Hemisphere</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>action</th>

    </x-slot:headings>
</x-admin.datatable>

@push('script')
<script> var season_dt_url = "{{route('admin.season.dt.list')}}";</script>
<script src="{{asset('assets/js/dt/setting-seasons-dt.js')}}"></script>

@endpush
 @endsection
