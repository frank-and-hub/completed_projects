@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <x-admin.breadcrumb active="Details" :breadcrumbs="$breadcrumbs">
    </x-admin.breadcrumb>

    <div class="row" style="position: relative">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Park Details</h5>
                    <div>
                        <a href="{{ route('admin.park.image.edit',$park->id) }}" rel="tooltip"
                            title="Add Images"><i class='bx bx-image-add text-primary'
                                style="font-size: 40px !important;"></i></a>





                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-primary">Name:</label>
                            <span class="text-muted">{{ ucwords($park->name) }}</span><br>

                            <label class="text-primary" style="vertical-align:-2px;">Average Rating:</label>
                            <div id="avg-rating" style='display:inline-block;'>
                            <x-admin.ratingcomponent rating="{{ $avg_ratings }}" />
                            <span style='vertical-align:-3px;'
                                class='p-1 text-muted'>{{ $avg_ratings }} (<i class="tf-icons bx bxs-user"
                                    style="font-size:1rem; vertical-align:-2px"></i> {{ count($ratings) }})</span>
                            </div>

                        </div>

                        <div class="col-md-6">
                            <label class="text-primary">Link:</label>
                            @if(!empty($park->url))
                            <a href="{{ $park->url }}" class="text-muted">{{ $park->url }}</a>
                            @else
                            N/A
                            @endif
                            <br>
                        </div>
                        <div class="col-md-12">
                            <label class="text-primary">Description</label><br>

                            <div style="word-break: break-all;" class="text-muted border border-muted custom-box p-3">
                                {{ ucfirst($park->description ?? 'N/A') }}
                            </div>
                        </div>

                        <div class="d-md-flex justify-content-between align-items-center mt-3 py-3 bg-lightblue">
                            <div class="d-flex align-items-center mb-3 mb-md-0">
                                <div class="circle-icon-green">
                                    <i class="bx bxs-map text-white circle-icon-text"></i>
                                </div>
                                <div class="ml-4 mt-3">
                                    <h6 class="mb-0 text-primary">Address</h6>
                                    <div class="text-muted" style="width:280px;">{{ $park->address ?? 'NA' }}</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3 mb-md-0">
                                <div class="circle-icon-green">
                                    <i class="bx bx-world text-white circle-icon-text"></i>
                                </div>
                                <div class="ml-4 mt-3">
                                    <h6 class="mb-0 text-primary">Country</h6>
                                    <div style="width: 100px;" class="text-muted">{{ $park->country ?? 'N/A' }}</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3 mb-md-0">
                                <div class="circle-icon-green">
                                    <i class="bx bxs-map-pin text-white circle-icon-text"></i>
                                </div>
                                <div class="ml-4 mt-3">
                                    <h6 class="mb-0 text-primary">City</h6>
                                    <div class="text-muted" style="width:200px;">{{ $park->city ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="row mt-4">
                        @if ($parentCategory > 0)
                            <div class="card px-0">
                                <div class="card-header bg-lightblue">
                                    <label class="form-label">Home Page Categories</label>

                                </div>
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach ($categories as $category)
                                                @if (in_array($category->id, $selected_categories) && $category->type == 'parent')
                                                    <div class="col-md-6 mb-3">
                                                        <div class="custom-box border border-muted text-center">
                                                            <div class="text-left text-muted px-3 pt-2">
                                                                <h6 class="text-muted">{{ $category->name }}</h6>

                                                            </div>
                                                            <hr class="mb-0">
                                                            <div class="row d-flex text-center mx-2 mt-3">
                                                                @foreach ($subcategories as $subcategory)
                                                                    @if (in_array($subcategory->id, $selected_subcategories) && $subcategory->category_id == $category->id)
                                                                        <span class="sub-box-selected"
                                                                            style="cursor: default" rel="tooltip"
                                                                            title="Child Category">
                                                                            <span>{{ $subcategory->name }}</span>
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>



                                    </div>

                                </div>
                            </div>
                        @endif

                        @if (count($standalone) > 0)
                            <div class="card mt-3 px-0">
                                <div class="card-header bg-lightblue">
                                    <label class="form-label">Standalone Categories</label>

                                </div>
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="custom-box border border-muted text-center">
                                                    <div class="row d-flex text-center mx-2 mt-3">
                                                        @foreach ($standalone as $data)
                                                            <span class="sub-box-selected" style="cursor: default">
                                                                <span>{{ ucfirst($data->category->name) }}
                                                                    {{ $data->category->special_category == 1 ? '(Special Category)' : null }}</span>
                                                            </span>
                                                        @endforeach
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif

                        @if (count($park->features) > 0)
                            <div class="card mt-3 px-0">
                                <div class="card-header bg-lightblue">
                                    <label class="form-label">Features</label>

                                </div>
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach ($feature_types as $feature_type)
                                                @if (in_array($feature_type->id, $selected_feature_types))
                                                    <div class="col-md-6 mb-3">
                                                        <div class="custom-box border border-muted text-center">
                                                            <div class="text-left text-muted px-3 pt-2">
                                                                <h6 class="text-muted">{{ ucfirst($feature_type->name) }}
                                                                </h6>

                                                            </div>
                                                            <hr class="mb-0">
                                                            <div class="row d-flex text-center mx-2 mt-3">
                                                                @foreach ($features as $feature)
                                                                    @if (in_array($feature->id, $selected_features) && $feature->feature_type_id == $feature_type->id)
                                                                        <span class="sub-box-selected"
                                                                            style="cursor: default" rel="tooltip"
                                                                            title="Child Feature">
                                                                            <span>{{ ucwords($feature->name) }}</span>
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach


                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif


                        {{-- Park Availability --}}
                        <div class="card mt-3 px-0">
                            <div class="card-header bg-lightblue">
                                <label class="form-label">Park Availabilities</label>
                            </div>
                            <div class="col-md-12">

                                <div class="card-body">
                                    <div class="custom-box border border-muted">
                                        <table class="table text-center">
                                            <thead>
                                                <tr>
                                                    <th>Day(s)</th>
                                                    <th>Availability</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($days as $day)
                                                    <tr>
                                                        <td>
                                                            <div class="selected-day ml-5 text-center greenDay">
                                                                {{ ucfirst($day) }}</div>
                                                        </td>
                                                        <td class="availability">
                                                            <span>{{ $availability_days[$day] == '' ? 'Not available' : $availability_days[$day] }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach


                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                            </div>

                        </div>
                        @can('users-show')
                        <div class="card mt-3 px-0" style='position: relative;'>
                            <div class="loader d-none"
                                id="review-loader"style="margin: auto; position: absolute;left: 50%;top: 45%;">
                            </div>
                            <div class="card-header bg-lightblue">
                                <label class="form-label" id='total-reviews'>Reviews ({{ count($ratings) }})</label>

                            </div>
                            <section id="reviews">
                            </section>
                            {{-- @foreach ($ratings as $rating)
                                <section id="reviews">

                                    <div class="col-md-12">
                                        <div class="card-body border border-muted rounded mt-3 mb-3">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <div class="flex-shrink-0 me-3">
                                                        <div @class(['avatar'])>
                                                            <img src="{{ !empty($rating->user->image) ? $rating->user->image->full_path : asset('images/user.svg') }}"
                                                                class="w-px-40 h-auto rounded-circle">
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <a href="{{ route('admin.user.view', $rating->user->id) }}"><span
                                                                class="fw-semibold d-block">{{ ucfirst($rating->user->name) }}</span></a>
                                                        <x-admin.ratingcomponent rating="{{ $rating->rating }}" />
                                                        <small class="text-muted"
                                                            style="vertical-align: -2px;">{{ \Carbon\Carbon::parse($rating->user->created_at)->format('M d, Y') }}</small>

                                                    </div>
                                                </div>
                                                <div>
                                                    <i class='bx bxs-trash-alt text-danger delete-icon'
                                                        onclick="deleteReview(this)" park-id='{{ $rating->park->id }}'
                                                        user-id='{{ $rating->user->id }}' rel="tooltip"
                                                        title="Delete"></i>
                                                </div>
                                            </div>
                                            <div class="text-muted me-3  mt-3 pl-1">
                                                {{ ucfirst($rating->review) }}
                                            </div>

                                        </div>

                                    </div>
                                </section>
                            @endforeach --}}


                        </div>

                        @endcan



                    </div>
                </div>





            </div>



        </div>
    </div>

    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/details/park-details.js') }}"></script>
    <script>
        var delete_user_review_url = "{{ route('admin.park.delete.user.review') }}";
        var load_more_url = "{{ route('admin.park.load.more.reviews') }}";
        var park_id = "{{ $park->id }}";
        var offsetVal = 10;
    </script>
    <script>
        $(document).ready(function() {
            ShowTooltip();
        })
    </script>
@endpush
