@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
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
                <div class="card bg-white">
                    <div class="card-body">
                        <h3 class="card-title mb-3 text-center">Receipt Detail</h3>
                        <div class="row">
                            <label class=" col-lg-4">Investment Plan : </label>
                            <div class="col-lg-4">
                                @if($renewFields['renewplan_id'] == 0)
                                Daily Deposite
                                @elseif($renewFields['renewplan_id'] == 1)
                                RD/FRD Renewal
                                @else
                                Deposite Saving Account
                                @endif
                            </div>
                        </div>
                        @if($renewFields['renewplan_id'] == 0)
                        <div class="row">
                            <label class=" col-lg-4">Collector Code : </label>
                            <div class="col-lg-4">
                                {{ $renewFields['collector_code'] }}
                            </div>
                        </div>
                        <div class="row">
                            <label class=" col-lg-4">Collector Name : </label>
                            <div class="col-lg-4">
                                {{ $renewFields['collector_name'] }}
                            </div>
                        </div>
                        @endif
                        <div class="row">
                            <label class=" col-lg-4">Date : </label>
                            <div class="col-lg-4">
                                @php
                                $bName = str_replace('"}',"",str_replace('{"name":"',"",$branchName));
                                $stateid = getBranchState($bName);
                                $rDate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
                                @endphp
                                {{ $renewFields['renewal_date'] }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4">Branch Code : </label>
                            <div class="col-lg-4">
                                {{ $branchCode }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4">Branch Name : </label>
                            <div class="col-lg-4">
                                {{ $bName }}
                            </div>
                        </div>
                        @if($renewFields['renewplan_id'] == 1)
                        <div class="row">
                            <label class="col-lg-4">Associate Code : </label>
                            <div class="col-lg-4">
                                {{ $renewFields['rdfrd_associate_code'] }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4">Associate Name : </label>
                            <div class="col-lg-4">
                                {{ $renewFields['rdfrd_associate_name'] }}
                            </div>
                        </div>
                        @endif
                        @if($renewFields['renewplan_id'] == 2)
                        <div class="row">
                            <label class="col-lg-4">Associate Code : </label>
                            <div class="col-lg-4">
                                {{ $renewFields['associate_code'] }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4">Associate Name : </label>
                            <div class="col-lg-4">
                                {{ $renewFields['associate_name'] }}
                            </div>
                        </div>
                        @endif
                        <?php $i = 1; ?>
                        @foreach($renewFields['account_number'] as $key => $accountDetail)
                        @if($accountDetail)
                        @if($renewFields['renewplan_id'] != 2)
                        <h3>Account Details - {{ $i }}</h3>
                        @endif
                        <div class="row">
                            <label class="col-lg-4">Account number : </label>
                            <div class="col-lg-8">
                                {{ $accountDetail }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4">Account Holder Member Name : </label>
                            <div class="col-lg-8">
                                <?php //print_r($renewFields['scheme_name']);exit;
                                ?>
                                @if($renewFields['scheme_name'] == 'Saving Account' || $renewFields['scheme_name'] == 'Daily Deposite' || $renewFields['scheme_name'] == 'Recurring Deposit')
                                {{ $renewFields['name'][$key] }}
                                @else
                                {{ $renewFields['name']->$key }}
                                @endif
                            </div>
                        </div>
                        @if($renewFields['renewplan_id'] != 2)
                        <div class="row">
                            <label class="col-lg-4">Scheme Name : </label>
                            <div class="col-lg-8">
                                {{ $renewFields['scheme_name'] }}
                            </div>
                        </div>
                        @if($renewFields['renew_investment_plan_id'] != 2)
                        <div class="row">
                            <label class="col-lg-4">Tenure : </label>
                            <div class="col-lg-8">
                                @if($renewFields['scheme_name'] == 'Saving Account' || $renewFields['scheme_name'] == 'Daily Deposite' || $renewFields['scheme_name'] == 'Recurring Deposit')
                                {{ $renewFields['investment_tenure'][$key] }} Years
                                @else
                                {{ $renewFields['investment_tenure']->$key }} Years
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="row">
                            <label class="col-lg-4">Tenure : </label>
                            <div class="col-lg-8">
                                @if($renewFields['scheme_name'] == 'Saving Account' || $renewFields['scheme_name'] == 'Daily Deposite' || $renewFields['scheme_name'] == 'Recurring Deposit')
                                {{ $renewFields['investment_tenure'][$key] }} Months
                                @else
                                {{ $renewFields['investment_tenure']->$key }} Months
                                @endif
                            </div>
                        </div>
                        @endif
                        @endif
                        <div class="row">
                            <label class="col-lg-4">Amount : </label>
                            <div class="col-lg-8">
                                @if($renewFields['scheme_name'] == 'Saving Account' || $renewFields['scheme_name'] == 'Daily Deposite' || $renewFields['scheme_name'] == 'Recurring Deposit')
                                {{ $renewFields['amount'][$key] }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                                @else
                                {{ $renewFields['amount']->$key }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                                @endif
                            </div>
                        </div>
                        @if($renewFields['renewplan_id'] == 0 || $renewFields['renewplan_id'] == 1)
                        <div class="row">
                            <label class="col-lg-4">Due Amount : </label>
                            <div class="col-lg-8">
                                @if($renewFields['scheme_name'] == 'Saving Account' || $renewFields['scheme_name'] == 'Daily Deposite' || $renewFields['scheme_name'] == 'Recurring Deposit')
                                {{ $renewFields['deo_amount'][$key] }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                                @else
                                {{ $renewFields['deo_amount']->$key }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($renewFields['renewplan_id'] == 0)
                        <?php /*?> {{-- <div class="  row">
                    <label class=" col-lg-4  ">Associate Code : </label>
                    <div class="col-lg-8   ">
                      @if($renewFields['scheme_name'] == 'Saving Account' || $renewFields['scheme_name'] == 'Daily Deposite'  || $renewFields['scheme_name'] == 'Recurring Deposit')
                        {{ $renewFields['acoount_associate_code'][$key] }}
                      @else
                        {{ $renewFields['acoount_associate_code']->$key }}
                      @endif
                    </div>
                  </div> --}}<?php */ ?>
                        <div class="row">
                            <label class="col-lg-4">Associate Name : </label>
                            <div class="col-lg-8">
                                @if($renewFields['scheme_name'] == 'Saving Account' || $renewFields['scheme_name'] == 'Daily Deposite' || $renewFields['scheme_name'] == 'Recurring Deposit')
                                {{ $renewFields['acoount_associate_name'][$key] }}
                                @else
                                {{ $renewFields['acoount_associate_name']->$key }}
                                @endif
                            </div>
                        </div>
                        @endif
                        @endif
                        <?php $i++; ?>
                        @endforeach
                        <div class="row">
                            <label class="col-lg-4">Payment Mode : </label>
                            <div class="col-lg-8">
                                @if($renewFields['payment_mode'] == 0)
                                Cash
                                @elseif($renewFields['payment_mode'] == 1)
                                Cheque
                                @elseif($renewFields['payment_mode'] == 2)
                                DD
                                @elseif($renewFields['payment_mode'] == 3)
                                Online transaction
                                @elseif($renewFields['payment_mode'] == 4)
                                SSB Account
                                @endif
                            </div>
                        </div>
                        @if($renewFields['payment_mode'] == 1)
                        <div class="row">
                            <label class="col-lg-4">Cheque Number : </label>
                            <div class="col-lg-8">
                                {{ $renewFields['cheque-number'] }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4">Bank Name : </label>
                            <div class="col-lg-8">
                                {{ $renewFields['bank-name'] }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4">Branch Name : </label>
                            <div class="col-lg-8">
                                {{ $renewFields['branch-name'] }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4">Cheque Date : </label>
                            <div class="col-lg-8">
                                {{ date("d/m/Y", strtotime(str_replace('/','-',$renewFields['cheque-date']))) }}
                            </div>
                        </div>
                        @endif
                        <!--  @if($renewFields['renewplan_id'] == 2)
              <div class="  row">
                <label class=" col-lg-4  ">Total Amount : </label>
                <div class="col-lg-8   ">
                  {{ $ssb_amount->opening_balance}} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                </div>
              </div>
              @else
 -->
                        <?php //print_r($ssb_amount);exit;
                        ?>
                        <div class="row">
                            <label class="col-lg-4">Total Amount : </label>
                            <div class="col-lg-8">
                                {{ $ssb_amount->opening_balance }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                            </div>
                        </div>
                        <!--  @endif -->
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body">
                        <div class="text-center">
                            {{-- @if( in_array('Renewal Print', auth()->user()->getPermissionNames()->toArray() ) )
                  <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>
                @endif --}}
                            <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>
                            <a href="{!! route('admin.renew') !!}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('script')
@include('templates.admin.investment_management.partials.script')
@stop
