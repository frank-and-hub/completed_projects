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
                        {{Form::open(['url'=>'#','method'=>'POST','enctype'=>'multipart/form-data','id'=>'branch_log_filter','name'=>'branch_log_filter'])}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Branch</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="">
                                                <select class="form-control " id="branchId" name="branchId" >
                                                    <option value=""  >----Select Branch----</option> 
                                                    <option value="0" >All Branches</option>
                                                    @foreach($branch as $key => $val)
                                                    <option value="{{$key}}" data-id="{{$key}}">{{ ($val) }}</option> 
                                                    @endforeach
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch Code</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="">
                                                <input type="text" name="branchCode" id="branchCode" class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <button type="submit" class=" btn bg-dark legitRipple" onClick="searchReportLogForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_log" onClick="resetReportLogForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                    <div id="update_log_data"></div>
            </div>
        </div>
    </div>
@include('templates.admin.branch.partials.branch-script')
@stop