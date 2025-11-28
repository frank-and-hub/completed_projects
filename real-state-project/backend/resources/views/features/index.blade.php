@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Features'))
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                Features
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="mb-3">
                        <h4 class="card-title"></h4>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('add_features') }}" class="btn btn-danger mr-2"><i class="fa fa-plus"> </i> Add
                            New</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="featuresTable" class="table">
                                <thead>
                                    <tr>
                                        <th>Created At</th>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Heading</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('custom-script')
        <script type="text/javascript">
            var table = $('#featuresTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('features_list') }}",
                columns: [
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function (data, type, row) {
                            return dateF2(data);
                        }
                    }, {
                        data: 'heading',
                        name: 'heading'
                    }, {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }],
                drawCallback: function (settings, json) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });
        </script>
    @endpush
@endsection
