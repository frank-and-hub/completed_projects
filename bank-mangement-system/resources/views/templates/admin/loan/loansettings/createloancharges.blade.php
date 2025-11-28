@extends('templates.admin.master')

@section('content')
<?php
$startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),33));
$currentDatee =  date('d/m/Y',strtotime($startDatee));

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
                        <h6 class="card-title font-weight-semibold">@if($type =="add") Create @else Edit @endif</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-danger"></p>
                        <form action="{{route('admin.loan.loansettings.loancharges.store')}}" method="post" name="loanchargeform" id="loanchargeform">
                            @csrf
							<div class="row">	
								<input type="hidden" name="created_at" class="created_at" value="{{$startDatee}}">
								
								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Type :</label>
										<div class="col-lg-9 error-msg">
											<select class="form-control type" name="type">
												<option value="">Select Type</option>
												
												
												<option value="1" <?php if(old('type')!=''){ ?>@if(old('type') == 1)){ selected } @endif   <?php }elseif(isset($loancharge->type)){ ?>  @if('1' == $loancharge->type)){ selected } @endif   <?php } ?>>File Charge</option>

												<option value="2" <?php if(old('type')!=''){ ?>@if(old('type') == 2)){ selected } @endif   <?php }elseif(isset($loancharge->type)){ ?>  @if('2' == $loancharge->type)){ selected } @endif   <?php } ?>>Insurance Charge</option>
												 
											</select>
										</div>
									</div>
								</div>
								
								
								
								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Loan Type : </label>
										<div class="col-lg-9 error-msg">
											<select class="form-control" id="loan_type" name="loan_type">
												<option value="">----Select Loan Type----</option>

												<option value="1" <?php if(old('loan_type')!=''){ ?>@if(old('loan_type') == 1)){ selected } @endif   <?php }elseif(isset($loancharge->loan_type)){ ?>  @if('1' == $loancharge->loan_type)){ selected } @endif   <?php } ?>>Loan</option>

												<option value="2" <?php if(old('loan_type')!=''){ ?>@if(old('loan_type') == 2)){ selected } @endif   <?php }elseif(isset($loancharge->loan_type)){ ?>  @if('2' == $loancharge->loan_type)){ selected } @endif   <?php } ?>>Group loan</option>

												
											</select>
										</div>
									</div>
								</div>
							
								@if($type =="add")
								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Plan Name: </label>
										<div class="col-lg-9 error-msg">
											<select class="form-control" id="plan_name" name="plan_name">
												
												<option value="">----Select Plan----</option>
												
												
											</select>
										</div>
									</div>
								</div>
								@elseif($type =="edit")
								

								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Plan Name: </label>
										<div class="col-lg-9 error-msg">
											<select class="form-control" id="plan_name" name="plan_name">
												
												<option value="">----Select Plan----</option>
												
												@foreach($loans as $loan)

													@if($loan->loan_category==3)
													<option  data-status = "{{$loan->status}}" value="{{$loan->id}}" <?php if(old('plan_name')!=''){ ?>@if(old('plan_name') == "{{$loan->id}}")){ selected } @endif   <?php }elseif(isset($loan->id)){ ?>  @if($loancharge->plan_name == $loan->id)){ selected } @endif   <?php } ?>>{{$loan->name}}</option>

													
													
													@else
													<option  data-status = "{{$loan->status}}" value="{{$loan->id}}"  <?php if(old('plan_name')!=''){ ?>@if(old('plan_name') == "{{$loan->id}}")){ selected } @endif   <?php }elseif(isset($loan->id)){ ?>  @if($loancharge->plan_name == $loan->id)){ selected } @endif   <?php } ?>>{{$loan->name}}</option>
													
													@endif

												@endforeach
												
											</select>
										</div>
									</div>
								</div>
								@endif

								@if($type =="add")
								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Tenure : </label>
										<div class="col-lg-9 error-msg">
											<select class="form-control" id="tenure" name="tenure">
												<option value="">----Select Tenure----</option>
												
											</select>
											<span class="tenurewarning  text-danger"></span>
											
										</div>
									</div>
								</div>
								@elseif($type =="edit")
								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Tenure : </label>
										<div class="col-lg-9 error-msg">
											<select class="form-control" id="tenure" name="tenure">
												<option value="">----Select Tenure----</option>
												@foreach($tenure as $tenure)

													@if($tenure->loan_id==3)
													<option data-tstatus = "{{$tenure->status}}" value="{{$tenure->id}}" <?php if(old('tenure')!=''){ ?>@if(old('tenure') == "{{$tenure->id}}")){ selected } @endif   <?php }elseif(isset($tenure->id)){ ?>  @if($loancharge->tenure == $tenure->id)){ selected } @endif   <?php } ?>>{{$tenure->name}}</option>

													
													
													@else
													<option data-tstatus = "{{$tenure->status}}" value="{{$tenure->id}}" <?php if(old('tenure')!=''){ ?>@if(old('tenure') == "{{$tenure->id}}")){ selected } @endif   <?php }elseif(isset($tenure->id)){ ?>  @if($loancharge->tenure == $tenure->id)){ selected } @endif   <?php } ?>>{{$tenure->name}}</option>
													@endif

												@endforeach
											</select>
											<span class="tenurewarning  text-danger"></span>
											
										</div>
									</div>
								</div>
								@endif

								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Charge Type : </label>
										<div class="col-lg-9 error-msg">
											
											<select class="form-control charge_type" name="charge_type">
												<option value="">Select Charge Type</option>
												
												<option value="0" <?php if(old('charge_type')!=''){ ?>@if(old('charge_type') == '0')){ selected } @endif   <?php }elseif(isset($loancharge->charge_type)){ ?>  @if('0' == $loancharge->charge_type) selected  @endif   <?php } ?>>Percentage</option>
												

												<option value="1" <?php if(old('charge_type')!=''){ ?>@if(old('charge_type') == 1)){ selected } @endif   <?php }elseif(isset($loancharge->charge_type)){ ?>  @if('1' == $loancharge->charge_type) selected  @endif   <?php } ?>>Fixed</option>
												
											</select>
										</div>
									</div>
								</div>
								
								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Charge : </label>
										<div class="col-lg-9 error-msg">
											
											
											<input type="text" name="charge" id="charge" class="form-control charge"  value="<?php if(old('charge')!=''){ echo old('charge'); }elseif(isset($loancharge->charge)){ echo number_format($loancharge->charge, 2, '.', ''); }?>">
											
										</div>
									</div>
								</div>
								
								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Min Amount :</label>
										<div class="col-lg-9 error-msg">
																					
											<input type="text" name="min_amount" id="min_amount" min="0" class="form-control min_amount" autocomplete="off"  value="<?php if(old('min_amount')!=''){ echo old('min_amount'); }elseif(isset($loancharge->min_amount)){ echo number_format($loancharge->min_amount, 2, '.', ''); }?>">
										</div>
									</div>
								</div>

								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-lg-3 col-form-label">Max Amount :</label>
										<div class="col-lg-9 error-msg">
											
											<input type="text" step="any" name="max_amount" id="max_amount" min="0" class="form-control max_amount" autocomplete="off"  value="<?php if(old('max_amount')!=''){ echo old('max_amount'); }elseif(isset($loancharge->max_amount)){ echo number_format($loancharge->max_amount, 2, '.', '') ; }?>">

											<span id="warning-msg" class="text-danger"></span>
										</div>
									</div>
								</div>
							
								

								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-form-label col-lg-3">Effective from Date: </label>
										<div class="col-lg-9 error-msg">
											<div class="input-group">
												<input type="text" name="effective_from_date" id="effective_from_date"  class="form-control" onkeydown="event.preventDefault()"  value="<?php if(old('effective_from_date')!=''){ echo old('effective_from_date'); }elseif(isset($loancharge->effective_from )){ echo date("d/m/Y", strtotime($loancharge->effective_from)); }?>" autocomplete="off">
											</div>
										</div>
									</div>
								</div>
								
								<div class="form-group col-md-12"> 
									<div class="col-lg-12 page text-center">
										<input type="hidden" name="adm_report_currentdate" id="adm_report_currentdate" class="create_application_date" >
										<input type="hidden" name="id" id="id" value="<?php if(isset($loancharge->id)){ echo $loancharge->id; } ?>"/>
										<button type="submit" class="btn bg-dark submitloancharge" disabled>Submit<i class="icon-paperplane ml-2"></i></button>
										
									</div>
								</div>
								
							</div>
								
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.loan.loansettings.partials.loanchargescript')
@stop
