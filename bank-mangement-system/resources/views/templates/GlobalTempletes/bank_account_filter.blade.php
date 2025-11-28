
@if($design_type === 4)                     
  <div class="col-md-4">
        <div class="form-group row">
            <label class="col-form-label col-lg-12">Bank</label>
            <div class="col-lg-12 error-msg">
                <div class="input-group">
                <select class="form-control " name="{{$bankName}}" id="bank" title="{{$placeHolder2}}">
              
              </select>
                    </div>
            </div>
        </div>
    </div>

  @else 
  <div class="col-md-4">
    <div class="form-group row">
      <label class="col-form-label col-lg-6">Bank </label>
      <div class="col-lg-6 error-msg">
        <select class="form-control " name="{{$bankName}}" id="bank" >
         
        </select>
      </div>
    </div>
  </div>                         
  @endif


