<h6 class="card-title font-weight-semibold ">Loan Account Details</h6>
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
          <label class=" col-lg-4">Plan Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
			    {{$loanData['loans']->name}}
          </div>
        </div>
      </div>
	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Member Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
			  {{$loanData['loanMember']->first_name}} {{$loanData['loanMember']->last_name}}
          </div>
        </div>
      </div>
	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Member Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
       {{$loanData['loanMemberCompany']->member_id}}
          </div>
        </div>
      </div>
	    <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Collector  Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">			  
			     {{$loanData['CollectorAccount'] ? ($loanData['CollectorAccount']['member_collector']->first_name.' '.$loanData['CollectorAccount']['member_collector']->last_name):''}}
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Customer Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
       {{$loanData['loanMember']->member_id}}
          </div>
        </div>
      </div>
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Collector Code</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		   {{$loanData['CollectorAccount']['member_collector']->associate_no}}
          </div>
        </div>
      </div>
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Branch Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		   {{$loanData['loanBranch']->name}}
		 </div>
        </div>
      </div>
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Branch Code</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
           {{$loanData['loanBranch']->branch_code}}
          </div>
        </div>
      </div>	  
	  
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Sanction Date</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
          {{ date("d/m/Y", strtotime(convertDate($loanData->approve_date))) ?? 'N/A'}}
          </div>
        </div>
    </div>
	  	  

    
 </div>
 <input type="hidden" name="loan_id" id="loan_id" value="{{$loanData->id}}"> 
 <input type="hidden" name="loan_type" id="loan_type" value="{{$loanData->loan_type}}"> 