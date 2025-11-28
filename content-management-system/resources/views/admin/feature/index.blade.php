@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    <div class="d-flex align-items-center py-3 mb-4">
        <h5 class="fw-bold mb-0" style="flex: 1"><span><a href="{{ route('admin.dashboard') }}"`><u
                        class="text-primary fw-light">Dashboard</u>
                </a></span><span class="text-primary fw-light"> / </span><span><a
                    href="{{ route('admin.feature_type.index') }}"`><u class="text-primary fw-light">Feature Types</u>
                </a></span><span class="text-primary fw-light"> / </span>Features</h5>
        <div class=" justify-content-end">
            <a href="{{ route('admin.feature.create', ['id' => $feature_type->id]) }}" class="btn btn-primary">
                <div class="d-flex align-items-center"><i class='bx bx-plus-medical'></i>&nbsp; Add New Feature</div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Features</h5>

                </div>
                <div class="card-body table-responsive text-nowrap">
                    <table class="table table-striped table-hover w-100" id="feature-table">
                        <thead>
                            <tr class="text-nowrap">
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
    </div>
@endsection

@push('script')
    <script type="text/javascript">
        $(document).ready(function() {
            db_table = $("#feature-table").DataTable({
                serverSide: true,
                stateSave: false,

                ajax: {
                    url: "{{ route('admin.feature.dt_list') }}",
                    data: function(d) {
                        d.feature_type_id = "{{ $feature_type->id }}"
                    }
                },
                columns: [{
                        name: 'name',
                        data: 'name'
                    },{
                        name: 'type',
                        data: 'type'
                    },
                    {
                        name: 'action',
                        data: 'action',
                        orderable: false
                    },
                ],
                order: [0, 'aesc'],
                drawCallback: function(settings, json) {
                    $('[rel="tooltip"]').tooltip();
                }

            });

        });




    </script>
@endpush
