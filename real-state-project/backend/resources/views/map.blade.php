@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Admin Dashboard'))
<div id="mapContainer" class="allpropertyMap">
    <div id="map"></div>
</div>
<style>
    #map {
        height: 100vh;
    }
</style>
@push('custom-script')
    @if (auth()->guard('admin')->user()->hasRole('admin'))
        <script type="text/javascript">
            const PROPERTY_ALL_LAT_LNG_URL = "{{ route('common.property-lat-lng') }}";
            const PROPERTY_ADMIN_VIEW_PAGE = "{{ route('property.view', 123456) }}"
            const EXTERNAL_PROPERTY_ADMIN_VIEW_PAGE = "{{ route('property.view-external', 123456) }}"
        </script>
        <script src="{{ asset('assets/admin/js/chart.js?' . config('constants.asert_version')) }}"></script>
        <script src="{{ asset('assets/admin/js/dashboard.js?' . config('constants.asert_version')) }}"></script>
        <script src="{{ asset('assets/admin/js/admin_all_property_marker_map.js?' . config('constants.asert_version')) }}" type="module"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.gm-fullscreen-control').click();
                $('.navbar-toggler').click();
            });
        </script>
    @endif
@endpush
@endsection
