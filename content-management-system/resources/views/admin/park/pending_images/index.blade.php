@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}
    <x-admin.breadcrumb active="Pending Images" />
    {{-- DataTable --}}
    <x-admin.datatable id="dt-table" title="User uploaded images">
        <x-slot:headings>
            <th>Name</th>
            <th>User</th>
            <th>Pending Images</th>
            <th>Verified Image</th>
            <th>Action</th>
        </x-slot:headings>
    </x-admin.datatable>
    @push('script')
        <script>
            var uRL = "{{ route('admin.park.pendingimage.dt_list') }}";
        </script>
        <script src="{{ asset('assets/js/dt/pending-image-dt.js') }}"></script>
    @endpush
@endsection
