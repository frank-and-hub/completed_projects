@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}
    <x-admin.breadcrumb active="Pending Reviews" />
    {{-- DataTable --}}
    <x-admin.datatable id="dt-table" title="Pending Reviews">
        <x-slot:headings>
            <th>Park</th>
            <th>User</th>
            <th>Pending Reviews</th>
            <th>Ratings</th>
            <th>Action</th>
        </x-slot:headings>
    </x-admin.datatable>
    @push('script')
        <script>
            var uRL = "{{ route('admin.park.pending.review.dt_list') }}";
        </script>
        <script src="{{ asset('assets/js/dt/pending-reviews-dt.js') }}"></script>
    @endpush
@endsection
