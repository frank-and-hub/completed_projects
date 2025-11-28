@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
    <style>
        .background-image {
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: contain;
            height: 500px;
            background-position: center;
        }


        .fas {
            font-size: 16px
        }


        /* Full-screen overlay */
        .preview-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 100000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Preview content */
        .preview-content {
            position: relative;
            width: 90vw;
            max-width: 1200px;
        }

        /* Close button */
        .close-preview {
            position: absolute;
            top: -40px;
            right: 0;
            background: none;
            border: none;
            color: white;
            font-size: 36px;
            cursor: pointer;
            z-index: 1001;
        }

        .close-preview:hover {
            color: #ccc;
        }

        /* Navigation arrows */
        #previewCarousel .owl-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }

        #previewCarousel .owl-prev,
        #previewCarousel .owl-next {
            padding: 10px;
        }

        #previewCarousel .owl-prev:hover,
        #previewCarousel .owl-next:hover {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
    </style>
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Properties</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('adminSubUser.property.index') }}">List</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
            </nav>
        </div>


        <div class="card mt-3">
            <div class="card-body">
                <div class="card_contain_data">
                    <div class="">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="card-text property-row-text"><span class="property-row-value-text"
                                        style="color:#000">{{ $data->title }}</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div>





            <div class="drt_card card card">
                <div class="col-md-12 mt-2">
                    <h5 class="custom-heading-property">Property Images:-</h5>
                </div>
                <div class="owl-carousel owl-theme  w-100 mt-4 " id="mainGallery">
                    @foreach ($data->photos as $k => $image)
                        <div class="item">
                            <img class="img-fluid "
                                style="height: 150px; object-fit: cover; cursor: pointer; border-radius: 14px;"
                                src="{{  filter_var($image->imgUrl, FILTER_VALIDATE_URL) ? $image->imgUrl : Storage::url($image->imgUrl)  }}"
                                alt="no-property-image" onclick="openPreview({{ $k }})" />
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Custom Full-Screen Preview Overlay -->
            <div id="imagePreview" class="preview-overlay" style="display: none;">
                <div class="preview-content">
                    <button class="close-preview" onclick="closePreview()">×</button>
                    <div class="owl-carousel owl-theme full-width" id="previewCarousel">
                        @foreach ($data->photos as $image)
                            <div class="item">
                                <img src="{{  filter_var($image->imgUrl, FILTER_VALIDATE_URL) ? $image->imgUrl : Storage::url($image->imgUrl)  }}"
                                    alt="Full Size Image" class="img-fluid"
                                    style="max-height: 80vh; object-fit: contain; width: 100%;" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>







            <div class="card_contain_data mt-3">
                <div class="drt_card card">
                    <div class="row">
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Created
                                Date : <br /> <span
                                    class="property-row-value-text">{{ $data->created_date ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Property type : <br /> <span
                                    class="property-row-value-text">{{ $data->propertyType }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Property status : <br /> <span
                                    class="property-row-value-text">{{ $data->propertyStatus }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Land Size : <br /> <span
                                    class="property-row-value-text">{{ $data->landSize }}
                                    m²</span>
                            </p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Building Size : <br /><span
                                    class="property-row-value-text">{{ $data->buildingSize }} m</span>
                            </p>
                        </div>
                        <div class="col-md-12 mt-2 ml-2">
                            <p class="card-text property-row-text">Description : <br /> <span
                                    class="property-row-value-text pt-1">{{ $data->description }}</span></p>
                        </div>
                    </div>
                </div>


                <div class="drt_card ">
                    <div class="col-md-12 mt-2">
                        <h5 class="custom-heading-property">Location & Address :-</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Country : <br /> <span class="property-row-value-text">
                                    {{ $data->country ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Province : <br /> <span class="property-row-value-text">
                                    {{ $data->province ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">City : <br /> <span class="property-row-value-text">
                                    {{ $data->town ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Suburb : <br /> <span class="property-row-value-text">
                                    {{ $data->suburb ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Street Number : <br /> <span
                                    class="property-row-value-text">
                                    {{ $data->address['streetNumber'] ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Street Name : <br /> <span
                                    class="property-row-value-text">
                                    {{ $data->address['streetName'] ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Unit Number : <br /> <span
                                    class="property-row-value-text">
                                    {{ $data->address['unitNumber'] ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text property-row-text">Complex Name : <br /> <span
                                    class="property-row-value-text">
                                    {{ $data->address['complexName'] ?? 'N/A' }}</span></p>
                        </div>
                    </div>
                </div>


                @if ($data->latlng)

                    <div class="drt_card mt-1">
                        <h4 class="custom-heading-property">Map:</h4>
                        <div class="row">
                            <div class="col-12 form-group">
                                <div id="map" style="height:300px "></div>
                            </div>
                        </div>
                    </div>

                @endif

                <div class="drt_card ">
                    <div class="col-md-12 mt-2">
                        <h5 class="custom-heading-property">Other Facilities :</h5>
                    </div>
                    <div class="row">
                        @if ($data->propertyFeatures)
                            <div class="col-md-4 mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check mr-0 my-1"></i>
                                    <div class="col-md-11 mt-0">
                                        <p class="card-text property-row-text mt-0">
                                            <span
                                                class="property-row-value-text mt-0 ">{{ $data->propertyFeatures ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($data->carports > 0)
                            <div class="col-md-4 mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check mr-0 my-1"></i>
                                    <div class="col-md-11 mt-0">
                                        <p class="card-text property-row-text mt-0">
                                            <span
                                                class="property-row-value-text mt-0">{{ $data->carports ? $data->carports > 1 ?? '' : '' }}
                                                Car ports </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($data->livingAreas > 0)
                            <div class="col-md-4 mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check mr-0 mt-0"></i>
                                    <div class="col-md-11 mt-0">
                                        <p class="card-text property-row-text mt-0">
                                            <span
                                                class="property-row-value-text mt-0">{{ $data->livingAreas ? $data->livingAreas > 1 ?? '' : '' }}
                                                Living Area </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>



            </div>
@endsection
        @push('custom-script')
            <script type="text/javascript">





                $(document).ready(function () {
                    $('#mainGallery').owlCarousel({
                        loop: true,
                        margin: 10,

                        responsiveClass: true,
                        responsive: {
                            0: {
                                items: 1,
                                nav: true
                            },
                            600: {
                                items: 3,
                                nav: true,

                            },
                            1000: {
                                items: 4,
                                nav: true,
                                loop: false
                            }
                        },
                        navText: [
                            '<i class="fa fa-chevron-left" style="color: white; font-size: 24px;"></i>',
                            '<i class="fa fa-chevron-right" style="color: white; font-size: 24px;"></i>'
                        ]
                    })


                    // Initialize Preview Carousel
                    const previewCarousel = $('#previewCarousel').owlCarousel({
                        loop: true,
                        margin: 0,
                        nav: true,
                        dots: false,
                        items: 1,
                        navText: [
                            '<i class="fa fa-chevron-left" style="color: white; font-size: 24px;"></i>',
                            '<i class="fa fa-chevron-right" style="color: white; font-size: 24px;"></i>'
                        ]
                    });

                });
                // Open preview and navigate to specific image
                function openPreview(index) {
                    $('#imagePreview').fadeIn(300);
                    $('#previewCarousel').owlCarousel('to', index, 0, true);
                    $('body').css('overflow', 'hidden'); // Prevent scrolling on the main page
                }

                // Close preview
                function closePreview() {
                    $('#imagePreview').fadeOut(300);
                    $('body').css('overflow', 'auto'); // Restore scrolling
                }

                var lat_marker = `{{ $data->lat  ?? ''}}`;
                var lng_marker = `{{ $data->lng  ?? ''}}`;

                async function initMap() {
                    const {
                        ColorScheme
                    } = await google.maps.importLibrary("core")

                    const {
                        Map,
                        InfoWindow
                    } = await google.maps.importLibrary("maps");
                    const {
                        AdvancedMarkerElement
                    } = await google.maps.importLibrary("marker");
                    const map = new Map(document.getElementById("map"), {
                        center: {
                            lat: lat_marker,
                            lng: lng_marker
                        },
                        zoom: 16,
                        mapId: "4504f8b37365c3d0",
                        colorScheme: ColorScheme.DARK,
                    });

                    const marker = new AdvancedMarkerElement({
                        map,
                        position: {
                            lat: lat_marker,
                            lng: lng_marker
                        },
                    });
                }


            </script>
        @endpush
