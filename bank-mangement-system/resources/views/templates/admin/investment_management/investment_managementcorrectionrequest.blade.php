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
                        <form action="#" method="post" enctype="multipart/form-data" id="correctionfilter" name="correctionfilter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value="">Select Branch</option>
                                                @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
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
                         
                                <!-- <div class="col-md-4">
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
                                </div> -->
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select Status</option>
                                                <option value="0">Pending</option> 
                                                <option value="1">Corrected</option> 
                                                <option value="2">Rejected</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="type" id="type" value="2">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
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
        <div class="col-lg-12">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Investments | Correction Requests</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple exportcorrection ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <button type="button" class="btn bg-dark legitRipple exportcorrection" data-extension="1">Export PDF</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="investment_correction_request_listing" class="table table-flush">
                       <thead class="">
                            <tr>
                                <th>S/N</th>
                                <th>Transaction Date</th>
                                <th>Branch</th>
                                <th>In context to</th>
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
@include('templates.admin.investment_management.partials.script')
@stop