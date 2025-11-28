@extends('layouts/branch.dashboard')



@section('content')



<div class="loader" style="display: none;"></div>

<div class="container-fluid mt--6">

  <div class="content-wrapper">



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

        <div class="col-lg-12" id="print_recipt"> 

            

          <div class="card bg-white" >

            <div class="card-body">

              <h3 class="card-title mb-3 text-center">Receipt Detail</h3>

              @foreach($investmentDetails as $investmentDetail)

              <div class="  row">

                <label class=" col-lg-4  ">Branch Name : </label>

                <div class="col-lg-4   ">

                  {{ $investmentDetail->branch['name'] }}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Branch Code : </label>

                <div class="col-lg-4   ">

                  {{ $investmentDetail->branch['branch_code'] }}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Date : </label>

                <div class="col-lg-4   ">

                  {{ date('d/m/Y', strtotime($investmentDetail->created_at)) }}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Member ID Number : </label>

                <div class="col-lg-8   ">

                  {{ $investmentDetail['member']->member_id }}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Member Name : </label>

                <div class="col-lg-8   ">

                  {{ $investmentDetail['member']->first_name }} {{ $investmentDetails[0]['member']->last_name }}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Plan Name : </label>

                <div class="col-lg-8   ">

                  {{ $investmentDetail['plan']->name }}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Account Number : </label>

                <div class="col-lg-8   ">

                  {{ $investmentDetail->account_number }}

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Tenure : </label>

                <div class="col-lg-8   ">

                  Not Available

                </div>

              </div>

              <div class="  row">

                <label class=" col-lg-4  ">Deno/ Amount : </label>

                <div class="col-lg-8   ">

                  {{ $investmentDetail->deposite_amount }} <img src="{{url('/')}}/asset/images/rs.png" width="9">

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

              @endforeach

              <?php //echo "<pre>"; print_r($associateDetails); die; ?>

              <div class="  row">

                <label class=" col-lg-4  ">Payment Type : </label>

                <div class="col-lg-8   ">

                  {{ $paymentType }}

                </div>

              </div>

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

                @if( in_array('Print Investment Receipt', auth()->user()->getPermissionNames()->toArray() ) )

                  <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>

                @endif

              <a href="{!! route('investment.plans') !!}" class="btn btn-secondary">Back</a>

            </div>

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