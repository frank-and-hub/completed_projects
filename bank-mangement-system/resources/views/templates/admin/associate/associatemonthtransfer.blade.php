@extends('templates.admin.master')
<?php
$last= date('Y')-1;
$now = date('Y');

?>

@section('content')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Commision Month End</h6>
                </div>
                <div class="card-body">
                    <form action="{!! route('admin.commision.month_save') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <label class="col-lg-3">Year</label><label class="col-lg-1">:</label>
                                    <div class="col-lg-5 error-msg">
                                        <select class="form-control" id="year_id" name="year_id">
                                            <option value="">Select Year</option>
                                            
                                            <option value="{{ $year }}">{{ $year }}</option>
                                           
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="row">
                                    <label class="col-lg-3">Month</label><label class="col-lg-1">:</label>
                                    <div class="col-lg-5 error-msg">
                                        <select class="form-control" id="month_id" name="month_id">
                                            <option value="">Select month</option>
                                            <option value="{{$month}}">{{$newmonth}}</option>
                                          
                                        </select>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="col-md-12 text-center" style="padding-top: 22px;">
                                <button type="Submit" class="btn bg-dark legitRipple">Submit</button>
                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetFormed()">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@include('templates.admin.associate.partials.commision_month_script_list') @stop
