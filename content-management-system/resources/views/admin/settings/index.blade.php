@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <x-admin.breadcrumb active="Settings" />

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Profile Details</h5>
                    {{-- <small class="text-primary float-end">Merged input group</small> --}}
                </div>
                <form action="{{ route('admin.settings.profile.update') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    {{-- <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <img src="{{ $admin->image ? $admin->image->full_path : asset('images/user.svg') }}"
                                alt="user-avatar" class="d-block rounded" height="100" width="100"
                                style="object-fit: cover;" id="uploadedAvatar" />
                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload new photo</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" class="account-file-input" hidden
                                        accept="image/png, image/jpeg" name="image" />
                                </label>
                                <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                </button>

                                <p class="text-primary mb-0">Allowed JPG or PNG. Max size of 1 MB</p>
                            </div>
                        </div>
                    </div> --}}

                    <x-admin.uploadimg id="{{ $admin->id ?? null }}"
                        imgpath="{{ $admin ? ($admin->image ? $admin->image->full_path : asset('images/user.svg')) : asset('images/user.svg') }}"
                        imgdeletelink="{{ !empty($admin->id) ? route('admin.reset.profile.img', $admin->id) : '' }}"  defaultimgurl="{{asset('images/user.svg')}}">
                    </x-admin.uploadimg>

                    <div class="card-body">

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                            class="bx bx-user"></i></span>
                                    <input type="text" class="form-control" id="basic-icon-default-fullname"
                                        placeholder="John Doe" aria-label="John Doe" value="{{ old('name', $admin->name) }}"
                                        name="name" aria-describedby="basic-icon-default-fullname2" />
                                </div>
                            </div>

                            <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-email">Email</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text " style="background:#eceef1;"><i
                                            class="bx bx-envelope"></i></span>
                                    <input type="text" id="basic-icon-default-email" class="form-control"
                                        placeholder="john.doe" aria-label="john.doe" value="{{ $admin->email }}"
                                        name="email" aria-describedby="basic-icon-default-email2" disabled />
                                    {{-- <span id="basic-icon-default-email2" class="input-group-text">@example.com</span> --}}
                                </div>
                                <div class="form-text">You can use letters, numbers & periods</div>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-phone">Timezone</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-phone2" class="input-group-text"><i
                                            class="bx bx-time"></i></span>
                                    <select name="timezone" id="input-timezone" data-style="border border-muted"
                                        class="selectpicker form-control" data-live-search="true" data-width="auto">
                                        @foreach ($timezonelist as $t)
                                            <option value={{ $t['zone'] }} {{ $t['selected'] }}>
                                                {{ $t['zone'] . ' (GMT' . $t['GMT_difference'] . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            {{-- <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-phone">Timezone</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-phone2" class="input-group-text"><i
                                            class="bx bx-time"></i></span>
                                    <select name="timezone" id="input-timezone" data-style="btn-secondary-sp btn-sm"
                                        class="form-select selectpicker" data-live-search="true">
                                        @foreach ($timezonelist as $t)
                                            <option value={{ $t['zone'] }} {{ $t['selected'] }}>
                                                {{ $t['zone'] . ' (GMT' . $t['GMT_difference'] . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}

                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    @can('custom-page-show')
    <div style="position: relative">
        <x-admin.datatable id="custom_page_tbl" title="Custom Pages" loaderID="dt-loader">
            <x-slot:custom_headings>
                <a href="{{ route('admin.setttings.create.custom.page') }}" class="btn btn-primary">
                    <div class="d-flex align-items-center"><i class="bx bx-plus-medical"></i>&nbsp; Add New Custom Page</div>
                </a>
            </x-slot:custom_headings>

            <x-slot:headings>
                <th>Name</th>
                <th>Action</th>
            </x-slot:headings>
        </x-admin.datatable>
    </div>
    @endcan




    @push('script')
        <script>
            var custom_db_list_url = "{{ route('admin.settings.dt_list') }}";
        </script>
        <script src="{{asset('assets/js/dt/custom-page-dt.js')}}"></script>

    @endpush
@endsection

<!-- / Content -->
