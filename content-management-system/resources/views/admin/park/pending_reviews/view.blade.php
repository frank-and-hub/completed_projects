@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}

    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1">
            <span><a href="{{ route('admin.dashboard') }}">
                    <u class="text-primary fw-light">Dashboard</u> </a></span>
            <span class="text-primary fw-light"> / </span>
            <span><u class="text-primary fw-light" style="cursor: pointer" onclick="history.back()">Pending Reviews</u></span>
            <span class="text-primary fw-light"> / </span>
            Review
        </h5>
    </div>

    <div class="row">
        <div class="col-xl">
            <div class="card p-2" style="position: relative;">
                <x-admin.loader id="verify-unverify-loader" />
                <div class="card-header" style="background: #f4fff3;">
                    <label class="form-label">Pending Review Of {{ $rating->user->name }}</label>
                </div>
                <div class="col-md-12">
                    <div class="card-body p-3" style="position: relative">
                        <x-admin.loader id="loader" />
                        <div class="d-row d-flex justify-content-between">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar">
                                        <a href="{{ route('admin.user.view', $rating->user->id) }}"><img
                                                src="{{ $rating->user->image_id ? $rating->user->image->full_path : asset('images/user.svg') }}"
                                                alt="" class="w-px-40 h-auto rounded-circle"></a>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="{{ route('admin.user.view', $rating->user->id) }}"><span
                                            class="fw-semibold d-block">{{ ucfirst($rating->user->name) }}</span></a>
                                    <a href="{{ route('admin.park.details', $rating->park->id) }}"><small
                                            class="text-muted">{{ ucwords($rating->park->name) }} (Park)</small></a>
                                    <br>
                                    <x-admin.ratingcomponent rating="{{ $rating->rating }}" />
                                    <small class="text-muted"
                                        style="vertical-align: -2px;">{{ \Carbon\Carbon::parse($rating->created_at)->format('M d, Y') }}</small>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-primary" id="VerifyBtn">Verify Review</button>
                            </div>

                        </div>
                        <div class="ml-3 pl-1">
                            <div class="text-muted ml-3 mt-3 pl-3 w-75">
                                {{ ucfirst($rating->review) }}
                            </div>
                        </div>

                    </div>



                </div>
            </div>
        </div>
        @push('script')
            <script>
                var park_id = " {{ $rating->park->id }}";
                var user_id = "{{ $rating->user->id }}";
                var verify_review_url = " {{ route('admin.park.verify.pending.review') }}";
                var pending_reviews_url = "{{ route('admin.park.review') }}";
            </script>
            <script src="{{ asset('assets/js/park/pending-review.js') }}"></script>
        @endpush
    @endsection
