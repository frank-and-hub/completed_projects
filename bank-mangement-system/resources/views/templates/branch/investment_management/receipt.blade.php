@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title">
                    <h3 class="">Transaction Receipt</h3>
                    <a href="{{ URL::previous() }}" class="btn btn-secondary">Back</a> 
                    
                </div>
                </div>
            </div>
        </div>
  <div class="row">
    <div class="col-lg-12" id="print_recipt"> 
      <div class="card bg-white" >
        <div class="card-body">
          <h3 class="card-title mb-3 text-center">Receipt Detail</h3>
		  <div class="  row">
            <label class=" col-lg-4  ">Transaction Id: </label>
            <div class="col-lg-4   ">
              {{ $data->id }}
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Branch Name : </label>
            <div class="col-lg-4   ">
               @if($data['dbranch'])
              {{ $data['dbranch']->name }}
              @else
              N/A
              @endif
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Branch Code : </label>
            <div class="col-lg-4   ">
             @if($data['dbranch'])
              {{ $data['dbranch']->branch_code }}
              @else
              N/A
              @endif
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Date : </label>
            <div class="col-lg-4   ">
              {{ date("d/m/Y", strtotime($data->created_at))}}
            </div>
          </div>
         <?php
         
         ?>
          <div class="  row">
            <label class=" col-lg-4  ">Customer ID Number : </label>
            <div class="col-lg-8   ">
              @if($data->type==0)              
               {{ $accountData['ssbcustomerDataGet']->member_id }}
               @else
               {{ $accountData->memberCompany->member->member_id }}
               @endif
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Member ID Number : </label>
            <div class="col-lg-8   "> 
               @if($data->type==0)
              
               {{ $accountData['ssbcustomerDataGet']->member_id }}
              @else
              {{ $accountData['memberCompany']->member_id }}
              @endif
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Member Name : </label>
            <div class="col-lg-8   ">
            @if($data->type==0)
                {{ $accountData['ssbcustomerDataGet']->first_name. ' '.$accountData['ssbcustomerDataGet']->last_name}}

                @else
                {{ $accountData->memberCompany->member->first_name. ' '.$accountData->memberCompany->member->last_name}}
              @endif
            </div>
          </div>
                @if($data->associate_id)        
          <div class="  row">
            <label class=" col-lg-4  ">Associate Name : </label>
            <div class="col-lg-8   ">
                @if($data->type==0)
                {{$aId->associateMember->first_name. ' '.$aId->associateMember->last_name}}

                @else
                {{ $accountData->associateMember->first_name. ' '.$accountData->associateMember->last_name}}
              @endif
		
            </div>
          </div>
            @endif
           @if($data->associate_id)     
            <div class="  row">
              <label class=" col-lg-4  ">Associate Code : </label>
              <div class="col-lg-8   ">
			       @if($data->type==0)
                {{$aId->associateMember->associate_no}}

                @else
                {{ $accountData->associateMember->associate_no}}
              @endif
              
			
              </div>
            </div>
       @endif
		  @if($data['investment'])
          <div class="  row">
            <label class=" col-lg-4  ">Plan Name : </label>
            <div class="col-lg-8   ">
				
               {{ $data['investment']->plan->name }}
		  
            </div>
          </div>
		   @endif
          <div class="  row">
            <label class=" col-lg-4  ">Account Number : </label>
            <div class="col-lg-8   ">
              {{ $data->account_no }}
            </div>
          </div>
        @if($data['investment'] && $data['investment']->plan->name  !=  'Saving Child Plan')
          <div class="  row">
            <label class=" col-lg-4  ">Tenure : </label>
            <div class="col-lg-8   ">
			
               {{ $data['investment']->tenure*12 }} Months
			
			
            </div>
          </div>
		   @endif
          <div class="  row">
		  @if($data->deposit && $data->deposit > 0)
            <label class=" col-lg-4  ">Deposite Amount : </label>
            <div class="col-lg-8   ">
              {{ $data->deposit }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
            </div>
			@else
            <label class=" col-lg-4  ">Withdrawal Amount : </label>
            <div class="col-lg-8   ">
              {{ $data->withdrawal }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
            </div>
			@endif
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Payment Type : </label>
            <div class="col-lg-8   ">
			@if($data->payment_type == 'CR')
             Credit
      @else
      Debit       
		  @endif
            </div>
          </div>
           <div class="  row">
            <label class=" col-lg-4  ">Payment Mode : </label>
            <div class="col-lg-8   ">
              @php
                $payment_mode = [
                  0 => 'Cash',
                  1 => 'Cheque',
                  2 => 'Demand Draft',
                  3 => 'Online Transaction',
                  4 => 'Saving Account',
                  5 => 'Loan Amount',
                  ];
                @endphp
                {{ $payment_mode[$data->payment_mode]   }}   
            </div>
          </div>
   
          {{--@include('templates.branch.investment_management.partials.nominee-recipt')--}}
        </div>
      </div>
    </div> 
    <div class="col-lg-12">
      <div class="card bg-white" >            
        <div class="card-body">
          <div class="text-center">
            <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>
           
        </div>
        </div>
      </div>
    </div>
  </div> 
</div>

@stop

@section('script')
@include('templates.branch.investment_management.partials.script')
@stop