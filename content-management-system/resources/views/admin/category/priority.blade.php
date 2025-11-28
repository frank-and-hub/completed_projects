@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}
    <x-admin.breadcrumb active="Priority" :breadcrumbs="$breadcrumbs" />
    {{-- DataTable --}}
    <div style="position: relative;">
        <x-admin.loader id="loader" />
        <x-admin.datatable id="priority-tbl" title="Categories">
            <x-slot:custom_headings>
                <div class="form-inline">
                    <label for="type" class="form-label">Type:</label>
                    <select name="type" id="type" class="ml-2 selectpicker" data-width="190px"
                        data-style="border border-muted" data-show-subtext='true'>

                        <option value="all" selected>
                            All
                        </option>
                        <option value="parent">
                            Parent
                        </option>
                        <option value="no-child">
                            Standalone
                        </option>
                        <optgroup label="Seasonal">
                            <option value="all_seasonal" data-subtext="(Seasonal)">
                                All
                            </option>
                            <option value="parent_special" data-subtext="(Seasonal)">
                                Parent
                            </option>
                            <option value="standalone_special" data-subtext="(Seasonal)">
                                Standalone
                            </option>
                        </optgroup>
                    </select>
                </div>
            </x-slot:custom_headings>
            <x-slot:headings>
                <th>Name</th>
                <th>Display</th>
                <th>Show</th>
                <th>Priority</th>
            </x-slot:headings>
        </x-admin.datatable>
    </div>
@endsection
@push('script')
    <script type="text/javascript">
        var uRL = "{{ route('admin.category.priority.dtlist') }}";
        var priority_udpate_url = "{{ route('admin.category.priority.update') }}";
    </script>
    <script src="{{ asset('assets/js/dt/priority-dt.js') }}"></script>
@endpush
