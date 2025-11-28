@extends('templates.admin.master')

@section('content')

	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-body">
					<form action="{!! route('admin.vendor-credit.save') !!}" method="post" enctype="multipart/form-data" id="vendor_credit" name="vendor_credit"  >
							@csrf
							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Vendor Name<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="v_name" id="v_name" class=" form-control"  value="{{$vendor->name}}" readonly>
								  <input type="hidden" name="create_application_date" id="create_application_date" class=" form-control create_application_date"  >
								  <input type="hidden" name="created_at" id="created_at" class=" form-control created_at"   >
								</div>
							  </div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Credit Note<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="credit_node" id="credit_node" class=" form-control" >
								</div>
							  </div>
							</div>

							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Order Number<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="order_number" id="order_number" class=" form-control" >
								</div>
							  </div>
							</div>

							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Vendor Credit Date<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="payment_date" id="payment_date" class=" form-control" >
								</div>
							  </div>
							</div>
							

							<!-- Table -->
							<table class="table" id="credit_list">
								<thead>
									<tr>
										<th>ITEM DETAILS</th>
										<th>Account Head</th>
										<th>Subaccount Head1</th>
										<th>Subaccount Head2</th>
										<th>Subaccount Head3</th>
										<th>Quntity</th>
										<th>Rate</th>
										<th>Bill Amount</th>
										<th>Amount</th>
										<!--<th> </th>-->
									</tr>
								</thead>
								<tbody>
									<?php 
										$billamount=0;
									?>
								@if($billItem)	
								@foreach( $billItem as $k =>$val )

								<?php
									$billamount=$billamount+$val->amount;
								?>
									<tr id="item_remove_{{$k}}">
										<td>{{$val->item_name}}</td>
										<td>{{  str_replace('N/A', ' ', getAcountHeadData($val->account_head))}}</td>
										<td>{{  str_replace('N/A', ' ', getAcountHeadData($val->sub_account_head1))}}</td>
										<td>{{  str_replace('N/A', ' ', getAcountHeadData($val->sub_account_head2))}}</td>
										<td>{{  str_replace('N/A', ' ', getAcountHeadData($val->sub_account_head3))}}</td>
										<td><input type="text" name="quntity[]" class="form-control quntity" id="quntity_{{$k}}" value="{{$val->quantity}}" readonly></td>
										<td><input type="text" name="rate[]" class="form-control rate" id="rate_{{$k}}" value="{{number_format((float)$val->rate, 2, '.', '')}}" readonly></td>
										<td><input type="text" name="billamount[]" class="form-control bii_amount" id="bii_amount_{{$k}}" value="{{number_format((float)$val->amount, 2, '.', '')}}" readonly></td>
										<td class="error-msg"><input type="text" name="pay_amount[]" class="form-control pay_amount" id="pay_amount_{{$k}}" onkeypress="return isNumberKey(event)"></td>
										<!--<td><button type="button" class="btn btn-danger" id="remove" data-row-id="{{$k}}"><i class="fas fa-trash"></i></button></td>-->

									</tr>
									@endforeach
								@endif	 
								</tbody> 
								
							</table>

							<div class="col-md-12">

								<div class="form-group row">
								<label class="col-form-label col-lg-6"></label>
								<label class="col-form-label col-lg-3">Total Bill Amount<sup class="required">*</sup> </label>
								<div class="col-lg-3 error-msg">
									<input type="text" name="total_amount_bill" class="form-control total_amount_bill" id="total_amount_bill" value="{{number_format((float)$billamount, 2, '.', '')}}">
								</div>
							  </div>

								<div class="form-group row">
								<label class="col-form-label col-lg-6"></label>
								<label class="col-form-label col-lg-3">Total<sup class="required">*</sup> </label>
								<div class="col-lg-3 error-msg">
									<input type="text" name="total_amount" class="form-control total_amount" id="total_amount" readonly>
								</div>
							  </div>
							</div>
							
							<div class="col-lg-12">
								<div class="form-group row text-center"> 
								<div class="col-lg-12 ">
									<button type="submit" class="btn btn-primary">Submit</button>
								</div>
								</div>
							</div>

						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	@include('templates.admin.vendor_management.vendorCredit.partials.script')
@stop