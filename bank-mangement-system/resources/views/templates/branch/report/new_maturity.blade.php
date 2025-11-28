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
<?php

$getBranchId = getUserBranchId(Auth::user()->id);
$branch_id = $getBranchId->id;
?>
<div class="container-fluid mt--6">

    <div class="content-wrapper">
        <div class="row">

            <div class="col-lg-12">

                <div class="card bg-white">

                    <div class="card-body page-title">

                        <h3 class="">Maturity Listing</h3>



                    </div>

                </div>

            </div>

        </div>

        <div class="row">

            <div class="col-md-12">

                <div class="card bg-white">

                    <div class="card-header header-elements-inline">

                        <h3 class="card-title font-weight-semibold">Search Filter</h3>

                    </div>

                    <div class="card-body">

                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">

                            @csrf

                            <div class="row">

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">From Date </label>

                                        <div class="col-lg-12 error-msg">

                                            <input type="hidden" class="form-control  " name="from_date" id="from_date">

                                            <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off">



                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">To Date </label>

                                        <div class="col-lg-12 error-msg">



                                            <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off">



                                        </div>

                                    </div>

                                </div>


                                @include('templates.GlobalTempletes.role_type',[
                                'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                'name'=>'company_id',
                                'apply_col_md'=>false,
                                'filedTitle' => 'Company'
                                ])


                                <input type="hidden" class="form-control  " name="branch_id" id="branch_id" value="{{$branch_id}}">


                                <!-- <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Branch </label>

                                        <div class="col-lg-12 error-msg">

                                            <select class="form-control" id="branch_id" name="branch_id">

                                                <option value="">Select Branch</option>

                                                @foreach( $branch as $val )

                                                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> 

                                                @endforeach

                                            </select>

                                        </div>

                                    </div>

                                </div> -->

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Scheme Account Number </label>

                                        <div class="col-lg-12 error-msg">

                                            <input type="text" class="form-control  " name="scheme_account_number" id="scheme_account_number">

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Member Name </label>

                                        <div class="col-lg-12 error-msg">

                                            <input type="text" class="form-control  " name="member_name" id="member_name">

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

                                        <label class="col-form-label col-lg-12">Associate code </label>

                                        <div class="col-lg-12 error-msg">

                                            <input type="text" name="associate_code" id="associate_code" class="form-control">

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Plans </label>

                                        <div class="col-lg-12 error-msg">

                                            <select class="form-control" id="plan_id" name="plan_id">

                                                <option value="">Please Choose Company</option>

                                                <!-- @foreach( $plans as $plan )

                                                    <option value="{{ $plan->id }}"  >{{ $plan->name }}</option>

                                                @endforeach -->

                                            </select>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Status </label>

                                        <div class="col-lg-12 error-msg">

                                            <select class="form-control" id="status" name="status">

                                                <option value="">Select status</option>

                                                <option value="0">Upcoming</option>

                                                <option value="1">Redemption</option>

                                                <option value="2">Over Due</option>

                                            </select>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-12">

                                    <div class="form-group row">

                                        <div class="col-lg-12 text-right">
                                            <input type="hidden" name="branch_report_currentdate" value="{{ date('d/m/Y',strtotime(checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid))) }}" class="branch_report_currentdate">

                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="export" id="export">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()">Submit</button>
                                            <button type="button" class="btn btn-secondary legitRipple" id="reset_form" onClick="resetForm()">Reset </button>

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

                                <h3 class="mb-0 text-dark">Maturity Details</h3>

                            </div>

                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-primary legitRipple export-maturity ml-2" data-extension="0" style="float: right;">Export Excel</button>

                                <!-- <button type="button" class="btn btn-primary legitRipple export-maturity" data-extension="1">Export PDF</button> -->

                            </div>

                        </div>

                    </div>



                    <div class="table-responsive">

                        <table id="maturity_list" class="table table-flush">

                            <thead>

                                <tr>

                                    <th>S/N</th>
                                    <th>company Name</th>

                                    <th>Branch Name</th>
                                    <th>Account No.</th>

                                  
                                    <th>Customer ID</th>
                                    <th>Member ID</th>
                                    <th>Member Name</th>


                                    <th>Plan </th>


                                    <th>Tenure</th>

                                    <th>Deposit Amount</th>

                                    <th>Deno</th>
                                    <th>Maturity Type</th>
                                    <th>Maturity Amount</th>
                                    <th>Maturity Payable Amount</th>

                                    <th>Maturity Date</th>

                                    <th>Associate code</th>

                                    <th>Associate Name</th>

                                    <th>Opening Date</th>

                                    <th>Due Amount</th>

                                    <th>Interest</th>

                                    <th>TDS Amount</th>

                                    <th>Final Payable Amount</th>

                                    <th>Payment Mode</th>

                                    <th>Payment Date</th>



                                    <th>Cheque No./RTGS No.</th>

                                    <th>RTGS Charge</th>

                                    <th>SSB Account No.</th>

                                    <th>Bank Name</th>

                                    <th>Bank Account Number</th>

                            </thead>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>




    @stop



    @section('script')

    @include('templates.branch.report.partials.new_maturity')

    @stop