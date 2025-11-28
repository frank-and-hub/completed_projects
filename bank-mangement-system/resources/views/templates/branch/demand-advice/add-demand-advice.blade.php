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
                    <a href="{!! route('branch.fundtransfer.branchtoho') !!}" style="float:right" class="btn btn-secondary">Back</a>
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
        <div class="col-lg-12">
          <form action="{!! route('branch.demand.saveadvice') !!}" method="post" enctype="multipart/form-data" id="add_demand_advice" name="add_demand_advice">
            @csrf

            @php
                $stateid = getBranchStateByManagerId(Auth::user()->id);
            @endphp
            <input type="hidden" name="created_at" class="created_at" value="{{ checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
            <input type="hidden" name="company_id" id="company_id" >
              <div class="row">

                <div class="col-lg-12">
                  <div class="card bg-white" >
                    <div class="card-body">
                      <h3 class="card-title mb-3">Select Payment Type and Branch</h3>
                      <div class="row">

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Payment Type <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <select name="paymentType" id="paymentType" class="form-control">
                                <option value="">Please Select</option>
                                {{-- <option data-val="expenses" value="0">Expenses</option> --}}
                                <option data-val="maturity-prematurity" value="2">Maturity / Prematurity</option>
                                <option data-val="death-help" value="4">Death Help  </option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Select Branch <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <select name="branch" id="branch" class="form-control">
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
                    </div>
                  </div>
                </div>

                <!----------------- Expense ----------------->
                <div class="col-lg-12 expenses payment-type-box" style="display: none;">
                  <div class="card bg-white" >
                    <div class="card-body">
                      <h3 class="card-title mb-3">Expenses</h3>
                      <div class="row">

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Date<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="date" id="dates" class="form-control" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Select Type <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <select name="expenseType" id="expenseType" class="form-control input-type">
                                <option value="">Please Select</option>
                                <option data-val="fresh-expense" value="0">Fresh Expense  </option>
                                <option data-val="ta-advance" value="1">TA advance / Imprest  </option>
                                <option data-val="advanced-salary" value="2">Advance Salary  </option>
                                <option data-val="advanced-rent" value="3">Advance rent  </option>
                              </select>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
                <!----------------- Expense ----------------->

                <!----------------- Fresh Expense ----------------->
                <div class="col-lg-12 fresh-expense payment-type-sub-box" style="display: none;">

                <div class="card bg-white" >

                  <div class="card-body">

                    <h3 class="card-title mb-3">Fresh Expense</h3>

                    <div class="row">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Expense categories <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="expense_category" id="expense_category" class="form-control input expense_category" data-row-id="1">
                                <option value="">Please Select</option>
                                @foreach($expenseCategories as $val)
                                  @if($val->id == 86)
                                    <option data-val="{{ $val->sub_head }}" value="{{ $val->id }}">{{ $val->sub_head }}</option>
                                  @endif
                                @endforeach
                            </select>
                          </div>
                        </div>
                      </div>


                      <div class="col-lg-6 expense_subcategory1 expense_subcategory1_box" style="display:none">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Expense Sub-categories1 <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="expense_subcategory1" id="expense_subcategory1" class="form-control input expense_category" data-row-id="2">
                                <option value="">Please Select</option>
                                @foreach($expenseSubCategories as $val)
                                <option data-val="{{ $val->sub_head }}" class="{{ $val->parent_id }}-expense expense-subcategory" value="{{ $val->id }}" style="display: none;">{{ $val->sub_head }}</option>
                                @endforeach
                            </select>
                          </div>
                        </div>
                      </div>



                      <div class="col-lg-6 expense_subcategory2 expense_subcategory2_box" style="display:none">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Expense Sub-categories2</label>
                          <div class="col-lg-8 error-msg">
                            <input type="hidden" name="subcategory_value2" id="subcategory_value2" value="0">
                            <select name="expense_subcategory2" id="expense_subcategory2" class="form-control input expense_category" data-row-id="3">
                                <option value="">Please Select</option>
                            </select>
                          </div>
                        </div>
                      </div>


                     <div class="col-lg-6 expense_subcategory3 expense_subcategory3_box" style="display:none">
                        <div class="form-group row">
                          <input type="hidden" name="subcategory_value3" id="subcategory_value3" value="0">
                          <label class="col-form-label col-lg-4">Select Expense Sub-categories3</label>
                          <div class="col-lg-8 error-msg">
                            <select name="expense_subcategory3" id="expense_subcategory3" class="form-control input expense_category" data-row-id="4">
                                <option value="">Please Select</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Party Name <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="party_name" id="party_name" class="form-control input">
                          </div>
                        </div>
                      </div>



                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Particular  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="particular" id="particular" class="form-control input">
                          </div>
                        </div>
                      </div>



                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile No <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="mobile_number" id="mobile_number" class="form-control input">
                          </div>
                        </div>
                      </div>



                      <div class="col-lg-6">

                        <div class="form-group row">

                          <label class="col-form-label col-lg-4">Amount  <sup class="required">*</sup></label>

                          <div class="col-lg-8 error-msg">

                            <input type="text" name="amount" id="amount" class="form-control input">

                          </div>

                        </div>

                      </div>



                      <div class="col-lg-6">

                        <div class="form-group row">

                          <label class="col-form-label col-lg-4">Bill No   <sup class="required">*</sup></label>

                          <div class="col-lg-8 error-msg">

                            <input type="text" name="bill_no" id="bill_no" class="form-control input">

                          </div>

                        </div>

                      </div>



                      <!-- <div class="col-lg-6">

                        <div class="form-group row">

                          <label class="col-form-label col-lg-4">Upload Bill Photo <sup class="required">*</sup></label>

                          <div class="col-lg-8 error-msg">

                            <input type="file" name="bill_photo" id="bill_photo" class="form-control">

                          </div>

                        </div>

                      </div> -->



                      <div class="col-lg-12">

                        <a href="javascript:void(0);" class="btn btn-primary add-fresh-expense" style="margin-bottom: 10px;">Add </a>

                      </div>

                      <input type="hidden" name="count-fresh-expense" id="count-fresh-expense" value="0">

                      <table class="table datatable-show-all">

                          <thead>

                              <tr>

                                  <!-- <th width="5%">S/N</th> -->

                                  <th width="10%">Expense categories</th>

                                  <th width="10%">Expense Sub- categories1</th>

                                  <th width="10%">Expense Sub- categories2</th>

                                  <th width="10%">Expense Sub- categories3</th>

                                  <th width="5%">Party Name</th>

                                  <th width="5%">Particular</th>

                                  <th width="5%">Mobile No</th>

                                  <th width="5%">Amount</th>

                                  <th width="5%">Bill No</th>

                                  <th width="5%">Upload Bill Photo</th>

                                  <th width="5%">Action</th>

                              </tr>

                          </thead>

                          <tbody class="fresh-expense-table">

                            <tr class="fresh-expense-total-amount">

                              <td colspan="9"><span style="padding-left:48% "><b class="fe_total_amount"></b></span></td>

                            </tr>

                          </tbody>

                      </table>



                    </div>

                  </div>

                </div>

              </div>
                <!----------------- Fresh Expense ----------------->

                <!----------------- TA Advanced /Imprest ----------------->
                <div class="col-lg-12 ta-advance payment-type-sub-box" style="display: none;">
                  <div class="card bg-white" >
                    <div class="card-body">
                      <h3 class="card-title mb-3">TA Advanced / Imprest</h3>
                      <div class="row">

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Employee code <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="ta_employee_code" id="ta_employee_code" class="form-control input" data-val="ta">
                              <input type="hidden" name="ta_employee_id" id="ta_employee_id">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Employee Name <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="ta_employee_name" id="ta_employee_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Particular  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="ta_particular" id="ta_particular" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Advance Amount   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="ta_advance_amount" id="ta_advance_amount" class="form-control input" min="0">
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
                <!----------------- TA Advanced /Imprest ----------------->

                <!----------------- Salary TA Advanced Imprest ----------------->
                <div class="col-lg-12 advanced-salary payment-type-sub-box" style="display: none;">
                  <div class="card bg-white" >
                    <div class="card-body">
                      <h3 class="card-title mb-3">Advanced Salary</h3>
                      <div class="row">

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Employee code <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_employee_code" id="advanced_salary_employee_code" class="form-control input" data-val="advanced_salary">
                              <input type="hidden" name="advanced_salary_employee_id" id="advanced_salary_employee_id">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Employee Name <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_employee_name" id="advanced_salary_employee_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_mobile_number" id="advanced_salary_mobile_number" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Amount   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_amount" id="advanced_salary_amount" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Letter's Photo   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="file" name="advanced_salary_letter_photo" id="advanced_salary_letter_photo" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Narration   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_narration" id="advanced_salary_narration" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">SSB Account <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_ssb_account" id="advanced_salary_ssb_account" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Bank Name <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_bank_name" id="advanced_salary_bank_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Bank A/C No.   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_bank_account_number" id="advanced_salary_bank_account_number" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">IFSC Code   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_salary_ifsc_code" id="advanced_salary_ifsc_code" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
                <!----------------- Salary TA Advanced Imprest ----------------->

                <!----------------- Rent Advance Rent Rent Security ----------------->
                <div class="col-lg-12 advanced-rent payment-type-sub-box" style="display: none;">
                  <div class="card bg-white" >
                    <div class="card-body">
                      <h3 class="card-title mb-3">Advanced Rent</h3>
                      <div class="row">

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Owner name  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <select name="advanced_rent_party_name" id="advanced_rent_party_name" class="form-control input" data-val="advanced_rent">
                                <option value="">Please Select</option>
                                @foreach($rentOwners as $value)
                                  <option value="{{ $value->id }}">{{ $value->owner_name }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Mobile Number <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_rent_mobile_number" id="advanced_rent_mobile_number" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Advance Rent Amount  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_rent_amount" id="advanced_rent_amount" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Narration   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_rent_narration" id="advanced_rent_narration" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">SSB Account <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_rent_ssb_account" id="advanced_rent_ssb_account" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Bank Name <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_rent_bank_name" id="advanced_rent_bank_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Bank A/C No.   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_rent_bank_account_number" id="advanced_rent_bank_account_number" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">IFSC Code   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="advanced_rent_ifsc_code" id="advanced_rent_ifsc_code" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
                <!----------------- Rent Advance Rent Rent Security ----------------->

                <!----------------- Maturity / Prematurity ----------------->
                <div class="col-lg-12 maturity-prematurity payment-type-box" style="display: none;">
                  <div class="card bg-white" >
                    <div class="card-body">
                      <h3 class="card-title mb-3">Maturity / Prematurity</h3>
                      <div class="row">

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Select Date<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="maturity_prematurity_date" id="maturity_prematurity_date" class="form-control" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}" readonly="">
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
				<div class="col-lg-12" id="bank_account_number_div1"  style="display: none;">
					<div class="card bg-white" > 
					   <div class="card-body">
						<h3 class="card-title mb-3">Payment Mode detail</h3>
						<div class="row">
						  <div class="col-lg-6">
							  <div class="form-group row">
								<label class="col-form-label col-lg-4">Payment Mode <sup class="required">*</sup></label>
								<div class="col-lg-8 error-msg">
									<select name="payment_mode" id="payment_mode" class="form-control input-type">
									  <option value="">Please Select</option>
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
                  <div class="card bg-white" >
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
                            <label class="col-form-label col-lg-4">Opening Date  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="maturity_opening_date" id="maturity_opening_date" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Plan Name   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="maturity_plan_name" id="maturity_plan_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Tenure   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="maturity_tenure" id="maturity_tenure" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Account Holder Name   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="maturity_account_holder_name" id="maturity_account_holder_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Father Name   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="maturity_father_name" id="maturity_father_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>
                      <!--
                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Category   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <select name="maturity_category" id="maturity_category" class="form-control input">
                                <option value="0" class="m_category maturity_regular_category" style="display: none;">Regular</option>

                                <option value="1" class="m_category maturity_defaulter_category" style="display: none;">Defaulter</option>
                              </select>
                            </div>
                          </div>
                        </div>-->

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Amount   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="maturity_amount" id="maturity_amount" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
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
                            <label class="col-form-label col-lg-4">Bank A/C No.  <sup class="required required__bank_account_number">*</sup> </label>
                            <div class="col-lg-8 error-msg">
                              <input type="password" name="maturity_bank_account_number" id="maturity_bank_account_number" class="form-control input">
                            </div>
                          </div>
                        </div>


                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Confirm Bank A/C No.  <sup class="required required__bank_account_number">*</sup> </label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="maturity_bank_account_number_confirm" id="maturity_bank_account_number_confirm" class="form-control input">
                            </div>
                          </div>
                        </div>


                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">IFSC Code  <sup class="required required__ifsc_code">*</sup></label>
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
                            <label class="col-form-label col-lg-4">Signature </label>
                            <div class="col-lg-8 error-msg">
                                <span class="maturity_signature"></span>
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Photo </label>
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
                  <div class="card bg-white" >
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
                            <label class="col-form-label col-lg-4">Opening Date  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="prematurity_opening_date" id="prematurity_opening_date" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Plan Name   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="prematurity_plan_name" id="prematurity_plan_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Tenure   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="prematurity_tenure" id="prematurity_tenure" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Account Holder Name   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="prematurity_account_holder_name" id="prematurity_account_holder_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Father Name   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="prematurity_father_name" id="prematurity_father_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>
                      <!--
                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Category   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <select name="prematurity_category" id="prematurity_category" class="form-control input">
                                <option value="0" class="m_category prematurity_regular_category" style="display: none;">Regular</option>

                                <option value="1" class="m_category prematurity_defaulter_category" style="display: none;">Defaulter</option>
                              </select>
                            </div>
                          </div>
                        </div>-->

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Amount   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="prematurity_amount" id="prematurity_amount" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
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
                            <label class="col-form-label col-lg-4">Bank A/C No.  <sup class="required required__bank_account_number">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="password" name="prematurity_bank_account_number" id="prematurity_bank_account_number" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Confirm Bank A/C No.  <sup class="required required__bank_account_number">*</sup> </label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="prematurity_bank_account_number_confirm" id="prematurity_bank_account_number_confirm" class="form-control input">
                            </div>
                          </div>
                        </div>



                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">IFSC Code  <sup class="required required__ifsc_code">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="prematurity_ifsc_code" id="prematurity_ifsc_code" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Passbook / Bond Photo <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="file" name="prematurity_letter_photo" id="prematurity_letter_photo" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Signature </label>
                            <div class="col-lg-8 error-msg">
                                <span class="prematurity_signature"></span>
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Photo </label>
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
                  <div class="card bg-white" >
                    <div class="card-body">
                      <h3 class="card-title mb-3">Death Help/Death Claim</h3>
                      <div class="row">

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Select Date<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_date" id="death_help_date" class="form-control" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}" readonly="">
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
                  <div class="card bg-white" >
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
                            <label class="col-form-label col-lg-4">Opening Date  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_opening_date" id="death_help_opening_date" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Plan Name   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_plan_name" id="death_help_plan_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Tenure   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_tenure" id="death_help_tenure" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Account Holder Name   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_account_holder_name" id="death_help_account_holder_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Deno   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_deno" id="death_help_deno" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Deposited Amount   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_deposited_amount" id="death_help_deposited_amount" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <!-- <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Death Help/ Death Claim amount   <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_death_claim_amount" id="death_help_death_claim_amount" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div> -->

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Nominee Customer ID  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_nominee_member_id" id="death_help_nominee_member_id" class="form-control input">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Nominee Name  <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <input type="text" name="death_help_nominee_name" id="nominee_name" class="form-control input" readonly="">
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
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
                            <label class="col-form-label col-lg-4">Signature </label>
                            <div class="col-lg-8 error-msg">
                                <span class="death_help_signature"></span>
                            </div>
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <div class="form-group row">
                            <label class="col-form-label col-lg-4">Photo </label>
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
</div>
@stop

@section('script')
    @include('templates.branch.demand-advice.partials.script')
@stop
