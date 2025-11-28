@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->
@section('content')
@php
    $backRoute = app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName()
@endphp

    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1">
            <span>
                <a href="{{ route('admin.dashboard') }}"`>
                    <u class="text-primary fw-light">Dashboard</u>
                </a>
            </span>
            <span class="text-primary fw-light"> / </span>
            <span>
                <a href="{{ route('admin.park.index') }}"`>
                    <u class="text-primary fw-light">Parks</u>
                </a>
            </span>
            <span class="text-primary fw-light"> / </span>{{ $park ? 'Edit Park' : 'Create New Park' }}
        </h5>
    </div>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $park ? 'Edit Park' : 'Create New Park' }}</h5>
                </div>
                <div class="card-body table-responsive text-nowrap">
                    <form action="{{ route('admin.park.save') }}" method="post" enctype="multipart/form-data" id="categoryForm">
                        @csrf
                        <input type="hidden" name="backRoute" value="{{$backRoute}}" />
                        <input type="hidden" name="previousUrl" value="{{URL::previous()}}" />
                        <div class="card-body">
                            <div class="card-body">
                                <input type="hidden" name="id" value="{{ $park->id ?? '' }}">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label" for="basic-icon-default-fullname">Name<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Enter Name" aria-label=""required value="{{ old('name', $park->name ?? '') }}" name="name" aria-describedby="basic-icon-default-fullname2" />
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label" for="basic-icon-default-url">Url</label>
                                        <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Enter Url" aria-label="" value="{{ old('url', $park->url ?? '') }}" name="url" aria-describedby="basic-icon-default-fullname2" />
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label" for="basic-icon-default-fullname">Address<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" placeholder="Enter Address" aria-label=""required value="{{ old('address', $park->address?? '') }}" name="address" id="address" aria-describedby="basic-icon-default-address" />
                                    </div>
                                    <div class="col-12">
                                        <div id="map" style='width: 100%; height: 300px;'></div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="basic-icon-default-description">Description</label>
                                        <textarea type="text" maxlength="1000"  class="form-control" id="basic-icon-default-description" placeholder="Enter Description" aria-label="" value="" name="description" rows="5" aria-describedby="basic-icon-default-description">{{ old('description', $park->description ?? '') }}</textarea>
                                    </div>
                                    <input type="hidden" value="{{ old('laitude', $park->latitude ?? '') }}" id="latitude" name="latitude" />
                                    <input type="hidden" value="{{ old('longitude', $park->longitude ?? '') }}" id="longitude" name="longitude" />
                                    <input type='hidden' name="country" id="country" value="{{old('country',$park->country??'')}}">
                                    <input type='hidden' name="country_short_name" id="country_short_name" value="{{old('country_short_name',$park->country_short_name??'')}}">
                                    <input type='hidden' name="city" id="city" value="{{old('city',$park->city??'')}}">
                                    <input type='hidden' name="state" id="state" value="{{old('state',$park?->state??'')}}">
                                    <input type='hidden' name="timezone" id="timezone" value="{{old('timezone',$park->timezone??'')}}">
                                    <input type='hidden' name="location_longitude" id="location_longitude" value="">
                                    <input type='hidden' name="location_latitude" id="location_latitude" value="">

                                    {{-- ---------------------------------------------------------------------------------                                 --}}
                                    <div class="col-6 mb-3" style="position: relative">
                                        <div class="d-flex justify-content-center d-none;" id="categoriesloader" style="margin: auto; position: absolute; opacity: 9; z-index: 1; top: 200px; bottom: 10px; right: 230px; ">
                                            <div class="loader"> </div>
                                        </div>
                                        <label for="largeSelect" class="form-label">Categories</label>
                                        <div class="mt-2 mb-3">
                                            <div class="categories-box">
                                                <select id="parent-category" class="form-select-lg" style="width:100%">
                                                    <option value="parent">Parent Category</option>
                                                    <option value="no-child">Standalone</option>
                                                </select>
                                                <div class="d-flex align-items-center border border-secondary rounded p-1 mt-4 bg-light">
                                                    <i class="bx bx-search fs-4 lh-0 ml-3 text-muted"></i>
                                                    <input type="text" class="form-control border-0 shadow-none" onclick="$(this).parent().find('i').addClass('d-none')" placeholder="Search" aria-label="Search" id="search" onblur="if($(this).val()==0){ $(this).parent().find('i').removeClass('d-none'); }">
                                                </div>
                                            </div>
                                            <div id="categorieslist" class="categorieslist"></div>
                                        </div>
                                    </div>

                                    <div class="col-6 mb-3">
                                        <label for="largeSelect" class="form-label">Saved Categories </label>
                                        <div class="mt-2 mb-3 savedcategories-box">
                                            <div class="saved-subcategories">
                                                @if(count($selected_categories)>0)
                                                    @foreach($categories as $category)
                                                        @if(in_array($category->id,$selected_categories) && ($category->type =='parent'))
                                                        <div class="border border rounded mb-3 subcategories">
                                                            <div class="text-left  mt-3 ml-3">
                                                                <label class="text-muted">{{$category->name}} {{($category->special_category==1)?(__("admin.seasonal_category")):null}}</label>
                                                                <input type="hidden" value="{{$category->id}}" name="category_ids[]">
                                                            </div>
                                                            <div class="row d-flex justify-content-start pl-4" id="parent_{{$category->id}}">
                                                                @foreach ($subcategories as $subcategory )
                                                                    @if(in_array($subcategory->id,$selected_subcategories) && ($subcategory->category_id==$category->id))
                                                                        <div @class(['sub-box-selected' ]) style="cursor: default">
                                                                            <span>{{$subcategory->name}}</span>
                                                                            <input type="hidden" name="subcategory_ids[]" value="{{$subcategory->id}}">
                                                                            <img src="{{asset('assets/img/icons/unicons/close.png')}}" draggable="false" active-status="1" subcategory_id="{{$subcategory->id}}" ondragstart="return false;" class="close-btn" index="0" parentidx="2">
                                                                        </div>
                                                                        <!--
                                                                            <div @class(['sub-box-selected','disable-box-selected'=>((($subcategory->parkcategory->first()->active??null)==0)||($category->active==0))]) style="cursor: default">
                                                                                <span>{{$subcategory->name}}</span>
                                                                                <input type="hidden" name="subcategory_ids[]" value="{{$subcategory->id}}">
                                                                                <img src="{{asset('assets/img/icons/unicons/close.png')}}" draggable="false" active-status="{{$category->active??$subcategory->parkcategory->first()->active}}" subcategory_id="{{$subcategory->id}}" ondragstart="return false;" class="close-btn" index="0" parentidx="2">
                                                                            </div>
                                                                        -->
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if(in_array($category->id,$selected_categories) && ($category->type =='no-child')) @php $display=""; @endphp  @endif
                                                    @endforeach

                                                    {{-- ------ no child categories-- --}}
                                                    @if(in_array(null,$selected_subcategories))
                                                        <div class="border border rounded mb-3 no-child-categories ">
                                                            <div class="text-left  mt-3 ml-3">
                                                                <label class="text-muted">Standalone</label>
                                                            </div>
                                                            <div class="row d-flex justify-content-start pl-4">

                                                                @foreach($categories as $category)
                                                                    @if(in_array($category->id,$selected_categories) && ($category->type =='no-child'))
                                                                        <div  @class([ "sub-box-selected" ]) style="cursor: default">
                                                                            <span>{{$category->name}}</span>
                                                                            <input type="hidden" name="category_ids[]" value="{{$category->id}}">
                                                                            <img src="{{asset('assets/img/icons/unicons/close.png')}}" draggable="false" ondragstart="return false;" status-active="1" child_category_id="{{$category->id}}" class="no-child-close-btn">
                                                                        </div>
                                                                        <!-- <div  @class(["sub-box-selected","disable-no-child-categories-box"=>($category->parks->first()->active==0) ]) style="cursor: default">
                                                                            <span>{{$category->name}}</span>
                                                                            <input type="hidden" name="category_ids[]" value="{{$category->id}}">
                                                                            <img src="{{asset('assets/img/icons/unicons/close.png')}}" draggable="false" ondragstart="return false;" status-active="{{$category->parks->first()->active}}" child_category_id="{{$category->id}}" class="no-child-close-btn">
                                                                        </div> -->
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 mb-3">
                                        <label for="lrgeSelect" class="form-label">Select Features</label>
                                        <div class="mt-2 mb-3">
                                            <div class="featurelist-box">
                                                <div class="d-flex align-items-center border border-secondary rounded p-1 mt-4 bg-light">
                                                    <i class="bx bx-search fs-4 lh-0 ml-3 text-muted"></i>
                                                    <input type="text" class="form-control border-0 shadow-none" onclick="$(this).parent().find('i').addClass('d-none')" placeholder="Search" aria-label="Search" id="search" onblur="if($(this).val()==0){  $(this).parent().find('i').removeClass('d-none'); }">
                                                </div>
                                            </div>
                                            <div id="featurelist" class="featurelist">
                                                @foreach($all_features as $feature_type)
                                                    <div class="featurelist-collapse-box d-flex justify-content-between mb-3">
                                                        <span>{{ucwords($feature_type->name)}}</span>
                                                        <span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-down-fill" viewBox="0 0 16 16">
                                                                <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"></path>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="feature-box mt-4 mx-1 row d-flex text-center d-none">
                                                        @if(count($feature_type->features)!=0)
                                                            @foreach($feature_type->features as $feature )
                                                                <span  @class(['sub-box'=>!empty($feature->active)==1,'disable-sub-box'=>!empty($feature->active)==0]) value="{{$feature->id}}" feature_type ="{{ucwords($feature_type->name)}}" featuretype_id="{{$feature_type->id}}">
                                                                    <span>{{ucwords($feature->name)}}</span>
                                                                </span>
                                                            @endforeach
                                                        @else
                                                        <span class='text-secondary msg' style='position:relative; bottom:10px;'>
                                                            <span>There are no child feature added yet !</span>
                                                        </span>
                                                        @endif

                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 mb-3">
                                        <label for="largeSelect" class="form-label">Saved Features </label>
                                        <div class="mt-2 mb-3 savedfeatures-box">
                                            @if(count($selected_feature_types)>0)
                                                @foreach($feature_types as $feature_type)
                                                    @if(in_array($feature_type->id,$selected_feature_types))
                                                        <div class="saved-subfeatures">
                                                            <div class="border border rounded mb-3 selectedfeatures">
                                                                <div class="text-left mt-3 ml-3">
                                                                    <label class="text-muted">{{ucwords($feature_type->name)}}</label>
                                                                    <input type="hidden" value="{{$feature_type->id}}" name="feature_type_ids[]">
                                                                </div>
                                                                <div class="row d-flex justify-content-start pl-4" id="feature_type_{{$feature_type->id}}">
                                                                    @foreach($features as $feature)
                                                                        @if(in_array($feature->id,$selected_features) && ($feature->feature_type_id==$feature_type->id))
                                                                            <div @class(['sub-box-selected','disable-sub-box'=>(!empty($feature->parkfeatures->first())?$feature->parkfeatures->first()->active:null)==0 ]) style="cursor: default">
                                                                                <span>{{ucwords($feature->name)}}</span>
                                                                                <input type="hidden" name="feature_ids[]" value="{{$feature->id}}">
                                                                                <img src="{{asset('assets/img/icons/unicons/close.png')}}" draggable="false" active-feature="{{!empty($feature->parkfeatures->first())?$feature->parkfeatures->first()->active:null}}" feature_id="{{$feature->id}}" ondragstart="return false;" class="close-btn">
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="largeSelect" class="form-label">Park Admission</label>
                                        <div class="parktiming-box">
                                            <div class="row pl-2">
                                                <div class="col-6 pl-4">
                                                    <label class="radio-inline">
                                                        <input type="radio" class="form-check-input mr-2" id="free" value="0" name="is_paid"  @checked(old('is_paid',0)||( isset($park->is_paid)?!$park->is_paid : true ))>
                                                        <label class="ml-2" for="free"> Free</label>
                                                    </label>
                                                    <label class="radio-inline ml-5">
                                                    <input type="radio" class="form-check-input mr-2 " id="paid" value="1" name="is_paid"  @checked(old('is_paid')||( isset($park->is_paid)?$park->is_paid : false ))>
                                                        <label class="ml-2" for="paid"> Paid</label>
                                                    </label>
                                                </div>
                                                <div class="col-2">
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-inline">
                                                        <label class="form-label" for="ticket_amount">Ticket Amount </label>
                                                        <input type="number" class="form-control admissionInput w-25 ml-2" disabled placeholder="0.00"  min="0" id="ticket_amount" name="ticket_amount" value="{{old('ticket_amount')??$park->ticket_amount??''}}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <label class="form-label" for="instructions">Instructions</label>
                                                    <textarea class="form-control admissionInput" name="instructions" id="instructions" disabled rows="3" style="height: 123px;" placeholder="Enter Instructions">{{old('instructions')??$park->instructions??''}}</textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <label class="form-label" id="instruction_url">Instruction Link</label>
                                                    <input type="url" class="form-control admissionInput" name="instruction_url" id="instruction_url" disabled placeholder="Enter Instruction Link" value="{{old('instruction_url')??$park->instruction_url??''}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- park availability section --}}
                                    <div class="col-6" style="position:relative;">
                                        <div  class="d-none" style="maring:auto; position:absolute; top:10px; left:46%; z-index:999; top:30%;" id="availabilityLoader">
                                            <div class="loader">
                                            </div>
                                        </div>
                                        <label for="largeSelect" class="form-label">Set Park Availability</label>
                                        <div class="parktiming-box">
                                            <div class="everyday-box">
                                                <div class="form-check park-timing-check-label">
                                                    <input class="form-check-input greenCheckBox" type="checkbox" value="4"  id="everyday" name="">
                                                    <label class="form-check-label" for="everyday">
                                                        Every Day
                                                    </label>
                                                </div>
                                                <div class="days-box mt-2 row d-flex text-center">
                                                    <div class="day day-box sunday">
                                                        <span>Sunday &nbsp;</span>
                                                    </div>
                                                    <div class="day day-box monday">
                                                        <span>Monday &nbsp;</span>
                                                    </div>
                                                    <div class="day day-box tuesday">
                                                        <span>Tuesday &nbsp;</span>
                                                    </div>
                                                    <div class="day day-box wednesday">
                                                        <span>Wednesday &nbsp;</span>
                                                    </div>
                                                    <div class="day day-box thursday">
                                                        <span>Thursday &nbsp;</span>
                                                    </div>
                                                    <div class="day day-box friday">
                                                        <span>Friday &nbsp;</span>
                                                    </div>
                                                    <div class="day day-box saturday">
                                                        <span>Saturday &nbsp;</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="type mt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="type" id="down_to_dusk" value="Dawn To Dusk" checked disabled />
                                                    <label class="form-check-label" for="down_to_dusk">
                                                        Dawn To Dusk
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="type" id="24_hours" value="24 Hours" disabled />
                                                    <label class="form-check-label" for="24_hours"> 24 Hours </label>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="type" id="custom_time" value="custom" disabled />
                                                    <label class="form-check-label" for="custom_time"> Custom Time </label>
                                                </div>
                                            </div>
                                            <div class="custom-time d-none">
                                                <label for="opening_time">Opening Time</label>
                                                <input type="time" class="form-control" placeholder="06:00" id="opening_time">

                                                <label for="closing_time">Closing Time</label>
                                                <input type="time" class="form-control" placeholder="20:00" id="closing_time">
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <input type="button" class="btn btn-danger mr-2" style="visibility: hidden" id="day_remove_btn" value="Remove">
                                                <input type="button" class="btn btn-primary mr-2 d-none" id="day_update_btn" value="Update">
                                                <input type="button" class="btn btn-primary d-none" id="day_save_btn" value="Save">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <label for="largeSelect" class="form-label">Park Availability</label>
                                        <div class="park-availability-box" style="position: relative;">
                                            <div class="d-flex justify-content-center d-none" id="loader" style=" margin: auto; position: relative; opacity: 9; z-index: 1; top: 200px; bottom: 5; top: 270px;">
                                                <div class="loader"></div>
                                            </div>
                                            <table class="table text-center">
                                                <thead>
                                                    <th>Day(s)</th>
                                                    <th>Availability</th>
                                                </thead>
                                                <tbody>
                                                    <tr id="sunday" class="d-none"><td><div class="selected-day" style="width:100% !important; height:28px !important; cursor: default;">Sunday</div></td>
                                                        <td class="availability"></td>
                                                    </tr>
                                                    <tr id="monday" class="d-none"><td><div class="selected-day" style="width:100% !important; height:28px !important; cursor: default;">Monday</div></td>
                                                        <td class="availability"></td>
                                                    </tr>
                                                    <tr id="tuesday" class="d-none"><td><div class="selected-day" style="width:100% !important; height:28px !important; cursor: default;">Tuesday</div></td>
                                                        <td class="availability"></td>
                                                        </tr>
                                                    <tr id="wednesday" class="d-none"><td><div class="selected-day" style="width:100% !important; height:28px !important; cursor: default;">Wednesday</div></td>
                                                        <td class="availability"> </td>
                                                    </tr>
                                                    <tr id="thursday" class="d-none"><td><div class="selected-day" style="width:100% !important; height:28px !important; cursor: default;">Thursday</div></td>
                                                        <td class="availability"></td>
                                                    </tr>
                                                    <tr id="friday" class="d-none"><td><div class="selected-day" style="width:100% !important; height:28px !important; cursor: default;">Friday</div></td>
                                                        <td class="availability"></td>
                                                    </tr>
                                                    <tr id="saturday" class="d-none"><td><div class="selected-day" style="width:100% !important; height:28px !important; cursor: default;">Saturday</div></td>
                                                        <td class="availability"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <input type="hidden" name="deleted_images" id="deleted_images">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <input type="submit" class="btn btn-primary" value="Save Park">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if($park)
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <x-admin.datatable id="reviews-park-tbl" loaderID="review-tbl-loader" title="Reviews">
                    <x-slot:headings>
                        <th>User</th>
                        <th>Reviews</th>
                        <th>Ratings</th>
                        <th>Action</th>
                    </x-slot:headings>
                </x-admin.datatable>
            </div>
        </div>
    </div>
    @endif
