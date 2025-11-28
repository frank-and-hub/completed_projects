
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
          <label class=" col-lg-4">Customber Id</label><label class=" col-lg-1">:</label>
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
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Associate App Status </label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
         @if($memberData->associate_app_status==1)
              Active
            @else
              Inactive
            @endif
          </div>
        </div>
      </div>

    </div>
   
      

    <h6 class="card-title font-weight-semibold ">Associate App Status</h6>
    <div class=" row">
      <div class="col-lg-12 ">
        <div class="form-group row">
          <label class="col-form-label col-lg-3">Status </label>
          <div class="col-lg-5 error-msg">
            @if($memberData->associate_app_status==1)
              Active
            @else
              Inactive
            @endif

          </div>
        </div>
      </div>
    </div>

    <h6 class="card-title font-weight-semibold ">Associate App Status Change To </h6>
    <div class=" row">
      <div class="col-lg-12 ">
        <div class="form-group row">
          <label class="col-form-label col-lg-3">Status </label>
          <div class="col-lg-5 error-msg">
             <select class="form-control select" name="app_status" id="app_status" >
               @if($memberData->associate_app_status==0)
                <option value="1">Active</option> 
                 
                @else

                <option value="0">Inactive</option>
                
                @endif 
            </select>
<input type="hidden" name="member_id" id="member_id" value="{{ $memberData->id }}">
          </div>
        </div>
      </div>
    </div>

   
