@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper"> 
      <div class="row"> 
        @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif
        <form action="{!! route('branch.demand.updatetaadvanced') !!}" method="post" enctype="multipart/form-data" id="add_ta_advanced" name="add_ta_advanced">
          @csrf
          <input type="hidden" name="demand_advice_id" class="demand_advice_id" value="{{ $demandAdvice->id }}">
            @php
                $stateid = getBranchStateByManagerId(Auth::user()->id);
            @endphp
            <input type="hidden" name="created_at" class="created_at" value="{{ checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
          <input type="hidden" name="adjustment_level" class="adjustment_level">
            <div class="row">

              @php
                  $expenseData = $demandAdvice->payment_type;
                  $subExpenseData = $demandAdvice->sub_payment_type;
              @endphp 

              <!----------------- TA Advanced /Imprest ----------------->
              <div class="col-lg-12 ta-advance payment-type-sub-box">
                <div class="card bg-white" > 
                  <div class="card-body">
                    <h3 class="card-title mb-3">TA Advanced / Imprest</h3>
                    <div class="row">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Advance Amount   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ta_advance_amount" id="ta_advance_amount" class="form-control input" value="{{ $demandAdvice->advanced_amount }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Particular  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ta_particular" id="ta_particular" class="form-control input" value="{{ $demandAdvice->particular }}" readonly="">
                          </div>
                        </div>
                      </div>
                      
                    </div> 
                  </div>
                </div>
              </div>

              <div class="col-lg-12 fresh-expense payment-type-sub-box">
                <div class="card bg-white" > 
                  <div class="card-body">
                    <h3 class="card-title mb-3">Ta Advanced</h3>
                    <div class="row">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Expense categories <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="ta_expense_category" id="ta_expense_category" class="form-control input">
                                <option value="">Please Select</option>
                                @foreach($expenseCategories as $val)
                                  <option value="{{ $val->id }}" data-val="{{ $val->sub_head }}">{{ $val->sub_head }}</option>
                                @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Expense Sub-categories <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="ta_expense_subcategory" id="ta_expense_subcategory" class="form-control input">
                                <option value="">Please Select</option>
                                @foreach($expenseSubCategories as $val)
                                <option class="{{ $val->parent_id }}-expense expense-subcategory" data-val="{{ $val->sub_head }}" value="{{ $val->id }}">{{ $val->sub_head }}</option>
                                @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Amount  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ta_amount" id="ta_amount" class="form-control input">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Bill No   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ta_bill_no" id="ta_bill_no" class="form-control input">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-12" style="margin-bottom: 10px;">
                        <a href="javascript:void(0);" class="btn btn-primary add-ta-expense">Add </a>
                        <!-- <a href="javascript:void(0);" class="btn btn-primary pay-expenses">Payment </a> -->
                        <input type="hidden" name="pay-expenses" class="pay-expenses">
                      </div>

                      <div class="col-lg-12">
                        
                      </div>

                      <input type="hidden" name="count-ta-expense" id="count-ta-expense" value="0">
                      <table class="table datatable-show-all">
                          <thead>
                              <tr>
                                  <!-- <th width="5%">S/N</th> -->
                                  <th width="10%">Expense categories</th>
                                  <th width="10%">Expense Sub- categories</th>
                                  <th width="5%">Amount</th>
                                  <th width="5%">Bill No</th>
                                  <th width="5%">Upload Bill Photo</th>
                                  <th width="5%">Action</th>
                              </tr>
                          </thead>
                          <tbody class="ta-expense-table">
                          </tbody>
                      </table>
   
                    </div> 
                  </div>
                </div>
              </div>

              <div class="col-lg-12 payment-option" style="display: none;">
                <div class="card bg-white" > 
                  <div class="card-body">
                      <h3 class="card-title mb-3">Payment</h3>
                      <div class="row">
                        <div class="col-lg-4">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Date<sup class="required">*</sup></label>
                            <div class="col-lg-12 error-msg">
                              <div class="input-group">
                                <input type="text" name="payment_date" id="payment_date" class="form-control">
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Difference Amount</label>
                                <div class="col-lg-12 error-msg">
                                    <div class="input-group">
                                        <input type="text" name="difference_amount" id="difference_amount" class="form-control" readonly="">
                                       </div>
                                </div>
                            </div>
                        </div>
                        
                         <div class="col-md-4 amount-mode-section">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Section Amount Mode<sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <select class="form-control" id="amount_mode" name="amount_mode">
                                              <option value="">---- Please Select ----</option>
                                              <option value="0" class="cash-option" data-val="cash-mode">Cash</option>
                                              <option value="1" class="ssb-option" data-val="ssb-mode">SSB</option>
                                              <option value="2" class="bank-option" data-val="bank-mode">Bank</option> 
                                          </select>
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 cash-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Select Branch <sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <select class="form-control paymemt-input" id="branch_id" name="branch_id">
                                              <option value="">----Please Select----</option>
                                              @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                @if($branch_id == $key)
                                                  <option selected="" value="{{ $key }}"  >{{ $val }}</option> 
                                                @endif
                                              @endforeach 
                                          </select>
                                         </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 cash-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Select Cash <sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <select class="form-control paymemt-input" id="cash_type" name="cash_type">
                                              <option value="">---- Please Select ----</option>
                                              <option value="0">Micro</option>
                                              <!-- <option value="1">Loan</option> -->
                                          </select>
                                         </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 cash-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Cash In Hand Balance <sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <input type="text" name="cash_in_hand_balance" id="cash_in_hand_balance" class="form-control paymemt-input" readonly="">
                                         </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 bank-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Select Bank <sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <select class="form-control paymemt-input" id="bank" name="bank">
                                              <option value="">----Please Select----</option>
                                              @foreach( $cBanks as $key => $bank)
                                                  @php
                                                  $balance = App\Models\SamraddhBankClosing::where('bank_id',$bank->id )->orderBy('id', 'desc')->first();
                                                  
                                              @endphp
                                                  @if($bank['bankAccount'])
                                                    
                                                      <option  value="{{ $bank->id }}" data-balance = "{{$balance ? $balance->balance:''}}" data-account="{{ $bank['bankAccount']->account_no }}">{{ $bank->bank_name }}</option>
                                                  @endif
                                              @endforeach
                                          </select>
                                         </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 bank-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Bank Account Number<sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <select class="form-control paymemt-input" id="bank_account_number" name="bank_account_number">
                                              <option value="">----Please Select----</option>
                                              @foreach($cBanks as $bank)
                                                  @if($bank['bankAccount'])
                                                      <option class="{{ $bank->id }}-bank-account c-bank-account" value="{{ $bank['bankAccount']->account_no }}" data-account="{{$bank['bankAccount']->id}}"  style="display: none;">
                                                      {{ $bank['bankAccount']->account_no }}</option>
                                                  @endif
                                              @endforeach
                                          </select>
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 bank-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Available Balance<sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <input type="text" name="available_balance" id="available_balance" class="form-control paymemt-input" readonly="">
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 bank-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Select Mode <sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <select class="form-control paymemt-input" id="mode" name="mode">
                                              <option value=""  >----Select----</option> 
                                              <option value="3"  >Cheque</option> 
                                              <option value="4"  >Online</option> 
                                          </select>
                                         </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 cheque-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Cheque Number<sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <select class="form-control paymemt-input" id="cheque_number" name="cheque_number">
                                              <option value="">----Please Select----</option>
                                              @foreach($cheques as $val)
                                                  <option value="{{ $val->cheque_no }}" class="{{ $val->account_id }}-c-cheque c-cheque" style="display: none;">{{ $val->cheque_no }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 online-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">UTR number / Transaction Number<sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <input type="text" name="utr_number" id="utr_number" class="form-control paymemt-input" >
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 common-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Amount<sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <input type="text" name="amount" id="amount" class="form-control" value="" readonly="">
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 online-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">RTGS/NEFT Charge <sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <input type="text" name="neft_charge" id="neft_charge" class="form-control paymemt-input">
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4 online-mode" style="display: none;">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Total amount  <sup>*</sup></label>
                                  <div class="col-lg-12 error-msg">
                                      <div class="input-group">
                                          <input type="text" name="total_amount" id="total_amount" class="form-control paymemt-input" readonly="">
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                    </div>
                </div>
              </div>

              <div class="col-lg-12">
                <div class="card bg-white">            
                  <div class="card-body">
                    <div class="text-center">
                    <button type="submit" class="btn btn-primary legitRipple submit-ta-advanced">Submit</button>
                  </div>
                  </div>
                </div>
              </div>

          </div> 
        </form>
      </div> 
  </div>
</div>
@stop

@section('script')
    @include('templates.branch.demand-advice.partials.script')
@stop