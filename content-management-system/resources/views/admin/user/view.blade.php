@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>
                </a></span><span class="text-primary fw-light"> / </span><span><a href="{{ route('admin.user.index') }}"`><u
                        class="text-primary fw-light">Users</u>
                </a></span><span class="text-primary fw-light"> / </span>Details</h5>

    </div>
    @php
        if (!empty($userpark->first())) {
            $park_id = $userpark->first()->first()->park_id;
        }
    @endphp

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="px-2">
                    <div class="card-header bg-lightblue">
                        <label class="form-label">Basic Information</label>
                    </div>
                    <div class="card-body py-4">
                        <div class="d-row d-flex justify-content-between">
                            <div class="d-row d-flex justify-content-between">
                                <div class="d-flex align-items-start align-items-sm-center gap-4">
                                    <img src="{{ $user->image->full_path ?? asset('images/user.svg') }}" alt="user-avatar"
                                        class="d-block rounded-circle " height="100" width="100"
                                        style="object-fit: cover;" id="uploadedAvatar">
                                    <div class="button-wrapper">
                                        <span class="fw-semibold d-block">{{ ucfirst($user->name) }}</span>
                                        <small class="text-muted">User</small>
                                    </div>
                                </div>

                            </div>
                            <div>
                                <div id="email-view-section">
                                    <i class='bx bxs-envelope text-primary' style="font-size:1.5rem;" rel="tooltip"
                                        title="Email"></i>
                                    <span class="text-muted" id="email" style="cursor: pointer"
                                        value="{{ $user->email }}">{{ Str::limit($user->email, 80) }}</span><br>
                                        <div class="pt-1">
                                        <i class="bx bxs-user text-primary" style="font-size:1.5rem;" rel="tooltip"
                                        title="Email"></i>
                                       <span class="text-muted">{{ Str::limit($user->username??'N/A', 80) }}</span>
                                        </div>
                                </div>
                                <div class="mt-2">
                                    <label class='form-label mr-1'>Total Uploaded Images:</label><span
                                        class="text-muted">{{ $user->parkimages()->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-2">
                    <x-admin.datatable id="bookmark-tbl" title="Bookmarks" loaderID='bookingtbl-loader' >
                        <x-slot:headings>
                            <th>Name</th>
                            <th>Bookmark Type</th>
                            <th>Created On</th>
                            {{-- <th>Action</th> --}}
                        </x-slot:headings>
                    </x-admin.datatable>

                    <x-admin.datatable id="dt-table" loaderID="dt-loader" title="Uploaded Image ({{ $user->parkimages()->count() }})">
                        <x-slot:headings>
                            <th>Park</th>
                            <th>Total Images</th>
                            <th>Pending Images</th>
                            <th>Verified Images</th>
                            <th>Action</th>
                        </x-slot:headings>
                    </x-admin.datatable>

                    <x-admin.datatable id="reviews-tbl" loaderID="review-tbl-loader" title="Reviews">
                        <x-slot:headings>
                            <th>Park</th>
                            <th>Reviews</th>
                            <th>Ratings</th>
                            <th>Action</th>
                        </x-slot:headings>
                    </x-admin.datatable>

                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script src="{{ asset('assets/js/dt/user-bookmark-dt.js') }}"></script>
        <script>
            var uRL = "{{ route('admin.user.bookmarkt.dt_list', $user->id) }}";
            var verifyImgUrl = "{{ route('admin.user.image.verify') }}";
            var user_id = "{{ $user->id }}";
            var userParkImgUrl = "{{ route('admin.user.park.images') }}";
            var park_id = "{{ $park_id ?? null }}";
            var user_upload_image_dt_url = "{{ route('admin.user.park.dt.list', $user->id) }}";
            var unarchive_image_url = "{{ route('admin.user.park.anachivedimage', ['park_id', 'user_id']) }}";
            var _method = 'post';
            var review_url = "{{ route('admin.user.park.reviews.dt_list', $user->id) }}";
            $(document).ready(() => {
                ShowTooltip('right');
            });
        </script>
        <script src="{{ asset('assets/js/users/view.js') }}"></script>
        <script src="{{ asset('assets/js/dt/user-uploaded-image-dt.js') }}"></script>
        <script src="{{ asset('assets/js/dt/user-review-dt.js') }}"></script>
        <script>
            function EnableTooltip(e) {
                // $(e).attr('data-bs-original-title','Unarchive');
                $(e).tooltip('enable');


            }

            function unarchive(park_id, user_id, e) {
                unarchive_image_url = unarchive_image_url.replace('park_id', park_id).replace('user_id', user_id);
                var data = {
                    'park_id': park_id,
                    'user_id': user_id
                };
                $.confirm({
                    title: 'Unarchive',
                    content: 'Do you want to unarchive?',
                    buttons: {
                        Yes: {
                            btnClass: 'btn btn-success',
                            action: function() {
                                $.post(unarchive_image_url, data, function(res) {
                                    location.reload();
                                });
                            }
                        },
                        No: function() {

                            // $(e).removeAttr('data-bs-original-title');
                            $(e).tooltip('disable');


                        },
                    }
                });
            }
        </script>
        <script>
            const txt = $("#email").text();

            if (txt.length > 80) {
                $('#email').click(function() {
                    if (!$("#email-view-section").hasClass('email_view_section')) {
                        $('#email').text($('#email').attr('value'));
                        $("#email-view-section").addClass('email_view_section')
                    } else {
                        $('#email').text(txt.substring(0, 77) + '...');
                        $("#email-view-section").removeClass('email_view_section')
                    }
                })
            }
        </script>
    @endpush
@endsection
