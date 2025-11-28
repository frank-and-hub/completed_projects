@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">list</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="mb-3">
                        <h4 class="card-title"></h4>
                    </div>
                    {{-- <div class="text-right">
                        <a href="{{route('adminSubUser.property.add')}}" data-toggle="tooltip" data-original-title="Add New"
                            class="btn  btn-danger  mr-2"><i class="fa fa-plus"> </i> Add New</a>
                    </div> --}}
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="subscriptionListing" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Purchase date</th>
                                        <th>Plan Name</th>
                                        <th>Amount</th>
                                        <th>Can Add property</th>
                                        <th>Expire At</th>
                                        <th>Total Property Added</th>
                                        <th>Status</th>
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
            var table = $('#subscriptionListing').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('adminSubUser.subscribe_list') }}",
                columns: [
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function (data, type, row) {
                            return dateF2(data);
                        }
                    }, {
                        data: 'plan_name',
                        name: 'plan_name'
                    },{
                        data: 'amount',
                        name: 'amount'
                    },{
                        data: 'can_add_property',
                        name: 'can_add_property'
                    },{
                        data: 'expired_at',
                        name: 'expired_at',
                        orderable: true,
                    },{
                        data: 'total_property',
                        name: 'total_property'
                    },{
                        data: 'status',
                        name: 'status'
                    }
                ],
                order: [
                    [3, 'desc'],
                ],
                drawCallback: function (settings, json) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });
            table.draw();
        </script>
    @endpush
@endsection
