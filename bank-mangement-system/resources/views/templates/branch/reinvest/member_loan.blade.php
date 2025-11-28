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
                        <h6 class="card-title font-weight-semibold">Members Loans</h6>
                    </div>
                    <div class="">
                        <table id="member_loan" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Date</th> 
                                    <th>Loan Name</th>
                                    <th>Amount</th> 
                                    <th>Branch Name</th> 
                                    <th>Associate Code</th>  
                                    <th>Associate Name</th> 
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
                        <h6 class="card-title font-weight-semibold">Members Group Loans</h6>
                    </div>
                    <div class="">
                        <table id="member_group_loan" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Date</th> 
                                    <th>Loan Name</th>
                                    <th>Leader Name</th>
                                    <th>Amount</th> 
                                    <th>Total Amount</th> 
                                    <th>Branch Name</th> 
                                    <th>Associate Code</th>  
                                    <th>Associate Name</th>
                                    <th>Total Member</th>   
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