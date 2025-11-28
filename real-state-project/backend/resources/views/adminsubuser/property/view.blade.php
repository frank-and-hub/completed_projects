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

    #previewCarousel .owl-prev, #previewCarousel .owl-next {
        padding: 10px;
    }

    #previewCarousel .owl-prev:hover, #previewCarousel .owl-next:hover {
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
                                <h5 class="card-text property-row-text"><span class="property-row-value-text" style="color:#000">{{ $data->title }}</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="drt_card card">
                        <div class="col-md-12 mt-2">
                            <h5 class="custom-heading-property">Property Images:-</h5>
                        </div>
                        <div class="owl-carousel owl-theme w-100 mt-4" id="mainGallery">
                        @foreach ($data->media as $k => $image)
                                <div class="item">
                                <img class="img-fluid " style="height: 150px; object-fit: cover; cursor: pointer; border-radius: 14px;" src="{{ filter_var($image->path, FILTER_VALIDATE_URL) ? $image->path : Storage::url($image->path) }}" alt="no-property-image"
                                    data-bs-toggle="modal"
                                    data-bs-target="#imageModal"
                                    onclick="openPreview({{ $k }})"
                                />
                            </div>
                            @endforeach
                        </div>
                    </div>
      <!-- Custom Full-Screen Preview Overlay -->
<div id="imagePreview" class="preview-overlay" style="display: none;">
    <div class="preview-content">
        <button class="close-preview" onclick="closePreview()">×</button>
        <div class="owl-carousel owl-theme" id="previewCarousel">
            @foreach ($data->media as $image)
                <div class="item">
                    <img
                        src="{{ filter_var($image->path, FILTER_VALIDATE_URL) ? $image->path : Storage::url($image->path) }}"
                        alt="Full Size Image"
                        class="img-fluid"
                        style="max-height: 80vh; object-fit: contain; width: 100%;"
                    />
                </div>
            @endforeach
        </div>
    </div>
