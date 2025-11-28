@extends('templates.admin.master')

@section('content')
<style type="text/css">
    #expense {
        margin: 4px, 4px;
        padding: 4px;

        height: 41rem;
        overflow-x: hidden;
        overflow-y: auto;
        text-align: justify;
    }
</style>
<?php
$data = '';
$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), 33));
$startDatee = $endDatee = date('d/m/Y', strtotime($startDatee));
?>
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
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  start_date create_application_date" name="start_date" id="start_date" value="{{$startDatee}}">


                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  create_application_date end_date" name="end_date" id="end_date" value="{{$endDatee}}">


                                    </div>
                                </div>
                            </div>
                            @include('templates.GlobalTempletes.new_role_type',[
									'dropDown'=>$company,
									'filedTitle'=>'Company',
									'name'=>'company_id',
									'value'=>'',
									'multiselect'=>'false',
									'design_type'=>4,
									'branchShow'=>true,
									'branchName'=>'branch_id',
									'apply_col_md'=>false,
									'multiselect'=>false,
									'placeHolder1'=>'Please Select Company',
									'placeHolder2'=>'Please Select Branch'
								])
                            <!-- <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Branch </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="branch_id" name="branch_id">
                                            <option value="">---Select Branch ---</option>
                                            @foreach( $branch as $val )
                                            <option value="{{ $val->id }}">{{ $val->name }} ({{$val->branch_code}})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div> -->



                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="associate_report_currentdate" id="associate_report_currentdate" class="create_application_date" value="{{$startDatee}}">
                                        <input type="hidden" name="is_search" id="is_search" value="yes">
                                        <input type="hidden" name="export" id="export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="container det">

        </div>

    </div>

</div>

@include('templates.admin.report.partials.branch_business_script')
@stop