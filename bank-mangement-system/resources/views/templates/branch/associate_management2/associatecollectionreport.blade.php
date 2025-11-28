@extends('layouts/branch.dashboard')

@section('content')
@section('css')
<style>
    .datatable{
        display:none;
    }
   
</style>


@endsection 
<?php
$stateid = getBranchState(Auth::user()->username);
$startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid));
$startDatee = $endDatee = date('d/m/Y',strtotime($startDatee));
?>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Associate Collection Report</h3>
                    
                </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-header header-elements-inline">
                    <h3 class="card-title font-weight-semibold">Search Filter</h3>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="associate_collection_filter" name="associate_collection_filter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                    <input type="text" class="form-control  create_application_date" name="start_date" id="start_date"  value="{{$startDatee}}" > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  create_application_date" name="end_date" id="end_date" value="{{$endDatee}}" >
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
                                
                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="adm_report_currentdate" id="adm_report_currentdate" class="create_application_date" value="{{$startDatee}}">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="associate_collection_export" id="associate_collection_filter" value="">
                                            <button type="button" class=" btn  btn-primary legitRipple" onClick="searchForm()" >Submit</button>
                                             <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
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
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Associates</h3>
                            </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class=" btn btn-primary legitRipple associate_collection_export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            
                        
                            </div>
                        </div>
                    
                    <div class="table-responsive">
                        <table id="associatecollectionreport" class="table table-flush">
                            <thead class="">
                              <tr>
                                <th>S/N</th>
                                <th>BR Name</th>
                                <th>BR Code</th>
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
</div>
@stop

@section('script')
@include('templates.branch.associate_management.partials.associatecollectionreport_script')
@stop