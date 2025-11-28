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
                        <form action="#" method="post" enctype="multipart/form-data" id="chequefilter" name="chequefilter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control" name="start_date" id="start_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control" name="end_date" id="end_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>


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
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select Status</option>
                                                <option value="0">Approved</option>
                                                <option value="1">Unapprove</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Mode </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value="">Select Mode</option>
                                                <option value="0">Cheque</option>
                                                <option value="1">Online transaction</option>
                                            </select>
                                        </div>
                                    </div>
                                </div> -->
                                
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="cheque_export" id="cheque_export" value="">    
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchCheckForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCheckForm()" >Reset </button>
                                        </div>
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
                        <h6 class="card-title font-weight-semibold">Payment Listing</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple exportchequelisting ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class="btn bg-dark legitRipple exportchequelisting" data-extension="1">Export PDF</button>
                            <input type="hidden" name="export_status" id="export_status">
                        </div>
                    </div>
                    <div class="">
                        <table id="member_investment_payment_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>BR Name</th>
            <th>BR Code</th>
            <th>SO Name</th>
            <th>RO Name</th>
            <th>ZO Name</th>
                                    <th>Amount</th>
                                    <th>Transaction Date</th>
                                    <th>Cheque Date</th>
                                    <th>Cheque Number</th> 
                                    <th>Bank Name</th>
                                    <th>Branch Name</th>
                                    <th>Status</th> 
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.member.partials.listing_script')
@stop