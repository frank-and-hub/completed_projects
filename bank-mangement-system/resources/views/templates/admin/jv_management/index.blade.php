@extends('templates.admin.master')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        {{Form::open(['url'=>'#','method'=>'POST','enctype'=>'multipart/form-data','id'=>'filter','name'=>'filter'])}}
                            <div class="row">
                                @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right">
                                            {{Form::hidden('is_search','no',['id'=>'is_search'])}}
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
                <input type="hidden" name="export" id="export" value="0" form="#filter">
            <div class="col-md-12" id="jv_table_container" style="display: none;">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Journal Voucher Listing</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0"
                                style="float: right;">Export xslx</button>
                            {{-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button> --}}
                        </div>
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at">
                        <input type="hidden" class="form-control create_application_date " name="create_application_date"
                            id="create_application_date">
                    </div>
                    <div class="">
                        <table id="designation_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Journal#</th>
                                    <th>Company Name</th>
                                    <th>Branch</th>
                                    <th>Reference Number</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Created</th>
                                    <th>Action</th>
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
    <script src="{{ url('/') }}/asset/js/sweetalert.min.js"></script>
    @include('templates.admin.jv_management.partials.listing_script')
@endsection