</div>

        <div class="card_contain_data mt-1">
                    <div class="drt_card card">
                        <div class="row">
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Created
                                    Date : <br /> <span class=" property-row-value-text">{{ $data->created_date ?? 'N/A' }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Property type : <br /> <span
                                        class=" property-row-value-text">{{ $data->propertyType }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Property status : <br /> <span
                                        class=" property-row-value-text">{{ $data->propertyStatus }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Land Size : <br /> <span class=" property-row-value-text">{{ $data->landSize }}
                                        m²</span>
                                </p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Building Size : <br /> <span
                                        class=" property-row-value-text">{{ $data->buildingSize }} m</span>
                                </p>
                            </div>
                            <div class="col-md-12 mt-2 ml-2">
                                <p class="card-text property-row-text">Description :<br /> <span
                                        class=" property-row-value-text pt-1">{{ $data->description }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="drt_card card ">
                        <div class="col-md-12 mt-2">
                            <h5 class="custom-heading-property">Financials (In
                                {{ $data->financials['currency'] ?? '' }}
                                ({{ $data->financials['currency_symbol'] ?? 'N/A' }})
                                ) :-
                            </h5>
                        </div>
                        <div class="row">
                            {{-- <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Currency :  <br /><span class="property-row-value-text">
                                        {{ $data->financials['currency'] ?? 'N/A' }}</span></p>
                            </div> --}}
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Price :  <br /><span class="property-row-value-text">
                                        {{ $data->financials['price'] ? numberFormat($data->financials['price']) : 'N/A' }}
                                        {{ $data->financials['price'] ? ($data->financials['currency_symbol'] ?? 'N/A') : '' }}</span>
                                </p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Rates And Taxes :  <br /><span class="property-row-value-text">
                                        {{ $data->financials['ratesAndTaxes'] ? numberFormat($data->financials['ratesAndTaxes']) : 'N/A' }}
                                        {{ $data->financials['ratesAndTaxes'] ? ($data->financials['currency_symbol'] ?? 'N/A') : '' }}</span>
                                </p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">levy :  <br /><span class="property-row-value-text">
                                        {{ $data->financials['levy'] ? numberFormat($data->financials['levy']) : 'N/A' }}
                                        {{ $data->financials['levy'] ? ($data->financials['currency_symbol'] ?? 'N/A') : '' }}</span>
                                </p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Deposit Required :  <br /><span class="property-row-value-text">
                                        {{ $data->financials['depositRequired'] ? numberFormat($data->financials['depositRequired']) : 'N/A' }}
                                        {{ $data->financials['depositRequired'] ? ($data->financials['currency_symbol'] ?? 'N/A') : '' }}</span>
                                </p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Lease Period (Months):  <br /><span class="property-row-value-text">
                                        {{ $data->financials['leasePeriod'] ?? 'N/A' }}</span></p>
                            </div>
                            {{-- <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Price Unit :  <br /><span class="property-row-value-text">
                                        {{ $data->financials['priceUnit'] ?? 'N/A' }}</span></p>
                            </div> --}}
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Is Reduced :  <br /><span class="property-row-value-text">
                                        {{ isset($data?->financials['isReduced']) ? ($data?->financials['isReduced'] ? 'Yes' : 'No') : '' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="drt_card card">
                        <div class="col-md-12 mt-2">
                            <h5 class="custom-heading-property">Location & Address :-</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Country :  <br /><span class="property-row-value-text">
                                        {{ $data->country ?? 'N/A' }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Province :  <br /><span class="property-row-value-text">
                                        {{ $data->province ?? 'N/A' }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">City :  <br /><span class="property-row-value-text">
                                        {{ $data->town ?? 'N/A' }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Suburb :  <br /><span class="property-row-value-text">
                                        {{ $data->suburb ?? 'N/A' }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Street Number :  <br /><span class="property-row-value-text">
                                        {{ $data->address['streetNumber'] ?? 'N/A' }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Street Name :  <br /><span class="property-row-value-text">
                                        {{ $data->address['streetName'] ?? 'N/A' }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Unit Number :  <br /><span class="property-row-value-text">
                                        {{ $data->address['unitNumber'] ?? 'N/A' }}</span></p>
                            </div>
                            <div class="col-md-4 mt-2">
                                <p class="card-text property-row-text">Complex Name :  <br /><span class="property-row-value-text">
                                        {{ $data->address['complexName'] ?? 'N/A' }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="drt_card mt-1">
                <h4 class="custom-heading-property">Map:</h4>
                <div class="row">
                    <div class="col-12 form-group">
                        <div id="map" style="height:300px "></div>
                    </div>
            </div>
        </div>

                    <div class="drt_card card">
                        <div class="col-md-12 mt-2">
                            <h5 class="custom-heading-property">Bedrooms & Bathrooms :</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mt-2 property-feature-row">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-bath fa-2x mr-2"></i>
                                    <p class="card-text mb-0 ">Number of bathrooms :</p>
                                </div>
                                <div class="d-flex align-items-center">
                                <i class="fas fa-bed fa-2x mr-2 " style="opacity:0"></i>
                                    <h5 class="font-weight-bold  mb-0">
                                        @if ($data->bathrooms > 4)
                                            {{ $data->bathrooms . '+' }}
                                        @else
                                            {{ $data->bathrooms }}
                                        @endif
                                    </h5>
                            </div>
                            </div>
                            <div class="col-md-4 mt-2 property-feature-row">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-bed fa-2x mr-2"></i>
                                    <p class="card-text mb-0 ">Number of bedrooms:</p>
                                </div>

                                <div class="d-flex align-items-center">
                                    <i class="fas fa-bed fa-2x mr-2 " style="opacity:0"></i>
                                    <h5 class="font-weight-bold   my-1">
                                        @if ($data->bedrooms > 4)
                                            {{ $data->bedrooms . '+' }}
                                        @else
                                            {{ $data->bedrooms }}
                                        @endif
                                    </h5>
                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="drt_card card">
                        <div class="col-md-12 mt-2">
                            <h5 class="text-center">Rental Property Features:-</h5>
                        </div>
                        @foreach ($property_feature_columns as $key => $property_feature_column)
                            <h5 class="custom-heading-property mt-3">
                                {{ ucwords(checkBoxTextUpadte($key)) }} :-
                            </h5>
                            <div class="row">
                                @php $n_a = 1; @endphp
                                @forelse ($property_feature_column as $key => $property_feature_)
                                    @if ($data->{$key})
                                        @php $n_a = 0; @endphp
                                        <div class="col-sm-4 col-md-3 col-lg-3 mt-2">
                                            <p class="card-text property-row-text"> <br /><span class="property-row-value-text">{{ checkBoxTextUpadte([$key]) }} :
                                                </span>
                                                <ul>
                                                @forelse ($data->{$key} as $k => $property_feature_item)
                                                <li> {{checkBoxTextUpadte($property_feature_item)}} </li>
                                                @empty
                                                    N/A
                                                @endforelse

                            </ul>

                                            </p>
                                        </div>
                                    @endif
                                @empty
                                    N/A
                                @endforelse
                                @if ($n_a)
                                    <div class="col-md-12 mt-2 text-center">
                                        <p class="card-text property-row-text">N/A</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                </div>
@endsection
    @push('custom-script')
        <script type="text/javascript">
            var lat_marker = {{ $data->lat }};
            var lng_marker = {{ $data->lng }};

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





            $(document).ready(function(){
                $('#mainGallery').owlCarousel({
    loop:true,
    margin:10,
    responsiveClass:true,
    responsive:{
        0:{
            items:1,
            nav:true
        },
        600:{
            items:3,
            nav:true,

        },
        1000:{
            items:4,
            nav:true,
            loop:false
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
        </script>
    @endpush


