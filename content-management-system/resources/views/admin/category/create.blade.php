@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1">
            <span>
                <a href="{{ route('admin.dashboard') }}"`>
                    <u class="text-primary fw-light">Dashboard</u>
                </a>
            </span>
            <span class="text-primary fw-light"> / </span>
            <span>
                <a href="{{ route('admin.category.index') }}"`>
                    <u class="text-primary fw-light">Categories</u>
                </a>
            </span>
            <span class="text-primary fw-light"> / </span>
            {{ $category ? 'Edit Category' : 'Create New Category' }}</h5>
    </div>
    <form action="{{ route('admin.category.save') }}" method="post" enctype="multipart/form-data" id="categoryForm">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $category ? 'Edit Category' : 'Create New Category' }}</h5>
                    </div>
                    <div class="card-body table-responsive text-nowrap">
                        @csrf
                        <x-admin.uploadimg id="{{ $category->id ?? null }}"
                            imgpath="{{ $category ? ($category->image ? $category->image->full_path : asset('images/default.jpg')) : asset('images/default.jpg') }}"
                            imgdeletelink="{{ !empty($category->id) ? route('admin.category.delete.image', $category->id) : '' }}">
                            <x-slot:other>
                                Image is recommended when displaying as parent category card!
                            </x-slot:other>
                            <x-slot:imageSizeWarning>
                                Allowed JPG or PNG. Max size of 2 MB
                            </x-slot:imageSizeWarning>

                        </x-admin.uploadimg>
                        <div class="card-body">
                            <input type="hidden" name="id" value="{{ $category->id ?? '' }}">

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label" for="basic-icon-default-fullname">Name</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                                class="bx bx-category"></i></span>
                                        <input type="text" class="form-control" id="basic-icon-default-fullname"
                                            placeholder="Enter Name" aria-label=""
                                            value="{{ old('name', $category->name ?? '') }}" name="name"
                                            aria-describedby="basic-icon-default-fullname2" />
                                    </div>
                                </div>

                                <div class="col-6 mb-3">
                                    <label class="form-label" for="basic-icon-default-fullname">Priority</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullpriority2" class="input-group-text"><i
                                                class="bx bx-category"></i></span>
                                        <input type="text" class="form-control" id="basic-icon-default-fullpriority"
                                            placeholder="Enter Priority" aria-label=""
                                            value="{{ old('priority', $category->priority ?? '') }}" name="priority"
                                            aria-describedby="basic-icon-default-fullpriority2" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label" for="basic-icon-default-phone">Select Type</label>
                                    <div
                                        style="  flex:1;  border: 1px solid #d9dee3; border-radius: 0.375rem; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <select name="type" id="input-subcategory" style="border: 1px solid #d9dee3;"
                                            class="form-control  selectpicker" data-live-search="true">

                                            <option data-tokens="no-child" value="no-child"
                                                @if ($category) @if ($category->type == 'no-child') selected @endif
                                                @endif>
                                                Standalone
                                            </option>
                                            <option data-tokens="parent" value="parent"
                                                @if ($category) @if ($category->type == 'parent') selected @endif
                                                @endif>
                                                Parent Category
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label" for="basic-icon-default-fullname"></label>
                                    <div class="input-group input-group-merge pl-4 mt-2 pt-2">
                                        <input class="form-check-input" type="checkbox" name="special_category"
                                            id="special_category" @checked($category && $category->special_category)>
                                        <label class="form-label ml-1"
                                            for="special_category">{{ __('admin.seasonal_category') }}</label>
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="ml-4 input-group-merge ">
                                        <input class="form-check-input mt-0" type="checkbox" name="is_set_as_carousel"
                                            aria-label="Checkbox for following text input" id="is_set_as_carousel"
                                            @checked($category && $category->is_set_as_carousel)>
                                        <label class="form-label ml-1" for="is_set_as_carousel"
                                            id="is_set_as_carousel_label">
                                            @if ($category)
                                                @if ($category->type != 'no-child')
                                                    Display as separate category carousel
                                                @else
                                                    Display in "More Categories" carousel @endif
                                            @else
                                                Display in "More Categories" carousel
                                            @endif
                                        </label>
                                    </div>
                                    <div class="input-group-merge ml-4">
                                        <input class="form-check-input mt-0" type="checkbox" name="is_set_as_home"
                                            aria-label="Checkbox for following text input" id="is_set_as_home"
                                            @checked($category && $category->is_set_as_home)>
                                        <label class="form-label ml-1" id="is_set_as_home_label" for="is_set_as_home">
                                            @if ($category)
                                                @if ($category->type != 'no-child')
                                                    Display as parent category card (in collage)
                                                @else
                                                    Display as separate park carousel @endif
                                            @else
                                                Display as separate park carousel
                                            @endif
                                        </label>
                                    </div>
                                    <div class="ml-4 input-group-merge ">
                                        <input class="form-check-input mt-0" type="checkbox" name="is_display_by_itself" aria-label="Checkbox for following text input" id="is_display_by_itself" @checked($category && $category->is_display_by_itself)>
                                        <label class="form-label ml-1" for="is_display_by_itself" id="is_display_by_itself_label">
                                            @if ($category)
                                                @if ($category->type != 'no-child')
                                                    Display as parent category card (by itself)
                                                @else
                                                    Display as standalone category card (by itself) @endif
                                            @else
                                                Display as standalone category card (by itself)
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 mb-3" id="season"
                                    @if (!$category || !$category->special_category) style="display:none;" @endif>
                                    <label class="form-label" for="basic-icon-default-phone">Select Season</label>
                                    <div
                                        style="  flex:1;  border: 1px solid #d9dee3; border-radius: 0.375rem; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <select name="season" id="input-season" style="border: 1px solid #d9dee3;"
                                            class="form-control  selectpicker" data-live-search="true">
                                            @foreach (config('constants.seasons_array') as $season)
                                                <option data-tokens="{{ $season }}" value="{{ $season }}"
                                                    @if ($category) @if ($category->season == $season) selected @endif
                                                    @endif>
                                                    {{ config("constants.seasons.$season") }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label" for="description">Description</label>
                                    <textarea class="form-control" rows="6" placeholder="Write description..." name="description"
                                        id="description">{{ old('description', $category->description ?? '') }}</textarea>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ 'Meta Data' }}</h5>
                    </div>

                    <div class="card-body table-responsive text-nowrap">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label" for="meta_title">Meta Title <span class="metaLimit" data-limit='75'></span></label><br>
                                    <small class="text-primary">{{ 'Allowed 75 characters' }}</small>
                                    <div class="input-group input-group-merge">
                                        <input type="text" class="form-control" id="meta_title" placeholder="Add Title" aria-label="" value="{{ old('meta_title', $category->meta->title ?? '') }}" name="meta_title" />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="meta_description">Meta Description <span class="metaLimit" data-limit='200'></span></label><br>
                                    <small class="text-primary">{{ 'Allowed 200 characters' }}</small>
                                    <textarea class="form-control" rows="6" placeholder="Add Meta Description" name="meta_description"
                                        id="meta_description">{{ old('meta_description', $category->meta->description ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mt-3">
                                    <div class="d-flex justify-content-end p-2">
                                        <button type="submit" class="btn btn-primary">{{ $category ? 'Update Category' : 'Save Category' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

            @if (!empty($category) && $category->type != 'no-child')
                {{-- Child Category --}}
                <div class="row">
                    <div class="col-xl">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center bg-lightblue">
                                <h5 class="mb-0 form-label">Child Categories</h5>
                                <a href="{{ route('admin.subcategory.create', $category->id) }}">
                                    <button class="btn btn-primary"><i class="bx bx-plus-medical"></i> Add New Child
                                        Category</button>
                                </a>
                            </div>
                            <div class="card-body table-responsive text-nowrap">
                                <table class="table table-striped table-hover w-100" id="category-table">
                                    <thead>
                                        <tr class="text-nowrap">
                                            <th>Name</th>
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
            @else
                @if( $category)
                <x-admin.datatable id="park-table" title="Parks" loaderID='dt-loader'>
                    <x-slot:headings>
                        <th>Name</th>
                        <th>Location</th>
                        {{-- <th>Action</th> --}}
                    </x-slot:headings>
                </x-admin.datatable>
                @endif
            @endif

@endsection
@push('script')
    <script>
        var category_id;
        @if (!empty($category))
            category_id = "{{ $category->id }}";
        @endif
    </script>

    <script type="text/javascript">
        var db_table;
        $(document).ready(function() {

            const selectedVal = $("#input-subcategory :selected").val();

            if (selectedVal == 'no-child') {
                // $("#description").parent().removeClass('d-none');

            } else {
                // $("#description").parent().addClass('d-none');
            }

            db_table = $("#category-table").DataTable({
                serverSide: true,
                stateSave: false,
                retrieve: true,
                processing: true,
                bAutoWidth: false,
                serverMethod: "get",
                searching: false,
                pageLength: 100,
                ajax: {
                    url: "{{ route('admin.subcategory.dt_list') }}",
                    data: function(d) {
                        d.category_id = category_id
                    }
                },
                columns: [
                    // {
                    //         name: 'image_',
                    //         data: 'image_',
                    //         width: '50%',
                    //     },
                    {
                        name: 'name',
                        data: 'name',
                        width: '100%',

                    },
                    {
                        name: 'action',
                        data: 'action',
                        orderable: false
                    },
                ],
                order: [0, 'asc'],
                drawCallback: function(settings, json) {
                    $('[rel="tooltip"]').tooltip({
                        container: '#category-table'
                    });
                }

            });

            deleteDbTableData("#category-table");
            changeStatus("#category-table");
        });
    </script>

    <script>
        $("#input-subcategory").on('change', function() {
            $("#is_set_as_carousel").prop('checked', false);
            $("#is_set_as_home").prop('checked', false);
            $("#is_display_by_itself").prop('checked', false);

            if ($("#input-subcategory").val() == 'no-child') {
                $("#is_set_as_home_label").text(" Display as separate park carousel");
                $("#is_set_as_carousel_label").html('Display in "More Categories" carousel');
                $("#is_display_by_itself_label").html("  Display as standalone category card (by itself)");
                // $("#description").parent().removeClass('d-none');

            } else {
                $("#is_set_as_home_label").text(" Display as parent category card (in collage)");
                $("#is_set_as_carousel_label").html(" Display as separate category carousel");
                $("#is_display_by_itself_label").html("  Display as parent category card (by itself)");
                // $("#description").parent().addClass('d-none');
            }
        });

        $("#is_set_as_home").click(function() {
            $("#is_set_as_carousel").is(":checked") ? $("#is_set_as_carousel").prop("checked", false) : null;
            $("#is_display_by_itself").is(":checked") ? $("#is_display_by_itself").prop("checked", false) : null;

        })
        $("#is_set_as_carousel").click(function() {
            $("#is_set_as_home").is(":checked") ? $("#is_set_as_home").prop("checked", false) : null;
            $("#is_display_by_itself").is(":checked") ? $("#is_display_by_itself").prop("checked", false) : null;
        })
        $("#is_display_by_itself").click(function() {
            $("#is_set_as_carousel").is(":checked") ? $("#is_set_as_carousel").prop("checked", false) : null;
            $("#is_set_as_home").is(":checked") ? $("#is_set_as_home").prop("checked", false) : null;
        })

        $('#special_category').change(function() {
            if ($(this).prop("checked")) {
                $('#season').show();
                return;
            }
            $('#season').hide();
        });

        $(document).ready(function () {
            $('input[name="meta_title"], textarea[name="meta_description"]').each(function () {
                const $input = $(this);
                const $col = $input.closest('.col-6');
                const $metaLimit = $col.find('.metaLimit');
                const limit = parseInt($metaLimit.data('limit'));

                const updateCount = function () {
                    const length = $input.val().length;
                    $metaLimit.text(`${length}/${limit}`);

                    // Optional: Highlight if exceeded
                    if (length > limit) {
                        $metaLimit.addClass('text-danger');
                    } else {
                        $metaLimit.removeClass('text-danger');
                    }
                };

                // Initial update
                updateCount();

                // Update on input
                $input.on('input', updateCount);
            });
        });
    </script>
    @if (!empty($category) && $category->type != 'no-child')
    @else
    @if( $category)
        <script type="text/javascript">
            var db_table;
            $(document).ready(function () {
                db_table = $("#park-table").DataTable({
                    serverSide: true,
                    stateSave: false,
                    retrieve: true,
                    processing: true,
                    bAutoWidth: false,
                    serverMethod: "get",
                    searching: false,
                    pageLength: 100,


                    ajax: {
                        url: "{{ route('admin.category.park.dt_list') }}",
                        data: function(d) {
                            d.category_id = category_id
                        }
                    },
                    columns: [
                        {
                            name: 'name',
                            data: 'name',
                            width: '40%',
                        }, {
                            name: 'city',
                            data: 'city',
                            width: '40%'
                        // }, {
                        //     name: 'action',
                        //     data: 'action',
                        //     orderable: false
                        },
                    ],
                    order: [0, 'asc'],
                    drawCallback: function (settings, json) {
                        $('[rel="tooltip"]').tooltip({
                            container: '#park-table'
                        });
                    }

                });
                db_table.draw();
            });
        </script>
    @endif
    @endif
@endpush
