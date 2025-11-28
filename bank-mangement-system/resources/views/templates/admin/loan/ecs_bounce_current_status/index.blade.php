@extends('templates.admin.master')

@section('content')
<div class="content">
        <style>
            .hideTableData{
                display: none;
            }
        </style>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['url' => '#', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'filter', 'class' => '', 'name' => 'filter']) }}
                        <div class="row">                            
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="">
                                            {{Form::text('date','',['id'=>'date','class'=>'form-control','readonly'=>true])}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        {{Form::hidden('bounce_ecs_current_status_export','',['id'=>'bounce_ecs_current_status_export','class'=>''])}}
                                        {{Form::hidden('is_search','no',['id'=>'is_search','class'=>''])}}
                                        {{Form::hidden('created_at','',['id'=>'created_at','class'=>'created_at'])}}
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                </div>
            </div>
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">GST Collection Listing</h6>
                        <div class="col-md-8">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="bounce_ecs_current_status_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Region</th>
                                    <th>Branch</th>
                                    <th>Date</th>
                                    <th>Account Number</th>
                                    <th>Plan</th>
                                    <th>Customer Name</th>
                                    <th>Mobile No</th>
                                    <th>Code</th>
                                    <th>Collector Name</th>
                                    <th>Amount</th>
                                    <th>Mode</th>
                                    <th>Due Amount</th>
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
    @include('templates.admin.loan.ecs_bounce_current_status.partials.script')
@stop