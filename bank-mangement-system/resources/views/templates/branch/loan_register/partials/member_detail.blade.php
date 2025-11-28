
    <h4 class="card-title mb-3">Member's Personal Detail</h4>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">First Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->first_name}}
            
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-3">Last Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->last_name}} 
          </div>
        </div>
      </div>
    </div>

    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Father Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->father_husband}}
            
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-3">DOB</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{date('d/m/Y', strtotime($memberData->dob))}}
          </div>
        </div>
      </div>
    </div>

    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Marital Status</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            @if($memberData->marital_status==1)
              Married
            @else
              Un Married
            @endif
          </div>
        </div>
      </div>

      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Email Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->email}}
          </div>
        </div>
      </div>
    </div>

    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-3">Mobile No</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->mobile_no}} 
          </div>
        </div>
      </div>
    
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Address</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->address}}
          </div>
        </div>
      </div>
    </div>

   

             