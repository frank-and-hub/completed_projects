    <input type="hidden" name="new_associate_senior_id" id="new_associate_senior_id" class="form-control"  value="{{ $memberData->id }}">

    <input type="hidden" name="new_associate_senior_carder" id="new_associate_senior_carder" class="form-control"  value="{{ $memberData->current_carder_id }}">
    
    <h6 class="card-title font-weight-semibold ">Senior Associate's Detail</h6>
    <div class=" row">   
    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Customer ID</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 "> {{ $memberData->member_id }}  </div>
        </div>
      </div>     
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Associate Code</label><label class=" col-lg-1">:</label>
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

<!--
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
    -->
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
     
 