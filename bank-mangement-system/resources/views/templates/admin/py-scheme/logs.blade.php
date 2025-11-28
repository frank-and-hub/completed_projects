@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Type<sup class="error"> *</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <select name="filter_type" id="filter_type" class="form-control">
                                            <option value="">--- Please Select Type --- </option>
                                            <option value="1">Loan</option>
                                            <option value="2">Investment</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Plan Name<sup class="error"> *</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <select name="plan_name" id="plan_name" class="form-control">
                                            <option value="">--- Please Select Plan --- </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <button type="button" class="btn bg-dark legitRipple" id="form" onclick="searchForm()">Submit </button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div id="update_log_data"></div>
        </div>
    </div>
</div>
@include('templates.admin.py-scheme.partials.scriptLog')
@stop