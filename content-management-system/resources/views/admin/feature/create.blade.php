@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>
                </a></span><span class="text-primary fw-light"> / </span><span><a
                    href="{{ route('admin.feature_type.index', ['id' => $feature_type->id]) }}"`><u
                        class="text-primary fw-light">Parent Features</u>
                </a></span><span class="text-primary fw-light"> / </span><span>
                {{-- <a
                    href="{{ route('admin.feature.index', ['id' => $feature_type->id]) }}"`><u
                        class="text-primary fw-light">Child Features</u>
                </a> --}}
            </span>{{ $feature ? 'Edit Child Feature' : 'Create New Child Feature' }}</h5>

    </div>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $feature ? 'Edit Child Feature' : 'Create New Child Feature' }}</h5>

                </div>
                <div class="card-body table-responsive text-nowrap">
                    <form action="{{ route('admin.feature.save') }}" method="post" enctype="multipart/form-data"
                        id="categoryForm">
                        @csrf
                        <x-admin.uploadimg id="{{ $feature->id ?? null }}"
                            imgpath="{{ $feature ? ($feature->image ? $feature->image->full_path : asset('images/default.jpg')) : asset('images/default.jpg') }}"
                            imgdeletelink="{{ !empty($feature->id) ? route('admin.feature.reset.child.feature.uploaded.img', $feature->id) : '' }}" >
                        </x-admin.uploadimg>

                        <div class="card-body">
                            <input type="hidden" name="id" value="{{ $feature->id ?? '' }}">
                            <input type="hidden" name="feature_type_id" value="{{ $feature_type->id }}">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label" for="basic-icon-default-fullname">Name</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                                class="bx bx-category"></i></span>
                                        <input type="text" class="form-control" id="basic-icon-default-fullname"
                                            placeholder="Enter Name" aria-label=""
                                            value="{{ old('name', $feature->name ?? '') }}" name="name"
                                            aria-describedby="basic-icon-default-fullname2" />
                                    </div>
                                </div>


                                <div class="col-6 mb-3">
                                    <label class="form-label" for="basic-icon-default-phone">Select Type</label>

                                    <div
                                        style="  flex:1;  border: 1px solid #d9dee3; border-radius: 0.375rem; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <select name="type" id="input-type" style="    border: 1px solid #d9dee3;"
                                            class="form-control  selectpicker" data-live-search="true">

                                            <option data-tokens="normal" value="normal"
                                                @if ($feature) @if ($feature->type == 'normal') selected @endif
                                                @endif>
                                                Normal
                                            </option>
                                            <option data-tokens="popular" value="popular"
                                                @if ($feature) @if ($feature->type == 'popular') selected @endif
                                                @endif>
                                                Popular
                                            </option>

                                        </select>
                                    </div>


                                </div>

                                <div class="col-6 mb-3 d-none" id="priority_section">
                                    <label class="form-label" for="basic-icon-default-phone">Priority</label>

                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullpriority2" class="input-group-text"><i
                                                class="bx bx-category"></i></span>
                                        <input type="text" class="form-control" id="basic-icon-default-fullpriority"
                                            placeholder="Enter Priority" aria-label=""
                                            value="{{ old('priority', $feature->priority ?? '') }}" name="priority"
                                            aria-describedby="basic-icon-default-fullpriority2">
                                    </div>
                                </div>

                                {{-- <div class="col-sm-12">
                                <label for="type" class="form-label">{{ __('Subcategories') }}</label>

                                <div class="tag_container ">
                                    <input type="text" id="tags-input" data-role="tagsinput" onkeypress=""
                                        name="subcategory" placeholder="Enter Subcategories"
                                        value="{{ $subcategory ? $subcategory : null }}" class="sss" />

                                </div>


                            </div> --}}

                                <div class="card-body table-responsive text-nowrap pt-0 d-none">
                                    <label class="form-label" for="basic-icon-default-seo_description">SEO
                                        Description</label>
                                    <textarea type="text" class="form-control mb-3" id="basic-icon-default-seo_description"
                                        rows="5" placeholder="Enter Description for SEO" aria-label=""
                                        name="seo_description"
                                        value="{{ old('seo_description', $feature->seo_description ?? '') }}" {{--
                                        readonly --}}
                                        aria-describedby="basic-icon-default-seo_description">{{ old('seo_description', $feature->seo_description ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')

    <script>
        prioritySection(); //custom.js
    </script>
@endpush
