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
            {{-- <th>Total Image</th> --}}
            <th>Pending Images</th>
            <th>Verified Image</th>
            <th>Action</th>
        </x-slot:headings>
    </x-admin.datatable>

    {{-- users upload images datatable --}}
    <div id="user-image-datatable" class="d-none" style="position: relative;">
<x-admin.loader id="dt-loader"/>
        <x-admin.datatable id="pending-user-img-dt-tbl" title="">
            <x-slot:headings>
                <th>Name</th>
                <th>Total Images</th>
                <th>Pending Images</th>
                <th>Verified Images</th>
                <th>Action</th>
            </x-slot:headings>
        </x-admin.datatable>
    </div>

    @push('script')
        <script type="text/javascript">
            var uRL = "{{ route('admin.park.user.pending.images') }}";
            var user_pending_image_url = "{{ route('admin.park.unverified_users_images', 'park_id') }}";
            var user_pending_image_url1 = "{{ route('admin.park.unverified_users_images') }}";
        </script>

        <script src="{{ asset('assets/js/dt/pending-image-dt.js') }}"></script>
        {{-- <script src="{{ asset('assets/js/dt/users-unverified-dt.js') }}"></script> --}}
    @endpush
@endsection
