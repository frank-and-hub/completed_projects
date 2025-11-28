@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - Loans Listing</h6>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Personal/Staff/Investment loans listing</h6>
                </div>
                <div class="">
                    <table id="member_loan" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Date</th>
                                <th>Loan Name</th>
                                <th>Transfer Amount</th>
                                <th>Loan Amount</th>
                                <th>Account Number</th>
                                <th>File Charge</th>
                                <th>Insurence Charge</th>
                                <th>File Charge Payment Mode</th>
                                <th>Branch Name</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Approved Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Group loan listing</h6>
                </div>
                <div class="">
                    <table id="member_group_loan" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Date</th>
                                <th>Loan Name</th>
                                <th>Account Number</th>
                                <th>Leader Name</th>
                                <th>Transfer Amount</th>
                                <th>Insurence Charge</th>  
                             
                                <th>File Charge</th>
                                <th>File Charge Payment Mode</th>
                                <th>Loan Amount</th>                         
                                <th>Total Recovery Amount</th>
                                <th>Branch Name</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Approved Date</th>
                                <th>Total Member</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.member.partials.listing_js')
@stop