@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}

    <x-admin.breadcrumb :breadcrumbs="$breadcrumbs" active="{{ 'Unverified Images Of ' . ucwords($park->name) }}" />

    {{-- DataTable --}}

    <x-admin.datatable id="pending-user-img-dt-tbl" title="Pending Images ({{ ucwords($park->name) }})">
        <x-slot:headings>
            <th>Name</th>
            <th>Total Image(s)</th>
            <th>Verified Image(s)</th>
            <th>Unverified Image(s)</th>
            <th>Action</th>
        </x-slot:headings>
    </x-admin.datatable>
    @push('script')
        <script>
            var user_pending_image_url = "{{ route('admin.park.unverified_users_images', $park->id) }}";
        </script>
        <script src="{{ asset('assets/js/dt/users-unverified-dt.js') }}"></script>
    @endpush
@endsection
