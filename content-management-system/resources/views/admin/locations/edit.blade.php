@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <style>
        .category-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            /* justify-content: center; */
        }

        .category-card {
            background-color: white;
            min-width: 350px;
            border-radius: 8px;
            border: 2px solid transparent;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .cross-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: transparent;
            border: none;
            font-size: 10px;
            cursor: pointer;
        }

        .cross-btn:hover {
            color: red;
        }

        .category-header {
            background-color: #48D33A;
            color: white;
            padding-top: 3px;
            padding-bottom: 2px;
            text-align: center;
        }

        .category-content {
            padding: 15px 15px 0px 15px;
            overflow-y: none;
            overflow-x: none;
        }

        .category-content img {
            width: 40%;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .park-list {
            list-style: none;
            padding: 0;
        }

        .park-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .park-item img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
        }

        .park-item .cross-btn {
            background-color: transparent;
            border: none;
            font-size: 16px;
            cursor: pointer;
        }

        .park-item .cross-btn:hover {
            color: red;
        }

        /* Basic CSS for grid */
        .grid-container-category-park {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)) !important;
            gap: 20px;
            grid-template-columns: 1fr 1fr 1fr;
        }

        .grid-item-category-park {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .wrap-normal {
            white-space: normal !important;
        }

        .card_custom_style {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            white-space: normal
        }

        /* Optional: Add responsiveness for smaller screens */
        @media (max-width: 1100px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .grid-container {
                grid-template-columns: 1fr;
                /* 1 column on small screens */
            }


        }

        input.fake-checkbox[type="radio"] {
            appearance: checkbox;
            -webkit-appearance: none;
            background-color: #fff;
            width: 18px;
            height: 18px;
            border: 1px solid #ccc;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
        }

        /* "Checked" style that looks like a checkmark */
        input.fake-checkbox[type="radio"]:checked::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 6px;
            width: 5px;
            height: 10px;
            border: solid #48D33A;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .defaultContainerShadow {
            box-shadow: -2px 2px 10px rgba(72, 211, 58, 0.5) !important;
        }
    </style>

    <div class="d-flex align-items-center py-2 mb-0 justify-content-between">
        <h5 class="fw-bold mb-0" style="flex: 1" >
            <span>
                <a href="{{ route('admin.dashboard') }}" `>
                    <u class="text-primary fw-light">Dashboard</u>
                </a>
            </span>
            <span class="text-primary fw-light"> / </span>
            <span>
                <a href="{{ route('admin.locations.index') }}">
                    <u class="text-primary fw-light">Locations</u>
                </a>
            </span>
            <span class="text-primary fw-light"> / </span>
            {{ $custom_headings }}
        </h5>
        <?php
        $is_allowed = (isset($location->title) && isset($location->subtitle) && isset($location->thumbnail_id) && isset($location->banner_id)) ? true : false;
        $status = ($location->status == 1) ? 'checked' : '';
        ?>
        <h5 class="mb-0 my-auto">
            @if($is_allowed)
            <label class="switch m-0" rel="tooltip" title={{ $status == 'checked' ? 'Active' : 'Inactive' }}>
                <input type="checkbox" link="{{ route('admin.locations.status', [$location->id]) }}" {{ $status }} id="{{ $location->id ?? null }}" is_allowed="{!! $is_allowed !!}">
                <span class="slider round"></span>
            </label>
            @endif
        </h5>
    </div>
    <div class="d-flex align-items-center py-3 mb-3 justify-content-between">
         <h5 class="mb-0 text-primary fw-light">{{ $location->city }}</h5>
     </div>

    <form action="{{ route('admin.locations.update', $location->id) }}" method="POST" enctype="multipart/form-data" id="LocationForm">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center bg-lightblue">
                        <h5 class="mb-0">{{ 'Location Details' }}</h5>
                    </div>
                    <div class="card-body table-responsive text-nowrap">
                        @csrf
                        @php
                            $thumbnailImgPath = $location
                                ? ($location->thumbnail_id
                                    ? $location->thumbnail->full_path
                                    : asset('images/default.jpg'))
                                : asset('images/default.jpg');
                            $bannerImgPath = $location
                                ? ($location->banner_id
                                    ? $location->banner->full_path
                                    : asset('images/default.jpg'))
                                : asset('images/default.jpg');
                            $thumbnailImgDeleteLink = $location
                                ? route('admin.locations.reset.uploaded.img', [$location->id, 'thumbnail'])
                                : '';
                            $bannerImgDeleteLink = $location
                                ? route('admin.locations.reset.uploaded.img', [$location->id, 'banner'])
                                : '';
                        @endphp
                        <label class="form-label" for="basic-icon-default-fullname">Carousel Card Image<span class="text-danger"> *</span></label>
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <img src="{{ $thumbnailImgPath }}" alt="user-avatar" class="d-block rounded" height="100" width="100" style="object-fit: cover;" id="uploadedAvatarThumbnail" />
                            <div class="button-wrapper">
                                <label for="thumbnail" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload new image</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="thumbnail" class="account-file-input-thumbnail" hidden accept="image/png, image/jpeg" name="thumbnail" />
                                </label>

                                <button type="button" class="btn btn-outline-secondary account-image-reset-thumbnail mb-4" id="{{ $location->id ?? null }}" link="{{ $thumbnailImgDeleteLink }}" onclick="rst(this)" default-img-url="{{ asset('images/default.jpg') }}">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                </button>
                                <p class="text-primary mb-0">{{ 'Allowed JPG or PNG. Max size of 10 MB' }}<br></p>
                            </div>
                        </div>
                        <br>
                        <label class="form-label" for="basic-icon-default-fullname">Banner Image<span class="text-danger">
                                *</span></label>
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <img src="{{ $bannerImgPath }}" alt="user-avatar" class="d-block rounded" height="100"
                                width="400" style="object-fit: cover;" id="uploadedAvatarBanner" />
                            <div class="button-wrapper">
                                <label for="banner" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload new image</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="banner" class="account-file-input-banner" hidden accept="image/png, image/jpeg" name="banner" />
                                </label>

                                <button type="button" class="btn btn-outline-secondary account-image-reset-banner mb-4" id="{{ $location->id ?? null }}" link="{{ $bannerImgDeleteLink }}" onclick="rstbnr(this)" default-img-url="{{ asset('images/default.jpg') }}">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                </button>
                                <p class="text-primary mb-0">{{ 'Allowed JPG or PNG. Max size of 10 MB' }}<br> </p>
                            </div>
                        </div>
                        <br>
                        <div class="row">

                            <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-title">Banner Title<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" id="basic-icon-default-title" placeholder="Enter Banner Title" aria-label="" name="title" value="{{ old('title', $location->title ?? '') }}" aria-describedby="basic-icon-default-title" />
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label" for="basic-icon-default-content">Description<span class="text-danger"> *</span></label>
                                <textarea type="text" class="form-control" id="basic-icon-default-content" rows="4" placeholder="Enter Banner Description" aria-label="" name="content" value="{{ old('content', $location->subtitle ?? '') }}" aria-describedby="basic-icon-default-content">{{old('content', $location->subtitle ?? '')}}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">{{ 'Save Details' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-lightblue">
                    <h5 class="mb-0">{{ 'Container Pages' }}</h5>
                </div>
                <div class="card-body table-responsive text-nowrap">
                    <div class="row my-4">
                        <h4 style="flex:1">
                            <a href="{{ route('admin.container.create', $location->id) }}" class="btn btn-primary" style="width: auto">
                                <div class="d-flex align-items-center"><i class='bx bx-plus-medical'></i>&nbsp; Create Container Page</div>
                            </a>
                        </h4>
                    </div>
                    <div class="grid-container-category-park">
                        @forelse ($location->containers as $container)
                            <div class="category-card @if($location->default_container_id == $container->id) defaultContainerShadow @endif"
                                @if($location->default_container_id == $container->id) style="border: 2px solid rgb(72, 211, 58);"
                                @endif>
                                <button class="cross-btn"></button>
                                <div class="category-header pt-2 position-relative" data-toggle="tooltip" data-placement="top" title="Default Container">
                                    <div style="width: auto;top:1em;left:1em;" class="position-absolute">
                                        <input type="radio" name="defaultFeature" value="1" class="fake-checkbox text-white {{$location->default_container_id}}" @if($location->default_container_id == $container->id) checked @endif data-location_id="{{$location->id}}" data-container_id="{{$container->id}}" />
                                    </div>
                                    <div class="text-center">
                                        <h4 class="text-truncate w-75 mx-auto my-2">{{ ucwords($container->name) }}</h4>
                                    </div>
                                    <div style="width: auto;top:0.8rem;right:1rem" class="position-absolute">
                                        <a href="{{ route('admin.container.edit', [$location->id, $container->id]) }}" class="text-white font-weight-bold"><i class='bx bx-edit'></i>&nbsp;
                                        </a>
                                    </div>
                                </div>
                                <div class="category-content">
                                    <div class="row">
                                        <div class="col-4">
                                            <img src="{{ $container->image->full_path }}" alt="" style="border:1px solid black; padding:10px; border-radius:4px; object-fit:contain; width: 100%" class="img-fluid">
                                        </div>
                                        <div class="col-8">
                                            <p class="card_custom_style">{{ $container->description }}</p>
                                            <p style="overflow-auto"> Total Parks : {{ $container->parks()->count() }}</p>

                                            @if($containerFeature = ($container->feature()->first() ?? $container->feature_type()->first()))
                                            <p style="overflow-auto"> Feature : {{ $containerFeature->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                        @endforelse
                    </div>
                    <br>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $('#open-form').on('click', function () {
            $('#form-modal').show();
        });
        $('#close-form').on('click', function () {
            $('#form-modal').hide();
        });
        var uRL = `{{ route('admin.locations.index') }}`;

        $(document).on('click', '.seo_description_edit', function () {
            const $input = $('#basic-icon-default-seo_description');
            const isReadonly = $input.prop('readonly');
            $('.seo_description_submit').toggleClass('d-flex');
            $('.seo_description_submit').toggleClass('d-none');
            $input.prop('readonly', !isReadonly);
        });

        $(document).on('click', '.fake-checkbox', function () {
            const location_id = $(this).data('location_id');
            const container_id = $(this).data('container_id');
            $('.category-card').removeClass('defaultContainerShadow');
            $('.category-card').css('border', '2px solid #fff');
            $(this).closest('.category-card').addClass('defaultContainerShadow');
            $(this).closest('.category-card').css('border', '2px solid #48D33A');

            // Dynamically build the route using location_id
            const update_default_container_url = `{{route('admin.locations.update_default_container', ['location' => $location->id])}}`;

            $.post(update_default_container_url, {
                location_id: location_id,
                container_id: container_id,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
                .done(function (res) {
                    console.info(res);
                })
                .fail(function (err) {
                    console.error(err);
                });
        });
        changeStatus(document);
    </script>
    <script src="{{ asset('assets/js/dt/location-dt.js') }}"></script>
@endpush
