@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{ $memberDetail->first_name }} {{ $memberDetail->last_name }}({{ $memberDetail->member_id}}) - Account Detail</h6>
                    </div>
                </div>
            </div> 
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Member Account Detail</h6>
                    </div>
                    @if($accountDetail)
                    <div class="">
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
                    </div>

                        <table id="member_account" class="table datatable-show-all">
                            <thead>
                                <tr>
                                        <th colspan="5"><h5 class="text-right">Current Blance</h5></th>
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

                    <br>
                    <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <a href="{{ route('admin.accountstatement',['id'=>$accountDetail->id,'member'=>$memberDetail->id])}}" class="text-right btn btn-default">View Statement</a>
                                    
                        </div>
                    </div>
                </div>
                    @else
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h3 class="">You does not have SSB account</h3>
                                    
                        </div>
                    </div>
                </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.member.partials.listing_js')
@stop