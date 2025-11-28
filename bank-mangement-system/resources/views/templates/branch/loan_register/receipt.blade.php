@extends('layouts/branch.dashboard')

@section('content')

<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body page-title">
                        <h3 class="">Transaction Receipt</h3>
                        <a href="{{ URL::previous() }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="print_recipt">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body">
                        <h3 class="card-title mb-3 text-center">Receipt Detail</h3>

                        <div class="row" id="">

                            <div class="row col-lg-12">
                                <label class="col-lg-4">Branch Name : </label>
                                <div class="col-lg-4">
                                    @if($data->branch_id)
                                    {{ $data->loanBranch->name }}
                                    @else
                                    N/A
                                    @endif
                                </div>
                            </div>
                            <div class="row col-lg-12">
                                <label class="col-lg-4">Branch Code : </label>
                                <div class="col-lg-4">
                                    @if($data->branch_id)
                                    {{ $data->loanBranch->branch_code }}
                                    @else
                                    N/A
                                    @endif
                                </div>
                            </div>
                            <div class="row col-lg-12">
                                <label class="col-lg-4">Date : </label>
                                <div class="col-lg-4">
                                    {{ date("d/m/Y", strtotime($data->created_at))}}
                                </div>
                            </div>

                            <?php

                            ?>

                            <div class="row col-lg-12">
                                <label class="col-lg-4">Customer ID Number : </label>
                                <div class="col-lg-8">
                                    @if(isset($data->customer_id))
                                    {{ $data->member->member_id }}
                                    @else
                                    N/A
                                    @endif
                                </div>
                            </div>
                            <div class="row col-lg-12">
                                <label class="col-lg-4">Member ID Number : </label>
                                <div class="col-lg-8">
                                    @if(isset($data->applicant_id))
                                    {{ $data->memberCompany->member_id }}
                                    @else
                                    N/A
                                    @endif
                                </div>
                            </div>
                            <div class="row col-lg-12">
                                <label class="col-lg-4">Member Name : </label>
                                <div class="col-lg-8">
                                    @if(isset($data->customer_id))
                                    {{ $data->member->first_name. ' '.$data->member->last_name }}
                                    @else
                                    N/A
                                    @endif
                                </div>
                            </div>
                            @if($data->associate_member_id)
                            <div class="row col-lg-12">
                                <label class="col-lg-4">Associate Name : </label>
                                <div class="col-lg-8">
                                    {{ $data->loanMemberCustom->first_name. ' '.$data->loanMemberCustom->last_name }}
                                </div>
                            </div>
                            @endif
                            @if($data->associate_member_id)
                            <div class="row col-lg-12">
                                <label class="col-lg-4">Associate Code : </label>
                                <div class="col-lg-8">
                                    {{ $data->loanMemberCustom->associate_no }}
                                </div>
                            </div>
                            @endif
                            @if($data->loan_type)
                            <div class="row col-lg-12">
                                <label class="col-lg-4">Plan Name : </label>
                                <div class="col-lg-8">
                                    {{ $data->loan->name }}
                                </div>
                            </div>
                            @endif

                            <div class="row col-lg-12">
                                <label class="col-lg-4">Loan Amount : </label>
                                <div class="col-lg-8">
                                    {{ $data->amount }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                                </div>
                            </div>

                            @if($data->mi_charge > 0)
                            <div class="row col-lg-12">
                                <label class="col-lg-4">Mi Charge : </label>
                                <div class="col-lg-8">
                                    {{ $data->mi_charge }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                                </div>
                            </div>
                            @endif

                            @if($data->stn_charge > 0)
                            <div class="row col-lg-12">
                                <label class="col-lg-4">STN Charge : </label>
                                <div class="col-lg-8">
                                    {{ $data->stn_charge }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($data->ssb_id))
        <div class="col-lg-12" id="">
            <div class="card bg-white">
                <div class="card-body">
                    <h3 class="card-title mb-3 text-center">SSB Receipt Detail</h3>
                    <div class="row">

                        <div class="row col-lg-12">
                            <label class="col-lg-4">Date : </label>
                            <div class="col-lg-4">
                                {{ date("d/m/Y", strtotime($data->newLoanSSB->created_at))}}
                            </div>
                        </div>

                        <?php

                        ?>

                        <div class="row col-lg-12">
                            <label class="col-lg-4">Customer ID Number : </label>
                            <div class="col-lg-8">
                                @if(isset($data->customer_id))
                                {{ $data->member->member_id }}
                                @else
                                N/A
                                @endif
                            </div>
                        </div>
                        <div class="row col-lg-12">
                            <label class="col-lg-4">Member ID Number : </label>
                            <div class="col-lg-8">
                                @if(isset($data->applicant_id))
                                {{ $data->memberCompany->member_id }}
                                @else
                                N/A
                                @endif
                            </div>
                        </div>
                        <div class="row col-lg-12">
                            <label class="col-lg-4">Member Name : </label>
                            <div class="col-lg-8">
                                @if(isset($data->customer_id))
                                {{ $data->member->first_name. ' '.$data->member->last_name }}
                                @else
                                N/A
                                @endif
                            </div>
                        </div>
                        <div class="row col-lg-12">
                            <label class="col-lg-4">Account Number : </label>
                            <div class="col-lg-8">
                                {{ $data->newLoanSSB->account_no }}
                            </div>
                        </div>

                        @if($data->loan_type)
                        <div class="row col-lg-12">
                            <label class="col-lg-4">Plan Name : </label>
                            <div class="col-lg-8">
                                {{ $data->newLoanSSB->getPlanCompany->name }}
                            </div>
                        </div>
                        @endif
                        @if(isset($data->newLoanSSB->getRegisterAmount))
                        <div class="row col-lg-12">
                            <label class="col-lg-4">Amount : </label>
                            <div class="col-lg-8">
                                {{ $data->newLoanSSB->getRegisterAmount->deposit }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                            </div>
                        </div>
                        @endif
                        @if($data->stationary_charge > 0)
                        <div class="row col-lg-12">
                            <label class="col-lg-4">Stationary Charge : </label>
                            <div class="col-lg-8">
                                {{ $data->stationary_charge }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                            </div>
                        </div>
                        @endif

                        @if($data->newLoanSSB->getMemberinvestments->cgst_stationary_chrg > 0)
                        <div class="row col-lg-12">
                            <label class="col-lg-4">CGST Charge : </label>
                            <div class="col-lg-8">
                                {{ $data->newLoanSSB->getMemberinvestments->cgst_stationary_chrg }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                            </div>
                        </div>
                        @endif

                        @if($data->newLoanSSB->getMemberinvestments->sgst_stationary_chrg > 0)
                        <div class="row col-lg-12">
                            <label class="col-lg-4">SGST Charge : </label>
                            <div class="col-lg-8">
                                {{ $data->newLoanSSB->getMemberinvestments->sgst_stationary_chrg }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                            </div>
                        </div>
                        @endif

                        @if($data->newLoanSSB->getMemberinvestments->igst_stationary_chrg > 0)
                        <div class="row col-lg-12">
                            <label class="col-lg-4">IGST Charge : </label>
                            <div class="col-lg-8">
                                {{ $data->newLoanSSB->getMemberinvestments->igst_stationary_chrg }} <img src="{{url('/')}}/asset/images/rs.png" width="9">
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
        @endif
        </div>

       

        <!-- <div class="col-lg-12">
            <div class="card bg-white">
                <div class="card-body">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" onclick="printDiv('print_recipt');">Print<i class="icon-paperplane ml-2"></i></button>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</div>
@endsection


@section('script')
@include('templates.branch.investment_management.partials.script')
@stop
