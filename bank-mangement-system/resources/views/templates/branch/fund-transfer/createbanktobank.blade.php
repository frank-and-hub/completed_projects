@extends('layouts/branch.dashboard')

@section('content')

    <div class="loader" style="display: none;"></div>

    <div class="container-fluid mt--6">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body page-title">
                            <h3 class="">{{$title}}</h3>
                            <a href="{!! route('branch.fundtransfer.banktobank') !!}" style="float:right" class="btn btn-secondary">Back</a>
                            <!-- Validate error messages -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                        @endif
                        <!-- Validate error messages -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <!-- Basic layout-->
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <div class="card-body" id="bank-to-bank">
                                <form action="{{route('branch.fund.transfer.bank')}}" method="post" id="fund-transfer-bank" name="fund-transfer-bank">
                                    @csrf
                                    @php
                                        $stateid = getBranchStateByManagerId(Auth::user()->id);
                                    $branchLogin = null;
                                        foreach( $branches as $branch)
                                            if ($branch_id == $branch->id) {
                                                $branchLogin = $branch;
                                            }
                                    @endphp
                                    <input type="hidden" name="branch_id" id="branch_id" value="{{ $branch_id }}">
                                    <input type="hidden" name="branch_code" value="{{ $branchLogin->branch_code }}">
                                    <input type="hidden" name="created_at" id="created_at" value="{{ checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
                                    <div class="form-group row">
                                        <div class="form-group col-lg-6">
                                            <div class="form-group row"><h4 class="mb-0">From Bank</h4></div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Bank<sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <select name="from_bank" id="from_bank" class="form-control">
                                                        @foreach( $banks as $key => $bank)
                                                            @if($bank['bankAccount'])
                                                                <option value="{{ $bank->id }}" data-account="{{ $bank['bankAccount']->account_no }}">{{ $bank->bank_name }}</option>
                                                            @else
                                                                <option value="{{ $bank->id }}" data-account="">{{ $bank->bank_name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Bank A/c<sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="from_Bank_account_no" id="from_Bank_account_no" class="form-control" placeholder="Bank A/c Account" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Transfer Cheque No/UTR No <sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="from_cheque_number" id="from_cheque_number" class="form-control" placeholder="Transfer Cheque No/UTR No" required="" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">RTGS/NEFT Charge <sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="rtgs_neft_charge" id="rtgs_neft_charge" class="form-control" placeholder="RTGS/NEFT Charge" required="" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Transfer Amount<sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="bank_transfer_amount" id="bank_transfer_amount" class="form-control" placeholder="Transfer Amount" required="" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-lg-6">
                                            <div class="form-group row"><h4 class="mb-0">To Bank</h4></div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Bank<sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <select name="to_bank" id="to_bank" class="form-control">
                                                        @foreach( $banks as $bank)
                                                            @if($bank['bankAccount'])
                                                                <option value="{{ $bank->id }}" data-account="{{ $bank['bankAccount']->account_no }}">{{ $bank->bank_name }}</option>
                                                            @else
                                                                <option value="{{ $bank->id }}" data-account="">{{ $bank->bank_name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Bank A/c<sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="to_Bank_account_no" id="to_Bank_account_no" class="form-control" placeholder="Bank A/c Account" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Receive Cheque No/UTR No <sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="to_cheque_number" id="to_cheque_number" class="form-control" placeholder="Receive Cheque No/UTR No" required="" autocomplete="off">
                                                </div>
                                            </div>
                                            {{-- <label class="col-form-label col-lg-12">RTGS/NEFT Charge <sup>*</sup></label>
                                             <div class="col-lg-12">
                                                 <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control" placeholder="SSB Account" required="" autocomplete="off">
                                             </div>--}}
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Receive Amount<sup>*</sup></label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="bank_receive_amount" id="bank_receive_amount" class="form-control" placeholder="Receive Amount" required="" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-4">Remark</label>
                                                <div class="col-lg-8">
                                                    <textarea type="text" name="remark" id="remark" class="form-control" placeholder="Remark" required="" autocomplete="off"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit-investment">
                                    </div>

                                </form>
                            </div>
                        </div>
                        <!-- /basic layout -->
                    </div>
                </div>
@stop

@section('script')
    @include('templates.branch.fund-transfer.partials.script')
@stop