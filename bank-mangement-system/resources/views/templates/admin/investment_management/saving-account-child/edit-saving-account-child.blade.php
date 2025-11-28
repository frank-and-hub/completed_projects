<div class="form-group row">
  <label class="col-form-label col-lg-2">Amount</label>
  <div class="col-lg-4">
  	<div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt" value="{{$investments['deposite_amount']}}" disabled="">
  </div>
</div>  
<div class="form-group row">
  <label class="col-form-label col-lg-2">Date of Birth<sup>*</sup></label>
  <div class="col-lg-4">
    <div class="input-group relative-dob">
      <span class="input-group-prepend">
        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
      </span>
       <input type="text" name="re_member_dob" id="re_member_dob" class="re_member_dob form-control" value="{{ date('d/m/Y',strtotime($investments['re_dob']) ) }}" data-val="ex_re_age" disabled="">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Age</label>
  <div class="col-lg-4">
    <input type="text" name="ex_re_age" id="ex_re_age" class="form-control" readonly="" value="{{$re_age}}" disabled="">
    </div>  
</div>
<div class="form-group row">
  <label class="col-form-label col-lg-2">Son/Daughter Name<sup>*</sup></label> 
  <div class="col-lg-4">
    <input type="text" name="ex_re_name" id="ex_re_name" class="form-control" value="{{$investments['re_name']}}" disabled="">
    </div>
  <label class="col-form-label col-lg-2">Relation with Guardians</label>
  <div class="col-lg-4">
    <input type="text" name="ex_re_guardians" id="ex_re_guardians" value="{{$investments['re_guardians']}}" class="form-control" disabled="">
  </div>
</div>
<div class="form-group row">
  <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
  <div class="col-lg-4 error-msg">
    <div class="row">
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">
        <input type="radio" id="ex_re_gender_male" name="ex_re_gender" class="custom-control-input" value="1" @if($investments['re_gender'] == 1) checked @else disabled @endif value="1"  @if($action != 'edit') readonly="" @endif>
        <label class="custom-control-label" for="ex_re_gender_male">Male</label>  
        
        </div>
      </div>
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">
          <input type="radio" id="ex_re_gender_female" name="ex_re_gender" class="custom-control-input" value="0" @if($investments['re_gender'] == 0) checked @else disabled @endif value="1"  @if($action != 'edit') readonly="" @endif>
          <label class="custom-control-label" for="ex_re_gender_female">Female</label>

        </div>
      </div>
      </div>
      
  </div>
  <div class="col-lg-4">&nbsp;</div>
</div>

<!-- <div class="row">
	<label class="col-form-label col-lg-2">Primary account</label>
	<div class="col-lg-4">
		<div class="custom-control custom-checkbox mb-3 col-form-label">
			<input type="checkbox" id="primary_account" name="primary_account" class="custom-control-input" value="1">
			<input type="hidden" id="hidden_primary_account" name="hidden_primary_account" class="custom-control-input" value="0">
			<label class="custom-control-label" for="primary_account">Yes</label>
		</div>
	</div>
</div>  -->

@include('templates.branch.investment_management.partials.edit-nominees')