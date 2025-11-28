@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) -  Transaction Listing <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a></h3> 

                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Filter</h3>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12"> 
                            <form action="#" method="post" enctype="multipart/form-data" id="fillter" name="fillter">
                        @csrf
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Start Date</label>
                                            <div class="col-lg-12 error-msg">
                                              <div class="input-group">
                                                <span class="input-group-prepend">
                                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                                </span>
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                      <div class="form-group row">
                                          <label class="col-form-label col-lg-12">End Date</label>
                                            <div class="col-lg-12 error-msg">
                                              <div class="input-group">
                                                <span class="input-group-prepend">
                                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                                </span>
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                                 <input type="hidden" class="form-control  " name="member_id" id="member_id"  value="{{ $memberDetail->id }}">
                                                 <input type="hidden" class="form-control  " name="is_search" id="is_search"  value="yes">
                                               </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 text-center">
                                        <div class=" " style="margin-top: 45px"> 
                                            <button type="button" class="btn btn-primary" onClick="searchForm()" >Submit<i class="icon-paperplane ml-2"></i></button>

                                            <button type="button" class="btn btn-secondary" id="reset_form" onClick="resetForm()" >Reset </button>
                                             
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Transaction Listing</h3>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12"> 
                            <div class="table-responsive">
                                <table id="passbook" class="table table-flush">
                                    <thead class="">
                                      <tr>
                                        <th>S/N</th> 
                                        <th>Date Time</th>
                                        <th>BR Name</th> 
                                        <th>BR Code</th> 
                                        <th>SO Name</th> 
                                        <th>RO Name</th> 
                                        <th>ZO Name</th>
                                        <th>Member Name</th>
                                        <th>Member Id</th> 
                                         
                                        <th>Type</th> 
                                        <th> A/c No</th>
                                        <th>Amount</th> 
                                        <th>Description</th> 
                                        <th>Payment Type</th> 
                                        <th>Payment Mode</th> 
                                      </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
         
    </div>
@stop

@section('script')
@include('templates.branch.transcation.partials.listing_script')
@stop