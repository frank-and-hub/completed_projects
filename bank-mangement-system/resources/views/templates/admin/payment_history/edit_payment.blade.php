@extends('templates.admin.master')

@section('content')

	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-body">
						<form>
							@csrf
							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Vendor Name<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="v_name" id="v_name" class=" form-control" >
								</div>
							  </div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Amount<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="amount" id="amount" class=" form-control" >
								</div>
							  </div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Payment Date<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="payment_date" id="payment_date" class=" form-control" >
								</div>
							  </div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Payment Mode </label>
								<select name="payment_mode" id="payment_mode" class="form-control col-md-9">
									<option value = "0">Cash</option>
									<option value = "1">Bank</option>
								</select>
							  </div>
							</div>	
							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Payment Through<sup>*</sup> </label>
								<select name="pay_through" id="pay_through" class="form-control col-md-9">
									<option value = "0">Cash</option>
									<option value = "1">Bank</option>
								</select>
							  </div>
							</div>	
							<div class="col-md-6">
								<div class="form-group row">
								<label class="col-form-label col-lg-3">Reference#</label>
								<div class="col-md-9 error-msg">
									<input type="text" name="ref_no" class="form-control" id="ref_no">
								</div>
							  </div>
							</div>	

							<!-- Table -->
							<table class="table" id="pay_list">
								<thead>
									<tr>
										<th>Date</th>
										<th>Bill#</th>
										<th>Purchase Order</th>
										<th>Bill Amount</th>
										<th>Amount Due</th>
										<th>Payment</th>
									</tr>
								</thead>
								<tbody>
									
									
									<tr>
										<td>07/07/2021 <br><small><span class="text-muted">Due Date: </span>22/07/2021</small></td>
										<td>546465</td>
										<td></td>
										<td>30,000</td>
										<td>2,000	</td>
										<td><input type="text" name="pay_amount" class="form-control t_amount" id="pay_amount"></td>
									</tr>
									<tr>
										<td>07/07/2021 <br><small><span class="text-muted">Due Date: </span>22/07/2021</small></td>
										<td>546465</td>
										<td></td>
										<td>30,000</td>
										<td>2,000	</td>
										<td><input type="text" name="pay_amount" class="form-control  t_amount" id="pay_amount"></td>
									</tr>
								</tbody>
									<tr>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td>Total:</td>
										<td><input type="text" name="total_amount" class="form-control total_amount" id="total_amount"></td>
									</tr>
							</table>
							<div class="border-dashed alert alert-warning offset-lg-7">
								<div class="row">
									<p class="col-lg-8 text-right">Amount Paid:</p>
									<p class="col-lg-4 text-right">2000.00</p>	
								</div>
								<div class="row">
									<p class="col-lg-8 text-right">Amount used for Payments:</p>
									<p class="col-lg-4 text-right">2000.00</p>	
								</div>
								<div class="row">
									<p class="col-lg-8 text-right">Amount Refunded:</p>
									<p class="col-lg-4 text-right">2000.00</p>	
								</div>
								<div class="row">
									<p class="col-lg-8 text-right"><i class="fas fa-exclamation-triangle mx-1" style="color:red;"></i>   Amount in Excess:</p>
									<p class="col-lg-4 text-right affected_amount">0</p>	
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@include("templates.admin.payment_history.partials.edit_script")
@stop


    <!-- @foreach($data = array() as $index =>$value)
                                        <tr>
                                            <td>07/07/2021 <br><small><span class="text-muted">Due Date: </span>22/07/2021</small></td>
                                            <td>546465</td>
                                            <td></td>
                                            <td>30,000</td>
                                            <td>2,000   </td>
                                            <td><input type="text" name="pay_amount" class="form-control" id="pay_amount"></td>
                                        </tr>

                                    @endforeach -->