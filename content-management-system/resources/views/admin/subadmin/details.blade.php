@extends('admin.layout.master')
@section('content')
    <x-admin.breadcrumb active='Details' :breadcrumbs="$breadcrumbs" />
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="px-2">
                    <div class="card-header bg-lightblue">
                        <label class="form-label">Basic Information</label>
                    </div>
                    <div class="card-body py-4">
                        <div class="d-row d-flex justify-content-between">
                            <div style="position: relative">
                                {{-- <div @class(['online-icon' => $user->is_active])></div> --}}
                                <div class="d-flex align-items-start align-items-sm-center gap-4">
                                    <img src="{{ $user->image->full_path ?? asset('images/user.svg') }}" alt="user-avatar"
                                        class="d-block rounded-circle " height="100" width="100"
                                        style="object-fit: cover;" id="uploadedAvatar">
                                    <div class="button-wrapper">
                                        <span class="fw-semibold d-block">{{ ucfirst($user->name) }}</span>
                                        <small class="text-muted">Admin</small>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div style="cursor:pointer">
                                    <i class='bx bxs-envelope text-primary' style="font-size:1.5rem;" rel="tooltip"
                                        title="Email"></i>
                                    <span class="text-muted">{{ $user->email }}</span>
                                </div>
                                <div class="mt-2">
                                    <label class='form-label mr-1'>Total Parks:</label><span class="text-muted">{{$user->parks()->count()}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-2">
                    <x-admin.datatable id="dt-table" title="Parks" otherClass='bg-lightblue'>
                        <x-slot:headings>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Created On</th>
                            <th>Action</th>
                        </x-slot:headings>
                    </x-admin.datatable>
                </div>
            </div>
        </div>
    </div>
    @push('script')
        <script>
            var uRL = "{{ route('admin.subadmin.park.dt_list', $user->id) }}";
        </script>
        <script src="{{ asset('assets/js/dt/subadmin-details-dt.js') }}"></script>
    @endpush
@endsection
