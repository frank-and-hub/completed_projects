@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}
    <x-admin.breadcrumb active="Parent Features" header-button-route="{{ route('admin.feature_type.create') }}"
        header-button="Add New Parent Feature" />

    {{-- Datatable --}}

    <div class="card">
        <div class="card-header bg-lightblue mb-0">
            <div class="d-flex">
                <div class="form-inline mr-auto">
                    <label for="selecteFeatureDropDown" class="form-label">Display:</label>

                    <select name="type" id="selecteFeatureDropDown" class="ml-2 selectpicker" data-width="190px"
                        data-style="border border-muted">
                        <option value="parent" selected="">
                            Parent Features
                        </option>
                        <option value="child">
                            Child Features
                        </option>

                    </select>
                </div>

                <div class="form-inline ml-3" id="parent-feature-type-filter">
                    <label for="type" class="form-label">Type:</label>
                    <select name="type" id="type" class="ml-2 selectpicker" data-width="190px"
                        data-style="border border-muted">
                        <option value="all" selected="">
                            All
                        </option>
                        <option value="normal">
                            Normal
                        </option>
                        <option value="popular">
                            Popular
                        </option>
                    </select>
                </div>

                <div class="form-inline ml-3 d-none" id="child-feature-type-filter">
                    <label for="type" class="form-label">Type:</label>
                    <select name="type" id="child_feature_type" class="ml-2 selectpicker" data-width="190px"
                        data-style="border border-muted">
                        <option value="all" selected="">
                            All
                        </option>
                        <option value="normal">
                            Normal
                        </option>
                        <option value="popular">
                            Popular
                        </option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Parent Table --}}
        <div style="position: relative">
        <div id="parent_feature">
            <x-admin.datatable id="feature_type-table" title="" loaderID="dt-loader">
                <x-slot:headings>
                    <th>Name</th>
                    {{-- <th>Priority</th> --}}
                    <th>Type</th>
                    <th>Total Child Features</th>
                    <th>Priority</th>
                    <th>Action</th>
                </x-slot:headings>
            </x-admin.datatable>
        </div>
    </div>

        {{-- Child table  --}}
        <div style="position: relative">
        <div class="d-none" id="child_feature">
            <x-admin.datatable id="child-feature-dt-table" title="" loaderID='child-feature-dt-loader'>
                <x-slot:headings>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Parent Feature</th>
                    <th>Action</th>
                </x-slot:headings>
            </x-admin.datatable>
        </div>
        </div>

    </div>


    <div style="position: relative">
        <div id="popular_child_feature" class="d-none">
            <x-admin.datatable id="feature-table" title="Popular Child Features" loaderID='popular-child-feature-dt-loader'>
                <x-slot:headings>
                    <th>Name</th>
                    <th>Parent Feature</th>
                    <th>Priority</th>
                    <th>Action</th>
                </x-slot:headings>
            </x-admin.datatable>
        </div>
    </div>
@endsection
@push('script')
    <script type="text/javascript">
        var uRL = "{{ route('admin.feature_type.dt_list') }}";
        var childFeatureUrl = "{{ route('admin.popular.child.feature.db.list') }}";
        var ChildFeatureDttbl = "{{ route('admin.child_feature.dt') }}";
    </script>
    <script src="{{ asset('assets/js/dt/feature-type-dt.js') }}"></script>
@endpush
