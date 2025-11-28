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
                    @include('templates.admin.gst.filter')
                </div>
            </div>
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">GST Customer Transactions Listing</h6>
                        <div class="col-md-8">
                            <button type="button" class="btn bg-dark legitRipple export_gst_payable ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="gst_payable_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Created Date</th>
                                    <th>Company</th>
                                    <th>Branch</th>
                                    <th>Head Name</th>
                                    <th>Customer Name</th>
                                    <th>Customer Id</th>
                                    <th>DR</th>
                                    <th>CR</th>
                                    <th>Balance</th> 
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
    @include('templates.admin.duties_taxes.gst.partials.script')
@stop