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
							  <h3 class="card-title mb-3">Credit Card Infomation </h3>
							  <div class="form-group row">
								<label class="col-form-label col-lg-3">Card Type<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="card_name" id="card_name" class=" form-control" value="<?php if(isset($credit_details[0]->card_name)) { echo $credit_details[0]->card_name; } ?>" >
								</div>
							  </div>
							  <div class="form-group row">
								<label class="col-form-label col-lg-3">Card Holder Name<sup class="required">*</sup> </label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="card_holder_name" id="card_holder_name" class=" form-control" value="<?php if(isset($credit_details[0]->card_holder_name)) { echo $credit_details[0]->card_holder_name; } ?>" >
								</div>
							  </div>
							  <div class="form-group row">
								<label class="col-form-label col-lg-3">Credit card Number<sup class="required">*</sup></label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="credit_card_number" id="credit_card_number" class="form-control"  value="<?php if(isset($credit_details[0]->credit_card_number)) { echo $credit_details[0]->credit_card_number; } ?>">
								</div>
							  </div>
							  <div class="form-group row">
								<label class="col-form-label col-lg-3">Bank Account Number<sup class="required">*</sup></label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="credit_card_account_number" id="credit_card_account_number" class="form-control"  value="<?php if(isset($credit_details[0]->credit_card_account_number)) { echo $credit_details[0]->credit_card_account_number; } ?>" >
								</div>
							  </div>
							  <div class="form-group row">
								<label class="col-form-label col-lg-3">Credit card Bank<sup class="required">*</sup></label>
								<div class="col-lg-9 error-msg">
								  <input type="text" name="credit_card_bank" id="credit_card_bank" class="form-control"  value="<?php if(isset($credit_details[0]->credit_card_bank)) { echo $credit_details[0]->credit_card_bank; } ?>"> 
								</div>
							  </div>
							  <div class="col-lg-12">
									  <div class="text-right">
									  <input type="hidden" name="credit_card_id" id="credit_card_id" value="<?php if(isset($credit_details[0]->id)) { echo $credit_details[0]->id; } ?>"/>
									  
									  <input type="hidden" name="credit_card_head_id" id="credit_card_head_id" value="<?php if(isset($credit_card_head_id)) { echo $credit_card_head_id; } ?>"/>
									  
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
@include('templates.admin.credit_card.script_list')
@stop