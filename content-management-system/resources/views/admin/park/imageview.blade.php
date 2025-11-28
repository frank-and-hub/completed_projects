@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->

@section('content')
    @push('custom-style')
        <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="{{ asset('assets/image-gallery/css/lc_lightbox.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/image-gallery/skins/minimal.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/park-image-gallery.css') }}">
    @endpush
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1">
            <span><a href="{{ route('admin.dashboard') }}"`><u class="text-primary fw-light">Dashboard</u>
                </a></span>
            <span class="text-primary fw-light"> / </span>
            <span><a href="{{ route('admin.park.index') }}"><u class="text-primary fw-light">Parks</u>
                </a></span>
            <span class="text-primary fw-light"> / </span>View Images
        </h5>

    </div>


    <div class="row">
        <div class="col-md-12">

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="card_title">Image(s) by parkscape</h5>
                    @if ($total_users_park_images > 0)
                        <div>
                            <label for="selectpicker">Select:</label>
                            <select class="selectpicker"id="selectpicker" data-style='border border-muted'>
                                <option value="parkscape">Image(s) by parkscape</option>
                                <option value='user'>Image(s) by users </option>
                            </select>
                        </div>
                    @endif

                </div>
                <div class="card-body" style="position:relative;">
                    <div class="loader d-none" style="position: absolute; z-index:999; top:30%; left:50%;">

                    </div>
                    <div class="content">
                        @foreach ($parkimages as $parkimage)
                            <a @class(['elem', 'box-shadow' => $parkimage->set_as_banner == 1]) href="{{ Storage::url($parkimage->media->path) }}"
                                title="{{ ucfirst($parkimage->media->name) }}"
                                data-lcl-thumb="{{ Storage::url($parkimage->media->path) }}">
                                <span style="background-image: url({{ Storage::url($parkimage->media->path) }});"></span>
                                <div class="text-center p-2 galleryBannerBtn d-none">
                                    <button @class([
                                        'btn',
                                        'btn-sm',
                                        'btn-primary' => $parkimage->set_as_banner != 1,
                                        'bannerBtn' => $parkimage->set_as_banner != 1,
                                        'unsetBanner' => $parkimage->set_as_banner == 1,
                                        'btn-danger' => $parkimage->set_as_banner == 1,
                                    ])
                                        id="{{ $parkimage->img_tmp_id }}">{{ $parkimage->set_as_banner == 1 ? 'Unset Banner' : 'Set As Banner' }}</button>
                                </div>
                                @if ($parkimage->set_as_banner == 1)
                                    <div class="check-mark"> <svg xmlns="http://www.w3.org/2000/svg" width="30"
                                            height="30" fill="#2fa224" class="bi bi-check-circle-fill"
                                            viewBox="0 0 16 16">
                                            <path
                                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z">
                                            </path>
                                        </svg> </div>
                                @endif
                            </a>
                        @endforeach

                    </div>
                </div>
            </div>


        </div>
    </div>

    </div>
@endsection
@push('script')
    <script src="{{ asset('assets/image-gallery/js/lc_lightbox.lite.js') }}"></script>
    <script src="{{ asset('assets/image-gallery/lib/AlloyFinger/alloy_finger.js') }}"></script>
    <script>
        var setunsetbannerUrl = "{{ route('admin.park.setunset.banner') }}";
        var park_id = "{{ $park->id }}";
        var href, a, href1, a1, bannerBtn, offsetVal = 9;
        var viewUrl = "{{ route('admin.park.image.view', $park->id) }}";
    </script>
    <script>
        $(document).ready(function(e) {
            lc_lightbox('.elem', {
                wrap_class: 'lcl_fade_oc',
                gallery: true,
                thumb_attr: 'data-lcl-thumb',
                skin: 'minimal',
                radius: 0,
                padding: 0,
                border_w: 0,
                slideshow_time: 1000,
                download: false,
                skin: 'dark',
                show_title: false,
            });
        });
    </script>

    {{-- <script src="{{ asset('assets/js/park/view-gallery-min.js') }}"></script> --}}
    <script src="{{ asset('assets/js/park/view-gallery.js') }}"></script>
@endpush
