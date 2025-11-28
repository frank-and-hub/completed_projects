@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>
                </a></span><span class="text-primary fw-light"> / </span><span><a
                    href="{{ route('admin.category.index') }}"`><u class="text-primary fw-light">Categories</u>
                </a></span><span class="text-primary fw-light"> / </span>Child Categories</h5>
        <div class=" justify-content-end">
            <a href="{{ route('admin.subcategory.create', ['id' => $category->id]) }}" class="btn btn-primary">
                <div class="d-flex align-items-center"><i class='bx bx-plus-medical'></i>&nbsp; Add New Child Category</div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Child Categories</h5>

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
@endsection

@push('script')
    <script type="text/javascript">
        var db_table;
        $(document).ready(function() {
            db_table = $("#category-table").DataTable({
                serverSide: true,
                retrieve: true,
                stateSave: false,
                processing: true,
                bAutoWidth: false,
                serverMethod: "get",
                ajax: {
                    url: "{{ route('admin.subcategory.dt_list') }}",
                    data: function(d) {
                        d.category_id = "{{ $category->id }}"
                    }
                },
                columns: [{
                        name: 'name',
                        data: 'name'
                    },
                    {
                        name: 'action',
                        data: 'action',
                        orderable: false
                    },
                ],
                order: [0, 'desc'],
                drawCallback: function(settings, json) {
                    $('[rel="tooltip"]').tooltip();
                }

            });

            deleteDbTableData("#category-table");
            changeStatus("#category-table");
        });


        function item_prompt(url) {
            reset_dialog = $.dialog({
                title: '',
                content: 'url:' + url,
                type: 'yellow',
                typeAnimated: true,
                columnClass: 'col-md-6 col-sm-12 col-lg-6',
            });
        }
    </script>
@endpush
