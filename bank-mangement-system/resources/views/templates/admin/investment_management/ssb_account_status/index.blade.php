@extends('templates.admin.master')
@section('content') 
    <div class="content">
        <div class="row">
             @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif
            <div class="col-md-12"> 
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold"> SSB Account Status Active/Inactive</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{!! route('admin.investment.ssbaccountstatus.show') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">                              
						<div class="row">
							 <div class="col-md-12">
								<div class="form-group row">
									<label class="col-form-label col-lg-3">Account Number <span class="text-danger">*</span> </label>
									<div class="col-lg-6 error-msg ">
										<input type="text" name="account_number" id="account_number" class="form-control">
									</div>
								</div>
							</div>
							<div class="col-md-12" id="account_detail">
							</div>
							<div class="col-md-12 text-center">
								 <button type="Submit" class=" btn bg-dark legitRipple" id="Change_status" disabled>Change status</button>
									<input type="hidden" id="transaction_status" value="" name="transaction_status"/>
								 <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
							</div>
						</div> 
                            <!-- <input type="hidden" name="member_export" id="member_export" value="" /> -->
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Inactive SSB Account List</h6>
                        <div class=""> 
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                        </div>
                    </div>
                    <div class="">
                        <table id="ssb_member_account_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Account Number</th>
                                    <th>Branch Name</th>
                                    <th>Branch Code</th>
                                    <th>Customer ID</th>
                                    <th>Member Name</th>
                                    <th>Current Balance</th>       
                                    <th>Account Status</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.investment_management.ssb_account_status.partials.script')
@stop