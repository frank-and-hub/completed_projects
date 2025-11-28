@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
			
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="bill_payment_form" name="bill_payment_form">
                        @csrf
							<div class="row form-group">
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Party Name</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="vendor_name" id="vendor_name"  > 
											   </div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Company Name</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="vendor_company_name" id="vendor_company_name"  > 
											   </div>
										</div>
									</div>
								</div>
							</div>
							<div class="row form-group">
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Credit Amount</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="credit_amount" id="credit_amount"  > 
											   </div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Payable Amount</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="payable_amount" id="payable_amount"  > 
											   </div>
										</div>
									</div>
								</div>
							</div>
							<div class="row form-group">
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Branch Name</label>
										<div class="col-lg-8">
											<div class="input-group">
												 <select class="form-control" id="bank_id" name="bank_id">
													@if(is_null(Auth::user()->branch_ids))
														<option value=""  >All</option>
														@foreach( $branch as $k =>$val )
															<option value="{{ $val->id }}"   @if($k==0) selected @endif>{{ $val->name }}</option> 
														@endforeach
													@else
														<?php $an_array = explode(",", Auth::user()->branch_ids); ?>
														<option value=""  >All</option>
														@foreach( $branch as $k =>$val )
															 @if (in_array($val->id, $an_array))
																<option value="{{ $val->id }}"   @if($k==0) selected @endif>{{ $val->name }}</option> 
															@endif
														@endforeach
													@endif	
												</select>
											 </div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Remaining Amount</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="remaining_amount" id="remaining_amount"  > 
											   </div>
										</div>
									</div>
								</div>
							</div>
							<div class="row form-group">
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Payment Mode</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="payment_mode" id="payment_mode"  > 
											   </div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Bank Name</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="bank_name" id="bank_name"  > 
											   </div>
										</div>
									</div>
								</div>
							</div>
							<div class="row form-group">
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Available Balance</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="available_balance" id="available_balance"  > 
											   </div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Bank A\c</label>
										<div class="col-lg-8">
											 <div class="input-group">
												 <input type="text" class="form-control" name="bank_account" id="bank_account"  > 
											   </div>
										</div>
									</div>
								</div>
							</div>
							<div class="row form-group">
							   <div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Select Mode</label>
										<div class="col-lg-8">
											<div class="input-group">
												 <select class="form-control" id="mode" name="mode">
														<option value="">Choose</option>
												</select>
											 </div>
										</div>
									</div>
								</div>
							    <div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Select Cheque</label>
										<div class="col-lg-8">
											<div class="input-group">
												 <select class="form-control" id="cheque" name="cheque">
														<option value="">Choose</option>
												</select>
											 </div>
										</div>
									</div>
								</div>
							</div>
							<div class="row form-group">
								<div class="col-md-12">
									<div class="form-group row"> 
										<div class="col-lg-12 text-right" >
											<input type="hidden" name="is_search" id="is_search" value="yes">
											<input type="hidden" name="bill_payment_export" id="bill_payment_export" value="">
											<button type="button" class=" btn bg-dark legitRipple">Submit</button>
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
                        <h6 class="card-title font-weight-semibold">Bill Payment List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export_bill_payment ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class="btn bg-dark legitRipple export_bill_payment ml-2" data-extension="1" style="float: right;">Export PDF</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="tds_payable_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Date</th>
                                    <th>Bill Number</th>
                                    <th>Expense</th>
                                    <th>Particulars</th>
                                    <th>Bill Amount</th>
                                    <th>Amount Due</th>
                                    <th>Payment</th>   
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.bill_payment.partials.script')
@stop