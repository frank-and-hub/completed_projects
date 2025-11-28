@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
   <x-admin.breadcrumb active="Create" :breadcrumbs="$breadcrumbs"/>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ !empty($subadmin) ? 'Edit Sub-Admin' : 'New Sub-Admin' }}</h5>
                </div>
                <form
                    action="{{ !empty($subadmin) ? route('admin.subadmin.update', $subadmin->id) : route('admin.subadmin.store') }}"
                    method="post" enctype="multipart/form-data">
                    @csrf


                    <x-admin.uploadimg id="{{ $subadmin->id ?? null }}"
                        imgpath="{{ $subadmin ? ($subadmin->image ? $subadmin->image->full_path : asset('images/default.jpg')) : asset('images/default.jpg') }}"
                        imgdeletelink="#" >
                    </x-admin.uploadimg>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
                                <div class="input-group input-group-merge">
                                    {{-- <span class="input-group-text " style="background:#eceef1;"><i
                                            class="bx bx-user"></i></span> --}}
                                    <input type="text" class="form-control" id="basic-icon-default-fullname"
                                        placeholder="Enter Name" aria-label="John Doe" required
                                        value="{{ $subadmin->name ?? old('name') }}" name="name"
                                        aria-describedby="basic-icon-default-fullname2" />
                                </div>
                            </div>

                            <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-email">Email</label>
                                <div class="input-group input-group-merge">
                                    {{-- <span class="input-group-text " style="background:#eceef1;"><i
                                            class="bx bx-envelope"></i></span> --}}
                                    <input type="text" id="basic-icon-default-email" class="form-control"
                                        placeholder="Enter Email" aria-label="john.doe"
                                        @if (!$subadmin) required @endif
                                        value="{{ $subadmin->email ?? old('email') }}" name="email" autocomplete="nope$#"
                                        aria-describedby="basic-icon-default-email2" />

                                </div>
                                <div class="form-text">You can use letters, numbers & periods</div>
                            </div>

                                <div class="col-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label"
                                            for="new-password">{{ __('admin.new_password') }}</label>
                                            <div class="input-group input-group-merge">
                                                <input type="password"  required  autocomplete="new-password"  id="new-password" class="form-control" name="password"
                                                    placeholder="{{ __('admin.new_password') }}" aria-describedby="password">
                                                <span class="input-group-text cursor-pointer" onclick="PassHideShow(this)"><i
                                                        class="bx bx-show"></i></span>
                                            </div>
                                    </div>
                                </div>

                                <div class="col-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label"
                                            for="confirm-password">{{ __('admin.confirm_password') }}</label>

                                            <div class="input-group input-group-merge">
                                                <input type="password"   required  autocomplete="new-password"  id="confirm-password" class="form-control" name="confirm_password"
                                                    placeholder="{{ __('admin.confirm_password') }}" aria-describedby="password">
                                                <span class="input-group-text cursor-pointer" onclick="PassHideShow(this)"><i
                                                        class="bx bx-show"></i></span>
                                            </div>
                                    </div>
                                </div>


                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>

                </form>
            </div>
        </div>
    </div>

    @push('script')
        <script src={{ asset('js/image.js') }}></script>
    @endpush
@endsection
