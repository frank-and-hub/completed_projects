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
                            
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Company <span style="color:red;"> *</span></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="">
                                            <select name='company_id' id='company_id' class="form-control">
                                                <option value="" >---- Please Select Company----</option>
                                                @forelse($company as $k => $v)
                                                    <option value="{{$k}}" >{{$v}}</option>
                                                @empty
                                                    <option value="" >No Company Found</option>
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">State </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="">
                                            <select name='state' id='state' class="form-control" >
                                                <option value="" >---- Please Select State----</option>
                                                @forelse($state as $k => $v)
                                                    <option value="{{$k}}" >{{$v}}</option>
                                                @empty
                                                    <option value="" >No State Found</option>
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date <span style="color:red;"> *</span></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="">
                                            {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','readonly'=>true])}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date <span style="color:red;"> *</span></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="">
                                            {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','readonly'=>true])}}
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                           
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        {{Form::hidden('gst_collection_export','',['id'=>'gst_collection_export','class'=>''])}}
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
                        <table id="gst_collection_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>State</th>
                                    <th>Branch</th>
                                    <th>Date</th>
                                    <th>Customer Id</th>
                                    <th>Customer Name</th>
                                    <th>Amount</th>
                                    <th>Gst</th>
                                    <th>Head</th>
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
    @include('templates.admin.duties_taxes.gst.report.partials.script')
@stop