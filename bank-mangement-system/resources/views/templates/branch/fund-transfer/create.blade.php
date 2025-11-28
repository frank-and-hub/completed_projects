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
                            <a href="{!! route('') !!}" style="float:right" class="btn btn-secondary">Back</a>
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
                            <div class="form-group row">
                                <div class="col-lg-4">
                                    <h3 class="mb-0">Fund Transfer</h3>
                                    <div class="header-elements">
                                        <div class="list-icons"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <select name="fund_transfer" id="fund_transfer" class="form-control">
                                        <option value="0">Branch To Head Office</option>
                                        <option value="1">Bank To Bank</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body" id="branch-to-ho">
                                <form action="{{route('branch.fund.transfer.head.office')}}" method="post" enctype="multipart/form-data" id="fund-transfer-head-office"
                                      name="fund-transfer-head-office">
                                    @csrf
                                    @php
                                        $stateid = getBranchStateByManagerId(Auth::user()->id);
                                    @endphp
                                    <input type="hidden" name="branch_id" id="branch_id" value="{{ $branch_id }}">
                                    <input type="hidden" name="created_at" id="created_at" value="{{ checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">

                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Branch</label>
                                        <div class="col-lg-4">
                                            <select name="branch_id" id="" class="form-control" readonly>
                                                @foreach( $branches as $branch)
                                                    <option value="{{ $branch->id }}" {{$branch_id == $branch->id ? 'selected' : ''}}>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label class="col-form-label col-lg-2">Select Date<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <input type="text" id="date" name="date" class="form-control" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Branch Code</label>
                                        <div class="col-lg-4">
                                            @php
                                                $branchLogin = null;
                                                foreach( $branches as $branch)
                                                    if ($branch_id == $branch->id) {
                                                        $branchLogin = $branch;
                                                    }
                                            @endphp
                                            <input type="text" name="branch_code" id="branch_code" class="form-control" placeholder="Branch Code" value="{{ $branchLogin->branch_code }}" readonly="">
                                        </div>

                                        <label class="col-form-label col-lg-2">	Loan Daybook Amount </label>
                                        <div class="col-lg-4">
                                            <input type="text" name="loan_daybook_amount" id="loan_daybook_amount" class="form-control" placeholder="0.00" >
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Transfer Mode <sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <select name="transfer_mode" id="transfer_mode" class="form-control">
                                                <option value="">----Select Transfer Mode----</option>
                                                <option value="0">Loan</option>
                                                <option value="1">Micro</option>
                                            </select>
                                        </div>

                                        <label class="col-form-label col-lg-2">Micro Daybook Amount <sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <input type="text" name="micro_daybook_amount" id="micro_daybook_amount" class="form-control" placeholder="0.00" >
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">	Transfer Amount </label>
                                        <div class="col-lg-4">
                                            <input type="text" name="transfer_amount" id="transfer_amount" class="form-control" placeholder="0.00" >
                                        </div>

                                        <label class="col-form-label col-lg-2">Confirm Amount</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="conform_transfer_amount" id="conform_transfer_amount" class="form-control" placeholder="0.00" >
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">	Bank</label>
                                        <div class="col-lg-4">
                                            <select name="bank" id="bank" class="form-control">
                                                @foreach( $banks as $bank)
                                                    <option value="{{ $bank->id }}" data-account="{{ $bank->account_number }}">{{ $bank->title }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" id="bank_to_head_office" name="bank_to_head_office" value="{{ $banks[0]->account_number }}"/>
                                        </div>

                                        <label class="col-form-label col-lg-2">Upload Bank Slip </label>
                                        <div class="col-lg-4">
                                            <span class="signature"><input type="file" class="" name="bank_slip"/></span>
                                        </div>
                                    </div>

                                    <div class="form-group row bank" style="display: none;">
                                        <label class="col-form-label col-lg-2">Bank A/c</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" placeholder="Bank A/c" readonly="">
                                        </div>

                                        <label class="col-form-label col-lg-2">Select Mode</label>
                                        <div class="col-lg-4">
                                            <select name="bank_mode" id="bank_mode" class="form-control">
                                                <option value="">----Please Select----</option>
                                                <option value="0">Cheque</option>
                                                <option value="1">Online</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2 cheque" style="display: none;">Cheque Number</label>
                                        <div class="col-lg-4 cheque" style="display: none;">
                                            <select name="cheque_number" id="cheque_number" class="form-control">
                                                <option value="">----Please Select----</option>
                                                @foreach( $cheques as $key => $val )
                                                    <option value="{{ $val->cheque_no }}"  >{{ $val->cheque_no }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <label class="col-form-label col-lg-2 online" style="display: none;">No/UTR No</label>
                                        <div class="col-lg-4 online" style="display: none;">
                                            <input type="text" name="utr_no" id="utr_no" class="form-control">
                                        </div>

                                        <label class="col-form-label col-lg-2 online" style="display: none;">RTGS/NEFT Charge</label>
                                        <div class="col-lg-4 online" style="display: none;">
                                            <input type="text" name="rtgs_neft_charge" id="rtgs_neft_charge" class="form-control">
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit-investment">
                                    </div>

                                </form>
                            </div>
                            <div class="card-body" id="bank-to-bank" style="display: none;">
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
                                                        @foreach( $banks as $bank)
                                                            <option value="{{ $bank->id }}" data-account="{{ $bank->account_number }}">{{ $bank->title }}</option>
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
                                                            <option value="{{ $bank->id }}" data-account="{{ $bank->account_number }}">{{ $bank->title }}</option>
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