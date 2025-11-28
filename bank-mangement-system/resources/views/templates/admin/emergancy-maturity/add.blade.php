@extends('templates.admin.master')

@section('content')

<div class="content"> 
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
      <form action="{!! route('admin.emergancymaturity.save') !!}" method="post" enctype="multipart/form-data" id="add_emergancy_maturity" name="add_emergancy_maturity">
        @csrf
        <input type="hidden" name="created_at" class="created_at">

        <input type="hidden" name="f_amount" class="f_amount">

        <input type="hidden" name="tds_per" id="tds_per" value="">

        <input type="hidden" name="tds_per_amount" id="tds_per_amount" value="">

        <input type="hidden" name="company_id" id="company_id" >


          <div class="row">

            <!----------------- Maturity ----------------->
            <div class="col-lg-12">
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3">Emergency Maturity</h3>
                  <div class="row">
                    <input type="hidden" name="emergancy_investmnet_id" id="emergancy_investmnet_id">
                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Account Number <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_account_number" id="emergancy_account_number" class="form-control">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Opening Date  <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_opening_date" id="emergancy_opening_date" class="form-control" readonly="">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Plan Name   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_plan_name" id="emergancy_plan_name" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Tenure   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_tenure" id="emergancy_tenure" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Account Holder Name   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_account_holder_name" id="emergancy_account_holder_name" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Deposit Amount  <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_deposite_amount" id="emergancy_deposite_amount" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Maturity Amount Till Date   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_maturity_amount" id="emergancy_maturity_amount" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div>

                    <!-- <div class="col-lg-6 fd-interest-amount" style="display:none">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">1 FD with interest AMT <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="fd_interest_amt" id="fd_interest_amt" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div> -->

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_mobile_number" id="emergancy_mobile_number" class="form-control input">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">SSB Account <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_ssb_account" id="emergancy_ssb_account" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Bank Name   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_bank_name" id="emergancy_bank_name" class="form-control input">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Bank A/C No.   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_bank_account_number" id="emergancy_bank_account_number" class="form-control input">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">IFSC Code   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_ifsc_code" id="emergancy_ifsc_code" class="form-control input">
                        </div>
                      </div>
                    </div>

                    <!-- <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Letter's Photo <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="file" name="emergancy_letter_photo" id="emergancy_letter_photo" class="form-control input">
                        </div>
                      </div>
                    </div> -->

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Payment Date <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="hidden" class="create_application_date">
                          <input type="text" name="emergancy_payment_date" id="emergancy_payment_date" class="form-control input" readonly>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Branch</label>
                        <div class="col-lg-8">
                            <select name="branch_id" id="branch_id" class="form-control" readonly>
                                <option value="">---Please Select Branch ---</option>
                                @foreach( $branches as $branch)
                                    <option value="{{ $branch->id }}" data-value={{$branch->branch_code}}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">

                      <div class="form-group row">

                        <label class="col-form-label col-lg-4">TDS Amount <sup class="required">*</sup></label>

                        <div class="col-lg-8 error-msg">

                          <input type="text" name="tds_amount" id="tds_amount" class="form-control input" readonly="">

                          <input type="hidden" name="tds_percentage" id="tds_percentage">

                          <input type="hidden" name="tds_percentage_on_amount" id="tds_percentage_on_amount">

                        </div>

                      </div>

                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Maturity Amount Payable   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="emergancy_maturity_payable" id="emergancy_maturity_payable" class="form-control input" min="0">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6 eli-amount" style="display:none">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Eli AMT <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="eli_amt" id="eli_amt" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Final Amount <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="final_tds_amount" id="final_tds_amount" class="form-control input" readonly="">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-12">
                      <a href="javascript:void(0);" class="btn btn-primary add-emergancy-maturity">Add </a>
                    </div>
                    <input type="hidden" name="count-emergancy-maturity" id="count-emergancy-maturity" value="0">
                    <div class="col-md-12">
                    <div class="table-responsive">
                    <table class="table datatable-show-all" id="emergancy-maturity-t">
                        <thead>
                            <tr>
                                <!-- <th width="5%">S/N</th> -->
                                <th width="10%">Opening Date</th>
                                <th width="10%">Plan Name</th>
                                <th width="5%">Tenure</th>
                                <th width="5%">Account holder name</th>
                                <th width="5%">Deposit Amount</th>
                                <th width="5%">Maturity Amount Till Date</th>
                                <th width="5%">Maturity Amount Payable</th>
                                <th width="5%">Letter's Photo</th>
                                <th width="5%">Mobile Number</th>
                                <th width="5%">SSB Account</th>
                                <th width="5%">Bank Name</th>
                                <th width="5%">Bank A/C No.</th>
                                <th width="5%">IFSC</th>
                                <th width="5%">Payment Date</th>
                                <th width="5%">TDS Amount</th>
                                <th width="5%">Final Amount</th>
                                <th width="5%">Action</th>
                             
                            </tr>
                        </thead>
                        <tbody class="emergancy-maturity-table">
                        </tbody>
                    </table>
                  </div>
                    </div>
                  </div> 
                </div>
              </div>
            </div>
            <!----------------- Maturity ----------------->

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

@include('templates.admin.emergancy-maturity.partials.script')
@stop