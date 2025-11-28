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
                <label class=" col-lg-4  ">Form Number : </label>
                <div class="col-lg-4   ">
                  {{ $receipt->memberReceipt->form_no }}
                </div>
              </div>
              <div class="  row">
                <label class=" col-lg-4  ">Member Id Number : </label>
                <div class="col-lg-8   ">
                  {{ $receipt->memberReceipt->member_id }}
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
                      <label class=" col-lg-7  ">Member ID Number(Fee)  : </label>
                      <div class="col-lg-5   ">
                        <span class="ammount-text"> {{ number_format($val->amount, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="9"></span>
                      </div>
                    </div>
                  @else
                    <div class="col-lg-12 row"> 
                      <label class=" col-lg-7  ">ST. Charge : </label>
                      <div class="col-lg-5   ">
                        <span class="ammount-text">{{ number_format($val->amount, 2, '.', ',') }} <img src="{{url('/')}}/asset/images/rs.png" width="9"></span>
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
@include('templates.admin.member.partials.script')
@stop