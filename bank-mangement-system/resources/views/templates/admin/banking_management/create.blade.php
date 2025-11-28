@extends('templates.admin.master')

@section('content')

<style>

    .search-table-outter { overflow-x: scroll; }

    .frm{ min-width: 200px; }
    h5{
     background-color: gray;
     margin: 0 -10px 0;
     padding: 4px 0 4px 10px ;
    }

</style>

<div class="loader" style="display: none;"></div>

<div class="content">

    <div class="row">

        <div class="col-md-12">

            <!-- Basic layout-->

            <div class="card">

                <div class="card-header header-elements-inline">

                    <div class="card-body" >
					
						@if($banking_type == "Expense")
							<form method="post" action="{!! route('admin.banking.save') !!}" id="banking-form"  enctype="multipart/form-data" data-type="1">
								@csrf

								<input type="hidden" name="type" class="type" value="1">
								<input type="hidden" name="subtype" value="1">

								<div class="row">
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Expense Account<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control expence_head_id" name="expense_account" id="expense_account" data-row-id="1">
													<option value="">Choose expence account...</option>
													@foreach( $expence_heads as $expence_head)
													<option value="{{ $expence_head->head_id }}"  >{{ $expence_head->sub_head }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Sub Head1<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control expence_head_id" name="expense_account1" id="expense_account1" data-row-id="2">
											   	<option value=''>Choose Sub Head</option>
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Sub Head2<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control expence_head_id" name="expense_account2" id="expense_account2" data-row-id="3">
											   	<option value=''>Choose Sub Head</option>
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Sub Head3<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control expence_head_id" name="expense_account3" id="expense_account3" data-row-id="4">
											   	<option value=''>Choose Sub Head</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="expense_branch_id" id="expense_branch_id">
													<!-- <option value="">Choose branch...</option> -->
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="expense_date" id="expense_date">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="expense_amount" id="expense_amount">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Upload Receipt<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="file" name="expense_receipt" id="expense_receipt">
											</div>
										</div>
									</div> 	

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Description<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="expense_description" id="expense_description">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="expense_mode" id="expense_mode">
													<option value="">Choose Mode...</option>
													<option value="1">Bank</option>
													<option value="2">Cash</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 bankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control banks_id" name="expense_bank_id" id="expense_bank_id">
													<option value="">Choose bank...</option>
													@foreach( $banks as $bank)
													<option value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 bankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via_account_number" name="expense_account_no" id="expense_account_no">
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 bankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via" name="expense_paid_via" id="expense_paid_via">
													<option value="">Choose payment type...</option>
													<option value="1">Cheque</option>
													<option value="2">bank transfer</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 bankneftutrDiv" style="display:none">
										<div class="form-group row bankutrDiv">
											<label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="expense_utr" id="expense_utr" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6 bankutrDiv" style="display:none">
										<div class="form-group row bankutrDiv">
											<label class="col-form-label col-lg-2">NEFT Charges<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="expense_neft" id="expense_neft" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6 bankDiv" style="display:none">
										<div class="form-group row chequeDiv" style="display:none">
											<label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="expense_cheque_no" id="expense_cheque_no">
											   </select>
											</div>
										</div>
									</div>

									<!-- <div class="col-lg-6 CashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="expense_branch_id" id="expense_branch_id">
													<option value="">Choose branch...</option>
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div> -->

									<!--
									<div class="col-lg-6 CashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="expense_cash_type" id="expense_cash_type">
													<option value="">Select Cash...</option>
													<option value="1">Micro Cash</option>
											   </select>
											</div>
										</div>
									</div> -->
								</div>

								<div class="text-right mt-10">
									<input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
								</div>
							</form>
						@endif
						
						
						@if($banking_type == "Payment")
							<form method="post" action="{!! route('admin.banking.save') !!}" id="banking-form" data-type="2">
								@csrf
								<input type="hidden" name="type" value="2">
								<input type="hidden" name="subtype" value="2">
								<div class="row">
									<div class="col-lg-12">
										<div class="form-group row">
											<label class="col-form-label col-lg-1">Account<sup>*</sup></label>
											<div class="col-lg-11 error-msg">
											   <select class="form-control" name="payment_account_payment" id="payment_account_payment" required="">
													<option value="">Choose account type...</option>
													<option value="1">Vendor</option>
													<option value="2">Customer</option>
											   </select>
											</div>
										</div>
									</div>
								</div>

								<div class="row payment_vendor_div" id="payment_vendor_div" style="display:none">
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Vendor Type<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="payemnt_vendor_type" id="payemnt_vendor_type" required="">
													<option value="">Choose vendor type...</option>
													<option value="0">Rent</option>
													<option value="1">Salary</option>
													<option value="2">Associates</option>
													<option value="3">Vendors</option>
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="payment_branch_id" id="payment_branch_id" required="">
													<!-- <option value="">Choose branch...</option> -->
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2 vendor-associate-name">Vendor Name <sup>*</sup></label>
											<div class="col-lg-10 error-msg" required="">

												<select name="payment_vendor_name" id="payment_vendor_name" class="form-control frm select2" data-row="1" data-value="1">
                                                      <option value="">Please Selct</option>
                                               	</select>

											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="vendor_payment_amount" id="vendor_payment_amount" required="">

											   <input type="hidden" name="vendor_total_amount" id="vendor_total_amount">
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="payment_vendor_date" id="payment_vendor_date" required="">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Description<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="payment_vendor_description" id="payment_vendor_description" required="">
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="vendor_payment_mode" id="vendor_payment_mode" required="">
													<option value="">Choose Mode...</option>
													<option value="1">Bank</option>
													<option value="2">Cash</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentVendorbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control banks_id" name="payment_vendor_bank_id" id="payment_vendor_bank_id" required="">
													<option value="">Choose bank...</option>
													@foreach( $banks as $bank)
													<option value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6 PaymentVendorbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via_account_number" name="payment_vendor_bank_account_number" id="payment_vendor_bank_account_number" required="">
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentVendorbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via" name="payment_vendor_paid_via" id="payment_vendor_paid_via" required="">
													<option value="">Choose payment type...</option>
													<option value="1">Cheque</option>
													<option value="2">bank transfer</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentVendorbankneftutrDiv" style="display:none">
										<div class="form-group row PaymentVendorbankneftutrDiv">
											<label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="vendor_utr" id="vendor_utr" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentVendorbankutrDiv" style="display:none">
										<div class="form-group row PaymentVendorbankutrDiv">
											<label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="vendor_neft" id="vendor_neft" required="">
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentVendorChequebankDiv" style="display:none">
										<div class="form-group row paymentchequeDiv">
											<label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="payment_vendor_cheque_no" id="payment_vendor_cheque_no" required="">
											   </select>
											</div>
										</div>
									</div>

									<!-- <div class="col-lg-6 PaymentVendorCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="payment_vendor_branch_id" id="payment_vendor_branch_id">
													<option value="">Choose branch...</option>
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div> -->
									
									<!-- <div class="col-lg-6 PaymentVendorCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="payment_vendor_cash_type" id="payment_vendor_cash_type" required="">
													<option value="">Select Cash...</option>
													<option value="1">Micro Cash</option>
											   </select>
											</div>
										</div>
									</div> -->	
								</div>
								
								<div class="row payment_customer_div" id="payment_customer_div" style="display:none">
									<input type="hidden" name="cus_advance_type" id="cus_advance_type" value="4">
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="customer_branch_id" id="customer_branch_id" required>
													<!-- <option value="">Choose branch...</option> -->
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Customer Name <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<select name="payment_customer_name" id="payment_customer_name" class="form-control frm select2" data-row="1" data-value="1" required>
                                                      <option value="">Please Selct</option>
                                               	</select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="payment_customer_date" id="payment_customer_date" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="payment_customer_amount" id="payment_customer_amount" required="">

											   <input type="hidden" name="customer_total_amount" id="customer_total_amount">
											</div>
										</div>
									</div>

									<div class="col-lg-6 ">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Description<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="payment_customer_description" id="payment_customer_description" required>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="customer_payment_mode" id="customer_payment_mode" required>
													<option value="">Choose Mode...</option>
													<option value="1">Bank</option>
													<option value="2">Cash</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentCustomerbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control banks_id" name="payment_customer_bank_id" id="payment_customer_bank_id" required>
													<option value="">Choose bank...</option>
													@foreach( $banks as $bank)
													<option value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6 PaymentCustomerbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via_account_number" name="payment_customer_bank_account_number" id="payment_customer_bank_account_number" required>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentCustomerbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via" name="payment_customer_paid_via" id="payment_customer_paid_via" required>
													<option value="">Choose payment type...</option>
													<option value="1">Cheque</option>
													<option value="2">bank transfer</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentCustomerbankneftutrDiv" style="display:none">
										<div class="form-group row PaymentCustomerbankneftutrDiv">
											<label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="customer_utr" id="customer_utr" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentCustomerbankutrDiv" style="display:none">
										<div class="form-group row PaymentCustomerbankutrDiv">
											<label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="customer_neft" id="customer_neft" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6 PaymentCustomerChequebankDiv" style="display:none">
										<div class="form-group row paymentchequeDiv">
											<label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="payment_customer_cheque_no" id="payment_customer_cheque_no" required>
											   </select>
											</div>
										</div>
									</div>

									<!-- <div class="col-lg-6 PaymentCustomerCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="payment_customer_branch_id" id="payment_customer_branch_id">
													<option value="">Choose branch...</option>
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div> -->
									
									<!-- <div class="col-lg-6 PaymentCustomerCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="payment_customer_cash_type" id="payment_customer_cash_type" required>
													<option value="">Select Cash...</option>
													<option value="1">Micro Cash</option>
											   </select>
											</div>
										</div>
									</div> -->									
								</div>

								<div class="rent_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Transaction List</h6>

									</div>
									<input type="hidden" name="rent_pending_bills" id="rent_pending_bills" value="0">
				                    <table id="rent_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>

												<th>Month</th>

												<th>Year</th>    

				                                <th>Due Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list rent_transaction_table_list">

										</tbody>

				                    </table>

								</div>

								<div class="salary_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Transaction List</h6>

									</div>

				                    <table id="salary_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>

												<th>Month</th>

												<th>Year</th>    

				                                <th>Due Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list salary_transaction_table_list">

										</tbody>

				                    </table>

								</div>

								<div class="associate_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Customer Advanced Payment List</h6>

									</div>

				                    <table id="associate_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Commission Amount</th>

				                                <th>Fuel Amount</th>

				                                <th>Commission Payment(INR)</th>

				                                <th>Fuel Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list associate_transaction_table_list">

										</tbody>

				                    </table>

								</div>

								<div class="vendor_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Transaction List</h6>

									</div>

				                    <table id="vendor_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Bill Number</th>

				                                <th>Pending Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list vendor_transaction_table_list">

										</tbody>

				                    </table>

								</div>

								<div class="customer_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Customer Advanced Payment List</h6>

									</div>

				                    <table id="customer_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Advanced Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list customer_transaction_table_list">

										</tbody>

				                    </table>

								</div>

								<div class="text-right mt-10">
									<input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
								</div>
							</form>
						@endif	
						
						
						@if($banking_type == "Card")
							<form method="post" action="{!! route('admin.banking.save') !!}" id="banking-form" data-type="3">
								<input type="hidden" name="type" value="3">
								<input type="hidden" name="subtype" value="2">
								@csrf
								<div class="row">
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Card<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="credit_card_id" id="credit_card_id" required>
													<option value="">Choose credit card...</option>
													@foreach( $credit_cards as $credit_card)
													<option value="{{ $credit_card->id }}"  >{{ $credit_card->credit_card_number }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="credit_card_branch_id" id="credit_card_branch_id">
													<!-- <option value="">Choose branch...</option> -->
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="credit_card_payment_date" id="credit_card_payment_date" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="credit_card_amount" id="credit_card_amount" required readonly>
												<input type="hidden" name="credit_card_total_amount" id="credit_card_total_amount">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Description<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="credit_card_description" id="credit_card_description" required>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="credit_card_mode" id="credit_card_mode" required>
													<option value="">Choose Mode...</option>
													<option value="1">Bank</option>
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6 CreditCardbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control banks_id" name="credit_card_bank_id" id="credit_card_bank_id" required>
													<option value="">Choose bank...</option>
													@foreach( $banks as $bank)
													<option value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6 CreditCardbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
											<div class="col-lg-10 error-msg" required>
											   <select class="form-control paid_via_account_number" name="credit_card_account_number" id="credit_card_account_number" required>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 CreditCardbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via" name="credit_card_customer_paid_via" id="credit_card_customer_paid_via" required>
													<option value="">Choose payment type...</option>
													<option value="1">Cheque</option>
													<option value="2">bank transfer</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 CreditCardbankneftutrDiv" style="display:none">
										<div class="form-group row CreditCardbankneftutrDiv">
											<label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="credit_card_utr" id="credit_card_utr" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6 CreditCardbankutrDiv" style="display:none">
										<div class="form-group row CreditCardbankutrDiv">
											<label class="col-form-label col-lg-2">NEFT Charges<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="credit_card_neft" id="credit_card_neft" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6 CreditCardCustomerChequebankDiv" style="display:none">
										<div class="form-group row paymentchequeDiv">
											<label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="credit_card_customer_cheque_no" id="credit_card_customer_cheque_no" required>
											   </select>
											</div>
										</div>
									</div>
									
								</div>

								<div class="credit_card_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Transaction List</h6>

									</div>

				                    <table id="credit_card_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Bill Number</th>

				                                <th>Amount</th>

				                                <th>Due Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list credit_card_transaction_table_list">

										</tbody>

				                    </table>

								</div>	
								
								<div class="row">
									<div class="col-lg-12">
										<div class="text-right mt-10">
											<input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
										</div>
									</div>
								</div>
							</form>
						@endif	
						
						
						@if($banking_type == "Receive")
							<form method="post" action="{!! route('admin.banking.save') !!}" id="banking-form" data-type="4">
								@csrf
								<input type="hidden" name="type" value="4">
								<input type="hidden" name="subtype" value="1">
								<div class="row">
									<div class="col-lg-12">
										<div class="form-group row">
											<label class="col-form-label col-lg-1">Account<sup>*</sup></label>
											<div class="col-lg-11 error-msg">
											   <select class="form-control" name="receive_payment_account_type" id="receive_payment_account_type" required="">
													<option value="">Choose account type...</option>
													<option value="1">Vendor</option>
													<option value="2">Customer</option>
											   </select>
											</div>
										</div>
									</div>
								</div>
								<div class="row received_payment_vendor_div" id="received_payment_vendor_div" style="display:none">
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Vendor Type<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_vendor_type" id="received_payment_vendor_type" required="">
													<option value="">Choose vendor type...</option>
													<option value="0">Rent</option>
													<option value="1">Salary</option>
													<option value="2">Associates</option>
													<option value="3">Vendors</option>
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_branch_id" id="received_payment_branch_id" required="">
											   		<!-- <option value="">Choose branch...</option> -->
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2 received-vendor-associate-name">Vendor Name <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<select name="received_payment_vendor_name" id="received_payment_vendor_name" class="form-control frm select2" data-row="1" data-value="1" required="">
                                                      <option value="">Please Selct</option>
                                               	</select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="vendor_received_payment_amount" id="vendor_received_payment_amount">
											   <input type="hidden" name="vendor_received_total_amount" id="vendor_received_total_amount" required="">
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="received_payment_vendor_date" id="received_payment_vendor_date" required="">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Description<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="received_payment_vendor_description" id="received_payment_vendor_description" required="">
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="vendor_received_payment_mode" id="vendor_received_payment_mode" required="">
													<option value="">Choose Mode...</option>
													<option value="1">Bank</option>
													<option value="2">Cash</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 ReceivedPaymentVendorbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control banks_id" name="received_payment_vendor_bank_id" id="received_payment_vendor_bank_id" required="">
													<option value="">Choose bank...</option>
													@foreach( $banks as $bank)
													<option value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6 ReceivedPaymentVendorbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via_account_number" name="received_payment_vendor_bank_account_number" id="received_payment_vendor_bank_account_number" required="">
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 ReceivedPaymentVendorbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via" name="received_payment_vendor_paid_via" id="received_payment_vendor_paid_via" required="">
													<option value="">Choose payment type...</option>
													<option value="1">Cheque</option>
													<option value="2">bank transfer</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 ReceivedPaymentVendorbankneftutrDiv" style="display:none">
										<div class="form-group row ReceivedPaymentVendorbankneftutrDiv">
											<label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="received_payment_vendor_utr" id="received_payment_vendor_utr" required>
											</div>
										</div>
									</div>

									<!-- <div class="col-lg-6 ReceivedPaymentVendorbankutrDiv" style="display:none">
										<div class="form-group row ReceivedPaymentVendorbankutrDiv">
											<label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="received_payment_vendor_neft" id="received_payment_vendor_neft" required>
											</div>
										</div>
									</div> -->
									<input type="hidden" name="received_payment_vendor_neft" id="received_payment_vendor_neft" value="0">

									<div class="col-lg-6 ReceivedPaymentVendorChequebankDiv" style="display:none">
										<div class="form-group row paymentchequeDiv">
											<label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_vendor_cheque_no" id="received_payment_vendor_cheque_no" required="">
											   </select>
											</div>
										</div>
									</div>

									

									<!-- <div class="col-lg-6 ReceivedPaymentVendorCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_vendor_branch_id" id="received_payment_vendor_branch_id">
													<option value="">Choose branch...</option>
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div> -->
									
									<!-- <div class="col-lg-6 ReceivedPaymentVendorCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_vendor_cash_type" id="received_payment_vendor_cash_type">
													<option value="">Select Cash...</option>
													<option value="1">Micro Cash</option>
											   </select>
											</div>
										</div>
									</div> -->
									
								</div>
								
								<div class="row received_payment_customer_div" id="received_payment_customer_div" style="display:none">
									<input type="hidden" name="received_cus_advance_type" id="received_cus_advance_type" value="5">
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_customer_branch_id" id="received_payment_customer_branch_id" required>
													<!-- <option value="">Choose branch...</option> -->
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Customer Name <sup>*</sup></label>
											<div class="col-lg-10 error-msg">

												<select name="received_payment_customer_name" id="received_payment_customer_name" class="form-control frm select2" data-row="1" data-value="1" required>
                                                      <option value="">Please Selct</option>
                                               	</select>

											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="received_payment_customer_date" id="received_payment_customer_date" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="received_customer_payment_amount" id="received_customer_payment_amount" required>
											   <input type="hidden" name="received_customer_total_amount" id="received_customer_total_amount" required>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Description<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="received_payment_customer_description" id="received_payment_customer_description" required>
											</div>
										</div>
									</div>
									
									<!--
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="received_payment_customer_date" id="received_payment_customer_date">
											</div>
										</div>
									</div> -->
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_customer_mode" id="received_payment_customer_mode" required>
													<option value="">Choose Mode...</option>
													<option value="1">Bank</option>
													<option value="2">Cash</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 ReceivedPaymentCustomerbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control banks_id" name="received_payment_customer_bank_id" id="received_payment_customer_bank_id" required>
													<option value="">Choose bank...</option>
													@foreach( $banks as $bank)
													<option value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6 ReceivedPaymentCustomerbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
											<div class="col-lg-10 error-msg" required>
											   <select class="form-control paid_via_account_number" name="received_payment_customer_bank_account_number" id="received_payment_customer_bank_account_number">
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 ReceivedPaymentCustomerbankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via" name="received_payment_customer_paid_via" id="received_payment_customer_paid_via" required>
													<option value="">Choose payment type...</option>
													<option value="1">Cheque</option>
													<option value="2">bank transfer</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 ReceivedPaymentCustomerbankneftutrDiv" style="display:none">
										<div class="form-group row ReceivedPaymentCustomerbankneftutrDiv">
											<label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="received_payment_customer_utr" id="received_payment_customer_utr" required>
											</div>
										</div>
									</div>

									<!-- <div class="col-lg-6 ReceivedPaymentCustomerbankutrDiv" style="display:none">
										<div class="form-group row ReceivedPaymentCustomerbankutrDiv">
											<label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="received_payment_customer_neft" id="received_payment_customer_neft" required>
											</div>
										</div>
									</div> -->
									<input type="hidden" name="received_payment_customer_neft" id="received_payment_customer_neft" value="0">

									<div class="col-lg-6 ReceivedPaymentCustomerChequebankDiv" style="display:none">
										<div class="form-group row receivedPaymentchequeDiv">
											<label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_customer_cheque_no" id="received_payment_customer_cheque_no" required>
											   </select>
											</div>
										</div>
									</div>

									<!-- <div class="col-lg-6 ReceivedPaymentCustomerCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_customer_branch_id" id="received_payment_customer_branch_id">
													<option value="">Choose branch...</option>
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div> -->
									
									<!-- <div class="col-lg-6 ReceivedPaymentCustomerCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="received_payment_customer_cash_type" id="received_payment_customer_cash_type" required>
													<option value="">Select Cash...</option>
													<option value="1">Micro Cash</option>
											   </select>
											</div>
										</div>
									</div> -->
									
								</div>

								<div class="rent_advanced_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Transaction List</h6>

									</div>

				                    <table id="rent_advanced_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Total Amount</th>

				                                <th>Advanced Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list rent_advanced_transaction_table_list">

										</tbody>

				                    </table>
								</div>

								<div class="salary_advanced_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Transaction List</h6>

									</div>

				                    <table id="salary_advanced_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Total Amount</th>

				                                <th>Advance Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list salary_advanced_transaction_table_list">

										</tbody>

				                    </table>
								</div>

								<div class="associate_advanced_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Transaction List</h6>

									</div>

				                    <table id="associate_advanced_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Total Amount</th>

				                                <th>Advanced Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list associate_advanced_transaction_table_list">

										</tbody>

				                    </table>
								</div>

								<div class="vendor_advanced_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Transaction List</h6>

									</div>

				                    <table id="vendor_advanced_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Total Amount</th>

				                                <th>Advanced Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list vendor_advanced_transaction_table_list">

										</tbody>

				                    </table>
								</div>

								<div class="received_customer_transaction_table transaction_table" style="display:none;">

									<div class="card-header header-elements-inline">

										<h6 class="card-title font-weight-semibold">Customer Advanced Payment List</h6>

									</div>

				                    <table id="received_customer_transaction_list" class="table datatable-show-all" >

				                        <thead>

				                            <tr>

				                                <th>Transaction ID.</th>  

				                                <th>Advanced Amount</th>

				                                <th>Payment(INR)</th>

				                            </tr>

				                        </thead> 

										<tbody class="transaction_table_list received_customer_transaction_table_list">

										</tbody>

				                    </table>

								</div>

								<div class="text-right mt-10">
									<input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
								</div>
							</form>
						@endif
						
						
						@if($banking_type == "Income")
							<form method="post" action="{!! route('admin.banking.save') !!}" id="banking-form" data-type="5">
								@csrf
								<input type="hidden" name="type" value="5">
								<input type="hidden" name="subtype" value="1">
								<div class="row">
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Indirect Income<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control income_head_id" name="income_head_id" id="income_head_id" data-row-id="1">
													<option value="">Choose indirect income account...</option>
													@foreach( $indirect_income_heads as $indirect_income_head)
														@if($indirect_income_head->head_id != 12)
															<option value="{{ $indirect_income_head->head_id }}"  >{{ $indirect_income_head->sub_head }}</option> 
														@endif
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Sub Head1<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control income_head_id" name="income_head_id1" id="income_head_id1" data-row-id="2">
											   	<option value="">Please Select</option>
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Sub Head2<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control income_head_id" name="income_head_id2" id="income_head_id2" data-row-id="3">
											   	<option value="">Please Select</option>
											   </select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Sub Head3<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control income_head_id" name="income_head_id3" id="income_head_id3" data-row-id="4">
											   	<option value="">Please Select</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="indirect_income_branch_id" id="indirect_income_branch_id">
													<!-- <option value="">Choose branch...</option> -->
													@foreach( $branches as $branch)
													<option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
												<input class="form-control" type="text" name="indirect_income_date" id="indirect_income_date">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="indirect_income_amount" id="indirect_income_amount">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Description<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="indirect_income_description" id="indirect_income_description">
											</div>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="indirect_income_mode" id="indirect_income_mode">
													<option value="">Choose Mode...</option>
													<option value="1">Bank</option>
													<option value="2">Cash</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 IndirectIncomebankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control banks_id" name="indirect_income_bank_id" id="indirect_income_bank_id">
													<option value="">Choose bank...</option>
													@foreach( $banks as $bank)
													<option value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
													@endforeach
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 IndirectIncomebankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via_account_number" name="indirect_income_account_no" id="indirect_income_account_no">
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 IndirectIncomebankDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control paid_via" name="indirect_income_paid_via" id="indirect_income_paid_via">
													<option value="">Choose payment type...</option>
													<option value="1">Cheque</option>
													<option value="2">bank transfer</option>
											   </select>
											</div>
										</div>
									</div>

									<div class="col-lg-6 IndirectIncomebankneftutrDiv" style="display:none">
										<div class="form-group row IndirectIncomebankneftutrDiv">
											<label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="indirect_income_utr" id="indirect_income_utr" required>
											</div>
										</div>
									</div>

									<!-- <div class="col-lg-6 IndirectIncomebankutrDiv" style="display:none">
										<div class="form-group row IndirectIncomebankutrDiv">
											<label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <input class="form-control" type="text" name="indirect_income_neft" id="indirect_income_neft" required>
											</div>
										</div>
									</div> -->
									<input type="hidden" name="indirect_income_neft" id="indirect_income_neft" value="0">

									<div class="col-lg-6 IndirectIncomebankDiv" style="display:none">
										<div class="form-group row IncomdchequeDiv" style="display:none">
											<label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="indirect_income_cheque_no" id="indirect_income_cheque_no">
											   </select>
											</div>
										</div>
									</div>
								
									<!-- <div class="col-lg-6 IndirectIncomeCashDiv" style="display:none">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
											<div class="col-lg-10 error-msg">
											   <select class="form-control" name="indirect_income_cash_type" id="indirect_income_cash_type">
													<option value="">Select Cash...</option>
													<option value="1">Micro Cash</option>
											   </select>
											</div>
										</div>
									</div> -->
								</div>
								<div class="text-right mt-10">
									<input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
								</div>
							</form>
						@endif	
						
                    </div>
                
                </div>

                <!-- /basic layout -->

            </div>

        </div>

    </div>

</div>

@stop

@section('script')

    @include('templates.admin.banking_management.partials.create_script')

@stop



