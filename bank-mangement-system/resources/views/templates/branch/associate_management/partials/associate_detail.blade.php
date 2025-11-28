@if(($memberData->current_carder_id)<=3)
    <h4 class=" text-dark ">Member's Personal Detail</h4>
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
    <?php
    $finacialYear=getFinacialYear();
    ?>    
    <h4 class=" text-dark ">Business Detail({{ date('d/m/Y', strtotime($finacialYear['dateStart'])) }} - {{ date('d/m/Y', strtotime($finacialYear['dateEnd'])) }}) </h4>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-7">Target (Self Business) Amt.</label><label class=" col-lg-1">:</label>
          <div class="col-lg-4 ">
            {{ number_format($businessTarget->self, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7">
            
          </div>
        </div>
      </div> 
    
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-7">Achieved Target (Self Business) Amt.</label><label class=" col-lg-1">:</label>
          <div class="col-lg-4 ">
            {{ number_format($commissionSelf, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7">
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-7">Target (Team Business) Amt.</label><label class=" col-lg-1">:</label>
          <div class="col-lg-4 ">
            {{ number_format($businessTarget->credit, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7">
            
          </div>
        </div>
      </div> 
    
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-7">Achieved Target (Team Business) Amt.</label><label class=" col-lg-1">:</label>
          <div class="col-lg-4 ">
            {{  number_format($commissionCredit, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7">
          </div>
        </div>
      </div>
      <!--<div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-7">Total Connected Member</label><label class=" col-lg-1">:</label>
          <div class="col-lg-4 ">
            {{  $memberCount }} 
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
           
        </div>
      </div>-->
      @for($i=1;$i<$memberData->current_carder_id;$i++)
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-7">Target (Associate Connect) Carder-{{$i}}</label><label class=" col-lg-1">:</label>
          <div class="col-lg-4 ">
            <?php $a = 'Carder-'.$i;?>
            {{ $businessTarget->$a }} 
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-7">Achieved Target (Associate Connect) Carder-{{$i}}</label><label class=" col-lg-1">:</label>
          <div class="col-lg-4 ">
            {{  countAssociateByCarder($memberData->id,$i,$finacialYear['dateStart'],$finacialYear['dateEnd']) }} 
          </div>
        </div>
      </div>
      @endfor
      <div class="col-lg-12 ">
        <div class=" row">
          <?php
          $achivedTargetStatus=getFinacialYearBusinessTarget($memberData->id,$finacialYear['dateStart'],$finacialYear['dateEnd'],$memberData->current_carder_id);
          ?>
          <label class="col-lg-12"> <h4 class=" text-dark "><label class="col-lg-2"> Business Target  </label> <label class=" col-lg-1"> - </label> <label class="col-lg-4">
 @if($achivedTargetStatus==0) Not Achieved @else Achieved @endif</label> </h4></label>
        </div>
      </div>

    </div>
        @if($type=='status')

   <h4 class=" text-dark ">Associate Status</h4>
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

    <h4 class=" text-dark ">Associate Status Change To </h4>
    <div class=" row">
      <div class="col-lg-12 ">
        <div class="form-group row">
          <label class="col-form-label col-lg-3">Status </label>
          <div class="col-lg-5 error-msg">
             <select class="form-control select" name="current_status" id="current_status" >
               @if($memberData->associate_status==0)
                <option value="1">Active</option> 
                @else

                <option value="0">Inactive</option>
                @endif 
            </select>

             <input type="hidden" name="old_status" id="old_status" value="{{ $memberData->associate_status }}" >

          </div>
        </div>
      </div>
    </div>

    @endif
    @if($type=='upgrade' || $type=='downgrade')
   <h4 class=" text-dark ">Current carder</h4>
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
      @if(($memberData->current_carder_id)==3 && $type=='upgrade')
      <div class="alert alert-danger alert-block">Associate Carder grater than Carder-3.So branch have no permission to update carder or status </div>
      @endif
@else
	 <div class="alert alert-danger alert-block">Associate Carder grater than Carder-3.So branch have no permission to update carder or status </div>
@endif