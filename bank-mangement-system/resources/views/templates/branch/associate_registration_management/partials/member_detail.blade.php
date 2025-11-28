
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
          <label class=" col-lg-3">Email Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->email}}
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-3">Mobile No</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->mobile_no}} 
          </div>
        </div>
      </div>
    </div>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Address</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->address}}
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">State </label><label class=" col-lg-1">:</label>
          <div class="col-lg-8  "> {{ getStateName($memberData->state_id) }}   </div>
        </div>
      </div>
    </div>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">District</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">{{ getDistrictName($memberData->district_id) }}
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">City </label><label class=" col-lg-1">:</label>
          <div class="col-lg-8  "> {{ getCityName($memberData->city_id) }}   </div>
        </div>
      </div>
    </div>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Village</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">{{$memberData->village}}  </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Pin Code </label><label class=" col-lg-1">:</label>
          <div class="col-lg-8  "> {{$memberData->pin_code}}   </div>
        </div>
      </div>
    </div>

    
    <h4 class="card-title mb-3">Member's Id Proofs </h4>
    <div class="row">
                @if($idProofDetail)
                <div class="col-lg-6">
                  <h5 class="card-title mb-3">First ID Proof </h5>
                  <div class="row">
                    <label class=" col-lg-3">ID Proof Type </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ getIdProofName($idProofDetail->first_id_type_id)}} </div>  
                  </div>  
                  <div class="row">
                    <label class=" col-lg-3">ID No </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->first_id_no }}</div>  
                  </div>
                  <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->first_address }}</div>  
                  </div> 
                </div>
                <div class="col-lg-6">
                  <h5 class="card-title mb-3">Second ID Proof</h5> 
                  <div class="row">
                    <label class=" col-lg-3">ID Proof Type </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ getIdProofName($idProofDetail->second_id_type_id)}}</div>  
                  </div>
                  <div class="row">
                    <label class=" col-lg-3">ID No </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->second_id_no }}</div>  
                  </div> 
                  <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->second_address }}</div>  
                  </div> 
                </div>
                @else
                <div class="col-lg-12">
                    <label class=" col-lg-12">No record found!. </label> 
                </div>
                @endif
              </div>
