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
				<form action="{!! route('admin.credit-card.credit_card_save') !!}" method="post" enctype="multipart/form-data" id="credit_card_register" name="credit_card_register"  >
					@csrf
					  <div class="row">
						<div class="col-md-12">
						
						  <div class="card bg-white" >
							<div class="card-body">
							  <h3 class="card-title mb-3">Add Banking Infomation </h3>
							  
							  <div class="form-group row">
								<label class="col-form-label col-lg-3">Type<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <select class=" form-control" id="type" name="type">
									<option value="">Choose..</option>
									<option value="1">MONEY IN</option>
									<option value="2">MONEY OUT</option>
								  </select>
								</div>
							  </div>
							  
							  
							  <div class="form-group row" id="moneyOutDiv" style="display:none">
								<label class="col-form-label col-lg-3">MONEY OUT<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <select class=" form-control" id="money_out" name="money_out">
									<option value="">Choose..</option>
									<option value="1">Expense</option>
									<option value="2">Payment</option>
									<option value="3">Card Payment</option>
									<option value="4"> Payment deposit to bank A/C</option>
								  </select>
								</div>
							  </div>
							  
							  <div class="form-group row" id="moneyInDiv" style="display:none">
								<label class="col-form-label col-lg-3">MONEY IN<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <select class=" form-control" id="money_in" name="money_in">
									<option value="">Choose..</option>
									<option value="1">Receive Payment</option>
									<option value="2">Other Income</option>
								  </select>
								</div>
							  </div>
							  
							  
							  <div id="creditCardDiv" style="display:none">
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Card<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
									  <select class=" form-control" id="credit_card_id" name="credit_card_id">
										<option value="">Select credit card..</option>
										@foreach( $credit_card as $val )
										<option value="{{ $val->id }}">{{ $val->credit_card_number }}</option>
										@endforeach
									  </select>
									</div>
								  </div>
								  
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Date<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
										<input type="text" class="form-control" name="start_date" id="start_date"  > 
									</div>
								  </div>
								  
								  
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Amount<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
										<input type="text" class="form-control" name="amount" id="amount"  > 
									</div>
								  </div>
								  
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Bank Name<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
									  <select class=" form-control bank_id" id="bank_id" name="bank_id">
										<option value="">Select bank name..</option>
										@foreach( $banks as $val )
										<option value="{{ $val->id }}">{{ $val->bank_name }}</option>
										@endforeach
									  </select>
									</div>
								  </div>
								  
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Bank Account Number<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
									  <select class=" form-control bank_account_number" id="bank_account_number" name="bank_account_number">
										<option value="">Select bank account number..</option>
									  </select>
									</div>
								  </div>
								  
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Cheque<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
									  <select class=" form-control" id="cheque_id" name="cheque_id">
										<option value="">Select cheque..</option>
									  </select>
									</div>
								  </div>
								  
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Description<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
										<input type="text" class="form-control" name="description" id="description"> 
									</div>
								  </div>
							  </div>
							  
							  
							  <div id="otherIncomeDiv" style="display:none">
								
								 <div class="form-group row">
									<label class="col-form-label col-lg-3">Income Head<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
									  <select class=" form-control" id="head_id" name="head_id">
										<option value="">Select head..</option>
										@foreach( $account_heads as $val )
										<option value="{{ $val->head_id }}">{{ $val->sub_head }}</option>
										@endforeach
									  </select>
									</div>
								  </div>
								  
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Date<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
										<input type="text" class="form-control" name="head_date" id="head_date"  > 
									</div>
								  </div>
								  
								  <div class="form-group row">
									<label class="col-form-label col-lg-3">Mode<sup class="required">*</sup> </label>
									<div class="col-lg-9 error-msg">
									  <select class=" form-control" id="mode" name="mode">
										<option value="">Select Mode..</option>
										<option value="1">Bank</option>
										<option value="2">Cash</option>
									  </select>
									</div>
								  </div>
								  
								  <div id="bankOtherIncome" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-3">Bank Name<sup class="required">*</sup> </label>
											<div class="col-lg-9 error-msg">
											  <select class=" form-control bank_id" id="bank_other_income_bank_id" name="bank_other_income_bank_id">
												<option value="">Select bank name..</option>
												@foreach( $banks as $val )
												<option value="{{ $val->id }}">{{ $val->bank_name }}</option>
												@endforeach
											  </select>
											</div>
										</div>
										  
										<div class="form-group row">
											<label class="col-form-label col-lg-3">Bank Account Number<sup class="required">*</sup> </label>
											<div class="col-lg-9 error-msg">
											  <select class=" form-control bank_account_number" id="bank_other_income_account_number" name="bank_other_income_account_number">
												<option value="">Select bank account number..</option>
											  </select>
											</div>
										</div>
										  
										<div class="form-group row">
											<label class="col-form-label col-lg-3">Cheque<sup class="required">*</sup> </label>
											<div class="col-lg-9 error-msg">
											  <select class=" form-control" id="bank_other_income_cheque_id" name="bank_other_income_cheque_id">
												<option value="">Select cheque..</option>
											  </select>
											</div>
										</div>
								  </div>
								  
								  
								  <div id="cashOtherIncome" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-3">Branch<sup class="required">*</sup> </label>
											<div class="col-lg-9 error-msg">
											  <select class=" form-control branch_id" id="branch_id" name="branch_id">
												<option value="">Select branch name..</option>
												@foreach( $branch as $val )
												<option value="{{ $val->id }}">{{ $val->name }}</option>
												@endforeach
											  </select>
											</div>
										</div>
										  
										<div class="form-group row">
											<label class="col-form-label col-lg-3">Cash Type<sup class="required">*</sup> </label>
											<div class="col-lg-9 error-msg">
											  <select class=" form-control cash_type" id="cash_type" name="cash_type">
												<option value="">Select cash type..</option>
												<option value="1">Loan</option>
												<option value="2">Micro</option>
											  </select>
											</div>
										</div>
										
										<div class="form-group row">
											<label class="col-form-label col-lg-3">Description<sup class="required">*</sup> </label>
											<div class="col-lg-9 error-msg">
												<input type="text" class="form-control" name="other_type_money_in_description" id="other_type_money_in_description"  > 
											</div>
										</div>
								  </div>
								
							  </div>
							  
							  <div class="col-lg-12">
									  <div class="text-right">
									  <button type="submit" class="btn btn-primary">Submit</button>
									</div>
								</div>
							</div>
						  </div>
						</div>
					 </div> 
			</form>
		</div>	
    </div>
</div>
@include('templates.admin.banking.partials.script')
@stop