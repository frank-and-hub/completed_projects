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
			<?php
			$cur_year = date('Y');
			$end_year = $cur_year+9;
			
			$prev_year = $cur_year-5;
			
			?>

			<div class="row">
				<div class="col-md-12">
					<div class="card bg-white" >
						<div class="card-body">
							<h3 class="card-title mb-3">Debit Card Information </h3><br />

							<form action="{!! route('admin.debit-card.debit_card_update') !!}" method="post" enctype="multipart/form-data" id="debit_card_add" name="debit_card_add">
								@csrf
								<input type="hidden" name="ssb_id" id="ssb_id" value="<?=$debit_details[0]->ssb_id?>" />
								<input type="hidden" name="member_id" id="member_id" value="<?=$debit_details[0]->member_id?>" />
								<input type="hidden" name="branch_id_ssb" id="branch_id" value="<?=$debit_details[0]->branch_id?>" />
								<input type="hidden" name="table_id" value="<?=$debit_details[0]->id?>" />
								
								<input type="hidden" name="emp_id" id="emp_id" value="<?php if(isset($debit_details[0]->emp_id)){ echo $debit_details[0]->emp_id; }?>" />
								
								<div class="form-group row">
									<label class="col-form-label col-lg-3">
										SSB A/C<sup class="required">*</sup>
									</label>
									<div class="col-lg-9"> 
										<input type="text" name="ssb_ac" id="ssb_ac" class="form-control" value="<?=$debit_details[0]->account_no?>" readonly="" />
									</div>
								</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Created Date</label>
										<div class="col-lg-9">
											<span class=" form-control" id="created_at"> <?php
											if(isset($debit_details[0]->create_date)){ 
												echo $debit_details[0]->create_date; 
											} ?>
											</span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Member Code</label>
										<div class="col-lg-9">
											<span class=" form-control" id="member_code"> <?php
											if(isset($debit_details[0]->member_code)){ 
												echo $debit_details[0]->member_code; 
											} ?>
											</span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Member Name</label>
										<div class="col-lg-9">
											<span class=" form-control" id="member_name"> <?php
											if(isset($debit_details[0]->first_name)){ 
												echo $debit_details[0]->first_name; 
											} ?>
											</span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Member Photo</label>
										<div class="col-lg-9">
											<img id="member_photo" src="{{url('/')}}/asset/profile/<?php echo $debit_details[0]->photo?>" width="50" />
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Branch Code</label>
										<div class="col-lg-9">
											<span class=" form-control" id="branch_code"> <?php
											if(isset($debit_details[0]->branch_code)) { 
												echo $debit_details[0]->branch_code; 
											} ?>
											</span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Branch Name</label>
										<div class="col-lg-9">
											<span class=" form-control" id="branch_name"> <?php
											if(isset($debit_details[0]->branch_name)){ 
												echo $debit_details[0]->branch_name; 
											} ?>
											</span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Associate Code</label>
										<div class="col-lg-9">
											<span class=" form-control" id="ass_code"> <?php
											if(isset($debit_details[0]->ass_code)){ 
												echo $debit_details[0]->ass_code; 
											} ?>
											</span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Associate Name</label>
										<div class="col-lg-9">
											<span class=" form-control" id="ass_name"> <?php
											if(isset($debit_details[0]->ass_name)){ 
												echo $debit_details[0]->ass_name; 
											} ?>
											</span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">SSB Balance</label>
										<div class="col-lg-9">
											<span class=" form-control" id="ssb_bal"> <?php
											if(isset($debit_details[0]->opening_balance)){ 
												echo $debit_details[0]->opening_balance; 
											} ?>
											</span>
										</div>
									</div>
									
									<br />
									<h5 class="card-title mb-3"><strong>Assign Card</strong> </h5>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Card Type <sup class="required">*</sup> 
										</label>
										<div class="col-lg-9 error-msg">
											<div class="custom-control custom-radio mb-3 ">
												<div class="row">
													<div class="col-lg-3">
														<input type="radio" id="card_new" name="card_type" class="custom-control-input" value="1" <?php if($debit_details[0]->card_type == 1){ ?> checked="checked" <?php } else{?>  disabled="disabled" <?php } ?> />
														<label class="custom-control-label" for="card_new">
															New
														</label>
													</div>
													<div class="col-lg-3">
														<input type="radio" id="card_reissue" name="card_type" class="custom-control-input" value="2" <?php if($debit_details[0]->card_type == 2){ ?> checked="checked" <?php }  else{?>  disabled="disabled" <?php } ?> />
														<label class="custom-control-label" for="card_reissue">
															Reissue
														</label>
													</div>
												</div>
                      						</div>
										</div>
									</div>
									
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Card Number <sup class="required">*</sup> 
										</label>
										<div class="col-lg-9 error-msg">
											<input type="text" name="card_no" id="card_no" class=" form-control"  value="<?php if(isset($debit_details[0]->card_no)) { echo $debit_details[0]->card_no; } ?>" />
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Valid From<sup class="required">*</sup> 
										</label>
										<div class="col-lg-2 error-msg"> 
											<select class="form-control" name="from_month" id="from_month">
												<option value="">Month</option>
												<?php
												for($i = 1; $i <= 12; $i++){ ?>
													<option value="<?=$i?>" <?php if($debit_details[0]->valid_from_month == $i) { ?> selected="selected" <?php } ?>><?=date('M', mktime(0,0,0,$i,1));?></option>
												<?php
												}?>
											</select>
										</div>
										<div class="col-lg-2 error-msg"> 
											<select class="form-control" name="from_year" id="from_year">
												<option value="">Year</option>
												<?php
												for($i = $prev_year; $i <= $cur_year; $i++){ ?>
													<option value="<?=$i?>" <?php if($debit_details[0]->valid_from_year == $i) { ?> selected="selected" <?php } ?>><?=$i?></option>
												<?php
												}?>
											</select>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Valid To<sup class="required">*</sup> 
										</label>
										<div class="col-lg-2 error-msg"> 
											<select class="form-control" name="to_month" id="to_month">
												<option value="">Month</option>
												<?php
												for($i = 1; $i <= 12; $i++){ ?>
													<option value="<?=$i?>" <?php if($debit_details[0]->valid_to_month == $i) { ?> selected="selected" <?php } ?>><?=date('M', mktime(0,0,0,$i,1));?></option>
												<?php
												}?>
											</select>
										</div>
										<div class="col-lg-2 error-msg"> 
											<select class="form-control" name="to_year" id="to_year">
												<option value="">Year</option>
												<?php
												for($i = $cur_year; $i <= $end_year; $i++){ ?>
													<option value="<?=$i?>" <?php if($debit_details[0]->valid_to_year == $i) { ?> selected="selected" <?php } ?>><?=$i?></option>
												<?php
												}?>
											</select>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Debit Card Charge<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9 error-msg">
											<input type="text" name="card_charge" class=" form-control" id="card_charge" readonly="" value="<?php if(isset($debit_details[0]->card_charge)) { echo $debit_details[0]->card_charge; } ?>" />
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Reference Number<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9 error-msg">
											<input type="text" name="ref_no" class=" form-control" id="ref_no" value="<?php if(isset($debit_details[0]->reference_no)) { echo $debit_details[0]->reference_no; } ?>" />
										</div>
									</div>
									<?php if($debit_details[0]->payment_mode == 2) { ?>
										<div class="form-group row">
											<label class="col-form-label col-lg-3">
												Select Branch<sup class="required">*</sup> 
											</label>
											<div class="col-lg-9">
												<select class="form-control" name="branch_id">
													<option value="">Select Branch</option>
													@foreach($br_data as $row)
														<option value="{{$row['id']}}" <?php if($debit_details[0]->branch_id == $row['id']) { ?> selected="selected" <?php } ?>>{{$row['name']}}</option>
													@endforeach
												</select>
											</div>
										</div> <?php
									}
									else{ ?> 
										<div class="form-group row">
											<label class="col-form-label col-lg-3">
												SSB A/C<sup class="required">*</sup> 
											</label>
											<div class="col-lg-9">
												<span class=" form-control" id="ssb_account_no"> <?php
													if(isset($debit_details[0]->account_no)){ 
														echo $debit_details[0]->account_no; 
													} ?>
												</span>
											</div>
										</div> <?php
									} ?>
									@if($gstAmount > 0)
										@if($IntraState == true)
									<div class="form-group row"  >
										<label class="col-form-label col-lg-3" id="cgst" >CGST {{$percentage/2}}% Charge</label>
										<div class="col-lg-9">
											<span class=" form-control" id="cgst_amount">{{$gstAmount}}</span>
										</div>
									</div>
									<div class="form-group row cgst_type" >
										<label class="col-form-label col-lg-3" id="sgst">SGST {{$percentage/2}}% Charge</label>
										<div class="col-lg-9">
											<span class=" form-control" id="sgst_amount">{{$gstAmount}}</span>
										</div>
									</div>
									@else
									<div class="form-group row" id="">
									<label class="col-form-label col-lg-3" id="">IGST {{$percentage}}% Charge</label>
										<div class="col-lg-9">
											<span class=" form-control" id="">{{$gstAmount}}</span>
										</div>
									</div>
									@endif
									@endif
									<br />
									<h5 class="card-title mb-3"><strong>Authorized Person Details</strong> </h5>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Employee Code<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9 error-msg">
											<input type="text" name="emp_code" class=" form-control" id="emp_code" value="<?php if(isset($debit_details[0]->employee_code)) { echo $debit_details[0]->employee_code; } ?>" />
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Employee Name<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9">
											<input type="text" name="emp_name" class=" form-control" id="emp_name" readonly="" value="<?php if(isset($debit_details[0]->employee_name)) { echo $debit_details[0]->employee_name; } ?>" />
										</div>
									</div>
									<!--<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Payment Mode<sup class="required">*</sup>
										</label>
										<div class="col-lg-9 error-msg">
											<select class="form-control" id="payment_mode" name="payment_mode">
												<option value="">Select Payment Mode</option> <?php 
												if($debit_details[0]->payment_mode == 1) { ?>
													<option value="1" selected="selected">SSB</option> <?php
												} 
												else { ?>
													<option value="2" selected="selected">CASH</option> <?php
												} ?>
											</select>
										</div>
									</div>-->
									
									<div class="col-lg-12">
										<div class="text-right">
											<button type="submit" class="btn btn-primary">Submit</button>
										</div>
									</div>
								
								
							</form>
						</div>
					</div>
				</div>
			</div> 
		</div>	
    </div>
</div>
@include('templates.admin.debit_card.script_list')
@stop