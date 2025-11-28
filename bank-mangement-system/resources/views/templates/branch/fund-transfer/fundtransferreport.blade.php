@extends('layouts/branch.dashboard')

@section('content')
@php
$dropDown = $company;
$filedTitle  = 'Company';
$name = 'company_id';
@endphp
<style>
    .table-section, .hide-table{
        display: none;
    }
    .show-table{
        display: block;
    }
</style>
<div class="loader" style="display: none;"></div>

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Fund Transfer Report</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="start_date" id="start_date"  >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="end_date" id="end_date"  >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Cash In Hand Categories  </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="category" name="category">
                                                <option value="">---- Please Select ----</option>
                                                <option value="0">Loan</option>
                                                <option value="1">Micro</option>
                                            </select>
                                        </div>
                                    </div>
                                </div> -->
                                @include('templates.GlobalTempletes.role_type',[
                                    'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                    'name'=>'company_id',
                                    'apply_col_md'=>false,
                                    'filedTitle' => 'Company'
                                    ])
    
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch  Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="branch_name" id="branch_name" class="form-control" value="{{ $branchName }}" readonly="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="branch_code" id="branch_code" value="{{ $branchCode }}" class="form-control"  readonly="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select Status</option>
                                                <option value="1">Approved</option>
                                                <option value="0">Pending</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group text-right">
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="report_export" id="report_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple investment_filters" onClick="searchForm()" >Submit</button>
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

        <div class="row   table-section hide-table">
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Report List</h3>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                                {{-- <button type="button" class="btn btn-primary legitRipple export" data-extension="1">Export PDF</button> --}}
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="fund_transfer_listing" class="table table-flush">
                            <thead class="">
                                <tr>
                                    <th>S/N</th>
                                    <th>Request Type</th>
                                    <th>Company Name</th>
                                    <th>Branch Name</th>
                                    <th>Branch Code</th>
                                    <th>Branch Cash In Hand Amount </th>
                                    <th>Transfer Amount</th>
                                    <th>Transfer Date</th>
                                    <th>Transfer Mode</th>
                                    <th>Receive Amount</th>
									<th>Receive Bank Name</th>
									<th>Receive Bank A\C</th>
                                    <th>Request Date</th>
                                    <th>Bank Slip</th>
                                    <!-- <th>Approve/Reject Date</th> -->
                                    <th>Status</th>
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
    @include('templates.branch.fund-transfer.partials.script')
@stop
{{-- branch --}}