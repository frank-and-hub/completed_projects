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

              <label class=" col-lg-4  ">Branch Name : </label>

              <div class="col-lg-4   ">

                {{ $investmentDetails[0]->branch['name'] }}

              </div>

            </div>

            <div class="  row">

              <label class=" col-lg-4  ">Branch Code : </label>

              <div class="col-lg-4   ">

                {{ $investmentDetails[0]->branch['branch_code'] }}

              </div>

            </div>

            <div class="  row">

              <label class=" col-lg-4  ">Date : </label>

              <div class="col-lg-4   ">

                {{ date('d/m/Y', strtotime($investmentDetails[0]->created_at)) }}

              </div>

            </div>

            <div class="  row">

              <label class=" col-lg-4  ">Member ID Number : </label>

              <div class="col-lg-8   ">

                {{ $investmentDetails[0]['member']->member_id }}

              </div>

            </div>

            <div class="  row">

              <label class=" col-lg-4  ">Member Name : </label>

              <div class="col-lg-8   ">

                {{ $investmentDetails[0]['member']->first_name }} {{ $investmentDetails[0]['member']->last_name }}

              </div>

            </div>

            <div class="  row">

              <label class=" col-lg-4  ">Plan Name : </label>

              <div class="col-lg-8   ">

                {{ $investmentDetails[0]['plan']->name }}

              </div>

            </div>

            <div class="  row">

              <label class=" col-lg-4  ">Account Number : </label>

              <div class="col-lg-8   ">

                {{ $investmentDetails[0]->account_number }}

              </div>

            </div>

            <div class="  row">

              <label class=" col-lg-4  ">Tenure : </label>

              <div class="col-lg-8   ">

                {{ $investmentDetails[0]->tenure*12 }} ss

              </div>

            </div>

            <div class="  row">

              <label class=" col-lg-4  ">Deno/ Amount : </label>

              <div class="col-lg-8   ">

                {{ $investmentDetails[0]->deposite_amount }} <img src="{{url('/')}}/asset/images/rs.png" width="9">

              </div>

            </div>

            @php
                $stationaryCharge = \App\Models\Daybook::where('investment_id',$investmentDetails[0]->id)->where('account_no',$investmentDetails[0]->account_number)->where('transaction_type',19)->count();
              @endphp

              @if($stationaryCharge > 0)
              <div class="  row">

                <label class=" col-lg-4  ">Stationery Charges : </label>

                <div class="col-lg-8   ">

                  50 (Cash) <img src="{{url('/')}}/asset/images/rs.png" width="9">

                </div>

              </div>
              @endif

         <!--    @php
                $stationaryCharges=App\Models\AllHeadTransaction::where('type',3)->where('sub_type',35)->where('type_id',$investmentDetails[0]->id)->first();
              @endphp

              @if($stationaryCharges)
                <div class="  row">

                <label class=" col-lg-4  ">Stationery Charges : </label>

                <div class="col-lg-8   ">

                  50 <img src="{{url('/')}}/asset/images/rs.png" width="9">  (cash)

                </div>

              </div>
              @endif
 -->

            <div class="  row">

              <label class=" col-lg-4  ">Payment Type : </label>

              <div class="col-lg-8   ">

                {{ $paymentType }}

              </div>

            </div>

            

              @if($investmentDetails[0]->payment_mode=='1')



              <div class="  row">

                <label class=" col-lg-4  ">Cheque Number : </label>

                <div class="col-lg-8   ">

                  {{ $investmentDetails[0]['investmentPayment'][0]->cheque_number}}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Bank Name : </label>

                <div class="col-lg-8   ">

                  {{ $investmentDetails[0]['investmentPayment'][0]->bank_name}}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Branch Name : </label>

                <div class="col-lg-8   ">

                  {{ $investmentDetails[0]['investmentPayment'][0]->branch_name}}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Cheque Date: </label>

                <div class="col-lg-8   ">

                  {{ date("d/m/Y", strtotime($investmentDetails[0]['investmentPayment'][0]->cheque_date))}}

                </div>

              </div>



              @endif 

            <div class="  row">

              <label class=" col-lg-4  ">Associate Name : </label>

              <div class="col-lg-8   ">

                {{ $associateDetails[0]->first_name }}  {{ $associateDetails[0]->last_name }}

              </div>

            </div>

            @if ($associateDetails[0]->member_id == 9999999)

              <div class="  row">

                <label class=" col-lg-4  ">Associate Code : </label>

                <div class="col-lg-8   ">

                  {{ $associateDetails[0]->associate_no }}

                </div>

              </div>

            @else

              <div class="  row">

                <label class=" col-lg-4  ">Associate Code : </label>

                <div class="col-lg-8   ">

                  {{ $associateDetails[0]->associate_no }}

                </div>

              </div>

            @endif

            {{--@include('templates.branch.investment_management.partials.nominee-recipt')--}}

          </div>

        </div>

      </div> 

      <div class="col-lg-12">

        <div class="card bg-white" >            

          <div class="card-body">

            <div class="text-center">

              <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>

              <!-- <a href="{{ URL::Previous() }}" class="btn btn-secondary">Back</a> -->

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