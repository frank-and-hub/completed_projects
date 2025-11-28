@extends('templates.admin.master')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Transaction Detail</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Transaction ID </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $tDetails['id'] }} </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Transaction date </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">
                                        {{ date('d/m/Y', strtotime(str_replace('-', '/', $tDetails['created_at']))) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Name </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $memberDetail->member->first_name ?? 'N/A' }}
                                        {{ $memberDetail->member->last_name ?? 'N/A' }}
                                        <!--( {{ $memberDetail->member_id }} )--></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Account No</label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $tDetails['account_no'] }} </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Description </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $tDetails['description'] }} </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Payment Method</label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">
                                        @if ($tDetails['type'] == 1)
                                            @if ($tDetails['payment_mode'] == 0)
                                                Cash
                                            @elseif($tDetails['payment_mode'] == 1)
                                                Cheque
                                            @elseif($tDetails['payment_mode'] == 2)
                                                DD
                                            @elseif($tDetails['payment_mode'] == 3)
                                                Online
                                            @elseif($tDetails['payment_mode'] == 4)
                                                From SSB
                                            @elseif($tDetails['payment_mode'] == 5)
                                                From Loan Account
                                            @endif
                                        @else
                                            @if ($tDetails['payment_mode'] == 0)
                                                Cash
                                            @elseif($tDetails['payment_mode'] == 1)
                                                Cheque
                                            @elseif($tDetails['payment_mode'] == 2)
                                                DD
                                            @elseif($tDetails['payment_mode'] == 3)
                                                @if ($tDetails['deposit'] > 0)
                                                    Transfer By Other Account
                                                @else
                                                    Transfer To Other Account
                                                @endif
                                            @elseif($tDetails['payment_mode'] == 4)
                                                @if ($tDetails['deposit'] > 0)
                                                    From SSB
                                                @else
                                                    SSB Transfer
                                                @endif
                                            @elseif($tDetails['payment_mode'] == 5)
                                                Online
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">
                                        @if ($tDetails['type'] == 1)
                                            Amount
                                        @else
                                            @if ($tDetails['deposit'] > 0)
                                                Deposit
                                            @else
                                                Withdrawal
                                            @endif
                                        @endif
                                    </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">
                                        @if ($tDetails['deposit'] > 0)
                                            {{ $tDetails['deposit'] }}
                                        @else
                                            {{ $tDetails['withdrawal'] }}
                                        @endif
                                        <img src="{{ url('/') }}/asset/images/rs.png" width="7">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Total balance amount</label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  "> {{ $tDetails['opening_balance'] }} <img
                                            src="{{ url('/') }}/asset/images/rs.png" width="7"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Branch Name </label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">{{ getBranchDetail($tDetails['branch_id'])->name }} </div>
                                </div>
                            </div>
                            @if (isset($associateDetail->associate_no))
                                <div class="col-lg-6">
                                    <div class="  row">
                                        <label class=" col-lg-4">Associate Name </label><label class=" col-lg-1">:</label>
                                        <div class="col-lg-7  ">
                                            @if ($associateDetail)
                                                {{ $associateDetail->first_name }} {{ $associateDetail->last_name }}
                                            @endif
                                        </div>
                                    </div>
                            @endif
                        </div>
                        @if (isset($associateDetail->associate_no))
                            <div class="col-lg-6">
                                <div class="  row">
                                    <label class=" col-lg-4">Associate Code</label><label class=" col-lg-1">:</label>
                                    <div class="col-lg-7  ">
                                        @if ($associateDetail)
                                            {{ $associateDetail->associate_no }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @stop
