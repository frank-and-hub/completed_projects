@extends('templates.admin.master')
@section('content')

<head>
	<style>
		.search-table-outter { overflow-x: scroll; }
		th, td { min-width: 200px; }
	</style>
</head>

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="tds_payable_filter" name="tds_payable_filter">
                    @csrf
						<div class="row form-group">
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Vendor Name</label>
									<div class="col-lg-8">
										<div class="input-group">
											 <select class="form-control" id="vendor_id" name="vendor_id">
													<option value="">All</option>
													<option value="1">Vendor 1</option>
													<option value="2">Vendor 2</option>
											</select>
										 </div>
									</div>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Bill#</label>
									<div class="col-lg-8">
										<div class="input-group">
											<input type="text" class="form-control  " name="bill" id="bill"  >
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="row form-group">
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Bill Date </label>
									<div class="col-lg-8">
										 <div class="input-group">
											 <input type="text" class="form-control  " name="bill_date" id="bill_date"   autocomplete="off"> 
										   </div>
									</div>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Select Branch</label>
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
						</div>

						<div class="row form-group">
							<div class="col-md-12">
								<div class="form-group row">
									<h3>Multiple Item Add</h3>
								</div>
							</div>
						</div>

						<div class="row form-group">
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Discount</label>
									<div class="col-lg-8">
										<div class="input-group">
											 <select class="form-control" id="discount" name="discount">
												<option value="">Add transcation Level</option>
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
                                        <input type="hidden" name="tds_payable_export" id="tds_payable_export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
						</div>

						<div class="row form-group">
							<input type="hidden" name="itemCount" id="itemCount" value="0"/>
							<div class="col-md-12">
								<div class="search-table-outter wrapper">
									<table class="table">
										<thead>
											<tr>
												<th>Item</th>
												<th>Account</th>
												<th>Account Sub head</th>
												<th>HSN/SAC Code</th>
												<th>Quantity</th>
												<th>Rate</th>
												<th>Amount</th>
												<th>Taxable Value</th>
												<th>CGST AND SGST</th>
												<th>Upload Bill</th>
												<th>Total</th>
											</tr>
										</thead> 

										<tbody id="bill-expense-table">
											<tr id="trRow0">
												<td id="tdRow0">
													<select class="form-control item_id" id="item_id" name="item_id" data-row-id="0">
														<option value="">Choose...</option>
														@foreach( $expense_item as $k =>$val1 )
															<option value="{{ $val1->id }}">{{ $val1->name }}</option> 
														@endforeach
													</select>
												</td>
											</tr>
										</tbody>
									</table>
								</div>	
							</div>
						</div>

						<div class="row form-group">
							<div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <button type="button" class=" btn bg-dark addNewRow">Add New Row</button>
                                    </div>
                                </div>
                            </div>
						</div>
                    </form>

					<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
						  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
							<div class="modal-content">
							  <div class="modal-body p-0">
								<div class="card bg-white border-0 mb-0">
								  <div class="card-header bg-transparent pb-2ß">
									<div class="text-dark text-center mt-2 mb-3">Open Saving Account</div>
								  </div>
								  <div class="card-body px-lg-5 py-lg-5">
									<form action="#" method="post" id="saving-account-form" name="register-plan">
									  @csrf
									  <input type="hidden" name="created_at" class="created_at">
									  <input type="hidden" name="saving_account_m_id" id="saving_account_m_id">
									  <input type="hidden" name="saving_account_a_id" id="saving_account_a_id">
									  <input type="hidden" name="saving_account_m_name" id="saving_account_m_name">
									  <input type="hidden" name="nominee_form_class" id="nominee_form_class">
									  <input type="hidden" name="account_box_class" id="account_box_class">

									  <div class="form-group row">
										<label class="col-form-label col-lg-2">Amount</label>
										<div class="col-lg-4">
										  <div class="rupee-img">
										  </div>
										  <input type="text" name="ssbamount" id="ssbamount" class="form-control rupee-txt" value="100" readonly="">
										</div>

										<label class="col-form-label col-lg-2">Form Number</label>
										<div class="col-lg-4">
										  <input type="text" name="f_number" id="f_number" class="form-control">
										</div>
									  </div>

									  <div class="text-right">
										<!-- <button type="button" class="btn btn-primary" form="modal-details">Submit</button> -->
										<input type="submit" name="submitform" value="Submit" class="btn btn-primary">
									  </div>
									</form>
								  </div>
								</div>
							  </div>
							</div>
						  </div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('templates.admin.bill_expense.partials.script')
@stop