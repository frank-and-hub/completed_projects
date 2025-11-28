<div class="row" > 

        

            <div class="col-lg-12">

            <div class="card bg-white" >

              <div class="card-body">

               <?php

                $memberDetail = App\Models\Member::find($tDetails['member_id']);

                $associateDetail = App\Models\Member::find($tDetails['associate_id']);

            // print_r($databranch);die;

               ?>

                <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Transaction ID </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $data['id'] }} </div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Transaction date </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ date("d/m/Y", strtotime( str_replace('-','/',$data['created_at'] ) ) ) }} </div>

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

                    <label class=" col-lg-4">Branch Code </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  ">{{ $tDetails['branch_code'] }} </div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Branch name</label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $databranch['name'] }} </div>

                  </div>

                  </div>

                </div>

                <div class="row">

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Description </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $data['description'] }} </div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Payment Method</label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  ">

                      @if($data['type'] == 1)

                          @if($data['payment_mode'] == 0)

                            Cash

                          @elseif($data['payment_mode'] == 1)

                            Cheque

                          @elseif($data['payment_mode'] == 2)

                            DD

                          @elseif($data['payment_mode'] == 3)

                            Online 

                          @elseif($data['payment_mode'] == 4)

                            From SSB

                          @elseif($data['payment_mode'] == 5)

                            From Loan Account 

                          @elseif($data['payment_mode'] == 6)

                            From JV 
  
                          @endif

                      @else

                        @if($data['payment_mode'] == 0)

                            Cash

                          @elseif($data['payment_mode'] == 1)

                            Cheque

                          @elseif($data['payment_mode'] == 2)

                            DD

                          @elseif($data['payment_mode'] == 3)

                              @if($data['deposit']>0)

                                Transfer By Other Account 

                              @else

                                Transfer To Other Account 

                              @endif 

                          @elseif($data['payment_mode'] == 4)

                            @if($data['deposit']>0)

                                From SSB

                              @else

                                SSB Transfer

                              @endif

                          @elseif($data['payment_mode'] == 5)

                            Online

                           @elseif($data['payment_mode'] == 6)

                            JV
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

                      @if($data['type'] ==1)

                        Amount 

                      @else

                          @if($data['deposit'] >0)

                          Deposit

                        @else

                          Withdrawal

                        @endif

                      @endif

                    </label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> 

                      @if($data['deposit'] >0)

                        {{ $data['deposit'] }} 

                      @else

                        {{ $data['withdrawal'] }} 

                      @endif

                      <img src="{{url('/')}}/asset/images/rs.png" width="7">

                    </div>

                  </div>

                  </div>

                  <div class="col-lg-6">

                    <div class="  row">

                    <label class=" col-lg-4">Total balance amount</label><label class=" col-lg-1">:</label>

                    <div class="col-lg-7  "> {{ $data['opening_balance'] }} <img src="{{url('/')}}/asset/images/rs.png" width="7"></div>

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

        </div> 