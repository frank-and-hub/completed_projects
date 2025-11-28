
@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title">
                    <h3 class="">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - Saving Account Listing</h3>
                    <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a> 
                </div>
                </div>
            </div>
        </div>

        <div class="row">  
            <div class="col-lg-12">                
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Saving Account listing</h3>
                    </div>
                    <div class="table-responsive">
                    <table id="member_Saving" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>Branch Name</th>
                                    <th>Customer ID</th>
                                    <th>Member ID</th>                                 
                                    <th>Account Number</th>
                                    <th>Member Name</th>
                                    <th>Balance </th>
                                    <th class="text-center">Action</th>
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
@include('templates.branch.saving.partials.listing_saving')
@stop