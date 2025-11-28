<h3 class="cheque-mode cheque-mode-1 p-mode " @if($investments->payment_mode != 1) style="display:none;" @endif >Cheque</h3>

@foreach($investments['investmentPayment'] as $val)

<div class="form-group row cheque-mode cheque-mode-1 p-mode" @if($investments->payment_mode != 1) style="display:none;" @endif >
  <label class="col-form-label col-lg-2">Cheque Number</label>
  <div class="col-lg-4">
    <input type="text" name="cheque-number" id="cheque-number" class="form-control" value="{{ $val->cheque_number }}" disabled="">
  </div>
  <label class="col-form-label col-lg-2">Bank Name</label>
  <div class="col-lg-4">
    <input type="text" name="bank-name" id="bank-name" class="form-control" value="{{ $val->bank_name }}" disabled="">
  </div>
</div>

<div class="form-group row cheque-mode cheque-mode-1 p-mode" @if($investments->payment_mode != 1) style="display:none;" @endif >
  <label class="col-form-label col-lg-2">Branch Name</label>
  <div class="col-lg-4">
    <input type="text" name="branch-name" id="branch-name" class="form-control" value="{{ $val->branch_name }}" disabled="">
  </div>
  <label class="col-form-label col-lg-2">Cheque Date</label>
  <div class="col-lg-4">
    <input type="text" name="cheque-date" id="cheque-date" class="form-control dateofbirth" value="{{ date('d/m/Y', strtotime($val->cheque_date)) }}" disabled="">
  </div>
</div>

<h3 class="online-transaction-mode online-transaction-mode-2 p-mode" @if($investments->payment_mode != 2) style="display:none;" @endif >Online transaction</h3>
<div class="form-group row online-transaction-mode online-transaction-mode-2 p-mode" @if($investments->payment_mode != 2) style="display:none;" @endif >
  <label class="col-form-label col-lg-2">Transaction Id</label>
  <div class="col-lg-4">
    <input type="text" name="transaction-id" id="transaction-id" class="form-control" value="{{ $val->transaction_id }}" disabled="">
  </div>
  <label class="col-form-label col-lg-2">Date</label>
  <div class="col-lg-4">
    <input type="text" name="date" id="date" class="form-control calendardate" value="{{ $val->transaction_date }}" disabled="">
  </div>
</div>

@endforeach