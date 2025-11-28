@extends('layouts/branch.dashboard')
@section('content')

@if(session('success'))
<div class="alert alert-success">
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
  {{ session('error') }}
</div>
@endif


<div class="container-fluid mt--6">
<div class="row">
      <div class="col-lg-12">
          <div class="card bg-white">
          <div class="card-body">
              <div class="">
                  <h3 class="">Renewal Correction List</h3> 
              </div>
          </div>
          </div>
      </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header header-elements-inline">

          <h3 class="card-title font-weight-semibold">Search Filter</h3>

        </div>
        <div class="card-body">
          <form action="#" method="post" enctype="multipart/form-data" id="correctionfilterrenewal" name="correctionfilterrenewal">
            @csrf
            <div class="row">

              <div class="col-md-4">
                <div class="form-group row">
                  <label class="col-form-label col-lg-12">Company<sup class="required">*</sup></label>
                  <div class="col-lg-12 error-msg">
                    <select name="company_id" id="company_id" class="form-control" aria-invalid="false">

                      @foreach($company as $key => $c_name)
                        <option value="{{$key}}">{{$c_name}}</option>
                      
                      @endforeach
                    </select>
                    <div class="input-group">
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group row">
                  <label class="col-form-label col-lg-12">Date</label>
                  <div class="col-lg-12 error-msg">
                    <div class="input-group">
                        @php
                        $stateid = getBranchStateByManagerId(Auth::user()->id);
                        @endphp
                      <input type="text" class="form-control" name="correction_date" id="correction_date" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group row">
                  <label class="col-form-label col-lg-12">Member Name</label>
                  <div class="col-lg-12 error-msg">
                    <div class="input-group">
                      <input type="text" class="form-control" name="member_name" id="member_name">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group row">
                  <label class="col-form-label col-lg-12">Account No</label>
                  <div class="col-lg-12 error-msg">
                    <div class="input-group">
                      <input type="text" class="form-control" name="account_no" id="account_no">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group row">
                  <label class="col-form-label col-lg-12">Status</label>
                  <div class="col-lg-12 error-msg">
                    <select class="form-control" id="status" name="status">
                      <option value="">Select Status</option>
                      <option value="0" selected>Pending</option>
                      <option value="1">Corrected</option>
                      <option value="2">Rejected</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group text-right">
                  <div class="col-lg-12 page">
                    <input type="hidden" name="is_search" id="is_search" value="no">
                    <input type="hidden" name="correction_export_renewal" id="correction_export_renewal" value="">
                    <button type="button" class=" btn btn-primary legitRipple" onClick="correctionRenewalSearchForm()">Submit</button>
                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetRenewalCorrectionForm()">Reset </button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-12 table-section  ">
      <div class="card bg-white shadow ">
        <div class="card-header bg-transparent header-elements-inline">
          <h3 class="mb-0 text-dark">Correction Renewal Listing</h3>
          <div class="">
            <button type="button" class="btn btn-primary legitRipple exportRenewalcorrection ml-2" data-extension="0" style="float: right;">Export Excel</button>
          </div>
        </div>

        <div class="table-responsive">
          <table id="correction_renewal_request_listing" class="table table-flush">
            <thead class="">
              <tr>
                <th>S/N</th> 
                <th>Company Name</th>
                <th>Created At</th>
                <th>Account No</th>
                <th>Name</th>
                <th>Customer Id</th>
                <th>Member Id</th>
                <th>Amount</th>
                <th>Plan</th>
                <th>Correction Description</th>
                <th>Rejected Correction Description</th>
                <th>Status</th>
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

@include('templates.branch.CorrectionManagement.partials.script_request')
@stop