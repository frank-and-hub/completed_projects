@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Account Heads</h6>
                            <div class="header-elements">
                                <a class="font-weight-semibold" href="{{ route('admin.addsubaccounthead') }}"><i class="icon-file-plus mr-2"></i>Create Sub Account Head</a>
                            </div>
                    </div>
                    <table class="table datatable-show-all" id="sub-account-head">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Created At</th>
                                <th width="10%">Account Type</th>
                                <th width="10%">Account Head</th>
                                <th width="5%">Head FA code</th>
                                <th width="10%">Sub Head FA Code</th> 
                                <th width="10%">Title</th>
                                <th width="10%">Status</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.category.partials.script')
@endsection
