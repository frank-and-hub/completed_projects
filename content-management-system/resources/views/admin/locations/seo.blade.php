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

    <div class="d-flex align-items-center py-2 mb-4 justify-content-between">
        <h5 class="fw-bold mb-0" style="flex: 1" ><span><a href="{{ route('admin.dashboard') }}" `><u
                        class="text-primary fw-light">Dashboard</u>
                </a></span><span class="text-primary fw-light"> / </span><span><a
                    href="{{ route('admin.locations.index') }}" `><u class="text-primary fw-light">Locations</u>
                </a></span><span class="text-primary fw-light"> /
            </span>{{ $custom_headings }}</h5>
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
    {{-- <div class="d-flex align-items-center py-3 mb-4 justify-content-between">
         <h5 class="mb-0 text-primary fw-light">{{ $location->city }}</h5>
     </div> --}}
    <form action="{{ route('admin.locations.update.seo', $location->id) }}" method="POST" enctype="multipart/form-data" id="LocationForm">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center bg-lightblue">
                        <h5 class="mb-0">{{ "Description of $location->city for SEO"  }} </h5>
                        {{-- <h5 class="mb-0">
                            <a class="btn btn-primary seo_description_edit text-white">
                                <i class="bx bx-edit"></i>
                            </a>
                        </h5> --}}
                    </div>
                     @csrf
                    <div class="card-body table-responsive text-nowrap pt-0">
                        <label class="form-label" for="basic-icon-default-seo_description"></label>
                        <textarea type="text" class="form-control mb-3" id="basic-icon-default-seo_description" rows="10"
                            placeholder="Enter Description for SEO" aria-label="" name="seo_description"
                            value="{{ old('seo_description', $location->seo_description ?? '') }}"
                            {{-- readonly --}}
                            aria-describedby="basic-icon-default-seo_description">{{ old('seo_description', $location->seo_description ?? '') }}</textarea>

                        <div class="{{-- d-none --}} d-flex justify-content-end {{-- seo_description_submit --}}">
                            <button type="submit" class="btn btn-primary">{{ 'Save Description' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

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
