@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - Transactions Listing</h6>
                    </div>
                </div>
            </div> 
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
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
                                            <button type="button" class="btn bg-blue" onClick="searchForm()" >Submit </button>

                                            <button type="button" class="btn btn-secondary" id="reset_form" onClick="resetForm()" >Reset </button>
                                             
                                        </div>
                                    </div>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Member Transactions</h6>
                    </div>
                    <div class="">
                        <table id="member_transaction" class="table datatable-show-all">
                            <thead>
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
@include('templates.admin.member.partials.listing_js')
@stop