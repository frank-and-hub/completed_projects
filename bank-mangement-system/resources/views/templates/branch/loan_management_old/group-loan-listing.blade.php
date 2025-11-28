@extends('layouts/branch.dashboard')
@section('content')
<style type="text/css">
  .datepicker.dropdown-menu {
    z-index: 9999999 !important;
}
</style>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Group Loan Listing</h3>
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
                    <form action="#" method="post" enctype="multipart/form-data" id="grouploanfilter" name="grouploanfilter">
                    @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date From</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control from_date" name="date_from"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date To</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control to_date" name="date_to"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="loan_account_number" id="loan_account_number"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member Name </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="member_name" id="member_name"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="member_id" id="member_id" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate code  </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Group Leader ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="group_loan_common_id" id="group_loan_common_id" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select status</option>
                                            <!-- <option value="0">Pending</option> -->
                                            <option value="1">Approved</option>
											 <option value="0">Pending</option>
                                            <option value="3" selected>Clear</option>
                                            <option value="4">Due</option>
                                            <option value="5">Rejected</option>
                                            <option value="6">Hold</option>
                                            <option value="7">Approved but Hold</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @include('templates.GlobalTempletes.role_type',[
                                'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                'name'=>'company_id',
                                'apply_col_md'=>false,
                                'filedTitle' => 'Company'
                            ])

                            <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="group_loan_recovery_export" id="group_loan_recovery_export" value="">
                                        <button type="button" class=" btn btn-primary legitRipple" onClick="searchGroupLoanForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetGroupLoanForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                
               <div class="card bg-white shadow">
                    <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                            <h3 class="mb-0 text-dark">Loans</h3>
                        </div>
                            <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-primary legitRipple export-group-loan ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <button type="button" class="btn  btn-primary legitRipple export-group-loan" data-extension="1">Export PDF</button>
                        </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="group-loan-listing" class="table table-flush">
                            <thead class="">
                                <tr>
                                <th>S/N</th>
                                 <th>Action</th>
                                <th>BR Name</th>
                                <!-- <th>BR Code</th>
                                <th>SO Name</th>
                                <th>RO Name</th>
                                <th>ZO Name</th> -->
                                <th>A/C No.</th>
                                <th>Group Loan Common Id</th>
                                <th>Member Name</th>
                                <th>Member Id </th>
                                <th>Total Deposit</th>  
                                <th>Loan Type</th>
                                <th>Tenure</th>
                                <th>Emi Amount</th>
                                <th>Transfer Amount</th>
                                <th>Loan Amount</th>
                                <th>File Charges</th>
								<th>Insurance Charge</th>
                                <th>File Charge Payment Mode</th>
                                <th>Outstanding Amount</th>   
                                <th>Last Recovery Date</th>   
                                <th>Associate Code</th>   
                                <th>Associate Name</th>   
                                <th>Bank Name</th>
                                <th>Bank Account Number</th>
                                <th>IFSC Code</th>
                                <th>Total Payment</th>  
                                <th>Approved Date</th>  
                                <th>Sanction Date</th>  
                                <th>Application Date</th>
								<th>Collector Code</th>
                                <th>Collector Name</th>
                                <th>Reason</th>
                                <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="rejection-view" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="card bg-white border-0 mb-0">
              <div class="card-header bg-transparent pb-2ß">
                <div class="text-dark text-center mt-2 mb-3">View Admin  Remark</div>
              </div>
              <div class="card-body px-lg-5 py-lg-5">
                  <div class="form-group row">
                    <!-- <label class="col-form-label col-lg-2">Corrections</label> -->
                    <div class="col-lg-12 loan-rejected-description">              
                    </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  
@stop
@section('script')
@include('templates.branch.loan_management.partials.script')
@stop