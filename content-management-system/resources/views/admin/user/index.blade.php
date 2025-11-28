@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>
                </a></span><span class="text-primary fw-light"> / </span>Users</h5>

    </div>

    <div style="position: relative">
        <x-admin.datatable id="user-table" title="Users" loaderID='dt-loader'>
            <x-slot:headings>
                <th>Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Action</th>
            </x-slot:headings>
        </x-admin.datatable>
    </div>
@endsection

@push('script')
    <script>
        var uRL = "{{ route('admin.user.dt_list') }}";
    </script>
    <script src="{{ asset('assets/js/dt/user-dt.js') }}"></script>
@endpush
