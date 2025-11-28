@php
$selectedBranch = $selectedBranch;
@endphp
@if($design_type === 4)

<div class="col-md-4">
  <div class="form-group row">
    <label class="col-form-label col-lg-12">Branch</label>
    <div class="col-lg-12 error-msg">
      <div class="">
        <select class="form-control" name="{{$branchName}}" id="branch" title="{{$placeHolder2}}">
          <option value="">---Please Select Branch --- </option>
        </select>
      </div>
    </div>
  </div>
</div>
@elseif($design_type === 5)
<label class="col-form-label col-lg-2">Branch </label>
<div class="col-lg-4 error-msg">
  <select class="form-control" name="{{$branchName}}" id="branch">

  </select>
</div>
@elseif($design_type === 6)
    <div class="col-md-6">
      <div class="form-group row">
        <label class="col-form-label col-lg-12">Branch<sup class="required">*</sup></label>
        <div class="col-lg-12 error-msg">
          <div class="">
            <select class="form-control" name="{{$branchName}}" id="branch" title="{{$placeHolder2}}" required>
              <option value="">---Please Select Branch --- </option>
            </select>
          </div>
        </div>
      </div>
      </div>
    @else

    
    <div class="col-md-4">
      <div class="form-group row">
        <label class="col-form-label col-lg-6">Branch </label>
        <div class="col-lg-6 error-msg">
          <select class="form-control" name="{{$branchName}}" id="branch">

          </select>
        </div>
      </div>
    </div>

@endif


 

