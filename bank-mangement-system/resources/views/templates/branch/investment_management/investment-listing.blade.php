@extends('layouts/branch.dashboard')

@section('content')
@section('css')
<style>
    .datatable {
        display: none;
    }
</style>
@endsection
@php
$stateid = getBranchState(Auth::user()->username);
@endphp
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body">
                        <div class="">
                            <h3 class="">Investment Listing</h3>
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
                                                <input type="text" class="form-control  " name="start_date" id="start_date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="end_date" id="end_date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @include('templates.GlobalTempletes.role_type',[
                                'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                'name'=>'company_id',
                                'apply_col_md'=>false,
                                'filedTitle' => 'Company'
                                ])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Investment Plans </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="plan_id" name="plan_id">
                                                <option value="">Select Plan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Scheme Account Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="scheme_account_number" id="scheme_account_number" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="name" id="name" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member ID </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="member_id" id="member_id" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Customer ID </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="customer_id" id="customer_id" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <!--
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Amount Status: </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="amount_status" name="amount_status">
                                            <option value="">Select Status</option>
                                            <option value="0">Clear</option>
                                            <option value="0">Due</option>
                                        </select>
                                    </div>
                                </div>
                            </div> -->
                                <div class="col-md-12">
                                    <div class="form-group text-right">
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="branch_report_currentdate" value="{{ date('d/m/Y',strtotime(checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid))) }}" class="branch_report_currentdate">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="investments_export" id="investments_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple investment_filters" onClick="searchForm()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
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
            <div class="col-md-12 table-section datatable">

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Investments</h3>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="investment-listing" class="table table-flush">
                            <thead class="">
                                <tr>
                                    <th>S/N</th>
                                    <th>A/C Opening Date </th>
                                    <th>Form No</th>
                                    <th>Plan</th>
                                    <th>Company</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <!-- <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> -->
                                    <th>Member</th>
                                    <th>Customer Id</th>
                                    <th>Member Id</th>
                                    <th>Associate Code</th>
                                    <th>Account Number</th>
                                    <th>Tenure</th>
                                    <!-- <th>Balance</th>-->
                                    <th>Deposite Amount</th>
                                    <th>Address</th>
                                    <th>State</th>
                                    <th>District</th>
                                    <th>City</th>
                                    <th>Village Name</th>
                                    <th>Pin Code</th>
                                    <th>First ID Proof</th>
                                    <th>Second ID Proof</th>
                                    <th>Collector Code</th>
                                    <th>Collector Name</th>
                                    <th>Action</th>
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
    @include('templates.branch.investment_management.partials.script')
    @stop