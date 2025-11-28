@extends('layouts/branch.dashboard')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
               <div class="card bg-white">
                        <div class="card-body page-title">
                            <h3 class="">{{$title}}</h3>
                        
                        </div>
                    </div>
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch  Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch" name="branch_name">
                                                <option value="">Select Bank</option>
                                                @foreach( $branches as $val )
                                                    <option value="{{ $val->id }}" data-value={{ $val->branch_code }} >{{ $val->name }}</option> 
                                                @endforeach
                                            </select>

                                            <!-- <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  > -->
                                            
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="branch_code" id="branch_code" class="form-control"  readonly> 
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select Status</option>
                                                <option value="1">Approved</option>
                                                  <option value="0">Pending</option>
                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="cheque_export" id="cheque_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
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
                        <h6 class="card-title font-weight-semibold">Cheque List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="report_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Request Type</th>
                                    <th>Branch Name</th>
                                    <th>Branch Code</th>
                                    <th>Loan Daybook Amount</th>
                                    <th>Micro Daybook Amount</th>
                                    <th>Transfer Amount</th>
                                    <th>Transfer Date</th>
                                    <th>Transaction No.</th>
                                    <th>Transfer Bank Name</th>
                                    <th>Transfer bank Account</th>
                                    <th>Transfer Cheque No/UTR No</th>
                                    <th>RTGS/NEFT Charge </th>
                                    <th>Receive Cheque No/UTR No</th>
                                    <th>Receive Amount</th>
									<th>Receive Bank Name </th>
                                    <th>Receive Bank A/c </th>
                                    <th>Request Date</th>
<!--                                     <th> Approve/Reject Date</th>
 -->                                    <th>Approve/Reject By</th>
                                    <th>Remark </th>
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
    @include('templates.branch.fund-transfer.partials.script')
@stop