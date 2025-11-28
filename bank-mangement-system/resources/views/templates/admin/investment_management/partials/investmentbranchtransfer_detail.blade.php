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

		<div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Member Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 "> {{$investData['member']->first_name}} {{$investData['member']->last_name}}  </div>
        </div>
      </div> 
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Customer ID </label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 "> {{ $investData['member']->member_id }}  </div>
        </div>
      </div> 
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Member Code </label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 "> {{ $investData['memberCompany']->member_id }}  </div>
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
            {{$investData['member']->mobile_no}} 
          </div>
        </div>
      </div>  

      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Branch Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		 {{$investData['branch']->name}} 
		 </div>
        </div>
      </div>
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Branch Code</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
           {{$investData['branch']->branch_code}} 
          </div>
        </div>
      </div>

      
	  
    </div>
<h6 class="card-title font-weight-semibold ">Branch Transfer</h6>
 <div class=" row">
      
   
         <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Branch</label><label class=" col-lg-1">:</label>
          <div class="col-lg-5 error-msg">
			  <select class="form-control" id="branch_id" name="branch_id">
              <option value=""  >Select Branch</option>
                   @foreach( $branch as $k =>$val )
				   @if($val->id!=$investData->branch_id)
                        <option value="{{ $val->id }}" >{{ $val->name }}({{$val->branch_code}})</option> 
					@endif
                    @endforeach
           </select>
		 </div>
		  <input type="hidden" name="old_branch_id" id="old_branch_id" value="{{$investData->branch_id}}"> 
		   <input type="hidden" name="investment_id" id="investment_id" value="{{$investData->id}}">
		   <input type="hidden" name="plan_id" id="plan_id" value="{{$investData['plan']->id}}">
        </div>
      </div>
	</div>

    
 