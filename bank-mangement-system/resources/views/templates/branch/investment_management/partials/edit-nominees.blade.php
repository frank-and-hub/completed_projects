<p><h3 class="">First Nominee</h3></p>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="fn_first_name" id="fn_first_name" class="form-control" value="{{ $investments['investmentNomiees'][0]->name }}" readonly="">
  </div>
  <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
  <div class="col-lg-4">
      <select name="fn_relationships" id="fn_relationships" class="form-control" title="Please select something!" disabled="">
        <option value="">Select Relation</option>
        @foreach($relations as $relation)
          <option @if($relation->id==$investments['investmentNomiees'][0]->relation) selected @endif value="{{ $relation->id }}">{{ $relation->name }}</option>
        @endforeach
      </select>
      <input type="hidden" name="fn_relationship" id="fn_relationship" value="{{ $investments['investmentNomiees'][0]->relation }}">
  </div>
  <!-- <label class="col-form-label col-lg-2">Last Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="fn_second_name" id="fn_second_name" class="form-control" value="{{ $investments['investmentNomiees'][0]->second_name }}">
  </div> -->
</div> 

<div class="form-group row">
  <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
  <div class="col-lg-4">
    <div class="input-group nominee-dob">
      <span class="input-group-prepend">
        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
      </span>
       <input type="text" name="fn_dob" id="fn_dob12" class="form-control" value="{{ date('d/m/Y',strtotime($investments['investmentNomiees'][0]->dob) ) }}"
              data-val="fn_age" readonly="">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Age</label>
  <div class="col-lg-4">
    <input type="text" name="fn_age" id="fn_age" class="form-control" value="{{ $investments['investmentNomiees'][0]->age }}" readonly="">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
  <div class="col-lg-4 error-msg">
    <div class="row">
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">
          <input type="radio" id="fn_gender_male" name="fn_gender" class="custom-control-input" @if($investments['investmentNomiees'][0]->gender == 1) checked @else disabled @endif value="1" readonly="">
          <label class="custom-control-label" for="fn_gender_male">Male</label>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">
          <input type="radio" id="fn_gender_female" name="fn_gender" class="custom-control-input" @if($investments['investmentNomiees'][0]->gender == 0) checked @else disabled @endif value="0" readonly="">
          <label class="custom-control-label" for="fn_gender_female">Female</label>
        </div>
      </div>
    </div>
  </div>
  <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="fn_percentage" id="fn_percentage" class="form-control" value="{{ $investments['investmentNomiees'][0]->percentage }}" readonly="">
  </div>
</div>

@if(!empty($investments['investmentNomiees'][1]))
<h3 class="">Second Nominee</h3>
{{-- @if(empty($investments['investmentNomiees'][1]))
<div class="custom-control custom-checkbox mb-3 col-form-label">
<input type="checkbox" id="second_nominee" name="second_nominee" class="custom-control-input"><label class="custom-control-label" for="second_nominee">Yes</label>
</div> 

<div class="form-group row">
  <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_first_name" id="sn_first_name" class="form-control" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="sn_relationship" id="sn_relationship" class="form-control" title="Please select something!">
      <option value="">Select Relation</option>
      @foreach($relations as $relation)
        <option value="{{ $relation->id }}">{{ $relation->name }}</option>
      @endforeach
    </select>
  </div>
  <!-- <label class="col-form-label col-lg-2">Last Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_second_name" id="sn_second_name" class="form-control">
  </div> -->
</div> 

<div class="form-group row">
  <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
  <div class="col-lg-4">
    <div class="input-group nominee-dob">
      <span class="input-group-prepend">
        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
      </span>
       <input type="text" name="sn_dob" id="sn_dob" class="sn_dateofbirth form-control" data-val="sn_age">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Age<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_age" id="sn_age" class="form-control" readonly="">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_percentage" id="sn_percentage" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
  <div class="col-lg-4 error-msg">
    <div class="row">
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">        
          <input type="radio" id="sn_gender_male" name="sn_gender" class="custom-control-input" value="1">
          <label class="custom-control-label" for="sn_gender_male">Male</label>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3  CustomAddGender">
          <input type="radio" id="sn_gender_female" name="sn_gender" class="custom-control-input" value="0">
          <label class="custom-control-label" for="sn_gender_female">Female</label>
        </div>
      </div>
    </div>
  </div>
</div>
@else --}}
<div class="custom-control custom-checkbox mb-3 col-form-label">
<input type="checkbox" id="second_nominee" name="second_nominee" class="custom-control-input" @if($investments['investmentNomiees'][1]) checked @endif disabled><label class="custom-control-label" for="second_nominee">Yes</label>
</div> 

<div class="form-group row">
  <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_first_name" id="sn_first_name" class="form-control" class="form-control" @if($investments['investmentNomiees'][1]) value="{{ $investments['investmentNomiees'][1]->name }}" @endif readonly="">
  </div>
  <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="sn_relationship" id="sn_relationship" class="form-control" title="Please select something!" disabled="">
      <option value="">Select Relation</option>
      @foreach($relations as $relation)
        <option @if($relation->id==$investments['investmentNomiees'][1]->relation) selected @endif value="{{ $relation->id }}">{{ $relation->name }}</option>
      @endforeach
    </select>
  </div>
  <!-- <label class="col-form-label col-lg-2">Last Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_second_name" id="sn_second_name" class="form-control" @if($investments['investmentNomiees'][1]) value="{{ $investments['investmentNomiees'][1]->second_name }}" @endif>
  </div> -->
</div> 

<div class="form-group row">
  <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
  <div class="col-lg-4">
    <div class="input-group nominee-dob">
      <span class="input-group-prepend">
        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
      </span>
       <input type="text" name="sn_dob" id="sn_dob" class="form-control" @if($investments['investmentNomiees'][1]) value="{{ date('d/m/Y',strtotime($investments['investmentNomiees'][1]->dob))
        }}" @endif data-val="sn_age" readonly="">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Age<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_age" id="sn_age" class="form-control" @if($investments['investmentNomiees'][1]) value="{{ $investments['investmentNomiees'][1]->age }}" @endif readonly="">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
  <div class="col-lg-4 error-msg">
    <div class="row">
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">        
          <input type="radio" id="sn_gender_male" name="sn_gender" class="custom-control-input" @if($investments['investmentNomiees'][1] && $investments['investmentNomiees'][1]->gender == 1) checked @else disabled @endif value="1">
          <label class="custom-control-label" for="sn_gender_male">Male</label>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3  CustomAddGender">
          <input type="radio" id="sn_gender_female" name="sn_gender" class="custom-control-input" @if($investments['investmentNomiees'][1] && $investments['investmentNomiees'][1]->gender == 0) checked @else disabled @endif value="0">
          <label class="custom-control-label" for="sn_gender_female">Female</label>
        </div>
      </div>
    </div>
  </div>
  <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_percentage" id="sn_percentage" class="form-control" @if($investments['investmentNomiees'][1]) value="{{ $investments['investmentNomiees'][1]->percentage }}" @endif readonly="">
  </div>
</div>
@endif

