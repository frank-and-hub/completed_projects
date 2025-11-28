@extends('templates.admin.master')
@section('content')
    <div class="loader" style="display: none;"></div>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Basic layout-->
                <div class="card my-4">
                    <div class="card-header header-elements-inline">
                        <div class="card-body" id="bank-to-bank">
                            @if (count($errors))
                                <div class="form-group">
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                            {{ Form::open(['url' => route('admin.paytdspayableamount'), 'method' => 'POST', 'id' => 'tds_payable_from', 'name' => 'tds_payable_from', 'enctype' => 'multipart/form-data']) }}
                            <div class="row ">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Company <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <select class="form-control" id="company_id" name="company_id">
                                                <option value="">---- Please Select Company----</option>
                                                @foreach ($AllCompany as $key => $val)
                                                    <option value="{{ $key }}"
                                                        {{ $companyId == $key ? 'selected' : '' }}>{{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Payment Date <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            {{ Form::text('payable_payment_date', $payment_date ?? '', ['id' => 'payable_payment_date', 'class' => 'form-control', 'readonly' => true]) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Select Bank <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <select class="form-control" id="bank_id" name="bank_id">
                                                <option value="">---- Please Select ----</option>
                                                @foreach ($SamraddhBanks as $key => $val)
                                                    <option value="{{ $key }}" {{ isset($bank_id) ? ($key == $bank_id ? 'selected' : '') : '' }}> {{ $val }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Select A/C <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            <select class="form-control" id="account_id" name="account_id">
                                                @if ($view == 0)
                                                    <option value="">---- Please Select ----</option>
                                                    @foreach ($SamraddhBankAccounts as $bankAccounts)
                                                        <option data-bank-id="{{ $bankAccounts->bank_id }}" value="{{ $bankAccounts->id }}" class="bank-account {{ $bankAccounts->bank_id }}-bank-account" style="display:none;">{{ $bankAccounts->account_no }}</option>
                                                    @endforeach
                                                @else
                                                    <option>{{ $account_no ?? '' }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Bank Available Balance </label>
                                        <div class="col-lg-8">
                                            {{ Form::text('bank_available_balance', $bank_available_balance ?? '', ['id' => 'bank_available_balance', 'class' => 'form-control', 'readonly' => true]) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">TDS Amount <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            {{ Form::text('payable_tds_amount', $tds_amount??'0.00', ['id' => 'payable_tds_amount', 'class' => 'form-control', 'readonly' => false]) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Late Penalty <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            {{ Form::text('payable_late_penalty', $late_penalty ?? '0.00', ['id' => 'payable_late_penalty', 'class' => 'form-control']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Total Paid Amount <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            {{ Form::text('total_paid_amount', $total_paid ?? '', ['id' => 'total_paid_amount', 'class' => 'form-control', 'readonly' => true]) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">UTR number / Transaction Number
                                            <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            {{ Form::text('transaction_number', $transaction_number ?? '', ['id' => 'transaction_number', 'class' => 'form-control']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">RTGS/NEFT Charge</label>
                                        <div class="col-lg-8">
                                            {{ Form::text('neft_charge', $neft_charge ?? '', ['id' => 'neft_charge', 'class' => 'form-control']) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Upload Challan <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            @if ($view == 0)
                                                {{ Form::file('upload_challan', ['id' => 'upload_challan', 'class' => 'form-control', 'accept' => 'image/jpeg, image/png, image/jpg, image/ico, image/gif, image/svg, image/pdf, image/webp']) }}
                                            @endif
                                            <a href="{{ $ChalanSrc ?? '' }}" style="vertical-align: text-top" class="text-primary h-100 w-100 text-left" title="Vew File" target="_blank" class="">{{ $ChalanFile ?? '' }}</a>
                                            @if ($view == 1)
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Remark <sup>*</sup></label>
                                        <div class="col-lg-8">
                                            {{ Form::text('remark', $remark ?? '', ['id' => 'remark', 'class' => 'form-control']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($view != 1)
                                <div class="text-right">
                                    {{ Form::hidden('payable_start_date', $startDate, ['id' => 'payable_start_date', 'class' => 'form-control']) }}
                                    {{ Form::hidden('payable_end_date', $endDate, ['id' => 'payable_end_date', 'class' => 'form-control']) }}
                                    {{ Form::hidden('payable_head_id', $head_id, ['id' => 'payable_head_id', 'class' => '']) }}
                                    {{ Form::hidden('payable_paid_amount', '', ['id' => 'payable_paid_amount', 'class' => 'form-control']) }}
                                    {{ Form::hidden('daybook_diff', $daybook_diff, ['id' => 'daybook_diff', 'class' => 'form-control']) }}
                                    {{ Form::hidden('id', $id, ['id' => 'id', 'class' => 'form-control']) }}
                                    {{ Form::hidden('created_at', '', ['id' => 'created_at', 'class' => 'created_at']) }}
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit-payable">
                                </div>
                            @endif
                            {{ Form::close() }}
                        </div>
                    </div>
                    <!-- /basic layout -->
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script src="{{ url('/') }}/asset/js/sweetalert.min.js"></script>
    @include('templates.admin.tds_payable.partials.tds_payable_script')
@endsection
