
<?php 
      //  echo '<pre>';
      // print_r($investData);
      //  die();
      ?>

<h6 class="card-title font-weight-semibold ">Account Details</h6>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Account No</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
          {{$investData->account_number}}  
            
          </div>
        </div>
      </div>
    
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Plan Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            
            {{ $investData['plan']->name }}
            
          </div>
        </div>
      </div>
	  
     
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Member Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		 
			    {{$investData['member']->first_name}} {{$investData['member']->last_name}}
			  
			
          </div>
        </div>
      </div>
	  
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Customer Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		      
           
           {{$investData['member']->member_id}}
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Member Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		      
           
           {{$investData['memberCompany']->member_id}}
          </div>
        </div>
      </div>

      
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Collector  Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		 
           
			     {{$investData['CollectorAccount']['member_collector']['first_name'].' '.$investData['CollectorAccount']['member_collector']['last_name']}}
          </div>
        </div>
      </div>
	  
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Collector Code</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            
           
            {{$investData['CollectorAccount']['member_collector']['associate_no']}}
          </div>
        </div>
      </div>

	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Branch Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		        {{$investData['Branch']->name}}
		  
		      </div>
        </div>
    </div>
	  <div class="col-lg-6 ">
      <div class=" row">
        <label class=" col-lg-4">Branch Code</label><label class=" col-lg-1">:</label>
        <div class="col-lg-7 ">
          {{$investData['Branch']->branch_code}}
        </div>
      </div>
    </div>
	  
	  <div class="col-lg-6 ">
      <div class=" row">
        <label class=" col-lg-4">Creation Date</label><label class=" col-lg-1">:</label>
        <div class="col-lg-7 ">
            {{ date("d/m/Y", strtotime($investData->created_at)) }}
        </div>
      </div>
    </div>
 </div>
 <input type="hidden" name="invest_id" id="invest_id" value="{{$investData->id}}"> 
 
     

    
 