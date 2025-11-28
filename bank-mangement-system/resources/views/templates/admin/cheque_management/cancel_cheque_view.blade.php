@extends('templates.admin.master')



@section('content')

<?php
//print_r($setData['fundTransfer']->branch_id);die;
?>

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

          <h4 class="card-title mb-3">Cheque Details</h4>

        </div>

        <div class="card-body">

			  <div class="row">

				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Bank Name </label>

							<div class="col-lg-7 ">

								{{$cheque['samrddhBank']->bank_name}}

							</div>

						</div>

				  </div>

				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Account No.</label>

							<div class="col-lg-7 ">

								{{$cheque['samrddhAccount']->account_no}}

							</div>

						</div>

				  </div>

				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Cheque No.</label>

							<div class="col-lg-7 ">

								{{$cheque->cheque_no}}

							</div>

						</div>

				  </div> 

				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Cheque Created Date</label>

							<div class="col-lg-7 ">

								{{date("d/m/Y", strtotime($cheque->cheque_create_date))}}

							</div>

						</div>

				  </div>

				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Is Used </label>

							<div class="col-lg-7 ">

								<?php

								if($cheque->is_use==1)

								{

									$use = 'Yes';

								}

								else

								{

									$use = 'No';     

								} 



								?>

								{{  $use }}

							</div>

						</div>

				  </div>



				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Status</label>

							<div class="col-lg-7">

							  <?php

							  $status = 'New';

							  if($cheque->status==1)

							  {

								  $status = 'New';

							  }                

							  if($cheque->status==2)

							  {

								  $status = 'Pending';

							  }

							  if($cheque->status==3)

							  {

								  $status = 'cleared';

							  }

							  if($cheque->status==4)

							  {

								  $status = 'Canceled & Re-issued';

							  }

							  if($cheque->status==0)

							  {

								  $status = 'Deleted';

							  }

							  ?>

								{{$status}}

							</div>

						</div>

				  </div>

				  @if($cheque->status==0)



				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Cheque Deleted Date</label>

							<div class="col-lg-7 ">

								{{date("d/m/Y", strtotime($cheque->cheque_delete_date))}}

							</div>

						</div>

				  </div>

				  @endif

				  @if($cheque->status==4)



				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Cheque Cancel & Re-issue Date</label>

							<div class="col-lg-7 ">

								{{date("d/m/Y", strtotime($cheque->cheque_cancel_date))}}

							</div>

						</div>

				  </div>

				  <div class="col-md-6">

						<div class="form-group row">

							<label class="col-lg-5">Cheque Cancel & Re-issue Remark</label>

							<div class="col-lg-7 ">

								{{$cheque->remark_cancel}}

							</div>

						</div>

				  </div>

				  @endif

				</div>

			

			

				<br/>
				<h4 class="card-title mb-3">Assign Cheque Details:</h4>

				<div class="row">

					

					@if($type == 1)
						<div class="col-md-12"><h4 class="card-title mb-3">Fund Transfer </h4></div>

						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">Transfer Type</label>
								<div class="col-lg-7 ">
									@if($setData['fundTransfer']->transfer_type==0)
									Branch to HO
									@else
									Bank to Bank
									@endif

								</div>
							</div>
					  </div>
				@if($setData['fundTransfer']->transfer_type==0)
				<?php
				//echo $setData['fundTransfer']->branch_id;die;
					$getBranchDetail=getBranchDetail($setData['fundTransfer']->branch_id);
				?>
					  <div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">Branch Name</label>
								<div class="col-lg-7 ">
									{{$getBranchDetail->name}}
								</div>
							</div>
					  </div>

					  <div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">Branch Code</label>
								<div class="col-lg-7 ">
									{{$getBranchDetail->branch_code}}
								</div>
							</div>
					  </div>

					  <div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">Bank Name</label>
								<div class="col-lg-7 ">
									{{ getSamraddhBank($setData['fundTransfer']->head_office_bank_id)->bank_name}}
								</div>
							</div>
					  </div>
					  <div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">Bank Account Number</label>
								<div class="col-lg-7 ">
									{{ $setData['fundTransfer']->head_office_bank_account_number}}
								</div>
							</div>
					  </div>
					  <div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">Amount</label>
								<div class="col-lg-7 ">
									{{ number_format((float)$setData['fundTransfer']->amount, 2, '.', '')}}
								</div>
							</div>
					  </div>
			@else
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">From Bank Name</label>
								<div class="col-lg-7 ">
									{{ getSamraddhBank($setData['fundTransfer']->to_bank_id)->bank_name}}
								</div>
							</div>
					  	</div>
					  	<div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">From Bank A/c No</label>
								<div class="col-lg-7 ">
									{{ $setData['fundTransfer']->to_bank_account_number}}
								</div>
							</div>
					  	</div>
					  	<div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">To Bank Name</label>
								<div class="col-lg-7 ">
									{{ getSamraddhBank($setData['fundTransfer']->from_bank_id)->bank_name}}
								</div>
							</div>
					  	</div>
					  	<div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">To  Bank A/c No</label>
								<div class="col-lg-7 ">
									{{ $setData['fundTransfer']->from_bank_account_number}}
								</div>
							</div>
					  	</div>

					  	<div class="col-md-6">
							<div class="form-group row">
								<label class="col-lg-5">Amount</label>
								<div class="col-lg-7 ">
									{{ number_format((float)$setData['fundTransfer']->transfer_amount, 2, '.', '')}}
								</div>
							</div>
					  </div>
			@endif


					  

					  

					

					@elseif($type == 3)

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Loan Type</label>

								<div class="col-lg-7 ">

									{{$setData['loan_type']}}

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Associate Name</label>

								<div class="col-lg-7 ">

									{{$setData['associateName']}}

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Associate Code</label>

								<div class="col-lg-7 ">

									{{$setData['associateCode']}}

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Member Name</label>

								<div class="col-lg-7 ">

									{{$setData['membersName']}}

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Member ID</label>

								<div class="col-lg-7 ">

									{{$setData['member_id']}}

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Loan Account Number</label>

								<div class="col-lg-7 ">

									{{$setData['account_number']}}

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Amount</label>

								<div class="col-lg-7 "> 
									{{ number_format((float)$setData['amount'], 2, '.', '')}}

								</div>

							</div>

					  </div>

					@elseif($type == 4)

					<?php if(count($setData['MemberSalary']) > 0) { for($p=0; $p<count($setData['MemberSalary']); $p++){ ?>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Employee Name</label>

								<div class="col-lg-7 ">

									<?php echo $setData['MemberSalary'][$p]->employee_name; ?>

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Employee Code</label>

								<div class="col-lg-7 ">

									<?php echo $setData['MemberSalary'][$p]->employee_code; ?>

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Amount</label>

								<div class="col-lg-7 "> 
									{{ number_format((float)$setData['MemberSalary'][$p]->actual_transfer_amount, 2, '.', '')}}
								</div>

							</div>

					  </div>

					  <div class="col-md-6"></div>

					<?php } } ?> 

					@elseif($type == 5)

					<?php if(count($setData['MemberRent']) > 0) { for($q=0; $q<count($setData['MemberRent']); $q++){ ?>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Owner Name</label>

								<div class="col-lg-7 ">

									<?php echo $setData['MemberRent'][$q]->owner_name; ?>

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Rent Type</label>

								<div class="col-lg-7 ">

									<?php echo $setData['MemberRent'][$q]->sub_head; ?>

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Amount</label>

								<div class="col-lg-7 ">

									
									{{ number_format((float)$setData['MemberRent'][$q]->actual_transfer_amount, 2, '.', '')}}

								</div>

							</div>

					  </div>

					  <div class="col-md-6"></div>

					<?php }} ?>

					@elseif($type == 6 || $type == 2)

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Party Name</label>

								<div class="col-lg-7 ">

									N/A

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Amount</label>

								<div class="col-lg-7 ">

									N/A

								</div>

							</div>

					  </div>

					@elseif($type == 7)

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Name</label>

								<div class="col-lg-7 ">

									{{ getAcountHeadNameHeadId($setData['head']->type_id)}}

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Amount</label>

								<div class="col-lg-7 ">

									{{ number_format((float)$setData['head']->amount, 2, '.', '')}}

								</div>

							</div>

					  </div>

					@elseif($type == 8)

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Member ID</label>

								<div class="col-lg-7 ">

									N/A

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Account Number</label>

								<div class="col-lg-7 ">
									{{ $setData['ssb']->account_no }}

								</div>

							</div>

					  </div>

					  <div class="col-md-6">

							<div class="form-group row">

								<label class="col-lg-5">Amount</label>

								<div class="col-lg-7 ">

									@if($setData['ssb']->payment_type=='CR')
									 {{ number_format((float)$setData['ssb']->deposit, 2, '.', '')}}
									@else
										{{ number_format((float)$setData['ssb']->withdrawal, 2, '.', '')}}
									@endif

								</div>

							</div>

					  </div>  

					@endif  

				</div>



			

			

			

        </div>

      </div>

    </div>



        



  </div>

</div>

@stop