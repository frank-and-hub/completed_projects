
    <h6 class="card-title font-weight-semibold ">SSB Account Details</h6>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Account Number</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">  
		  {{$account_number}}
		  </div>
        </div>
      </div>   
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4"> Customer ID </label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">            
            {{$member_id}}
          </div>
        </div>
      </div> 
    
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-4">Member Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
		  {{$member_name}}
          </div>
        </div>
      </div>      
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Current Balance</label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
             {{number_format($current_balance,2)}}
          </div>
        </div>
      </div>   
	  <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-4">Status </label><label class=" col-lg-1">:</label>
          <div class="col-lg-7 ">
            {{$transaction_status}}
          </div>
        </div>
      </div> 
    </div> 

