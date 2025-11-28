<?php

//print_r($loanData);
?>
    <h6 class="card-title font-weight-semibold ">Member's Personal Detail</h6>
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
          <label class=" col-lg-4">Type</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            <?php
				$type="Group Loan";
				if($loanData->loan_type==1)
				{
					$type="Personal  Loan";
				}
				if($loanData->loan_type==2)
				{
					$type="Staff Loan";
				}
				if($loanData->loan_type==4)
				{
					$type="Loan against Investment Loan";
				}
			
			?>
			{{$type}}
            
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
          <label class=" col-lg-4">Customer Id</label><label class=" col-lg-1">:</label>
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
          <label class=" col-lg-4">Member Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">

  
	
           {{$loanData['loanMember']['memberCompany']['member_id']}}
            
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
          <label class=" col-lg-4">Creation Date</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
              {{ date("d/m/Y", strtotime($loanData->created_at)) }}
          </div>
        </div>
      </div>
 </div>
  <h6 class="card-title font-weight-semibold ">Branch Transfer</h6>
 <div class=" row">
  <div class="col-lg-12 ">
        <div class=" form-group row">
          <label class=" col-lg-3">Branch</label><label class=" col-lg-1">:</label>
          <div class="col-lg-5 error-msg">
		  <select class="form-control" id="branch_id" name="branch_id">
              <option value=""  >Select Branch</option>
                   @foreach( $branch as $k =>$val )
				   @if($val->id!=$loanData->branch_id)
                        <option value="{{ $val->id }}" >{{ $val->name }}({{$val->branch_code}})</option> 
					@endif
                    @endforeach
           </select>
		  
		 </div>
		  <input type="hidden" name="old_branch_id" id="old_branch_id" value="{{$loanData->branch_id}}"> 
		   <input type="hidden" name="loan_id" id="loan_id" value="{{$loanData->id}}">
		   <input type="hidden" name="loan_type" id="loan_type" value="{{$loanData->loan_type}}">
        </div>
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
	</div>

    
	 
