@extends('templates.admin.master')

@section('content')
  <div class="content"> 
      <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        {{Form::open(['url'=>'#','method'=>'POST','id'=>'filter','class'=>'','name'=>'filter','enctype'=>'multipart/form-data'])}}
                          <div class="row">
                              <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">From Date <span class="">*</span></label>
                                      <div class="col-lg-12 error-msg">
                                           <div class="">
                                               <input type="text" class="form-control  " name="start_date" id="start_date"  autocomplete="off" required readonly> 
                                             </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">To Date <span class="">*</span></label>
                                      <div class="col-lg-12 error-msg">
                                           <div class="">
                                               <input type="text" class="form-control  " name="end_date" id="end_date"  autocomplete="off" required readonly> 
                                             </div>
                                      </div>
                                  </div>
                              </div>
                            <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">Company <span class="">*</span></label>
                                      <div class="col-lg-12 error-msg">
                                           <div class="">
                                               <select name="company_id" id="company_id" class="form-control" required>                                                
												<option value="">Please select Company</option>
                                                
												<option value="0">All Company</option> 
												
                                                @foreach($companys as $k => $v)
                                                    <option value="{{$k}}" date-val="{{$k}}" >{{ucwords($v)}}</option>
                                                @endforeach
                                               </select>
                                            </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">Region  <span class="">*</span></label>
                                      <div class="col-lg-12 error-msg">
                                            <div class="">
                                                <select name="region" id="region" class="form-control" required>
                                                    <option value="">Please select Region</option>
                                                    @foreach($region as $k => $v)
                                                        <option value="{{$v}}" date-val="{{$k}}" >{{ucwords($v)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">Sector  </label>
                                      <div class="col-lg-12 error-msg">
                                            <div class="">
                                                <select name="sector" id="sector" class="form-control">
                                                    <option value="">Please select Sector</option>
                                                </select>
                                            </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-4">
                                  <div class="form-group row">
                                      <label class="col-form-label col-lg-12">Branch</label>
                                      <div class="col-lg-12 error-msg">
                                            <div class="">
                                                <select name="branch_id" id="branch_id" class="form-control">
                                                    <option value="0">Please select Branch</option>
                                                </select>
                                            </div>
                                      </div>
                                  </div>
                              </div>
                               
                              <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="export" id="export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple searchform" id="submitt" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>

      <!-- Table -->
            <div class="col-md-12" id="hiddentable">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Cash In hand Listing</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export xslx</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="cashInHand" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>Date</th>    
                                    <!-- <th>Region</th> -->
                                    <!-- <th>Sector</th> -->
                                    <th>Branch Name</th>
                                    <th>Opening</th>
                                    <th>Receiving</th>
                                    <th>Payment</th>
                                    <th>Closing</th>
                                    <th>Banking</th>
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
  </div>
@include('templates.admin.cash_in_hand.partials.script')
@stop