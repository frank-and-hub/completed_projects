@extends('templates.admin.master')

@section('content')

<div class="loader" style="display: none;"></div>

<div class="content">
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
          @if(isset($aId) && ($aId->plan->plan_category_code) == 'S')   

          
          <div class="  row">
            <label class=" col-lg-4  ">Customer ID Number : </label>
            <div class="col-lg-8   ">
               {{ $mId->ssbcustomerDataGet->member_id ?? '' }}
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Member ID Number : </label>
            <div class="col-lg-8   ">
               {{ $mId->ssbmembersDataGet->member_id ?? '' }}
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Member Name : </label>
            <div class="col-lg-8   ">
                {{ $mId->ssbcustomerDataGet->first_name. ' '.$mId->ssbcustomerDataGet->last_name}}
            </div>
          </div>

          @if($data->associate_id)         
          <div class="  row">
            <label class=" col-lg-4  ">Associate Name : </label>
            <div class="col-lg-8   ">
              {{ $aId->associateMember->first_name.' '.$aId->associateMember->last_name}}
		  
            </div>
          </div>
         @endif    
      @if($data->associate_id)       
            <div class="  row">
              <label class=" col-lg-4  ">Associate Code : </label>
              <div class="col-lg-8   ">
			 
                 {{ $aId->associateMember->associate_no}}
			
              </div>
            </div>
			 @endif
          @else

          <div class="  row">
            <label class=" col-lg-4  ">Customer ID Number : </label>
            <div class="col-lg-8   ">
               {{ $data->memberCompany->member->member_id }}
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Member ID Number : </label>
            <div class="col-lg-8   ">
               {{ $data->memberCompany->member_id }}
            </div>
          </div>
          <div class="  row">
            <label class=" col-lg-4  ">Member Name : </label>
            <div class="col-lg-8   ">
                {{ $data->memberCompany->member->first_name. ' '.$data->memberCompany->member->last_name}}
            </div>
          </div>

          @if($data->associate_id)         
          <div class="  row">
            <label class=" col-lg-4  ">Associate Name : </label>
            <div class="col-lg-8   ">
              {{ $data['investment']->associateMember->first_name.' '.$data['investment']->associateMember->last_name}}
		  
            </div>
          </div>
         @endif    
      @if($data->associate_id)       
            <div class="  row">
              <label class=" col-lg-4  ">Associate Code : </label>
              <div class="col-lg-8   ">
			 
                 {{ $data['investment']->associateMember->associate_no}}
			
              </div>
            </div>
			 @endif
          @endif
		  @if($data['investment'])
          <div class="  row">
            <label class=" col-lg-4  ">Plan Name : </label>
            <div class="col-lg-8   ">
				
               {{$data['investment']->plan->name }}
		  
            </div>
          </div>
		   @endif
          <div class="  row">
            <label class=" col-lg-4  ">Account Number : </label>
            <div class="col-lg-8   ">
              {{ $data->account_no }}
            </div>
          </div>
      
		  @if($data['investment'])
      @if($data['investment']->plan->plan_category_code != 'S')    
          <div class="  row">
            <label class=" col-lg-4  ">Tenure : </label>
            <div class="col-lg-8   ">
			
               {{ $data['investment']->tenure*12 }} Months
			
			
            </div>
          </div>
		   @endif
       @endif
          <div class="  row">
		  @if($data->deposit)
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
            <!-- <a href="{{ URL::previous() }}" class="btn btn-secondary">Back</a> -->
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