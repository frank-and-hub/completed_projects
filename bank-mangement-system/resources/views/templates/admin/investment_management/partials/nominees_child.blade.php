<h3 class="">First Nominee</h3>  
<div class="custom-control custom-checkbox mb-3 col-form-label">
<input type="checkbox" id="same_as_registered_nominee" name="same_as_registered_nominee" class="custom-control-input">
<label class="custom-control-label" for="same_as_registered_nominee">Yes</label>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="fn_first_name" id="fn_first_name" class="form-control">
    <input type="hidden" name="reg_nom_fn_first_name" id="reg_nom_fn_first_name" value="@if(count($member)) {{ $member[0]->name }} @endif">
  </div>
  <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
  <div class="col-lg-4">
      <select name="fn_relationship" id="fn_relationship" class="form-control" title="Please select something!" required>
        <option value="0">Select Relation</option>
        @foreach($relations as $relation)
          <option value="{{ $relation->id }}">{{ $relation->name }}</option>
        @endforeach
      </select>
      <input type="hidden" name="reg_nom_fn_relationship" id="reg_nom_fn_relationship" value="@if(count($member)) {{ $member[0]->relation }} @endif">
  </div>
  {{-- <label class="col-form-label col-lg-2">Last Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="fn_second_name" id="fn_second_name" class="form-control">
    <input type="hidden" name="reg_nom_fn_second_name" id="reg_nom_fn_second_name" value="@if(count($member)) {{ $member[0]->first_name }} @endif">
  </div> --}}
</div> 


<div class="form-group row">
  <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
  <div class="col-lg-4">
    <div class="input-group nominee-dob">
      <span class="input-group-prepend">
        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
      </span>
       <input type="text" name="fn_dob" id="fn_dob" class="fn_dateofbirth form-control" data-val="fn_age">
       <input type="hidden" name="reg_nom_fn_dob" id="reg_nom_fn_dob" value="@if(count($member)) {{ date("d/m/Y", strtotime( str_replace('-','/', $member[0]->dob)) )
        }}
       @endif">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Age</label>
  <div class="col-lg-4">
    <input type="text" name="fn_age" id="fn_age" class="form-control" readonly="">
    <input type="hidden" name="reg_nom_fn_age" id="reg_nom_fn_age" value="@if(count($member)) {{ $member[0]->age }} @endif">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
  <div class="col-lg-4 error-msg">
    <div class="row">
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">
          <input type="radio" id="fn_gender_male" name="fn_gender" class="custom-control-input" value="1">
          <label class="custom-control-label" for="fn_gender_male">Male</label>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">
          <input type="radio" id="fn_gender_female" name="fn_gender" class="custom-control-input" value="0">
          <label class="custom-control-label" for="fn_gender_female">Female</label>
        </div>
      </div>
      <input type="hidden" name="reg_nom_fn_gender" id="reg_nom_fn_gender" value="@if(count($member)) {{ $member[0]->gender }} @endif">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="fn_percentage" id="fn_percentage" data-id="sn_percentage" class="form-control nominee-percentage">
    <label id="percentage-error" class="error"></label>
  </div>
</div>

<h3 class="">Second Nominee </h3>
<input type="button" name="second_nominee" data-val="second_nominee_add" data-class="second-nominee" id="second_nominee" value="Add Nominee" class="btn btn-primary valid second-nominee-input add-second-nominee second-nominee-botton" aria-invalid="false">
{{-- <div class="custom-control custom-checkbox mb-3 col-form-label">
<input type="checkbox" id="second_nominee" name="second_nominee" data-class="second-nominee" class="custom-control-input">
<label class="custom-control-label" for="second_nominee">Yes</label>
</div> --}}
<input type="hidden" name="second_nominee_add" id="second_nominee_add" value="0">
                     

<div class="form-group row second-nominee" style="display: none;">
  <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_first_name" id="sn_first_name" class="form-control">
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

<div class="form-group row second-nominee" style="display: none;">
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

<div class="form-group row second-nominee" style="display: none;">
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
        <div class="custom-control custom-radio mb-3 CustomAddGender">
          <input type="radio" id="sn_gender_female" name="sn_gender" class="custom-control-input" value="0">
          <label class="custom-control-label" for="sn_gender_female">Female</label>
        </div>
      </div>
    </div>
  </div>
  <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="sn_percentage" id="sn_percentage" data-id="fn_percentage" class="form-control nominee-percentage">
  </div>
</div>
