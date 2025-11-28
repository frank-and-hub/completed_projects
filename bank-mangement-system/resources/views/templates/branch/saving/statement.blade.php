@extends('layouts/branch.dashboard')

@section('content')


<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Statement For : {{ $accountDetail->account_no  }}</h3>
                        <a href="{!! route('branch.savingDetail',['id'=>$memberDetail->id]) !!}" style="float:right" class="btn btn-secondary">Back</a>
                    
                </div>
                </div>
            </div> 
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-body">
                        
                        <form   method="post" enctype="multipart/form-data" action="{!! route('branch.statementfilter',['id'=>$accountDetail->id,'member'=>$memberDetail->id]) !!}" id="fillter" name="fillter">
                        @csrf
                         
                         
                                <h3 class="card-title mb-3">Filter</h3>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Start Date<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                              <div class="input-group">
                                                <span class="input-group-prepend">
                                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                                </span>
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"   >
                                                 <input type="hidden" class="form-control  " name="id" id="id"    value="{{ $accountDetail->id  }}">
                                               </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                      <div class="form-group row">
                                          <label class="col-form-label col-lg-12">End Date<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                              <div class="input-group">
                                                <span class="input-group-prepend">
                                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                                </span>
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                               </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 text-center">
                                        <div class=" " style="margin-top: 45px"> 
                                            <button type="submit" class="btn btn-primary">Submit<i class="icon-paperplane ml-2"></i></button>

                                            <button type="reset" class="btn btn-secondary" id="reset_form">Reset </button>
                                             
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" > 
        
            <div class="col-lg-12" id="print_passbook">                

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
                <?php if(isset($is_fillter))  {?> 
                        <div class="table-responsive">
                            <table   class="table table-flush" style="width: 100%" id="listPassbook">
                                <thead class="">
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
                                            <td>{{ date("m/d/Y", strtotime($val->created_at)) }}</td>
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
                        <?php } ?>                       
                    </div>
                </div> 
            </div> 

        </div>


    </div>
</div>
@stop

@section('script')
@include('templates.branch.saving.partials.script')
@stop