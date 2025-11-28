

        

            <div class="col-lg-12">

            <div class="card bg-white" >

              <div class="card-body">

               <?php

                $memberDetail = App\Models\Member::find($tDetails['member_id']);

                $associateDetail = App\Models\Member::find($tDetails['associate_id']);
				  $desc = str_replace('"}',"",str_replace('{"name":"',"",$tDetails->description));
                
            //dd($tDetails['investment']);die;

               ?>

                <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Transaction ID </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $tDetails['id'] }} </div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Transaction date </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ date("d/m/Y", strtotime( str_replace('-','/',$tDetails['created_at'] ) ) ) }} </div>

                  </div>

                  </div>

                </div> 



                <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Name </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $memberDetail->first_name }} {{ $memberDetail->last_name }} <!--( {{ $memberDetail->member_id }} )--></div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Account No</label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $tDetails['account_no'] }} </div>

                  </div>

                  </div>

                </div>
				 <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Plan Type </label><label class=" col-lg-1">:</label>
                                               
                    <div class="col-lg-7  ">
      
                    
					{{ getPlanDetail($tDetails['investment']->plan_id)->name }}</div>
                        
                  </div>

                  </div>

                  <div class="col-lg-6">
                                            
                    <div class="  row">

                    <label class=" col-lg-4">Tenure</label><label class=" col-lg-1">:</label>
                        
                    <div class="col-lg-7  "> {{ $tDetails['investment']->tenure }} year 
                  @switch($tDetails['emi_option'])
    @case(1)
        <span> month</span>
        @break

    @case(2)
        <span>week</span>
        @break

     @case(3)
        <span>days</span>
@endswitch
					</div>

                  </div>

                  </div>

                </div>

 <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Branch Code </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $tDetails['branch_code'] }}</div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Branch name</label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $databranch['name'] }}</div>

                  </div>

                  </div>

                </div>

                <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Description </label><label class=" col-lg-1">:</label>
                     
                    <div class="col-lg-7  ">{{$desc}} </div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Payment Method</label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  ">

                      @if($tDetails['type'] == 1)

                          @if($tDetails['payment_mode'] == 0)

                            Cash

                          @elseif($tDetails['payment_mode'] == 1)

                            Cheque

                          @elseif($tDetails['payment_mode'] == 2)

                            DD

                          @elseif($tDetails['payment_mode'] == 3)

                            Online 

                          @elseif($tDetails['payment_mode'] == 4)

                            From SSB

                          @elseif($tDetails['payment_mode'] == 5)

                            From Loan Account 

                          @endif

                      @else

                        @if($tDetails['payment_mode'] == 0)

                            Cash

                          @elseif($tDetails['payment_mode'] == 1)

                            Cheque

                          @elseif($tDetails['payment_mode'] == 2)

                            DD

                          @elseif($tDetails['payment_mode'] == 3)

                              @if($tDetails['deposit']>0)

                                Transfer By Other Account 

                              @else

                                Transfer To Other Account 

                              @endif 

                          @elseif($tDetails['payment_mode'] == 4)

                            @if($tDetails['deposit']>0)

                                From SSB

                              @else

                                SSB Transfer

                              @endif

                          @elseif($tDetails['payment_mode'] == 5)

                            Online

                          @endif

                      @endif

                   </div>

                  </div>

                  </div>

                </div>



                      

                <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">

                      @if($tDetails['type'] ==1)

                        Amount 

                      @else

                          @if($tDetails['deposit'] >0)

                          Deposit

                        @else

                          Withdrawal

                        @endif

                      @endif

                    </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> 

                      @if($tDetails['deposit'] >0)

                        {{ $tDetails['deposit'] }} 

                      @else

                        {{ $tDetails['withdrawal'] }} 

                      @endif

                      <img src="{{url('/')}}/asset/images/rs.png" width="7">

                    </div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Total balance amount</label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $tDetails['opening_balance'] }} <img src="{{url('/')}}/asset/images/rs.png" width="7"></div>

                  </div>

                  </div>

                  

                </div>


                 @if($associateDetail)
                <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Associate Name </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> @if($associateDetail) {{ $associateDetail->first_name }} {{ $associateDetail->last_name }} @endif </div>

                  </div>

                  </div>
                  @endif
                   @if($associateDetail)
                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Associate Code</label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  ">@if($associateDetail) {{ $associateDetail->associate_no }} @endif  </div>

                  </div>

                  </div>
                  @endif
                  

                </div>



              </div>

            </div>

