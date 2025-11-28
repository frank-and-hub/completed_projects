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
							<h3 class="card-title mb-3">Debit Card Create</h3><br />
							
								<!--<div class="form-group row">
									<label class="col-form-label col-lg-2">
										SSB A/C <sup class="required">*</sup> 
									</label>
									<div class="col-lg-8">
										<input type="text" name="ssb_ac" id="ssb_ac" class=" form-control" />
									</div>
									<div class="col-lg-2 text-right">
										<button type="submit" class="btn btn-primary" id="ssb_submit">Submit</button>
									</div>
								</div>-->
							
							<form action="{!! route('admin.debit-card.debit_card_save') !!}" method="post" enctype="multipart/form-data" id="debit_card_add" name="debit_card_add">
								@csrf
								<input type="hidden" name="ssb_id" id="ssb_id" />
								<input type="hidden" name="member_id" id="member_id" />
								<input type="hidden" name="branch_id_ssb" id="branch_id" />
								<input type="hidden" name="emp_id" id="emp_id" />
								<input type="hidden" name="user_name" id="user_name" />
								<div class="form-group row">
									<label class="col-form-label col-lg-3">
										SSB A/C<sup class="required">*</sup>
									</label>
									<div class="col-lg-9"> 
										<input type="text" name="ssb_ac" id="ssb_ac" class="form-control" />
									</div>
								</div>
								<div class="form-group row" id="show_card_detail">
									<div class="col-lg-3">&nbsp;</div>
									<div class="col-lg-9">
										<div class="alert alert-danger alert-block">  
											<strong>Please enter correct SSB A/C no. !</strong> 
										</div>
									</div>
								</div>
								
								<div id="debit_card_data">
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Created Date</label>
										<div class="col-lg-9">
											<span class=" form-control" id="created_at"></span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Member Code</label>
										<div class="col-lg-9">
											<span class=" form-control" id="member_code"></span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Member Name</label>
										<div class="col-lg-9">
											<span class=" form-control" id="member_name"></span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Member Photo</label>
										<div class="col-lg-9">
											<img id="member_photo" width="50" />
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Branch Code</label>
										<div class="col-lg-9">
											<input type="text" name="branch_code" id="branch_code" readonly="" class="form-control" />
											<!--<span class="form-control" id="branch_code"></span>-->
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Branch Name</label>
										<div class="col-lg-9">
											<span class=" form-control" id="branch_name"></span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Associate Code</label>
										<div class="col-lg-9">
											<span class=" form-control" id="ass_code"></span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">Associate Name</label>
										<div class="col-lg-9">
											<span class=" form-control" id="ass_name"></span>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">SSB Balance</label>
										<div class="col-lg-9">
											<span class=" form-control" id="ssb_bal"></span>
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
													<div class="col-lg-3" id="card_type_new">
														<input type="radio" id="card_new" name="card_type" class="custom-control-input" value="1" checked="checked">
														<label class="custom-control-label" for="card_new">
															New
														</label>
													</div>
													<div class="col-lg-3" id="card_type_reissue">
														<input type="radio" id="card_reissue" name="card_type" class="custom-control-input" value="2">
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
											<input type="text" name="card_no" id="card_no" class=" form-control" />
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
													<option value="<?=$i?>">
														<?=date('M', mktime(0,0,0,$i,1));?>
													</option> <?php
												}?>
											</select>
										</div>
										<div class="col-lg-2 error-msg"> 
											<select class="form-control" name="from_year" id="from_year">
												<option value="">Year</option>
												<?php
												for($i = $prev_year; $i <= $cur_year; $i++){ ?>
													<option value="<?=$i?>"><?=$i?></option>
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
													<option value="<?=$i?>">
														<?=date('M', mktime(0,0,0,$i,1));?>
													</option> <?php
												}?>
											</select>
										</div>
										<div class="col-lg-2 error-msg"> 
											<select class="form-control" name="to_year" id="to_year">
												<option value="">Year</option>
												<?php
												for($i = $cur_year; $i <= $end_year; $i++){ ?>
													<option value="<?=$i?>"><?=$i?></option>
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
											<input type="text" name="card_charge" class=" form-control" id="card_charge" readonly="" />
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Reference Number<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9 error-msg">
											<input type="text" name="ref_no" class=" form-control" id="ref_no" />
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											SSB A/C<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9">
											<span class=" form-control" id="ssb_account_no"></span>
										</div>
									</div>
									
									<div class="form-group row cgst_type"  id="cgst_type">
										<label class="col-form-label col-lg-3" id="cgst"></label>
										<div class="col-lg-9">
											<span class=" form-control" id="cgst_amount"></span>
										</div>
									</div>
									<div class="form-group row cgst_type" >
										<label class="col-form-label col-lg-3" id="sgst"></label>
										<div class="col-lg-9">
											<span class=" form-control" id="sgst_amount"></span>
										</div>
									</div>
								
									<div class="form-group row" id="igst_type">
									<label class="col-form-label col-lg-3" id="igst"></label>
										<div class="col-lg-9">
											<span class=" form-control" id="igst_amount"></span>
										</div>
									</div>
									
									<br />
									<h5 class="card-title mb-3"><strong>Authorized Person Details</strong> </h5>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Employee Code<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9 error-msg">
											<input type="text" name="emp_code" class=" form-control" id="emp_code" />
										</div>
									</div>
									<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Employee Name<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9">
											<input type="text" name="emp_name" class=" form-control" id="emp_name" readonly="" />
										</div>
									</div>
									
									<!--<input type="hidden" name="payment_mode" value="1" />-->
									<!--<div class="form-group row">
										<label class="col-form-label col-lg-3">
											Payment Mode<sup class="required">*</sup>
										</label>
										<div class="col-lg-9 error-msg">
											<select class="form-control" id="payment_mode" name="payment_mode">
												<option value="">Select Payment Mode</option>
												<option value="1" id="ssb_payment_mode">SSB</option>
												<option value="2">CASH</option>
											</select>
										</div>
									</div>
									<div class="form-group row" id="cash">
										<label class="col-form-label col-lg-3">
											Select Branch<sup class="required">*</sup> 
										</label>
										<div class="col-lg-9">
											<select class="form-control" name="branch_id">
												<option value="">Select Branch</option>
												@foreach($br_data as $row)
													<option value="{{$row['id']}}">{{$row['name']}}</option>
												@endforeach
											</select>
										</div>
									</div>-->
									
									<div class="col-lg-12">
										<div class="text-right">
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
    </div>
</div>
@include('templates.admin.debit_card.script_list')
@stop