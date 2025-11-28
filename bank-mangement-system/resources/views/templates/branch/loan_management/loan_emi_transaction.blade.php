@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        @if($loanDetails)
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body page-title">
                        <h3 class="">EMI Transaction For :{{ $loanDetails->account_number }} - {{ $loanTitle }}</h3>
                        <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a>
                    </div>
                </div>

                <!-- <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="loan_emi_transaction_filter" name="loan_emi_transaction_filter">
                            @csrf -->
                            <!-- <div class="row"> -->
                            <!-- @include('templates.GlobalTempletes.role_type',[
                            'dropDown'=> $branchCompany[Auth::user()->branches->id],
                            'name'=>'emi_transaction_company_id',
                            'apply_col_md'=>false,
                            'filedTitle' => 'Company'
                            ]) -->
                            <!-- <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="loan_recovery_export" id="loan_recovery_export" value="">
                                        <button type="button" class=" btn btn-primary legitRipple" onClick="emiTransactionSearchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="emiTransactionResetForm()">Reset </button>
                                    </div>
                                </div>
                            </div> -->
                            <!-- </div> -->
                        <!-- </form>
                    </div>
                </div> -->

                <!-- <div class="card bg-white shadow">
                  <div class="card-body">                      
                      <form   method="post" enctype="multipart/form-data" action="{!! route('admin.transaction_start') !!}" id="fillter" name="fillter">
                      @csrf
                        <h3 class="card-title mb-3">Print Fillter</h3>
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-5">Transaction ID From<sup class="required">*</sup></label>
                                    <div class="col-lg-7 error-msg ">
                                        <input type="text" name="transaction_id_from" id="transaction_id_from"  class="form-control  ">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-5">Transaction ID To<sup class="required">*</sup></label>
                                    <div class="col-lg-7 error-msg">
                                        <input type="text" name="transaction_id_to" id="transaction_id_to"  class="form-control  ">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-2 text-center">
                                <div class=" " > 
                                    <button type="submit" class="btn btn-primary">Submit<i class="icon-paperplane ml-2"></i></button> 
                                     
                                </div>
                            </div>
                        </div>
                      </form>
                  </div>
              </div> -->
            </div>
        </div>

        <div class="row ">
            <div class="col-lg-12">
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Transactions Listing</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <input type="hidden" name="loanId" id="loanId" value="{{ $id }}">
                            <input type="hidden" name="loanType" id="loanType" value="{{ $type }}">
                            <table class="table table-flush" style="width: 100%" id="listtansaction">
                                <thead class="">
                                    <tr>
                                        <th style="width: 10%"> S.No</th>
                                        <th style="width: 10%"> Transaction ID</th>
                                        <th style="width: 10%">Transaction Date</th>
                                        <th>Payment Mode</th>
                                        {{-- <th>Customer ID</th> --}}
                                        <th>Description</th>
                                        <th>Sanction Amount</th>
                                        {{-- <th>Loan Penalty</th> --}}
                                        <th>Deposit</th>
                                        <th>JV Amount</th>
                                        <th>IGST Charge</th>
                                        <th>CGST Charge</th>
                                        <th>SGST Charge</th>
                                        <!--<th>Principal Amount</th>-->
                                        <!--<th>Opening Balance</th>-->
                                        <th>Balance</th>
                                        <!-- <th>Action</th> -->
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body">
                        <div class="text-center">
                            <h3 class="">EMI not found</h3>
                            <a href="{!! route('branch.member_loanlist') !!}" style="float:right" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@stop

@section('script')
@include('templates.branch.loan_management.partials.script')
@endsection