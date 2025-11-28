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
            width: 350px;
            border-radius: 8px;
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
            padding: 15px;
            height: 140px;
            overflow-y: cover;
            overflow-x: hidden;
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
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            padding: 10px;
            grid-template-columns: 1fr 1fr 1fr;
        }

        .grid-item-category-park {}

        .wrap-normal {
            white-space: normal !important;
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
            }
        }
    </style>
    <form
        action="{{ isset($container) ? route('admin.container.update', [$location->id, $container->id]) : route('admin.container.store', $location->id) }}"
        method="POST" enctype="multipart/form-data" id="containerForm" class="">
        <div class="d-flex align-items-center py-3 mb-4">
            <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                            class="text-primary fw-light">Dashboard</u>
                    </a></span><span class="text-primary fw-light"> / </span><span><a
                        href="{{ route('admin.locations.index') }}"`><u class="text-primary fw-light">Locations</u>
                    </a></span><span class="text-primary fw-light"> /
                </span>{{ $custom_headings }}</h5>
        </div>
        @csrf
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ 'Category Details' }}</h5>
                    </div>
                    <div class="card-body table-responsive text-nowrap">
                        <label class="form-label" for="basic-icon-default-fullname">Thumbnail Image<span
                                class="text-danger"> *</span></label>
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            @php
                                $thumbnailImgPath = isset($container)
                                    ? ($container->image_id
                                        ? $container?->image?->full_path ?? asset('images/default.jpg')
                                        : asset('images/default.jpg'))
                                    : asset('images/default.jpg');
                                $thumbnailImgDeleteLink = isset($container)
                                    ? route('admin.container.reset.uploaded.img', [$location->id, $container->id])
                                    : '';
                            @endphp
                            <img src="{{ $thumbnailImgPath }}" alt="{{ isset($container) ? $container->name : 'Avatar' }}"
                                class="d-block rounded" height="100" width="100" style="object-fit: cover;"
                                id="uploadedAvatar" />
                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload new image</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" class="account-file-input" hidden
                                        accept="image/png, image/jpeg" name="image" />
                                </label>

                                <button type="button" class="btn btn-outline-secondary account-image-reset mb-4"
                                    id="{{ isset($container) ? $container?->id ?? null : null }}"
                                    link="{{ $thumbnailImgDeleteLink }}" onclick="rst(this)"
                                    default-img-url="{{ asset('images/default.jpg') }}">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                </button>
                                <p class="text-primary mb-0">{{ 'Allowed JPG or PNG. Max size of 2 MB' }}<br>
                                </p>
                            </div>
                        </div>
                        <br>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-fullname">Category Name<span
                                        class="text-danger"> *</span></label>
                                <input type="text" class="form-control" id="basic-icon-default-fullname"
                                    placeholder="Enter Category Name" aria-label="" name="name"
                                    value="{{ old('name', $container->name ?? '') }}"
                                    aria-describedby="basic-icon-default-fullname2" />
                            </div>
                        </div>
                    </div>

                    <div class="card-header d-flex justify-content-between align-items-center pt-0">
                        <h5 class="mb-0">{{ 'Container Details' }}</h5>
                    </div>
                    <div class="card-body table-responsive text-nowrap">

                        <div class="row">
                            <label class="form-label" for="basic-icon-default-fullname"></label>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="basic-icon-default-fullname">Container Title<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Enter Container Title" aria-label="" name="title" value="{{ old('title', $container->title ?? '') }}" aria-describedby="basic-icon-default-fullname2" />
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="feature">Corresponding Feature<span class="text-danger "> *</span></label>
                                <div style="flex:1; border: 1px solid #d9dee3; border-radius: 0.375rem; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                    <select name="feature_id" id="feature" style="border: 1px solid #d9dee3;" class="form-control selectpicker" data-live-search="true" data-dropup-auto="false">
                                        <option data-tokens="" value="" >Select Feature</option>
                                        @foreach ($featuresType as $key => $val)
                                            @if(in_array($key, $topFeatureList))
                                                <option
                                                    data-tokens="{{$val}}"
                                                    value="{{ $val }}"
                                                    data-type="parent"
                                                    @if (in_array($key,$disabledfeatures))
                                                        disabled
                                                    @endif
                                                    @if(isset($container))
                                                        @if(old('feature_id') == $val)
                                                            selected
                                                        @elseif(!empty($container?->feature_type()?->first()) && $container?->feature_type()?->first()->id == $val)
                                                            selected
                                                        @endif
                                                    @else
                                                        @if(old('feature_id') == $val)
                                                            selected
                                                        @endif
                                                    @endif
                                                    >
                                                    {{$key}}
                                                    @if (in_array($key,$disabledfeatures))
                                                        - <span class="text-red">is already in other corresponding feature</span>
                                                    @endif
                                                </option>
                                            @endif
                                        @endforeach
                                        @foreach ($features as $key => $val)
                                            @if(in_array($key, $topFeatureList))
                                                <option
                                                    data-tokens="{{$val}}"
                                                    value="{{ $val }}"
                                                    data-type="child"
                                                    @if (in_array($key,$disabledfeatures))
                                                        disabled
                                                    @endif
                                                    @if(isset($container))
                                                        @if(old('feature_id') == $val)
                                                            selected
                                                        @elseif(!empty($container?->feature()?->first()) && $container?->feature()?->first()->id == $val)
                                                            selected
                                                        @endif
                                                    @else
                                                        @if(old('feature_id') == $val)
                                                            selected
                                                        @endif
                                                    @endif
                                                    >
                                                    {{$key}}
                                                    @if (in_array($key,$disabledfeatures))
                                                        - <span class="text-red">is already in other corresponding feature</span>
                                                    @endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control" rows="4" placeholder="Write container description..." name="description" id="description">{{ old('description', $container->description ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="col-12">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ '' }}</h5>
                        </div>
                        <div class="col-12 mb-3 position-relative">
                            <label class="form-label" for="basic-icon-default-fullname ">Selected
                                Parks ( <span
                                    id="selected_park_count">{{ isset($container) ? $container->parks()->count() : 0 }}</span>
                                / 10 )</label>
                            <table class="table table-striped table-hover w-100" id="selected-park-table">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th>Park Name</th>
                                        <th>Address</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="selected_parks" id="selectedParks">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="col-12">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ '' }}</h5>
                        </div>
                        <div class="col-12 mb-3">
                            <table class="table table-striped table-hover w-100" id="park-table">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th>Park Name</th>
                                        <th>Address</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body table-responsive text-nowrap" id="container-park-btn">
                            <input type="hidden" id="feature_date_type" hidden name="feature_type" />
                            <div class="d-flex pt-3 justify-content-end">
                                @if($page_title === 'Edit Container' && isset($container))
                                <div class="mx-3 bg-danger text-white p-1  rounded">
                                    <button id="delete-container-btn"
                                        link="{{ route('admin.container.destroy',[$location->id, $container->id]) }}"
                                        {{-- rel="tooltip" title="{{ __('Delete') }}" --}}
                                        class="btn btn-iconbtn-danger dltBtn text-white"
                                        >
                                        Delete
                                    </button>
                                </div>
                                @endif
                                <button type="submit" class="btn btn-primary"
                                {{-- rel="tooltip" title="{{ __('Save') }}" --}}
                                >{{ 'Save Details' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('script')
    <script src={{ asset('js/image.js') }}></script>
    <script type="text/javascript">
        var uRL = `{{ route('admin.container.dt_park_list', $location->id) }}`;
        var uRL2 = `{{ route('admin.container.selected_park_list', [$location->id, isset($container) ? $container->id : '']) }}`;
        var container_id = `{{ isset($container) ? $container->id : null }}`;
    </script>
    <script src="{{ asset('assets/js/dt/container-dt.js') }}"></script>
@endpush
