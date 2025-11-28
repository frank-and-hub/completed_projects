@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            Client Property
        </h3>
        {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">User list</li>
            </ol>
        </nav> --}}
    </div>
    <div class="card">
        <div class="card-body">
            <h4 class="card-title"></h4>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="ClientpropertyTable" class="table">
                            <thead>
                                <tr>
                                    {{-- <th>Sr.No.</th> --}}
                                    <th>Client Office Name</th>
                                    <th>Logo</th>
                                    <th>Total Property</th>
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
        var table = $('#ClientpropertyTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('property_data') }}",
            columns: [
                {
                    data: 'name',
                    name: 'name',
                },{
                    data: 'logo',
                    name: 'logo',
                },{
                    data: 'total_count',
                    name: 'total_count',
                }
            ],
            drawCallback: function(settings, json) {
                $('[data-toggle=tooltip]').tooltip();
            }
        });
    </script>
@endpush
@endsection
