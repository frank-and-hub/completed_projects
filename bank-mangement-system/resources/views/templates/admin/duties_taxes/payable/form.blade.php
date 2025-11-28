<div class="col-md-6">
    <div class="form-group row">
        <label class="col-form-label col-lg-4">Payable Amount <sup>*</sup></label>
        <div class="col-lg-8">
            {{Form::text('payable_amount',$amount??0,['id'=>'payable_amount','class'=>'form-control','readonly'=>true])}}
        </div>
    </div>
</div>
{{Form::hidden('payable_paid_amount',$amount??0,['id'=>'payable_paid_amount','class'=>'form-control'])}}
{{Form::hidden('tds_transfer_export',$amount??0,['id'=>'tds_transfer_export','class'=>'form-control'])}}
<div class="col-md-6">
    <div class="form-group row">
        <label class="col-form-label col-lg-4">Payment Date <sup>*</sup></label>
        <div class="col-lg-8">
            {{ Form::text('payable_payment_date', $payment_date ?? '', ['id' => 'payable_payment_date', 'class' => 'form-control','readonly'=>true]) }}
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
        <label class="col-form-label col-lg-4">UTR / Transaction Number
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