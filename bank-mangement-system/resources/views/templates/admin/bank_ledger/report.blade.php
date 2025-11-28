@extends('templates.admin.master')
@section('content')
    @php
        $dropDown = $company;
        $filedTitle = 'Company';
        $name = 'company_id';
    @endphp
    <style type="text/css">
        .dataTables_paginate {
            display: none;
        }
        .dataTables_info {
            display: none;
        }
    </style>
    <div class="content">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="name" id="name" />
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="start_date" id="start_date"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="end_date" id="end_date"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('templates.GlobalTempletes.role_type', [
                                'dropDown' => $dropDown,
                                'filedTitle' => $filedTitle,
                                'name' => $name,
                                'value' => '',
                                'multiselect' => 'false',
                                'apply_col_md' => true,
                                'classes' => 'findBranh',
                            ])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Bank</label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="bank_name" name="bank_name">
                                            <option value="">Select Bank</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Bank Account</label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="bank_account" name="bank_account">
                                            <option value="">Select Bank Account</option>
                                        </select>
                                        <span id="msg" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="fund_transfer_export" id="fund_transfer_export"
                                            value="">
                                        <button type="button" class=" btn bg-dark legitRipple"
                                            onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple reset" id="reset_form"
                                            onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row" id="table" style="display:none;">
            <div class="col-md-12">
                <div class="card ">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Bank Ledger Report</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0"
                                style="float: right;">Export Excel</button>
                            {{-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button> --}}
                        </div>
                    </div>
                    <div class="">
                        <table id="bank_ledger" class="table datatable-show-all" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>Date</th>
                                    <th>Branch Code</th>
                                    <th>Branch Name</th>
                                    <th>Member Name</th>
                                    <!-- <th>Member Account Number/Member Id</th> -->
                                    <th>Account Number</th>
                                    <th>Particulars</th>
                                    <!-- <th>A/C Head No.</th> -->
                                    <!--<th>A/C Head Name</th> -->
                                    <th>Cheque No./UTR No.</th>
                                    <th>CR Amount</th>
                                    <th>DR Amount</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    @include('templates.admin.bank_ledger.partials.script')
@stop