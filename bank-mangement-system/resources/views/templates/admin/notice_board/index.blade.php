@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold"></h6>
                        <div class="">
                            <a href="{{ route('admin.noticeboard.create') }}" class="btn bg-dark legitRipple export ml-2" data-extension="0">Add</a>
                        </div>
                    </div>
                    <div class="">
                        <table id="notice-list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th width="5%">S/N</th>
                                    <th width="20%">Title</th>
                                    <th width="25%">Document Type</th>
                                    <th width="25%">Files</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Created</th>
                                    <th width="5%">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    <style>
        .delete-notice{cursor: pointer;}
    </style>
    <script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
    @include('templates.admin.notice_board.listing_script')
@endsection