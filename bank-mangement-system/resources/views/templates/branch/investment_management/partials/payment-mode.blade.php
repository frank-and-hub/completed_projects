<div class="form-group row ssb-account p-mode" style="display: none;">
  <label class="col-form-label col-lg-2">Account Number</label>
  <div class="col-lg-4">
    <div class="input-group">
      <input type="text" name="account_n" id="account_n" class="form-control" disabled="">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Account Balance</label>
  <div class="col-lg-4">
    <div class="input-group">
      <div class="rupee-img">
      </div>
      <input type="text" name="account_b" id="account_b" class="form-control rupee-txt" disabled="">
    </div>
  </div>
</div>

<h3 class="cheque-mode p-mode" style="display:none;">Cheque Detail</h3>
<div class=" form-group cheque-mode p-mode" style="display: none;">
    <div class="row form-group ">
     
      <label class="col-form-label col-lg-2">Cheque Number</label>
      <div class="col-lg-4">
        <select name="cheque_id" id="cheque_id" class="form-control" title="Please select something!">
            <option value="">Select Cheque</option> 
          </select> 
      </div>
    
    </div>
  <div style="display: none;" id='cheque_detail' class="">
    
  <div class="form-group row">
    <label class="col-form-label col-lg-2">Cheque Number</label>
    <div class="col-lg-4">
      <input type="text" name="cheque-number" id="cheque-number" class="form-control" readonly>
    </div>
  <label class="col-form-label col-lg-2">Bank Name</label>
    <div class="col-lg-4">
      <input type="text" name="bank-name" id="bank-name" class="form-control" readonly>
    </div>
  </div>
  
  <div class="form-group row">
    <label class="col-form-label col-lg-2">Branch Name</label>
    <div class="col-lg-4">
      <input type="text" name="branch-name" id="branch-name" class="form-control" readonly>
    </div>
  <label class="col-form-label col-lg-2">Cheque Date</label>
    <div class="col-lg-4">
      <input type="text" name="cheque-date" id="cheque-date" class="form-control" readonly>
    </div>
  </div>

  <div class=" form-group row">
    <label class="col-form-label col-lg-2">Cheque Amount</label>
    <div class="col-lg-4">
      <div class="rupee-img"></div>
      <input type="text" name="cheque-amt" id="cheque-amt" class="form-control rupee-txt" readonly>
    </div>
  <label class="col-form-label col-lg-2">Deposit Bank</label>
    <div class="col-lg-4">
      <input type="text" name="deposit_bank_name" id="deposit_bank_name" class="form-control"  readonly>    </div>
  </div> 
   <div class=" form-group row">
    <label class="col-form-label col-lg-2">Deposit bank Account</label>
    <div class="col-lg-4">
     <input type="text" name="deposit_bank_account" id="deposit_bank_account" class="form-control"  readonly>
    </div>
    </div>
  </div>  
</div>

<h3 class="online-transaction-mode p-mode" style="display: none;">Online transaction</h3>
<div class="form-group  online-transaction-mode p-mode" style="display: none;">
  <div class="form-group row">
  <label class="col-form-label col-lg-2">Transaction Id</label>
  <div class="col-lg-4">
    <input type="text" name="transaction-id" id="transaction-id" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Date</label>
  <div class="col-lg-4">
    <input type="text" name="date" id="date" class="form-control">
  </div>
  </div>
   <div class="form-group row">
  <label class="col-form-label col-lg-2">Deposit Bank</label>
  <div class="col-lg-4">
    <select name="rd_online_bank_id" id="rd_online_bank_id" class="form-control" >
        <option value="">Select Bank</option> 
    </select>
  </div>
   <label class="col-form-label col-lg-2">Deposit Bank Account</label>
  <div class="col-lg-4">
    <select name="rd_online_bank_ac_id" id="rd_online_bank_ac_id" class="form-control" >
        <option value="">Select Bank  Account</option> 
    </select>
  </div>
  </div>
</div>