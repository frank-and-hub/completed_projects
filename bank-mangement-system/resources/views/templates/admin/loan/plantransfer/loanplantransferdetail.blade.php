<?php
//echo '<pre>';
//print_r($loanData);
//print_r($loanData['loans']->name);
//print_r($loanData->emi_option);
//echo '</pre>';
?>
  <h6 class="card-title font-weight-semibold">Member's Personal Detail</h6>
  <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Account No</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$loanData->account_number}}  
            
          </div>
        </div>
      </div>
	  
	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Loan Type</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
          <?php
            
            if($loanData['loans']->loan_type=='L')
            {
              $loan_type="PERSONAL";
            }
            elseif($loanData['loans']->loan_type=='G')
            {
              $loan_type="GROUP";
            }
            else{
              $loan_type= "N/A";
            }
			
            ?>
            {{$loan_type}}
            
          </div>
        </div>
      </div>
	  
	   
	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Account Holder Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		 
			      {{$loanData['loanMember']->first_name}} {{$loanData['loanMember']->last_name}}
			  
			
          </div>
        </div>
      </div>
	  
	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Member Code</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		        {{$loanData['loanMember']->member_id}}
           
            
          </div>
        </div>
      </div>
	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Associate  Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		 
			      {{$loanData['loanMemberAssociate']->first_name}} {{$loanData['loanMemberAssociate']->last_name}}
			  
			
          </div>
        </div>
      </div>
	  
	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Associate Code</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		        {{$loanData['loanMemberAssociate']->associate_no}}
           
            
          </div>
        </div>
      </div>

      <div class="col-lg-6 ">
          <div class=" row">
            <label class=" col-lg-4">EMI</label><label class=" col-lg-1">:</label>
            <div class="col-lg-7 ">
              {{$loanData->emi_amount}}
            
            </div>
          </div>
      </div>

      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">EMI Option</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            <?php
            
            if($loanData->emi_option==1)
            {
              $emi_option="Monthly";
            }
            elseif($loanData->emi_option==2)
            {
              $emi_option="Weekly";
            }
            elseif($loanData->emi_option==3)
            {
              $emi_option="Daily";
            }else{
              $emi_option= "N/A";
            }
			
            ?>
            {{$emi_option}}
          </div>
        </div>
      </div>

	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Tenure</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
           {{$loanData->emi_period}}
          </div>
        </div>
      </div>
       
      
      
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">ROI</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$loanData->ROI}}
          </div>
        </div>
      </div>
      
      
      
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Sanctioned Amount</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$loanData->transfer_amount}}
          </div>
        </div>
      </div>

      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">OutStanding Amount</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            30000
          </div>
        </div>
      </div>


	    
  </div>
  <h6 class="card-title font-weight-semibold ">Plan Transfer</h6>
  <div class=" row">
      <div class="col-lg-12 ">
        <div class=" form-group row">
          <label class=" col-lg-2">Plan</label><label class=" col-lg-1">:</label>
          <div class="col-lg-9 error-msg">
		        <select class="form-control" id="plan_id" name="plan_id">
              <option value="">Select Plan</option>

                  @foreach($loantype as $loan)
                  <option value="{{$loan->id}}">{{$loan->name}}</option>
                  
                  @endforeach
                  

            </select>
		  
		      </div>
		      <input type="hidden" name="old_plan_id" id="old_plan_id" value="{{$loanData['loans']->id}}"> 
		      
		      <input type="hidden" name="loan_id" id="loan_id" value="{{$loanData->id}}">
		      <input type="hidden" name="sanctioned_amt" id="sanctioned_amt" value="{{$loanData->transfer_amount}}">
		      <input type="hidden" name="loan_type" id="loan_type" value="{{$loanData->loan_type}}">
        </div>
      </div>

      <div class="col-md-12" id="new_loanplan_detail">
                                    
      </div>
      <?php 
        if($loanData->loan_type==3)
        {
      ?>
	    <div class="col-lg-12 ">
        <div class=" form-group row">
          <div class="col-lg-12 ">
            <h6 style="color:red !important"> Note*- All Group Member Account transfer to selected branch.</h5>
          </div>
			
          <div class="col-lg-12 ">
            @foreach($groupList as $glist)
              <div class=" form-group row">
                <label class=" col-lg-2">Account No</label><label class=" col-lg-1">:</label>
                <div class="col-lg-3 ">
                {{$glist->account_number}} 
                </div>
                <label class=" col-lg-2">Member Name</label><label class=" col-lg-1">:</label>
                <div class="col-lg-3 ">
                {{$glist['loanMember']->first_name}} {{$glist['loanMember']->last_name}}
                </div>
              </div>
            @endforeach
          </div>
			
		    </div>
	    </div>
      <?php } ?>

      <div class="col-lg-12 ">
        <div class=" form-group row">
          <label class=" col-lg-2">Reason</label><label class=" col-lg-1">:</label>
          <div class="col-lg-9 error-msg">
		       
            <textarea class="form-control" id="reason_id" name="reason_id"></textarea>
		      </div>
		      
        </div>
      </div>   
	</div>
    
	 
