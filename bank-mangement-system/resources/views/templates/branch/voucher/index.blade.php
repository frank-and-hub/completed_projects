@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body page-title">
                        <h3 class="">Receive Voucher Lists</h3>

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
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf
                            <div class="row">
                                @include('templates.GlobalTempletes.both_company_filter',['branchShow'=> 'no'])

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="start_date"
                                                    id="start_date" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Payment type </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="payment_type" name="payment_type">
                                                <option value="">Select Payment Type</option>
                                                <option value="0">Cash</option>
                                                <option value="1">Cheque</option>
                                                <option value="2">Online</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Account Head </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="account_head" name="account_head">
                                                <option value="">Select Head</option>
                                                <!-- <option value="19">Director Capital</option>
                                                <option value="15">Shareholder capital</option>
                                                <option value="27">Bank Account</option>
                                                <option value="96">Eli Loan</option> -->
                                                <option value="32">Penal Interest</option>
                                                <option value="122">Investment Plan Stationery Charge</option>
                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12"> </label>
                                        <div class="col-lg-12 text-right">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="export" id="export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple"
                                                onClick="searchForm()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                                onClick="resetForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="table_voucher_listing" style="display: none;">
            <div class="col-lg-12">

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Receive Voucher List</h3>
                            </div>
                            <div class="col-md-4 text-right">

                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0"
                                    style="float: right;">Export xslx</button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="voucher_listing" class="table table-flush">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>BR Name</th>
                                    <th>Date</th>
                                    <th>Receive Mode</th>
                                    <th>Receive Amount</th>
                                    <th>Account Head</th>
                                    <th>Emp Code / Member Id</th>
                                    <th>Emp Name / Member Name</th>
                                    <th>Bank Name</th>
                                    <th>Bank A/c </th>
                                    <th>Eli Loan</th>
                                    <th>Cheque No.</th>
                                    <th>UTR/Transaction No.</th>
                                    <th>Transaction Date</th>
                                    <th>Party Bank Name</th>
                                    <th>Party Bank A/c</th>
                                    <th>Receive Bank</th>
                                    <th>Receive Bank A/c</th>
                                    <th>Transaction Slip</th>
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
    @include('templates.branch.voucher.partials.script_list')
    @stop