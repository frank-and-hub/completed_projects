@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}
    @push('custom-style')
        <link rel="stylesheet" href="{{ asset('assets/image-gallery/css/lc_lightbox.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/image-gallery/skins/minimal.css') }}">
    @endpush


    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1">
            <span><a href="{{ route('admin.dashboard') }}">
                    <u class="text-primary fw-light">Dashboard</u> </a></span>
            <span class="text-primary fw-light"> / </span>
            <span><u class="text-primary fw-light" style="cursor: pointer" onclick="history.back()">Pending
                    Images</u></span>
            <span class="text-primary fw-light"> / </span>
            Images
        </h5>
    </div>

    <div class="row">
        <div class="col-xl">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                                <a href="{{ route('admin.user.view', $user->id) }}"><img
                                        src="{{ $user->image_id ? $user->image->full_path : asset('images/user.svg') }}"
                                        alt="" class="w-px-40 h-auto rounded-circle"></a>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <a href="{{ route('admin.user.view', $user->id) }}"><span
                                    class="fw-semibold d-block">{{ ucfirst($user->name) }}</span></a>
                            <a href="{{ route('admin.park.details', $park->id) }}"><small
                                    class="text-muted">{{ ucwords($park->name) }} (Park)</small></a>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="card mt-3 px-0" style="position: relative;">
                            <x-admin.loader id="verify-unverify-loader" />
                            <div class="card-header" style="background: #f4fff3;">
                                <div class="row ">
                                    <div class="col-md-4">
                                        <label class="form-label">User's Uploaded Images
                                            ({{ $total_user_uploaded_image->count() }})</label>
                                    </div>
                                    <div class="col-md-3">

                                    </div>
                                    <div class="col-md-5 pl-3">
                                        <div class="row d-flex justify-content-around" id="select_labels">
                                            {{--  --}}
                                            <div class="col-md-2 ml-2">
                                                <input class="form-check-input" type="checkbox" name="select_all_image"
                                                    id="select_all_images">
                                                <label class="form-label ml-1" for="select_all_images"
                                                    style="display: inline-block;">All <span
                                                        id="all_image">(0)</span></label>
                                            </div>
                                            <div class="col-md-3">
                                                <input class="form-check-input" type="checkbox"
                                                    name="select_unverified_image" id='select_unverified_image'
                                                    @disabled($total_unverified_images == 0)>
                                                <label class="form-label ml-1" for="select_unverified_image">Pending <span
                                                        id="unverified_image">(0)</span></label>
                                            </div>

                                            <div class="col-md-3 ">
                                                <input class="form-check-input" type="checkbox" name="select_verified_image"
                                                    id='select_verified_image' @disabled($total_verified_images == 0)>
                                                <label class="form-label ml-1" for="select_verified_image">Verified <span
                                                        id="verified_image">(0)</span></label>
                                            </div>



                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card-body border border-muted rounded mt-3 mb-3" style="position: relative;">
                                    <div class="loader d-none" style="position: absolute; top:25%; left:50%; z-index:999;">
                                    </div>
                                    <div id="load_images">

                                    </div>
                                    <div class="d-flex justify-content-end mt-3">

                                        <button class="btn mr-2 btn-warning d-none" id="archiveBtn"><i
                                                class='bx bx-archive-in'></i> Archive</button>
                                        <button class="btn mr-2 btn-primary d-none" id="unarchiveBtn"><i
                                                class='bx bxs-archive-out'></i> Unarchive</button>

                                        <button class="btn btn-primary d-none" id="verifyBtn">Verify</button>
                                        <button class="btn btn-danger d-none" id="unverifyBtn">Unverify</button>
                                        <button class="btn btn-danger ml-2 d-none" id="DeleteBtn"><i class="bx bx-trash"></i> Delete <span>0</span></button>

                                    </div>
                                </div>

                            </div>


                        </div>
                    </div>

                </div>



            </div>
        </div>
    </div>
    @push('script')
        <script src="{{ asset('assets/image-gallery/js/lc_lightbox.lite.js') }}"></script>
        <script src="{{ asset('assets/image-gallery/lib/AlloyFinger/alloy_finger.js') }}"></script>
        <script>
            var user_id = "{{ $user->id }}";
            var userParkImgUrl = "{{ route('admin.park.pendingimage.view', [$park->id, $user->id]) }}";
            var park_id = "{{ $park->id }}";
            var verify_unverify_url = "{{ route('admin.park.pendingimage.verifyunverify') }}";
            var unarchive_image_url = "{{ route('admin.user.park.anachivedimage', ['$park->id', '$user->id']) }}";
            const deleteUplodedImgUrl = `{{route('admin.park.delete.user.uploadedimg',[$park->id,$user->id])}}`;

            var _method = 'get';
            var indexUrl = "{{ route('admin.park.pendingimage') }}";
            $(document).ready(() => {
                ShowTooltip('right');
            });
        </script>
        <script src="{{ asset('assets/js/park/pending-image.js') }}"></script>
    @endpush
@endsection
