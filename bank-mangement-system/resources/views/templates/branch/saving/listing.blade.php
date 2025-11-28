@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        

        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                       <h3 class="">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - SSB Account Listing</h3>
                       
                        <a href="{!! route('branch.member_list') !!}" style="float:right" class="btn btn-secondary">Back</a>
                </div>
                </div>
            </div>
        </div>

        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">SSB Accounts</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="member_ssb_listing" class="table table-flush">
                            <thead class="">
                              <tr>
                                <th>S/N</th>
                                <th>Date</th>
                                <th>Account No</th>
                                <th>Passbook No</th> 
                                <th>Account Type</th> 
                                <th>Current Balance</th> 
                                <th>Action</th>
                              </tr>
                            </thead>
                        </table>
                    </div>

                    
                </div>
            </div>
            <div class="col-lg-12 text-center ">
                <div class="card bg-white shadow">  
                    {{--<a href="{!! route('branch.member_list') !!}" class="btn btn-secondary">Back</a>--}}
                </div>
            </div>
        </div>
         
    </div>
@stop

@section('script')
@include('templates.branch.saving.partials.listing_script')
@stop