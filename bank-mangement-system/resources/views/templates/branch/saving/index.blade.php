@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        @if($accountDetail) 
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - Account Detail For : {{ $accountDetail->account_no  }}</h3> 
                       
                        <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a>
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-lg-5 ">
                                <h3 class="mb-0 text-dark ">Branch Detail</h3>
                                <div class="row">
                                    <label class=" col-lg-4">Account No:</label> 
                                    <div class="col-lg-7  "> {{ $accountDetail->account_no }} </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">Passbook No:</label> 
                                    <div class="col-lg-7  "> {{ $accountDetail->passbook_no }} </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">Branch Name:</label> 
                                    <div class="col-lg-7  "> {{ $accountDetail->savingBranch->name }} </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">Branch Address:</label> 
                                    <div class="col-lg-7  "> {{ $accountDetail->savingBranch->address }} </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">City:</label> 
                                    <div class="col-lg-7  "> {{ getCityName($accountDetail->savingBranch->city_id) }} </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">Pin:</label> 
                                    <div class="col-lg-7  "> {{ $accountDetail->savingBranch->pin_code }} </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">Branch Code:</label> 
                                    <div class="col-lg-7  "> {{ $accountDetail->savingBranch->branch_code }} </div>
                                </div>
                            </div>
                            <div class="col-lg-2 ">
                            </div>
                            <div class="col-lg-5 ">
                                <h3 class="mb-0 text-dark ">Customer Detail</h3>
                                <div class="row">
                                    <label class=" col-lg-4">Member Id :</label> 
                                    <div class="col-lg-7  "> {{ $memberDetail->member_id }}  </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">Customer Name:</label> 
                                    <div class="col-lg-7  "> {{ $memberDetail->first_name }} {{ $memberDetail->last_name }}</div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4"> Address:</label> 
                                    <div class="col-lg-7  "> {{ $memberDetail->address }}  </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">City:</label> 
                                    <div class="col-lg-7  "> {{ getCityName($memberDetail->city_id) }} </div>
                                </div>
                                <div class="row">
                                    <label class=" col-lg-4">Pin:</label> 
                                    <div class="col-lg-7  "> {{ $memberDetail->pin_code }} </div>
                                </div> 
                            </div>
                        </div>
                        <br>
                        <div class="table-responsive">
                            <table   class="table table-flush" style="width: 100%">
                                <thead class="">
                                    <tr>
                                        <th colspan="5"><h5 class="text-right">Current Balance</h5></th>
                                        <th colspan="2"><h5 id="current_balance">{{ $accountDetail->balance}} <img src="{{url('/')}}/asset/images/rs.png" width="9"></h5></th>
                                    </tr>
                                    <tr>
                                        
                                        <th style="width: 10%"> Date</th>
                                        <th>Reference No</th>
                                        <th>Withdrawal</th>
                                        <th>Deposit</th>
                                        <th>Opening Balance</th>
                                        <th>Description</th>  
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($accountTranscation)>0)
                                        @foreach($accountTranscation as $val)
                                        <tr>                                            
                                            <td>{{ date("d/m/Y", strtotime($val->created_at)) }}</td>
                                            <td>{{ $val->reference_no }}</td>
                                            <td>@if($val->withdrawal>0){{ $val->withdrawal }} <img src="{{url('/')}}/asset/images/rs.png" width="7">@endif</td>
                                            <td>@if($val->deposit>0){{ $val->deposit }} <img src="{{url('/')}}/asset/images/rs.png" width="7">@endif</td>
                                            <td>{{ $val->opening_balance }} <img src="{{url('/')}}/asset/images/rs.png" width="7"></td>
                                            <td>{{ $val->description }}</td>
                                        </tr>
                                        @endforeach
                                    @else
                                    <tr><td colspan="7" class="text-center"> No data found!</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 ">
                                <br>
                                <div class="row">
                                    <div class="col-lg-6 text-center ">
                                        <a href='{{URL::to("branch/member/passbook/cover_new/" . $accountDetail->member_investments_id)}}''>Print Passbook</a> 
                                    </div>
                                    <div class="col-lg-6 "> 
                                        <a href="{!! route('branch.passbook_transaction',['id'=>$accountDetail->member_investments_id,'code'=>'703']) !!}" class="text-right">View Statement</a>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
        @else

        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">You does not have SSB account</h3> 
                       
                        <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a>
                </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@stop

@section('script')
@include('templates.branch.saving.partials.listing_script')
@stop