@extends('templates.admin.master')
@section('content')
<div class="content">
  <div class="row">
    <div class="col-lg-12">
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
      <form action="{!! route('admin.demand.saveadvice') !!}" method="post" enctype="multipart/form-data" id="add_demand_advice" name="add_demand_advice">
        @csrf
        <input type="hidden" name="created_at" class="created_at">
        <input type="hidden" name="create_application_date" id="create_application_date" class="form-control create_application_date">

        <div class="row">
          <div class="col-lg-12">
            <div class="card bg-white">
              <div class="card-body">
                <h3 class="card-title mb-3">Select Payment Type and Branch</h3>
                <div class="row">
                  <div class="col-md-4">

                    <div class="form-group row">

                      <label class="col-form-label col-lg-12">Payment Type<sup>*</sup></label>

                      <div class="col-lg-12 error-msg">



                        <select name="paymentType" id="paymentType" class="form-control">
                          <option value="">Please Select</option>
                          <!-- <option data-val="expenses" value="0">Expenses</option> -->
                          <option data-val="maturity-prematurity" value="2">Maturity / Prematurity</option>
                         <option data-val="death-help" value="4">Death Help </option> 
                        </select>



                      </div>

                    </div>

                  </div>




                  @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$company,'filedTitle'=>"Company",'name'=>'company_id','value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>false,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])

                  <!----------------- Maturity / Prematurity ----------------->
                  <div class="col-lg-12 maturity-prematurity payment-type-box" style="display: none;">
                    <div class="card bg-white">
                      <div class="card-body">
                        <h3 class="card-title mb-3">Maturity / Prematurity</h3>
                        <div class="row">
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Select Date<sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_prematurity_date" readonly id="maturity_prematurity_date" class="form-control create_application_date">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Select Type <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <select name="maturity_prematurity_type" id="maturity_prematurity_type" class="form-control input-type">
                                  <option value="">Please Select</option>
                                  <option data-val="maturity" value="0">Maturity</option>
                                  <option data-val="prematurity" value="1">Prematurity</option>
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!----------------- Maturity / Prematurity ----------------->
                  <div class="col-lg-12" id="bank_account_number_div1" style="display: none;">
                    <div class="card bg-white">
                      <div class="card-body">
                        <h3 class="card-title mb-3">Payment Mode detail</h3>
                        <div class="row">
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Payment Mode <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <select name="payment_mode" id="payment_mode" class="form-control input-type">
                                  <option value="">Please Select</option>
                                  <!-- <option value="Cash">Cash</option> -->
                                  <option value="SSB">SSB</option>
                                  <option value="BANK">Bank</option>
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!----------------- Maturity ----------------->
                  <div class="col-lg-12 maturity payment-type-sub-box" style="display: none;">
                    <div class="card bg-white">
                      <div class="card-body">
                        <h3 class="card-title mb-3">Maturity</h3>
                        <div class="row">
                          <input type="hidden" name="maturity_investmnet_id" id="maturity_investmnet_id">
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Account Number <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_account_number" id="maturity_account_number" class="form-control input" data-val="maturity">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Opening Date <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_opening_date" id="maturity_opening_date" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Plan Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_plan_name" id="maturity_plan_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Tenure <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_tenure" id="maturity_tenure" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Account Holder Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_account_holder_name" id="maturity_account_holder_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Father Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_father_name" id="maturity_father_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>

                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Amount <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_amount" id="maturity_amount" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Mobile Number <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_mobile_number" id="maturity_mobile_number" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">SSB Account <sup class="required required__ssb_account">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_ssb_account" id="maturity_ssb_account" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Bank A/C No. <sup class="required required__bank_account_number">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="password" name="maturity_bank_account_number" id="maturity_bank_account_number" class="form-control input">
                              </div>
                            </div>
                          </div>



                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Confirm Bank A/C No. <sup class="required required__bank_account_number">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_bank_account_number_confirm" id="maturity_bank_account_number_confirm" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">IFSC Code <sup class="required required__ifsc_code">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_ifsc_code" id="maturity_ifsc_code" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Passbook / Bond Photo <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="file" name="maturity_letter_photo" id="maturity_letter_photo" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Signature <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <span class="maturity_signature"></span>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Photo <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <span class="maturity_photo"></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!----------------- Maturity ----------------->
                  <!----------------- Prematurity ----------------->
                  <div class="col-lg-12 prematurity payment-type-sub-box" style="display: none;">
                    <div class="card bg-white">
                      <div class="card-body">
                        <h3 class="card-title mb-3">Prematurity</h3>
                        <div class="row">
                          <input type="hidden" name="prematurity_investmnet_id" id="prematurity_investmnet_id">
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Account Number <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_account_number" id="prematurity_account_number" class="form-control input" data-val="prematurity">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Opening Date <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_opening_date" id="prematurity_opening_date" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Plan Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_plan_name" id="prematurity_plan_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Tenure <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_tenure" id="prematurity_tenure" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Account Holder Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_account_holder_name" id="prematurity_account_holder_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Father Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_father_name" id="prematurity_father_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>

                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Amount <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_amount" id="prematurity_amount" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Mobile Number <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_mobile_number" id="prematurity_mobile_number" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">SSB Account <sup class="required required__ssb_account">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_ssb_account" id="prematurity_ssb_account" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Bank A/C No. <sup class="required required__bank_account_number">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="password" name="prematurity_bank_account_number" id="prematurity_bank_account_number" class="form-control input">
                              </div>
                            </div>
                          </div>


                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Confirm Bank A/C No. <sup class="required required__bank_account_number">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_bank_account_number_confirm" id="prematurity_bank_account_number_confirm" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">IFSC Code <sup class="required required__ifsc_code">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_ifsc_code" id="prematurity_ifsc_code" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Passbook / Bond Photo<sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="file" name="prematurity_letter_photo" id="prematurity_letter_photo" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Signature <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <span class="prematurity_signature"></span>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Photo <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <span class="prematurity_photo"></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!----------------- Prematurity ----------------->
                  <!----------------- Death Help / Death Claim ----------------->
                  <div class="col-lg-12 death-help payment-type-box" style="display: none;">
                    <div class="card bg-white">
                      <div class="card-body">
                        <h3 class="card-title mb-3">Death Help/Death Claim</h3>
                        <div class="row">
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Select Date<sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_date" id="death_help_date" class="form-control">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Select Categories <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <select name="death_help_category" id="death_help_category" class="form-control input-type">
                                  <option value="">Please Select</option>
                                  <option data-val="death-help-claim" value="0">Death Help</option>
                                  <option data-val="death-help-claim" value="1">Death Claim</option>
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!----------------- Maturity / Prematurity ----------------->
                  <!----------------- Death Help ----------------->
                  <div class="col-lg-12 death-help-claim payment-type-sub-box" style="display: none;">
                    <div class="card bg-white">
                      <div class="card-body">
                        <h3 class="card-title mb-3">Death Help/Death Claim</h3>
                        <div class="row">
                          <input type="hidden" name="death_help_investmnet_id" id="death_help_investmnet_id">
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Account Number <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_account_number" id="death_help_account_number" class="form-control input" data-val="death_help">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Opening Date <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_opening_date" id="death_help_opening_date" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Plan Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_plan_name" id="death_help_plan_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Tenure <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_tenure" id="death_help_tenure" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Account Holder Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_account_holder_name" id="death_help_account_holder_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Deno <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_deno" id="death_help_deno" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Deposited Amount <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_deposited_amount" id="death_help_deposited_amount" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>

                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Nominee Customer ID <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_nominee_member_id" id="death_help_nominee_member_id" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Nominee Name <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_nominee_name" id="nominee_name" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Mobile Number <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_mobile_number" id="nominee_mobile_number" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">SSB Account <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="death_help_ssb_account" id="nominee_ssb_account" class="form-control input" readonly="">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Death Certificate <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="file" name="death_help_letter_photo" id="death_help_letter_photo" class="form-control input">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Signature <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <span class="death_help_signature"></span>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Photo <sup class="required">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <span class="death_help_photo"></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!----------------- Death Help ----------------->
                  <div class="col-lg-12">
                    <div class="card bg-white">
                      <div class="card-body">
                        <div class="text-center">
                          <button type="submit" class="btn btn-primary legitRipple submit-demand-advice">Submit</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
      </form>
    </div>
  </div>
</div>
@include('templates.admin.demand-advice.partials.script')
@stop