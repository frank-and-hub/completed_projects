@extends('templates.admin.master')

@section('content')
@section('css')
<style>
    .datatable{
         display:none;
    }
    label.error {
    color: red;
    font-size: 1rem;
    display: block;
    margin-top: 5px;
    }
</style>
@endsection
<?php
$startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),33));
$startDatee = $endDatee = date('d/m/Y',strtotime($startDatee));
?>
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Associate Collection Report Filter      </h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="associate_collection_filter" name="associate_collection_filter">
                        @csrf 

                        <div class="row">
                        @include('templates.GlobalTempletes.both_company_filter',['branchName'=> 'branch_id'])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Start Date <sup class="text-danger"> *</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="start_date" id="start_date"  value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> End Date <sup class="text-danger"> *</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="end_date" id="end_date" value="" >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate Code </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control"  >
                                    </div>
                                </div>
                            </div>
                        

                            <div class="form-group col-md-12">

                                <div class="col-lg-12 text-right" >
                                    <input type="hidden" name="adm_report_currentdate" id="adm_report_currentdate" class="create_application_date" value="{{$startDatee}}">
                                    <input type="hidden" name="is_search" id="is_search" value="yes">
                                    <input type="hidden" name="associate_collection_export" id="associate_collection_export" value="">
                                    <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-lg-12 table-section datatable">
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Associate Collection Report List</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple associate_collection_export ml-2" data-extension="0" style="float: right;">Export xslx</button>
             
                    </div>
                </div>

               <div class="table-responsive">
                    <table class="table table-flush" style="width: 100%"  id="associate-collection-report-listing" >
                       <thead class="">
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>BR Name</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Collection Amount</th>
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
    @include('templates.admin.associate.partials.associate_collection_script')
@stop
