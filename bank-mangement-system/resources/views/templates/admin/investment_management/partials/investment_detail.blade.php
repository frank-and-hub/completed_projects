    <input type="hidden" name="investment_id" id="investment_id" class="form-control"  value="{{ $investData->id }}"> 
    
    <h6 class="card-title font-weight-semibold ">Investment's Detail</h6>
    <div class=" row">      
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Account Number</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 "> {{ $investData->account_number }}  </div>
        </div>
      </div>   
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Plan Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 "> {{ $investData['plan']->name }}  </div>
        </div>
      </div> 
      <!--<div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-4">Customer ID</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$investData['associateMember']->member_id}} 
          </div>
        </div>
      </div>-->
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-4">Customer Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$investData['member']->first_name . ' ' . $investData['member']->last_name}} 
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-4">Associate Code</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$investData['associateMember']->associate_no}} 
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4"> Associate Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$investData['associateMember']->first_name}} {{$investData['associateMember']->last_name}} 
            
          </div>
        </div>
      </div> 
    
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-4">Mobile No</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$investData['associateMember']->mobile_no}} 
          </div>
        </div>
      </div>      
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Carder</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{ getCarderName($investData['associateMember']->current_carder_id) }} 

          </div>
        </div>
      </div> 


      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Associate Status </label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
         @if($investData['member']->associate_status==1)
              Active
            @else
              Inactive
            @endif
          </div>
        </div>
      </div>
    </div>
    <h6 class="card-title font-weight-semibold ">Associate's Detail</h6>
    <div class="row">

      <div class="col-lg-6 ">
        <div class="form-group row">
          <label class="col-form-label col-lg-4">Current Associate </label>
           <div class="col-lg-7 error-msg">
            <input type="text" name="old_associate_code" id="old_associate_code" class="form-control"  readonly="" value="{{$investData['associateMember']->associate_no}} ">
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class="form-group row">
          <label class="col-form-label col-lg-4">Current Associate </label>
           <div class="col-lg-7 error-msg">
            <input type="text" name="old_associate_name" id="old_associate_name" class="form-control"  readonly="" value="{{$investData['associateMember']->first_name}} {{$investData['associateMember']->last_name}} ">

            <input type="hidden" name="old_associate_id" id="old_associate_id" class="form-control"  readonly="" value="{{$investData->associate_id}} ">
          </div>
        </div>
      </div>
    </div>     
     

    
 