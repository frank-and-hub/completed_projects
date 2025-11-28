@extends('templates.admin.master')
@php
$dropDown = $company;
$filedTitle = 'Company';
$name = 'company_id';
@endphp
@section('content')
@section('css')
<style>
  .table-section, .hide-table{
      display: none;
  }
  .show-table{
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

                              @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control" name="correction_date" id="correction_date"  >
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <!--
                                  <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">In Context</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="in_context" name="in_context">
                                                <option value="">Select Context</option>
                                                <option value="0">Member Registration</option>
                                                <option value="1">Associate Registration</option>
                                                <option value="2">Investment Registration</option>
                                                <option value="3">Renewals Transaction</option>
                                                <option value="4">Withdrawals</option>
                                                <option value="5">Passbook print</option>
                                                <option value="6">Certificate print</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                              -->
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
                                            <input type="hidden" name="type" id="type" value="{{ $type }}">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="correction_export" id="correction_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="correctionSearchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCorrectionForm()" >Reset </button>
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
                    <h3 class="mb-0 text-dark">{{ $title }} Correction Listing</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple exportcorrection ml-2" data-extension="0" style="float: right;">Export Excel</button>
                        {{-- <button type="button" class="btn bg-dark legitRipple exportcorrection" data-extension="1">Export PDF</button> --}}
                    </div>
                </div>
                <?php
                  $a = [
                    0=> "Member Id",
                    1=> "Associate Id",
                    2=> "Account No",
                  ];
                ?>
                <div class="table-responsive">
                    <table id="investment_correction_request_listing" class="table table-flush">
                       <thead class="">
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Transaction Date</th>
                                <th>BR Name</th>
                                <th>{{ $a[$type]??'Account No' }}</th>
                                {{-- @if ($title == 'Renewals')
                                <th>Approved Date</th>
                                <th>Approved By</th>
                                @else --}}
                                <th>In context to</th>
                                <th>Print Type</th>
                                {{-- @endif --}}
                                <th>Correction</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="correction-delete" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="card bg-white border-0 mb-0">
          <div class="card-body  ">
              <div class="form-group row">
                <div class="col-lg-12 ">
                    <div class="alert alert-danger">
                        <strong>This transaction cannot delete.Because paid by cheque!  </strong>
                    </div>

                </div>
              </div>
          </div>
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
            {{Form::open(['url'=>route('correction.request.reject'),'method'=>'POST','id'=>'correction-reject-form','name'=>'correction-reject-form'])}}
              {{Form::hidden('correction_id','',['id'=>'correction_id'])}}
              <div class="form-group row">
                <div class="col-lg-12">
                  {{Form::textarea('rejection','',['rows'=>'6','class'=>'form-control','placeholder'=>'Remark','cols'=>'50'])}}
                </div>
              </div>
              <div class="text-right">
                {{Form::submit('Submit',['class'=>'btn btn-primary'])}}
              </div>
            {{Form::close()}}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="myModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLabel">Passbook Print Request</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="text-align: center;">
      <form method="post" action="{!! route('admin.printpassbook.updateprintstatus') !!}">
        @csrf
        <div class="container">
          <div class="form-check">
            <input class="form-check-input check" name="printstatus" type="radio" value="1">
            <label class="form-check-label" for="flexCheckDefault">
              Free Print
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input check" name="printstatus" type="radio" value="2" checked>
            <label class="form-check-label" for="flexCheckChecked">
              Paid Print
            </label>
          </div>
          <input type="hidden" id="csid" name="userid" value="">
          <input type="hidden" id="corr_id" name="corr_id" value="">
        </div>

    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
      </form>

      </div>
    </div>
  </div>
</div><!-- /.modal -->
@include('templates.admin.common.partials.script')
@stop
