<?php
//echo '<pre>';
//print_r($loantype[0]->name);
//print_r($loanData['loans']->name);
//print_r($loanData->emi_option);
//echo '</pre>';
?>
  <input type="hidden" name="new_plan_id" id="new_plan_id" value="{{$loantype[0]->id}}"> 
  <input type="hidden" name="new_plan_code" id="new_plan_code" value="{{$loantype[0]->code}}"> 
  <input type="hidden" name="new_plan_tenure" id="new_plan_tenure" value="{{$loantype[0]->tenure}}"> 
  <input type="hidden" name="new_plan_emi_option" id="new_plan_emi_option" value="{{$loantype[0]->emi_option}}"> 
  <input type="hidden" name="new_plan_roi" id="new_plan_roi" value="{{$loantype[0]->ROI}}"> 
  <input type="hidden" name="new_plan_roitype" id="new_plan_roitype" value="{{$loantype[0]->roi_type}}"> 
  <input type="hidden" name="new_plan_id" id="new_plan_id" value="{{$loantype[0]->id}}"> 

  <div class=" row">
    <div class="col-lg-6 ">
      <div class=" row">
        <label class=" col-lg-4">Name</label><label class=" col-lg-1">:</label>
        <div class="col-lg-7 ">
          {{$loantype[0]->name}}
          
        </div>
      </div>
    </div>
	
    <div class="col-lg-6 ">
      <div class=" row">
        <label class=" col-lg-4">Code</label><label class=" col-lg-1">:</label>
        <div class="col-lg-7 ">
        
          {{$loantype[0]->code}}
        </div>
      </div>
    </div>
	   
    <div class="col-lg-6 ">
      <div class=" row">
        <label class=" col-lg-4">Tenure</label><label class=" col-lg-1">:</label>
        <div class="col-lg-7 ">
    
          {{$loantype[0]->tenure}}
      
    
        </div>
      </div>
    </div>
	  
    <div class="col-lg-6 ">
      <div class=" row">
        <label class=" col-lg-4">EMI Option</label><label class=" col-lg-1">:</label>
        <div class="col-lg-7 ">
         
          @if(loantype[0]->emi_option == 1) Monthly @elseif(loantype[0]->emi_option == 2) Weekly @else Daily  @endif
        </div>
      </div>
    </div>

    <div class="col-lg-6 ">
      <div class=" row">
        <label class=" col-lg-4">ROI</label><label class=" col-lg-1">:</label>
        <div class="col-lg-7 ">
    
          
         {{$loantype[0]->ROI}}
    
        </div>
      </div>
    </div> 

    <div class="col-lg-6 ">
      <div class=" row">
        <label class=" col-lg-4">ROI Type</label><label class=" col-lg-1">:</label>
        <div class="col-lg-7 ">
    
          
          {{$loantype[0]->roi_type}}
    
        </div>
      </div>
    </div> 
  </div>

    
	 
