@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1">
            <span>
                <a href="{{ route('admin.dashboard') }}" `>
                    <u class="text-primary fw-light">Dashboard</u>
                </a>
            </span>
            <span class="text-primary fw-light"> / </span>
            <span>
                <a href="{{ route('admin.category.index', ['id' => $category->id]) }}" `><u
                        class="text-primary fw-light">Categories</u>
                </a>
            </span>
            <span class="text-primary fw-light"> / </span>
            {{ $subcategory ? 'Edit Child Category' : 'Create New Child Category' }}
        </h5>
    </div>
    <form action="{{ route('admin.subcategory.save') }}" method="post" enctype="multipart/form-data" id="categoryForm">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $subcategory ? 'Edit Child Category' : 'Create New Child Category' }}</h5>
                    </div>
                    <div class="card-body table-responsive text-nowrap">
                        @csrf
                        <x-admin.uploadimg id="{{ $subcategory->id ?? null }}"
                            imgpath="{{ $subcategory ? ($subcategory->image ? $subcategory->image->full_path : asset('images/default.jpg')) : asset('images/default.jpg') }}"
                            imgdeletelink="{{ !empty($subcategory->id) ? route('admin.category.subcategory.delete.image', $subcategory->id) : '' }}">
                            <x-slot:other>
                                <small class="text-primary" style="font-weight: bold">Image is required when show as parent
                                    category.</small>
                            </x-slot:other>
                        </x-admin.uploadimg>
                        <div class="card-body">
                            <input type="hidden" name="id" value="{{ $subcategory->id ?? '' }}">
                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label" for="basic-icon-default-fullname">Name</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-category"></i></span>
                                        <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Enter Name" aria-label="" value="{{ old('name', $subcategory->name ?? '') }}" name="name" aria-describedby="basic-icon-default-fullname2" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label" for="description">Description</label>
                                    <textarea class="form-control" rows="6" placeholder="Write description..." name="description" id="description">{{ old('description', $subcategory->description ?? '') }}</textarea>
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
                                        <input type="text" class="form-control" id="meta_title" placeholder="Add Title" aria-label="" value="{{ old('meta_title', $subcategory->meta->title ?? '') }}" name="meta_title" />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="meta_description">Meta Description <span class="metaLimit" data-limit='200'></span></label><br>
                                    <small class="text-primary">{{ 'Allowed 200 characters' }}</small>
                                    <textarea class="form-control" rows="6" placeholder="Add Meta Description" name="meta_description"
                                        id="meta_description">{{ old('meta_description', $subcategory->meta->description ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mt-3">
                                    <div class="d-flex justify-content-end p-2">
                                        <button type="submit" class="btn btn-primary">{{ $subcategory ? 'Update' : 'Save' }} Child Category</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @if( $subcategory)
    <x-admin.datatable id="park-table" title="Parks" loaderID='dt-loader'>
        <x-slot:headings>
            <th>Name</th>
            <th>Location</th>
            {{-- <th>Action</th> --}}
        </x-slot:headings>
    </x-admin.datatable>
    @endif
@endsection
<script src={{ asset('js/image.js') }}></script>
@push('script')
    @if( $subcategory)
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
                        d.category_id = `{{$category->id}}`,
                        d.subcategory_id = `{{$subcategory->id}}`
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
    @endif
@endpush
