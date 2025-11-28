@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}" `><u
                        class="text-primary fw-light">Dashboard</u>
                </a></span><span class="text-primary fw-light"> / </span><span><a
                    href="{{ route('admin.feature_type.index') }}" `><u class="text-primary fw-light">Parent Features</u>
                </a></span><span class="text-primary fw-light"> /
            </span>{{ $feature_type ? 'Edit Parent Feature' : 'Create Parent Feature' }}</h5>
    </div>
    <div class="row" style="position: relative">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-lightblue">
                    <h5 class="mb-0 form-label">{{ $feature_type ? 'Edit Parent Feature' : 'Create New Parent Feature' }}
                    </h5>

                </div>
                <div class="card-body table-responsive text-nowrap">
                    <form action="{{ route('admin.feature_type.save') }}" method="post" enctype="multipart/form-data"
                        id="categoryForm">
                        @csrf

                        <x-admin.uploadimg id="{{ $feature_type->id ?? null }}"
                            imgpath="{{ $feature_type ? ($feature_type->image ? $feature_type->image->full_path : asset('images/default.jpg')) : asset('images/default.jpg') }}"
                            imgdeletelink="{{ !empty($feature_type->id) ? route('admin.feature.reset.uploaded.img', $feature_type->id) : '' }}">
                        </x-admin.uploadimg>

                        <div class="card-body">
                            <input type="hidden" name="id" value="{{ $feature_type->id ?? '' }}">

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label" for="basic-icon-default-fullname">Name</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                                class='bx bx-category-alt'></i></span>
                                        <input type="text" class="form-control" id="basic-icon-default-fullname"
                                            placeholder="Enter Name" aria-label=""
                                            value="{{ old('name', $feature_type->name ?? '') }}" name="name"
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
                                                @selected(!empty($feature_type->type) && $feature_type->type == 'normal')>
                                                Normal
                                            </option>
                                            <option data-tokens="popular" value="popular"
                                                @selected(!empty($feature_type->type) && $feature_type->type == 'popular')>
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
                                            value="{{ old('priority', $feature_type->priority ?? '') }}" name="priority"
                                            aria-describedby="basic-icon-default-fullpriority2">
                                    </div>
                                </div>

                                <div class="card-body table-responsive text-nowrap pt-0 d-none">
                                    <label class="form-label" for="basic-icon-default-seo_description">SEO
                                        Description</label>
                                    <textarea type="text" class="form-control mb-3" id="basic-icon-default-seo_description"
                                        rows="5" placeholder="Enter Description for SEO" aria-label=""
                                        name="seo_description"
                                        value="{{ old('seo_description', $feature_type->seo_description ?? '') }}" {{--
                                        readonly --}}
                                        aria-describedby="basic-icon-default-seo_description">{{ old('seo_description', $feature_type->seo_description ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit"
                                    class="btn btn-primary">{{ $feature_type ? 'Update Parent Feature' : 'Save Parent Feature' }}</button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
        @if (!empty($feature_type))
            <div class="col-xl">
                <div class="card mb-4">
                    <div class=" card-header d-flex justify-content-between mt-2 align-items-center bg-lightblue">
                        <h5 class="mb-0 form-label">Child Features</h5>
                        <a href="{{ route('admin.feature.create', $feature_type->id) }}">
                            <button class="btn btn-primary">
                                <i class="bx bx-plus-medical"></i>
                                Add New Child Feature
                            </button>
                        </a>

                    </div>

                    <div class="card-body table-responsive text-nowrap">
                        <table class="table table-striped table-hover w-100" id="feature-table">
                            <thead>
                                <tr class="text-nowrap">
                                    {{-- <th>Image</th> --}}
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
@push('script')
    <script src={{ asset('js/image.js') }}></script>

    <script>
        $(function () {
            $('#tags-input').on('change', function (event) {
                var $element = $(event.target),
                    $container = $element.closest('.tag');
                if (!$element.data('tagsinput'))
                    return;
                var val = $element.val();
                if (val === null)
                    val = "null";
                $('code', $('pre.val', $container)).html(($.isArray(val) ? JSON.stringify(val) : "\"" + val
                    .replace('"', '\\"') + "\""));
                $('code', $('pre.items', $container)).html(JSON.stringify($element.tagsinput('items')));
            }).trigger('change');
        });




        function myFunction() {
            alert("You pressed a key inside the input field");
        }
        $(document).ready(function () {
            $('#categoryForm').keydown(function (event) {
                if (event.keyCode == 13) { }
            });

        });
    </script>

    <script>
        var child_features_url = "{{ route('admin.child_feature.dt') }}";
        @if (!empty($feature_type))
            var feature_type_id = "{{ $feature_type->id }}";
        @endif
    </script>

    <script src="{{ asset('assets/js/Features/FeatureForm.js') }}"></script>
@endpush
