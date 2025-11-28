@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->
@section('content')
 {{-- Breadcrumb --}}
 <x-admin.breadcrumb active="Categories" header-button-route="{{route('admin.category.create')}}" header-button="Add New Category" headerSeasonBtn="Seasons" />
<div style="position: relative">
    {{-- DataTable --}}
    <x-admin.datatable id="category-table" title="Categories" loaderID="dt-loader">
        <x-slot:custom_headings>
            <div class="form-inline">
                <label for="type" class="form-label">Type:</label>
                <select name="type" id="type" class="ml-2 selectpicker" data-width="190px" data-style="border border-muted" data-show-subtext='true'>
                    <option value="all" selected="">
                        All
                    </option>
                    <option value="no-child">
                        Standalone
                    </option>
                    <option value="parent">
                        Parent
                    </option>
                    <optgroup label="Special">
                    <option value="all_special" data-subtext="(Seasonal)" >
                       All
                    </option>
                     <option value="parent_special" data-subtext="(Seasonal)" >
                        Parent
                     </option>
                     <option value="standalone_special" data-subtext="(Seasonal)" >
                        Standalone
                     </option>
                    </optgroup>
                </select>
            </div>
        </x-slot:custom_headings>
        <x-slot:headings>
            <th>Name</th>
            <th>Display</th>
            <th>Priority</th>
            <th>Total Child Categories</th>
            <th>Action</th>
        </x-slot:headings>
    </x-admin.datatable>
</div>
@endsection
@push('script')
    <script type="text/javascript">
        var uRL = "{{ route('admin.category.dt_list') }}";
        // var types = "{{!empty($type)?$type:null}}";
    </script>
    <script src="{{ asset('assets/js/dt/category-dt.js') }}"></script>
@endpush
