@extends('layouts/branch.dashboard')

@section('content')
<?php
//print_r($tDetails);die;
?>
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body page-title ">
            <h3 class="">Transaction Detail </h3>
            <!-- <a href="{!! route('branch.member_list') !!}" style="float:right" class="btn btn-secondary">Back</a> -->
          </div>
        </div>
      </div>
    </div>

    <div class="row">
        
        <div class="col-lg-12">
            <div class="card bg-white" >
              <div class="card-body">
               <?php 
                $associateDetail = App\Models\Member::find($tDetails['associate_id']);
               // print_r($tDetails['associate_id']);die;
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
                    <div class="col-lg-7  ">
                    @if($tDetails['type'] == 1)
                      {{ $accountData['member']->first_name }} {{ $accountData['member']->last_name }} 
                    @else
                      {{ $accountData['ssbcustomerDataGet']->first_name }} {{ $accountData['ssbcustomerDataGet']->last_name }}
                    @endif
                  </div>
                  </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="  row">
                    <label class=" col-lg-4">Customer ID </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-7  ">
                    @if($tDetails['type'] == 1)
                      {{ $accountData['member']->member_id }} 
                    @else
                      {{ $accountData['ssbcustomerDataGet']->member_id }}
                    @endif
                   </div>
                  </div>
                  </div>
                </div>



                <div class="row">
                  <div class="col-lg-6">
                    <div class="  row">
                    <label class=" col-lg-4">Member ID  </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-7  "> 
                    @if($tDetails['type'] == 1)
                      {{ $accountData['memberCompany']->member_id }} 
                    @else
                      {{ $accountData['ssbmembersDataGet']->member_id }}
                    @endif  </div>
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
                    <label class=" col-lg-4">Description </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-7  "> {{ $tDetails['description'] }} </div>
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
                    <div class="col-lg-7  ">@if($associateDetail) {{ $associateDetail->first_name }} {{ $associateDetail->last_name }} @endif</div>
                  </div>
                  </div>
                  @endif
                  @if($associateDetail)
                  <div class="col-lg-6">
                    <div class="  row">
                    <label class=" col-lg-4">Associate Code</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-7  ">@if($associateDetail) {{ $associateDetail->associate_no }} @endif</div>
                  </div>
                  </div>
                  @endif
                </div>

              </div>
            </div>
        </div>
    </div> 
    
  </div>
</div>
@stop
@section('script')

@include('templates.branch.member_management.partials.script')
@stop