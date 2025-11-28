<p><h3 class="">First Nominee</h3></p>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="fn_first_name" id="fn_first_name" class="form-control" value="{{ $investments->fn_first_name }}" @if($action != 'edit') readonly="" @endif>
  </div>
  <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
  <div class="col-lg-4">
      <select name="fn_relationships" id="fn_relationships" class="form-control" title="Please select something!" @if($action != 'edit') disabled="" @endif>
        <option value="">Select Relation</option>
        @foreach($relations as $relation)
          <option @if($relation->id==$investments->fn_relationship) selected @endif value="{{ $relation->id }}">{{ $relation->name }}</option>
        @endforeach
      </select>
      <input type="hidden" name="fn_relationship" id="fn_relationship" value="{{ $investments->fn_relationship }}">
  </div>
</div> 

<div class="form-group row">
  <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
  <div class="col-lg-4">
    <div class="input-group nominee-dob">
      <span class="input-group-prepend">
        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
      </span>
       <input type="text" name="fn_dob" id="fn_dob" class="form-control" value="{{ $investments->fn_dob }}"
              data-val="fn_age" @if($action != 'edit') readonly="" @endif>
    </div>
  </div>
  <label class="col-form-label col-lg-2">Age</label>
  <div class="col-lg-4">
    <input type="text" name="fn_age" id="fn_age" class="form-control" value="{{ $investments->fn_age }}" readonly="">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
  <div class="col-lg-4 error-msg">
    <div class="row">
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">
          <input type="radio" id="fn_gender_male" name="fn_gender" class="custom-control-input" @if($investments->fn_gender == 1) checked @endif value="1"  @if($action != 'edit') readonly="" @endif>
          <label class="custom-control-label" for="fn_gender_male">Male</label>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 CustomAddGender">
          <input type="radio" id="fn_gender_female" name="fn_gender" class="custom-control-input" @if($investments->fn_gender == 0) checked @endif value="0"  @if($action != 'edit') readonly="" @endif>
          <label class="custom-control-label" for="fn_gender_female">Female</label>
        </div>
      </div>
    </div>
  </div>
  <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="fn_percentage" id="fn_percentage" data-id="sn_percentage" class="form-control nominee-percentage" value="{{ $investments->fn_percentage }}" @if($action != 'edit') readonly="" @endif>
    <label id="percentage-error" class="error"></label>
  </div>
</div>

<h3 class="">Second Nominee</h3>
@if($investments->second_nominee_add == 0)
  <input type="button" name="second_nominee" data-val="second_nominee_add" data-class="second-nominee" id="second_nominee" value="Add Nominee" class="btn btn-primary valid second-nominee-input add-second-nominee second-nominee-botton" aria-invalid="false">
  <input type="hidden" name="second_nominee_add" id="second_nominee_add" value="0">
  <div class="form-group row second-nominee" style="display: none;">
    <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="sn_first_name" id="sn_first_name" class="form-control" class="form-control">
    </div>
    <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="sn_relationship" id="sn_relationship" class="form-control" title="Please select something!"  @if($action != 'edit') disabled="" @endif>
        <option value="">Select Relation</option>
        @foreach($relations as $relation)
          <option value="{{ $relation->id }}">{{ $relation->name }}</option>
        @endforeach
      </select>
    </div>
  </div> 

  <div class="form-group row second-nominee" style="display: none;">
    <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
    <div class="col-lg-4">
      <div class="input-group nominee-dob">
        <span class="input-group-prepend">
          <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
        </span>
         <input type="text" name="sn_dob" id="sn_dob" class="sn_dateofbirth form-control" data-val="sn_age" @if($action != 'edit') readonly="" @endif>
      </div>
    </div>
    <label class="col-form-label col-lg-2">Age<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="sn_age" id="sn_age" class="form-control" @if($action != 'edit') readonly="" @endif>
    </div>
  </div>

  <div class="form-group row second-nominee" style="display: none;">    
    <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
    <div class="col-lg-4 error-msg">
      <div class="row">
        <div class="col-lg-4">
          <div class="custom-control custom-radio mb-3 CustomAddGender">        
            <input type="radio" id="sn_gender_male" name="sn_gender" class="custom-control-input" checked="" value="1" @if($action != 'edit') readonly="" @endif>
            <label class="custom-control-label" for="sn_gender_male">Male</label>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="custom-control custom-radio mb-3  CustomAddGender">
            <input type="radio" id="sn_gender_female" name="sn_gender" class="custom-control-input" value="0" @if($action != 'edit') readonly="" @endif>
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
@else
  <input type="button" name="second_nominee" data-val="second_nominee_add" data-class="second-nominee" id="second_nominee" value="Remove Nominee" class="btn btn-primary valid second-nominee-input remove-second-nominee second-nominee-botton" aria-invalid="false">
  <input type="hidden" name="second_nominee_add" id="second_nominee_add" value="1">
  <div class="form-group row second-nominee">
    <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="sn_first_name" id="sn_first_name" class="form-control" class="form-control" value="{{ $investments->sn_first_name }}"  @if($action != 'edit') readonly="" @endif>
    </div>
    <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="sn_relationship" id="sn_relationship" class="form-control" title="Please select something!"  @if($action != 'edit') disabled="" @endif>
        <option value="">Select Relation</option>
        @foreach($relations as $relation)
          <option @if($relation->id==$investments->sn_relationship) selected @endif value="{{ $relation->id }}">{{ $relation->name }}</option>
        @endforeach
      </select>
    </div>
  </div> 

  <div class="form-group row second-nominee">
    <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
    <div class="col-lg-4">
      <div class="input-group nominee-dob">
        <span class="input-group-prepend">
          <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
        </span>
         <input type="text" name="sn_dob" id="sn_dob" class="form-control" value="{{ $investments->sn_dob
          }}" data-val="sn_age" @if($action != 'edit') readonly="" @endif>
      </div>
    </div>
    <label class="col-form-label col-lg-2">Age<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="sn_age" id="sn_age" class="form-control" value="{{ $investments->sn_age }}" readonly="">
    </div>
  </div>

  <div class="form-group row second-nominee">
    <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
    <div class="col-lg-4 error-msg">
      <div class="row">
        <div class="col-lg-4">
          <div class="custom-control custom-radio mb-3 CustomAddGender">        
            <input type="radio" id="sn_gender_male" name="sn_gender" @if($investments->sn_gender == 1) checked @endif class="custom-control-input" value="1">
            <label class="custom-control-label" for="sn_gender_male">Male</label>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="custom-control custom-radio mb-3  CustomAddGender">
            <input type="radio" id="sn_gender_female" name="sn_gender" @if($investments->sn_gender == 0) checked @endif class="custom-control-input" value="0">
            <label class="custom-control-label" for="sn_gender_female">Female</label>
          </div>
        </div>
      </div>
    </div>
    <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="sn_percentage" id="sn_percentage" data-id="fn_percentage" class="form-control nominee-percentage" value="{{ $investments->sn_percentage }}" @if($action != 'edit') readonly="" @endif>
    </div>
  </div>
@endif

