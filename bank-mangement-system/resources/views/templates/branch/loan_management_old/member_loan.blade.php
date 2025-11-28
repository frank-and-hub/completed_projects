@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title">
                    <h3 class="">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - Loans Listing</h3>
                    <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a> 
                </div>
                </div>
            </div>
        </div>

        <div class="row">  
            <div class="col-lg-12">                
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Personal/Staff/Investment loans listing</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="member_loan_listing" class="table datatable-show-all">
                            <thead class="">
                              <tr>
                                <th>S/N</th>
                                <th>Application Date</th>
                                <th>Loan Type</th>
                                <th>Transfer Amount</th>
                                <th>Loan Amount</th>
                                <th>File Charges</th>
                                <th>File Charge Payment Mode</th>
                                <th>Branch Name</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>  
                                <th>Approved Date</th>  
                                <th>Status</th>  
                                <th>Action</th>
                              </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
       
            <div class="col-lg-12">                
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Group loan listing</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="member_group_loan_listing" class="table datatable-show-all">
                            <thead class="">
                              <tr>
                                <th>S/N</th>
                                <th>Application Date</th>
                                <th>Loan Type</th>
                                <th>Transfer Amount</th>
                                <th>Loan Amount</th>
                                <th>File Charges</th>
                                <th>File Charge Payment Mode</th>
                                <th>Branch Name</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th> 
                                <th>Approved Date</th> 
                                <th>Status</th>    
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
@include('templates.branch.loan_management.partials.listing_js')
@stop