@endsection

<script src={{ asset('js/image.js') }}></script>

@push('script')
<script src="{{ asset('assets/js/park/review-dt.js') }}"></script>
<script src="{{asset('assets/js/park/park-availability.js')}}"></script>
<script>
    var lat ="{{  $park ? $park->latitude :42.364506}}",
     @if($park)
     review_url = "{{ route('admin.park.user.reviews.dt_list', $park->id) }}",
     @endif
     long = "{{$park ? $park->longitude : -71.038887 }}",
     token_ = "{{ config('services.MAP_BOX_ACCESS_TOKEN') }}";
    </script>
    <script src="{{asset('assets/js/park/map-box.js')}}"></script>
    <script>
        var editUrl='';
        @if($park)
         editUrl="{{route('admin.park.edit',$park->id)}}";
        @endif

        var arr;
        window.deleted_images = [];
        $(function() {
            // Multiple images preview in browser
            var imagesPreview = function(input, placeToInsertImagePreview) {
                if (input.files) {
                    var filesAmount = input.files.length;
                    for (i = 0; i < filesAmount; i++) {
                        let innerHTML = ''
                        let count = i
                        var reader = new FileReader();
                        reader.onload = function(event) {
                            innerHTML += ` <div class="img-wrap" style=" position: relative;" id="${count}">
                                                    <span class="close"
                                                        style=" position: absolute;top: 1px; right: 1px;
                                                z-index: 100;background: red; border-radius: 20px; padding: 0px 5px 5px 5px;color: white;"
                                                        onclick="removeFile(${count})">&times;</span>
                                                    <img src="${event.target.result}" style="object-fit: cover; padding:10px"
                                                        class="d-block rounded" height="150px" width="150px"
                                                        id="${count}">
                                                </div>`;
                            $('#gallery-all-images').append(innerHTML)
                        }
                        reader.readAsDataURL(input.files[i]);
                    }
                    console.info({
                        innerHTML
                    });
                }
            };

            $('#gallery-photo-add').on('change', function() {
                $('#gallery-all-images').html("");
                arr = this
                imagesPreview(this, 'div.gallery-all-images');
            });

            $('#reset').on('click', function() {
                $('#gallery-all-images').html("");
                document.getElementById("gallery-photo-add").value = "";
            });
        });

        function removeFile(index) {
            var attachments = document.getElementById("gallery-photo-add")
                .files; // <-- reference your file input here
            var fileBuffer = new DataTransfer();
            // append the file list to an array iteratively
            for (let i = 0; i < attachments.length; i++) {
                // Exclude file in specified index
                if (index !== i)
                    fileBuffer.items.add(attachments[i]);
            }


            // Assign buffer to file input
            document.getElementById("gallery-photo-add").files = fileBuffer
                .files; // <-- according to your file input reference

            let temp = fileBuffer.files

            var filesAmount = temp.length;
            let placeToInsertImagePreview = 'div.gallery-all-images';
            $('#gallery-all-images').empty();
            for (i = 0; i < filesAmount; i++) {
                let innerHTML = ''
                let count = i

                var reader = new FileReader();
                reader.onload = function(event) {
                    innerHTML += ` <div class="img-wrap" style=" position: relative;" id="${count}">
                                                    <span class="close"
                                                        style=" position: absolute;top: 1px; right: 1px;
                                                z-index: 100;background: red; border-radius: 20px; padding: 0px 5px 5px 5px;color: white;"
                                                        onclick="removeFile(${count})">&times;</span>
                                                    <img src="${event.target.result}" style="object-fit: cover; padding:10px"
                                                        class="d-block rounded" height="150px" width="150px"
                                                        id="${count}">
                                                </div>`;
                    $('#gallery-all-images').append(innerHTML)
                }

                reader.readAsDataURL(temp[i]);


            }
        }
        function deleteImage(id) {
            $('#' + id).html("");
            window.deleted_images.push(id);
            document.getElementById("deleted_images").value = window.deleted_images;
        }
        $("#input_feature_type").on('change', function() {
            get_feature_list();
        });

        function get_feature_type_id() {
            return $("#input_feature_type").val();
        }

        function get_feature_list() {
            var feature_type_ids = get_feature_type_id();
            $.ajax({
                url: "{{ route('admin.get_features') }}",
                method: 'GET',

                data: {
                    feature_type_ids: feature_type_ids,
                },
                success: (result) => {
                    let select = $('#input-feature');
                    select.find('option').remove(); // remove all options
                    select.selectpicker('destroy'); // temporary patch!
                    select.selectpicker(); // temporary patch!
                    $(result.data.features).each(function(i, feature) {
                        $("#input-feature").append(
                            `<option data-tokens="${feature.id}" value="${feature.id}">${feature.name} (${feature.feature_type.name})</option>`
                        );
                    })
                    $("#input-feature").selectpicker("refresh").trigger('change');
                },
                error: function(result) {
                    console.error("error", result)
                }
            }).always(function() {

            })
        }

    function redirectWithPopup(ParkImageUplaodRoute){
        $.confirm({
            title: 'Add photos now',
            content: "Do you want to upload park images?",
            buttons: {
                Yes: {
                    btnClass: 'btn btn-success',
                    action: function (e) {

                        window.location.href=ParkImageUplaodRoute;
                    }
                },
                No: function () {

                    window.location.href="{{route('admin.park.index')}}";
                },
            }
        });
    }

@if(session('upload_image'))
redirectWithPopup("{{route('admin.park.image.upload',$park->id)}}");
@elseif (session('image_edit'))
redirectWithPopup("{{route('admin.park.image.edit',$park->id)}}");
@endif





    </script>
    <script>
       var categorylist = "{{ route('admin.categories.list') }}";
       var close_btn_icon ="{{ asset('assets/img/icons/unicons/close.png') }}";

    </script>
    <script src="{{asset('assets/js/park/park-create.js')}}"></script>
@endpush

