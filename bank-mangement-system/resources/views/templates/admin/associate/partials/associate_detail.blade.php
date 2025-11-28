
    <h6 class="card-title font-weight-semibold ">Member's Personal Detail</h6>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Join Date</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{ date("d/m/Y", strtotime($memberData->associate_join_date)) }}
            
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Customer Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 "> {{ $memberData->member_id }}  </div>
        </div>
      </div>  
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Associate Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 "> {{ $memberData->associate_no }}  </div>
        </div>
      </div>   
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4"> Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$memberData->first_name}} {{$memberData->last_name}} 
            
          </div>
        </div>
      </div> 
    
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-4">Mobile No</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$memberData->mobile_no}} 
          </div>
        </div>
      </div>      
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Carder</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{ getCarderName($memberData->current_carder_id) }} 
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Address</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$memberData->address}},{{$memberData->village}}, {{ getDistrictName($memberData->district_id) }},{{ getCityName($memberData->city_id) }},{{ getStateName($memberData->state_id) }},{{$memberData->pin_code}} 
          </div>
        </div>
      </div> 


      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Senior Code</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
           @if($memberData->associate_senior_id==0)   Admin @else {{ $memberData->associate_senior_code }} @endif
          </div>
        </div>
      </div> 


      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Senior Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
          {{ getSeniorData($memberData->associate_senior_id,'first_name') }}  {{ getSeniorData($memberData->associate_senior_id,'last_name') }}
          </div>
        </div>
      </div> 
      @if ( $memberData->associate_senior_code != "9999999")
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Senior Carder</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
          {{ getCarderName(getSeniorData($memberData->associate_senior_id,'current_carder_id')) }}
          </div>
        </div>
      </div>
      @endif
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Associate Status </label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
         @if($memberData->associate_status==1)
              Active
            @else
              Inactive
            @endif
          </div>
        </div>
      </div>

    </div>
    
        @if($type=='status')

    <h6 class="card-title font-weight-semibold ">Associate Status</h6>
    <div class=" row">
      <div class="col-lg-12 ">
        <div class="form-group row">
          <label class="col-form-label col-lg-3">Status </label>
          <div class="col-lg-5 error-msg">
            @if($memberData->associate_status==1)
              Active
            @else
              Inactive
            @endif

          </div>
        </div>
      </div>
    </div>

    <h6 class="card-title font-weight-semibold ">Associate Status Change To </h6>
    <div class=" row">
      <div class="col-lg-12 ">
        <div class="form-group row">
          <label class="col-form-label col-lg-3">Status </label>
          <div class="col-lg-5 error-msg">
             <select class="form-control select" name="current_status" id="current_status" >
               @if($memberData->associate_status==0)
                <option value="1">Active</option> 
                <input type="hidden" name="old_status" id="old_status" value="0" >
                @else

                <option value="0">Inactive</option>
                <input type="hidden" name="old_status" id="old_status" value="1" >
                @endif 
            </select>

          </div>
        </div>
      </div>
    </div>

    @endif
    @if($type=='senior')

    <h6 class="card-title font-weight-semibold ">Associate Carder</h6>
    <div class=" row">
      <div class="col-lg-4">
        <div class="form-group row">
          <label class="col-form-label col-lg-4">Carder</label>
          <div class="col-lg-8 error-msg"> 
            {{ getCarderName($memberData->current_carder_id) }} 
            <input type="hidden" name="associate_carder" id="associate_carder" value="{{ $memberData->current_carder_id }}">

          </div>
        </div>
      </div>
    </div>

    <h6 class="card-title font-weight-semibold ">Old Associate Senior Details</h6>
    <div class=" row">
      <div class="col-lg-4">
        <div class="form-group row">
          <label class="col-form-label col-lg-4">Senior Code</label>
          <div class="col-lg-8 error-msg"> 

            <input type="text" class="form-control" name="old_senior_code" id="old_senior_code" value="{{ getSeniorData($memberData->associate_senior_id,'associate_no') }}" readonly> 
            <input type="hidden" name="old_senior_id" id="old_senior_id" value="{{ $memberData->associate_senior_id }}" >

          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="form-group row">
          <label class="col-form-label col-lg-4">Senior Name</label>
          <div class="col-lg-8 error-msg">
            <input type="text" class="form-control" name="old_senior_name" id="old_senior_name" value="{{ getSeniorData($memberData->associate_senior_id,'first_name') }}  {{ getSeniorData($memberData->associate_senior_id,'last_name') }}" readonly>  

          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="form-group row">
          <label class="col-form-label col-lg-4">Senior Carder</label>
          <div class="col-lg-8 error-msg">

            <input type="text" class="form-control" name="old_senior_carder" id="old_senior_carder" value="{{ getCarderName(getSeniorData($memberData->associate_senior_id,'current_carder_id')) }}" readonly> 

          </div>
        </div>
      </div>
    </div>


    @endif
    @if($type=='upgrade' || $type=='downgrade')
   <h6 class="card-title font-weight-semibold ">Current carder</h6>
    <div class=" row">
      <div class="col-lg-12 ">
        <div class="form-group row">
          <label class="col-form-label col-lg-3">Carder </label>
          <div class="col-lg-5 error-msg">
            
            <select class="form-control select" name="current_carder_id1" id="current_carder_id1" disabled="disabled">
                <option value="{{ $memberData->current_carder_id }}">
                  {{ getCarderName($memberData->current_carder_id) }}
                </option> 
            </select>


        </div>
        </div>
      </div>
      @endif
      <div class=" row">
      <div class="col-lg-12 "> 
          
            
                        <input type="hidden" name="current_carder_id" id="current_carder_id" value="{{ $memberData->current_carder_id }}" readonly="readonly">
           <input type="hidden" name="member_id" id="member_id" value="{{ $memberData->id }}">
           <input type="hidden" name="branch_id" id="branch_id" value="{{ $memberData->associate_branch_id }}">
           <input type="hidden" name="associate_senior_id" id="associate_senior_id" value="{{ $memberData->associate_senior_id }}">
           <input type="hidden" name="senior_current_carder_id" id="senior_current_carder_id" value="{{ getSeniorData($memberData->associate_senior_id,'current_carder_id') }}">

        
        </div>
      </div>
