@extends('templates.admin.master')
@php
$dropDown = $AllCompany;
$filedTitle = 'Company';
$name = 'company_id';
@endphp
@section('content')
@section('css')
<style>
  .table-section,
  .hide-table {
    display: none;
  }

  .show-table {
    display: block;
  }
</style>
@endsection
<div class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header header-elements-inline">
          <h6 class="card-title font-weight-semibold">Search Filter</h6>
        </div>
        <div class="card-body">
          <form action="#" method="post" enctype="multipart/form-data" id="correctionfilter" name="correctionfilter">
            @csrf
            <div class="row">

              @php
              $dropDown = $AllCompany;
              $filedTitle = 'Company';
              $name = 'company_id';
              @endphp
              @include('templates.GlobalTempletes.both_company_filter',['all'=>true])

              <div class="col-md-4 d-none">
                <div class="form-group row">
                  <label class="col-form-label col-lg-12">Date</label>
                  <div class="col-lg-12 error-msg">
                    <div class="input-group">
                      <input type="text" class="form-control" name="correction_date" id="correction_date">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group row">
                  <label class="col-form-label col-lg-12">Associate Code</label>
                  <div class="col-lg-12 error-msg">
                    <div class="input-group">
                      <input type="text" class="form-control" name="associate_code" id="associate_code">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group row">
                  <label class="col-form-label col-lg-12">Customer ID</label>
                  <div class="col-lg-12 error-msg">
                    <div class="input-group">
                      <input type="text" class="form-control" name="customer_id" id="customer_id">
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
                    <input type="hidden" name="correction_export" id="correction_export" value="">
                    <button type="button" class=" btn bg-dark legitRipple" onClick="correctionSearchForm()">Submit</button>
                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCorrectionForm()">Reset </button>
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
          <h3 class="mb-0 text-dark">Correction Listing</h3>
          <div class="">
            <button type="button" class="btn bg-dark legitRipple exportcorrection ml-2" data-extension="0" style="float: right;">Export Excel</button>
          </div>
        </div>

        <div class="table-responsive">
          <table id="correction_request_listing" class="table table-flush">
            <thead class="">
              <tr>
                <th>S/N</th>
                <th>Created At</th>
                <th>Company Name</th>
                <th>BR Name</th>
                <th>Changes For</th>
                <th>Name</th>
                <th>A/C Number/Customer ID/Associate Code</th>
                <th>Field to Update</th>
                <th>Old Value</th>
                <th>New Value</th>
                <th>Description</th>
                <th>User</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Status Date</th>
                <th>Status Remark</th>
                <th>Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="correction-view" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="card bg-white border-0 mb-0">
          <div class="card-header bg-transparent pb-2ß">
            <div class="text-dark text-center mt-2 mb-3">View Correction Request</div>
          </div>
          <div class="card-body px-lg-5 py-lg-5">
            <div class="form-group row">
              <!-- <label class="col-form-label col-lg-2">Corrections</label> -->
              <div class="col-lg-12 form-corrections">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="correction-rejected" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="card bg-white border-0 mb-0">
          <div class="card-header bg-transparent pb-2ß">
            <div class="text-dark text-center mt-2 mb-3">Reject Correction Request</div>
          </div>
          <div class="card-body px-lg-5 py-lg-5">
            <form action="{{route('correction.reject.request')}}" method="post" id="correction-reject-form" name="correction-reject-form">
              @csrf
              <input type="hidden" name="correction_id" id="correction_id" value="">
              <input type="hidden" name="created_at" id="created_at" class="created_at" value="">
              <div class="form-group row">
                <div class="col-lg-12">
                  <textarea name="rejection" name="rejection" rows="6" cols="50" class="form-control" placeholder="Remark"></textarea>
                </div>
              </div>

              <div class="text-right">
                <input type="submit" name="submitform" value="Submit" class="btn btn-primary">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('templates.admin.common.partials.script_request')
@stop