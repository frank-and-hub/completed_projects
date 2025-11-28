@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title">
                    
                        <h3 class="">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - Investment Listing</h3>
                        <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a>
                  
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Investments</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="member_investment_listing" class="table table-flush">
                            <thead class="">
                              <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Date</th>
                                <th>Plan</th>
                                <th>Member Id</th>
                                <th>Member Name</th>
                                <th>Account No</th>
                                <th>Amount</th>
                                <th>Tenure</th>
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
@include('templates.branch.investment_management.partials.listing_js')
@stop