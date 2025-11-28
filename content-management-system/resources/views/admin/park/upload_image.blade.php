@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->
@php
    $heading = !empty($parkimages) ? 'Edit Image' : 'Upload Images';
@endphp
@section('content')
    @push('custom-style')
        <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/ajax-file-uploader/css/jquery.uploader.css') }}">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

        <link rel="stylesheet" href="{{ asset('assets/image-gallery/css/lc_lightbox.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/image-gallery/skins/minimal.css') }}">
        {{-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/park-image-gallery.css') }}"> --}}
    @endpush
    <x-admin.breadcrumb active="{{ $heading }}" :breadcrumbs="$breadcrumbs">
    </x-admin.breadcrumb>

    <div class="row" style="position: relative">
        <div class="col-md-12">

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-lightblue">
                    <label class="form-label">{{ $heading }} (<a href="{{ route('admin.park.details', $park->id) }}"
                            class="text-primary">{{ $park->name }}</a>)</label>

                </div>


                <div class="card-body">
                    <div class="banner-image mb-3 p-2">
                        <div class="card-header">
                            Banner Image
                        </div>
                        <div class="uploader-card">
                            <img id="bannerImg"
                                src="{{ !empty($banner_image && $banner_image->first()) ? Storage::url($banner_image->first()->media->path) : asset('images/default.jpg') }}"
                                width="250px" height="200px">
                        </div>

                    </div>


                    @if (!empty($parkimages))
                        <div @class([
                            'jquery-uploader',
                            'border',
                            'border-rounded',
                            'p-2',
                            'd-none' => count($parkimages) == 0,
                        ])>
                            @can('users-show')
                                @if ($parkimages->count() > 0)
                                    <div class="card-header d-flex justify-content-between bg-lightblue">
                                        <label class="form-label" id="imagesLabel">Images
                                            <div style="display:inline-block;">(<span id="image_group">All</span>
                                                <span class="text-light" id="total_img">{{ $parkimages->count() }}</span>)
                                            </div>
                                        </label>

                                        <div class="form-inline" id="dropdownContainer">
                                            <label for="type" class="form-label">Display:</label>

                                            <select name="type" id="select-picker" class="ml-2 selectpicker"
                                                onchange="filterImage(this)" data-width="190px" data-style="border border-muted"
                                                data-show-subtext='true' data-live-search='true'
                                                data-container="#dropdownContainer" data-hidden=>

                                                <option value="all" selected="" selected-txt="All">
                                                    All
                                                </option>

                                                @if ($ParkscapeUploadedImage->count() > 0)
                                                    <option value="parkscape" selected-txt="Parkscape">Parkscape</option>
                                                @endif

                                                @if ($SubadminUplodedImage->count() > 0)
                                                    <optgroup label='Sub-Admins' data-icon='bx bxs-user-detail'>
                                                        <option value="all_subadmins" data-subtext="(Sub-Admin)"
                                                            selected-txt='All'>All</option>
                                                        @foreach ($SubadminUplodedImage as $usr)
                                                            <option value={{ $usr->user->id }} type="subadmin"
                                                                data-subtext="(Sub-Admin)"
                                                                selected-txt="{{ $usr->user->name }}">
                                                                {{ $usr->user->name }}</option>
                                                        @endforeach

                                                    </optgroup>
                                                @endif


                                                @if ($UserUploadedImage->count() > 0)
                                                    <optgroup label="Users" data-icon="bx bxs-user">
                                                        <option value="all_users" type="user" data-subtext="(User)">
                                                            All
                                                        </option>
                                                        @foreach ($UserUploadedImage as $usr)
                                                            <option value="{{ $usr->user->id }}" type="user"
                                                                data-subtext="(User)">
                                                                {{ $usr->user->name }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif


                                            </select>
                                        </div>

                                    </div>
                                @endif
                            @endcan

                            <div class="card-header d-flex justify-content-start" style="position: relative">
                                <x-admin.loader id="image-loader" />

                                <div class="ml-2">
                                    <input class="form-check-input" type="checkbox" name="select_all_image"
                                        id="select_all_images">
                                    <label class="form-label ml-1" for="select_all_images">Select All Images</label>
                                </div>

                            </div>

                            <div class="jquery-uploader-preview-container" id="savedImage">
                                <x-admin.gallerycomponent :parkimages="$parkimages">
                                </x-admin.gallerycomponent>
                            </div>

                            <div class="d-flex justify-content-end mt-5">
                                <button @class([
                                    'btn',
                                    'btn-primary',
                                    'loadMoreBtn',
                                    'd-none' => empty($parkimages) || $more_data == 0,
                                ]) offset="{{ count($parkimages) }}">Load More
                                    Images</button>
                                <button class="btn btn-danger deleteImageBtn ml-3">Delete Images</button>
                            </div>
                        </div>
                    @endif





                    @if (!empty($parkimages))
                        <div id="imageuploader" class="border border-rounded mt-3 pb-4">
                            <div class="card-header bg-lightblue">
                                <label class="form-label"> Upload More Images</label>
                            </div>
                            <input type="text" id="parkimage" value="" class="d-none">

                        </div>
                    @endif

                    <div id="imageuploader">
                        <input type="text" id="parkimage" value="" class="d-none">
                    </div>

                    <div class="d-flex justify-content-end mt-2">
                        <button class="btn btn-primary saveImageBtn" id="saveImgBtn">Save Images</button>

                    </div>
                </div>


            </div>
        </div>
        <div class="d-flex justify-content-center d-none"
            style="margin: auto;
position: fixed;
opacity:9;
z-index:1;
left:8%;
bottom: 300px;">
            {{-- <div class="loader">

            </div> --}}
        </div>
    </div>
@endsection

@push('script')
    <script>
        // ShowTooltip(position = 'bottom');
        var default_img = "{{ asset('images/default.jpg') }}";
    </script>
    <script src="{{ asset('assets/ajax-file-uploader/dist/jquery.uploader.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script>
        var default_value =
            @if (!empty($parkimages))
                [
                    @foreach ($parkimages as $parkimage)
                        {
                            name: "{{ $parkimage->media->name }}",
                            url: "{{ Storage::url($parkimage->media->path) }}",
                            type: 'image',
                        },
                    @endforeach

                ];
            @else
                '';
            @endif


        var park_id = "{{ $park->id }}";
        var remvoeUrl = "{{ route('admin.park.delete.image') }}";
        var setunsetbannerUrl = "{{ route('admin.park.setunset.banner') }}";
        var save_image_url = " {{ route('admin.park.save.image') }}";
        var deleteMultipleImgUrl = "{{ route('admin.park.delete.multiple.image') }}";
        var loadMoreImageUrl = "{{ route('admin.park.load.more.image') }}";
        var draggableSortUrl = "{{ route('admin.park.sort.draggable.image') }}";
        var storeUrl = "{{ route('admin.park.store.image', $park->id) }}";
        var filterImgUrl = "{{ route('admin.park.filter.image', $park->id) }}";
        const searchOptionsUrl = "{{ route('admin.park.search.optiions', $park->id) }}";
    </script>
    {{-- <script src="{{asset('assets/js/park/park-image-uploader-select-dropdown.js')}}"></script> --}}

    <script src={{ asset('assets/js/park/image-upload.js') }}></script>
    <script src="{{ asset('assets/js/park/draggable-image.js') }}"></script>
    <script src="{{ asset('assets/image-gallery/js/lc_lightbox.lite.js') }}"></script>
    <script src="{{ asset('assets/image-gallery/lib/AlloyFinger/alloy_finger.js') }}"></script>

    <script>
        function ViewImage(e) {
            // var url = $(e).parent().parent().parent().parent().find('.gallery-img').click();
            // $(e).find('.gallery-img').click();
            $(e).parent().find('.gallery-img').click();

        };

        $(document).ready(function(e) {
            // $(".jquery-uploader-preview-main").click(function(){
            //     $(this).find('.gallery-img').click();
            // });
            lc_lightbox('.gallery-img', {
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

            lc_lightbox('.gallery-img', {
                touchswipe: false,
                mousewheel: false,
                rclick_prevent: false,
            });
        });
    </script>
@endpush
