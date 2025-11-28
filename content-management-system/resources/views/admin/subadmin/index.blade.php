@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}
    <x-admin.breadcrumb active="Admins" header-button-route="{{ route('admin.subadmin.create') }}"
        header-button="Create New" />

    {{-- DataTable --}}
    <div style="position: relative">
    <x-admin.datatable id="subadmin-table" title="Admins" loaderID="dt-loader">
        <x-slot:headings>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </x-slot:headings>
    </x-admin.datatable>
    </div>
@endsection
@push('script')
    <script type="text/javascript">
        var Url = "{{ route('admin.subadmin.dtlist') }}";
    </script>
    <script src="{{ asset('assets/js/dt/subadmin-dt.js') }}"></script>
@endpush
