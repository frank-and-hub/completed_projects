@extends('templates.admin.master')

@section('content')
<div class="content"> 
    <div class="row">  
    <div class="col-lg-12" > 
          @if (session('success')) 
          <div class="alert alert-success alert-dismissible fade show" role="alert">
              
              <span class="alert-text"><strong>Success!</strong> {{ session('success') }} </span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          @endif
        </div> 
        <div class="col-md-12" id="print_recipt">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h4 class="card-title font-weight-semibold  col-lg-12 text-center">Receipt Detail</h4>
                </div>
                <div class="card-body"> 
              <div class="  row">
                <label class=" col-lg-4  ">Branch Name : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->branchReceipt->name }}
                </div>
              </div>
              <div class="  row">
                <label class=" col-lg-4  ">Branch Code : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->branchReceipt->branch_code }}
                </div>
              </div> 
              <div class="  row">
                <label class=" col-lg-4  ">Date : </label>
                <div class="col-lg-8   ">
                  {{ date('d/m/Y', strtotime($receipt->created_at)) }}
                </div>
              </div> 
              <div class="  row">
                <label class=" col-lg-4  ">Receipt Number : </label>
                <div class="col-lg-4   ">
                  {{ $receipt->id }}
                </div>
              </div> 
              <div class="  row">
                <label class=" col-lg-4  ">Member Id Number : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->memberReceipt->member_id }}
                </div>
              </div>
              <div class="  row">
                <label class=" col-lg-4  ">Associate Id Number : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->memberReceipt->associate_no }}
                </div>
              </div>
              <div class="  row">
                <label class=" col-lg-4  ">Name : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->memberReceipt->first_name }} {{ $receipt->memberReceipt->last_name }}
                </div>
              </div>
              <div class="  row">
                <label class=" col-lg-4  ">Address : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->memberReceipt->address }} 
                </div>
              </div>
              @if($receipt->memberReceipt->rd_account!='' )
              <div class="  row">
                <label class=" col-lg-4  ">RD Account Number : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->memberReceipt->rd_account }}
                </div>
              </div>
              <div class="  row">
                <label class=" col-lg-4  ">RD Tenure  : </label>
                <div class="col-lg-8   ">
                  {{ getInvestmentAccount($receipt->memberReceipt->id,$receipt->memberReceipt->rd_account)->tenure*12 }} Months
                </div>
              </div>
              @endif
               @if($receipt->memberReceipt->ssb_account!='' || !is_null($receipt->memberReceipt->ssb_account))
              <div class="  row">
                <label class=" col-lg-4  ">SSB Account Number : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->memberReceipt->ssb_account }}
                </div>
              </div>
              @endif
              <div class="  row">
                <label class=" col-lg-4  ">Deposit Amount  : </label>
                 <div class="col-lg-8   ">
                  @if($receipt->memberReceipt->ssb_account!='' && $receipt->memberReceipt->rd_account)
                  {{ number_format($total_amount, 2, '.', ',') }}  <img src="{{url('/')}}/asset/images/rs.png" width="7"> 
                  @else
                  @if($ssb_receipt_amount)
                  {{ number_format($ssb_receipt_amount->amount, 2, '.', ',') }}  <img src="{{url('/')}}/asset/images/rs.png" width="7">
                  @else
                  {{ number_format($total_amount, 2, '.', ',') }}  <img src="{{url('/')}}/asset/images/rs.png" width="7"> 
                  @endif
                  @endif
                </div>
              </div>
              <div class="  row">
                <label class=" col-lg-4  ">Mobile Number : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->memberReceipt->mobile_no}}
                </div>
              </div>
              <div class="  row">
                <label class=" col-lg-4  ">Amount : </label>
                <div class="col-lg-8   row">
                @foreach($receipt_amount as $val)
                  @if($val->type_label==1)
                    <div class="col-lg-12 row">
                      <label class=" col-lg-6  ">SSB Account Amount :  </label>
                      <div class="col-lg-6   ">
                        {{ number_format($val->amount, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7">
                      </div>
                    </div>
                  @elseif($receipt->memberReceipt->rd_account)
                    <div class="col-lg-12 row"> 
                      <label class=" col-lg-6  ">RD Account Amount : </label>
                      <div class="col-lg-6   ">
                        {{ number_format($val->amount, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="7">
                      </div>
                    </div>
                  
                  @endif
                
                @endforeach
              </div>
              </div> 


                </div>
            </div>
             

             

        </div>
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
              <div class="text-center">
              <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print </button></div>
            </div>
          </div>
        </div>
         
    </div>
</div>
@include('templates.admin.associate.partials.print_js')
@stop