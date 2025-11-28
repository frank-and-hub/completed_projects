@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Users Listing</h6>
                            <div class="header-elements">
                                {{--<a class="font-weight-semibold" href="{{ route('branch.create') }}"><i class="icon-file-plus mr-2"></i>Create Branch</a>--}}
                            </div>
                    </div>
                    <table class="table datatable-show-all" id="users">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">User Name</th>
                                <th width="10%">Employee Name</th>
                                <th width="5%">Employee Code</th>
                                <th width="5%">User Id</th>
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
@include('templates.admin.user.partials.script')
<style type="text/css">
.datatable-scroll-wrap{
    min-height: 200px !important;
}
</style>
@endsection
