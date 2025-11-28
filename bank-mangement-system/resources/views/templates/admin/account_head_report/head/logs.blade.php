@extends('templates.admin.master')

@section('content')
    @php
        $dropDown = $allCompany;
        $filedTitle = 'Company';
        $name = 'company_id';
    @endphp
    <style>
        .table-section,
        .hide-table {
            display: none;
        }

        .show-table {
            display: block;
        }
    </style>

    <div class="loader" style="display: none;"></div>

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="header-elements">
                            {{-- @if ($BranchToHoTransfer == '1')
                        <a class="font-weight-semibold" href="{!! route('admin.fund-transfer.branchToHo.create') !!}"><i class="icon-file-plus mr-2"></i>Transfer Fund</a>
                        @endif --}}
                        </div>
                    </div>
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf
                            <input type="hidden" name="_token" value="LlxGEeWlSBf2MDw7vPj3KmhJ2LS40HwOLxtfhLMa">
                            <div class="row">




                                {{-- <div class="col-lg-4 ">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status .</label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="status" id="status" class="form-control">
                                                <option value="">---- Select Status. ----</option>
                                                <option value="1">Create Logs</option>
                                                <option value="2">Assign Logs</option>
                                                <option value="3">Grouping Logs</option>
                                                <option value="4">Edit Logs</option>

                                            </select>
                                        </div>
                                    </div>
                                </div> --}}
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
    
                                                <input type="text" class="form-control" name="start_date" id="start_date" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
    
                                                <input type="text" class="form-control" name="end_date" id="end_date" value="">
    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-4">
                                    <div class="form-group text-right">
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="report_export" id="report_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple investment_filters"
                                                onclick="searchheadlogs()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                                onclick="resetFormHo()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row table-section hide-table">
            <div class="col-md-12">
             
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Head Log Listing</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>
                    <table id="head_logs_listing" class="table table-flush">
                        
                        <thead class="">
                            
                              
                               
                            <tr>
                                <th>S/N</th>
                                <th>Parent</th>

                                <th>Head Name</th>
                                <th>Type</th>
                                <th>Companies</th>
                                {{-- <th>Branch Code</th> --}}
                               
                                <th>Description</th>
                                
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Action</th>

                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{ url('/') }}/asset/js/sweetalert.min.js"></script>
    @include('templates.admin.account_head_report.headlogs.partials.script')

@stop
{{-- admin  --}}
