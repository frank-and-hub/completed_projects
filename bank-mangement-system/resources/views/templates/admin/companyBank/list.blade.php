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
                    {{Form::open(['url'=>'#','method'=>'POST','id'=>'filter','enctype'=>'multipart/form-data','name'=>'filter'])}}
                        <div class="row">
                        {{--@include('templates.GlobalTempletes.role_type',['dropDown'=>$AllCompany,'filedTitle'=>'Compay','name'=>'company_id','apply_col_md'=>true])--}}
                        @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true,'branchShow'=>true])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','autocomplete'=>'off','readonly'=>'true'])}}
                                            {{Form::hidden('company_register_date','',['id'=>'company_register_date','class'=>'form-control','autocomplete'=>'off','readonly'=>'true'])}}
                                            {{Form::hidden('create_application_date','',['id'=>'create_application_date','class'=>'form-control create_application_date','autocomplete'=>'off','readonly'=>'true'])}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','autocomplete'=>'off','readonly'=>'true'])}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">FD Number </label>
                                    <div class="col-lg-12 error-msg">
                                        {{Form::text('fd_no','',['id'=>'fd_no','class'=>'form-control'])}}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select name="fd_status" id="fd_status" class="form-control">
                                            <option value="">-- SELECT STATUS --</option>
                                            <option value="0">Active</option>
                                            <option value="1">Closed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        {{Form::hidden('is_search','no',['id'=>'is_search','class'=>''])}}    
                                        {{Form::hidden('company_bond','',['id'=>'company_bond','class'=>''])}}
                                        <button type="button" class=" btn bg-dark legitRipple"  onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form"  onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {{Form::close()}}
                </div>
            </div>
        </div>
        <div class="col-md-12" id="bound_listing">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Company Bond List</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                </div>
                <div class="">
                    <table id="company_bound_list" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Bound Date</th>
                                <th>Bank Name</th>
                                <th>Fd No.</th>
                                <th>Amount</th>
                                <th>Remark</th>
                                <th>Maturity Date</th>
                                <th>File Upload</th>
                                <th>Receive Bank</th>
                                <th>Receive Bank Account</th>
                                <th>Created Date</th>
                                <th>Status</th>
                                @if(check_my_permission( Auth::user()->id,"257") == "1" ||check_my_permission(
                                Auth::user()->id,"261") == "1" || check_my_permission( Auth::user()->id,"259") != "1" ||
                                check_my_permission( Auth::user()->id,"262") == "1" || check_my_permission(
                                Auth::user()->id,"263") == "1")
                                <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.companyBank.partials.list_script')
@stop