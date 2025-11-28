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
        <form action="{!! route('branch.demand.update') !!}" method="post" enctype="multipart/form-data" id="add_demand_advice" name="add_demand_advice">
          @csrf
          <input type="hidden" name="demand_advice_id" class="demand_advice_id" value="{{ $demandAdvice->id }}">
            @php
                $stateid = getBranchStateByManagerId(Auth::user()->id);
            @endphp
            <input type="hidden" name="created_at" class="created_at" value="{{ checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
            <input type="hidden" name="company_id" id="company_id" value="{{$demandAdvice->company_id}}">
            <input type="hidden" name="branch" id="branch" value="{{$demandAdvice->branch_id}}">


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
                          <select name="paymentType" id="paymentType" class="form-control" @if($demandAdvice->payment_type == 0 || $demandAdvice->payment_type == 1 || $demandAdvice->payment_type == 2 || $demandAdvice->payment_type == 3) style="pointer-events:none; background-color:#e9ecef;opacity: 1;" @endif>
                              <option value="">Please Select</option>
                              <!-- <option data-val="expenses" @if($demandAdvice->payment_type == 0) selected @endif value="0">Expenses</option> -->
                              <option data-val="maturity-prematurity" @if($demandAdvice->payment_type == 2 || $demandAdvice->payment_type == 1) selected @endif value="2">Maturity / Prematurity</option>
                              <option data-val="death-help" @if($demandAdvice->payment_type == 3) selected @endif value="4">Death Help  </option>

                              
                            </select>
                          </div>
                        </div>
                      </div>

                      <!-- <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Branch <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="branch" id="branch" class="form-control">
                            @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                              @if($branch_id == $key)
                                <option @if($demandAdvice->branch_id == $key) selected @endif value="{{ $key }}"  >{{ $val }}</option>
                              @endif  
                                @endforeach

                            </select>
                          </div>
                        </div>
                      </div> -->
                      
                      {{-- <label class="col-form-label col-lg-2">Select Company<span>*</span></label>
                <div class="col-lg-4">
                  <select name="company_id" id="company_id" class="form-control" required>
                    <option value="">----Please Select Company----</option>
                    @foreach( $company as $key => $val )
                    @if(isset($val->get_company->name))
                    <option value="{{$val->company_id}}"  {{($val->company_id == $demandAdvice->company_id) ? 'selected'  : ' '}}>{{$val->get_company->name }}</option>
                    @endif
                    @endforeach

                  </select>
                </div> --}}
                    
                   

                    </div>
                  </div>
                </div>
              </div>

              @php
                  $expenseData = $demandAdvice->payment_type;
                  $subExpenseData = $demandAdvice->sub_payment_type;
              @endphp

              <!----------------- Expense ----------------->
              <div class="col-lg-12 expenses payment-type-box"  @if($demandAdvice->payment_type != 0) style="display: none;" @endif>
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">Expenses</h3>
                    <div class="row">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Date<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="date" id="date" class="form-control" value="{{ date("d/m/Y", strtotime(convertDate($demandAdvice->date))) }}" readonly>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Type <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="expenseType" id="expenseType" class="form-control input-type">
                              <option value="">Please Select</option>
                              <option data-val="fresh-expense" @if($subExpenseData == 0) selected @endif value="0">Fresh Expense  </option>
                              <option data-val="ta-advance" @if($subExpenseData == 1) selected @endif value="1">TA advance / Imprest  </option>
                              <option data-val="advanced-salary" @if($subExpenseData == 2) selected @endif value="2">Advance Salary  </option>
                              <option data-val="advanced-rent" @if($subExpenseData == 3) selected @endif value="3">Advance rent  </option>
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
              <div class="col-lg-12 fresh-expense payment-type-sub-box" @if($demandAdvice->payment_type != 0 || $demandAdvice->sub_payment_type != 0) style="display: none;" @endif>
              <div class="card bg-white" >
                <div class="card-body">
                  <h3 class="card-title mb-3">Fresh Expense</h3>
                  <div class="row">

                    <div class="col-lg-6 create-expense">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Select Expense categories <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <select name="expense_category" id="expense_category" class="form-control expense_category"  data-row-id="1">
                              <option value="">Please Select</option>
                              @foreach($expenseCategories as $val)
                                 @foreach($demandAdvice['expenses'] as $key => $exp)
                                  @if($val->id == 86)
                                    <option data-val="{{ $val->sub_head }}" value="{{ $val->id }}" @if($val->head_id == $exp->category) selected @endif>{{ $val->sub_head }}</option>
                                  @endif
                                @endforeach
                              @endforeach
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6 expense_subcategory1 expense_subcategory1_box create-expense">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Select Expense Sub-categories1 <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <select name="expense_subcategory1" id="expense_subcategory1" class="form-control expense_category"  data-row-id="2">
                              <option value="">Please Select</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6 expense_subcategory2 expense_subcategory2_box create-expense" style="display:none">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Select Expense Sub-categories2</label>
                        <div class="col-lg-8 error-msg">
                          <input type="hidden" name="subcategory_value2" id="subcategory_value2" value="0">
                          <select name="expense_subcategory2" id="expense_subcategory2" class="form-control input expense_category" data-row-id="3"  >
                              <option value="">Please Select</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6 expense_subcategory3 expense_subcategory3_box create-expense" style="display:none">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Expense Sub-categories3</label>
                          <div class="col-lg-8 error-msg">
                            <input type="hidden" name="subcategory_value3" id="subcategory_value3" value="0">
                            <select name="expense_subcategory3" id="expense_subcategory3" class="form-control input expense_category" data-row-id="4" >
                                <option value="">Please Select</option>
                            </select>
                          </div>
                        </div>
                    </div>

                    <!------------------ Edit Mode ------------------->
                    <div class="col-lg-6 edit-expense" style="display:none">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Select Expense categories <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <select name="edit_expense_category" id="edit_expense_category" class="form-control edit_expense_category" data-row-id="1">
                              <option value="">Please Select</option>
                              <option data-val="INDIRECT EXPENSES" value="86">INDIRECT EXPENSES</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6 edit-expense" style="display:none">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Select Expense Sub-categories1 <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <select name="edit_expense_subcategory1" id="edit_expense_subcategory1" class="form-control edit_expense_category" data-row-id="2">
                              <option value="">Please Select</option>
                              @foreach($subCategory1 as $val)
                                <option data-val="{{ $val->sub_head }}" class="{{ $val->parent_id }}-expense expense-subcategory" value="{{ $val->head_id }}">{{ $val->sub_head }}</option>
                              @endforeach
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6 edit-expense" style="display:none">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Select Expense Sub-categories2 <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="hidden" name="edit_subcategory_value2" id="edit_subcategory_value2" value="0">
                          <select name="edit_expense_subcategory2" id="edit_expense_subcategory2" class="form-control edit_expense_category" data-row-id="3">
                              <option value="">Please Select</option>
                              @foreach($subCategory2 as $val)
                                <option data-val="{{ $val->sub_head }}" class="{{ $val->parent_id }}-expense expense-subcategory" value="{{ $val->head_id }}">{{ $val->sub_head }}</option>
                              @endforeach
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6 edit-expense" style="display:none">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Select Expense Sub-categories3 <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="hidden" name="edit_subcategory_value3" id="edit_subcategory_value3" value="0">
                          <select name="edit_expense_subcategory3" id="edit_expense_subcategory3" class="form-control edit_expense_category" data-row-id="4">
                              <option value="">Please Select</option>
                              @foreach($subCategory3 as $val)
                                <option data-val="{{ $val->sub_head }}" class="{{ $val->parent_id }}-expense expense-subcategory" value="{{ $val->head_id }}">{{ $val->sub_head }}</option>
                              @endforeach
                          </select>
                        </div>
                      </div>
                    </div>
                    <!------------------ Edit Mode ------------------->

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Party Name <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="party_name" id="party_name" class="form-control">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Particular  <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="particular" id="particular" class="form-control">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Mobile No <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="mobile_number" id="mobile_number" class="form-control">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Amount  <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="amount" id="amount" class="form-control">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Bill No   <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="bill_no" id="bill_no" class="form-control">
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

                    <div class="col-lg-12 add-update-fe-button">
                      <a href="javascript:void(0);" class="btn btn-primary add-fresh-expense" style="margin-bottom: 10px;">Add </a>
                    </div>
                    <input type="hidden" name="count-fresh-expense" id="count-fresh-expense" value="{{ count($demandAdvice['expenses']) }}">
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
                            @if($expenseData == 0)
                                @foreach($demandAdvice['expenses'] as $key => $val)
                                <tr>
                                    <!-- <td>{{ $key+1 }}</td> -->
                                    <input type="hidden" name="fresh_expense[{{ $key }}][id]" value="{{ $val->id }}">
                                    <td class="{{ $val->id }}-category-td">{{ getAcountHead($val->category) }}</td>
                                    <input type="hidden" name="fresh_expense[{{ $key }}][expenseCategory]" value="{{ $val->category }}" class="{{ $val->id }}-category">

                                    @if($val->subcategory1)
                                      <td class="{{ $val->id }}-subcategory1-td">{{ getAcountHead($val->subcategory1) }}</td>
                                      <input type="hidden" name="fresh_expense[{{ $key }}][expenseSubCategory1]" value="{{ $val->subcategory1 }}" class="{{ $val->id }}-subcategory1">
                                    @else
                                      <td class="{{ $val->id }}-subcategory1-td"></td>
                                      <input type="hidden" name="fresh_expense[{{ $key }}][expenseSubCategory1]" value="" class="{{ $val->id }}-subcategory1">
                                    @endif

                                    @if($val->subcategory2)
                                      <td class="{{ $val->id }}-subcategory2-td">{{ getAcountHead($val->subcategory2) }}</td>
                                      <input type="hidden" name="fresh_expense[{{ $key }}][expenseSubCategory2]" value="{{ $val->subcategory2 }}" class="{{ $val->id }}-subcategory2">
                                    @else
                                      <td class="{{ $val->id }}-subcategory2-td"></td>
                                      <input type="hidden" name="fresh_expense[{{ $key }}][expenseSubCategory2]" value="" class="{{ $val->id }}-subcategory2">
                                    @endif

                                    @if($val->subcategory3)
                                      <td class="{{ $val->id }}-subcategory3-td">{{ getAcountHead($val->subcategory3) }}</td>
                                      <input type="hidden" name="fresh_expense[{{ $key }}][expenseSubCategory3]" value="{{ $val->subcategory3 }}" class="{{ $val->id }}-subcategory3">
                                    @else
                                      <td class="{{ $val->id }}-subcategory3-td"></td>
                                      <input type="hidden" name="fresh_expense[{{ $key }}][expenseSubCategory3]" value="" class="{{ $val->id }}-subcategory3">
                                    @endif

                                    <input type="hidden" name="fresh_expense[{{ $key }}][expenseSubCategory]" value="{{ $val->subcategory }}" class="{{ $val->id }}-subcategory">
                                    <td class="{{ $val->id }}-party-name-td">{{ $val->party_name }}</td>
                                    <input type="hidden" name="fresh_expense[{{ $key }}][party_name]" value="{{ $val->party_name }}" class="{{ $val->id }}-party_name">
                                    <td class="{{ $val->id }}-particular-td">{{ $val->particular }}</td>
                                    <input type="hidden" name="fresh_expense[{{ $key }}][particular]" value="{{ $val->particular }}" class="{{ $val->id }}-particular">
                                    <td class="{{ $val->id }}-mobile-number-td">{{ $val->mobile_number }}</td>
                                    <input type="hidden" name="fresh_expense[{{ $key }}][mobile_number]" value="{{ $val->mobile_number }}" class="{{ $val->id }}-mobile_number">
                                    <td class="{{ $val->id }}-amount-td">{{ round($val->amount,2) }} &#8377</td>
                                    <input type="hidden" name="fresh_expense[{{ $key }}][amount]" value="{{ $val->amount }}" class="{{ $val->id }}-amount fe-amount">
                                    <td class="{{ $val->id }}-billNumber-td">{{ $val->bill_number }}</td>
                                    <input type="hidden" name="fresh_expense[{{ $key }}][billNumber]" value="{{ $val->bill_number }}" class="{{ $val->id }}-billNumber">
                                    <td>
                                      <input type="file" name="fresh_expense[{{ $key }}][bill_photo]">
                                      @if($val->bill_file_id)
                                        @php
                                          $fileName = getFirstFileData($val->bill_file_id)->file_name;
                                        @endphp

                                        <span>
                                          <!-- <a href="{{ URL('core/storage/images/demand-advice/expense/'.$fileName.'') }}" target="blank">{{ $fileName }}</a> -->
                                          <a href="{{ImageUpload::generatePreSignedUrl('demand-advice/expense/'.$fileName)}}" target="blank">{{ $fileName }}</a>
                                        </span>
                                      @endif
                                    </td>
                                    @if($val->bill_file_id)
                                      <input type="hidden" name="fresh_expense[{{ $key }}][file_id]" value="{{ $val->bill_file_id }}">
                                    @else
                                      <input type="hidden" name="fresh_expense[{{ $key }}][file_id]" value="">
                                    @endif
                                    <td><a href="javascript:void(0);" data-id="{{ $val->id }}" class="edit-fresh-expense">Edit</a></td>
                                </tr>
                                @endforeach

                                <tr class="fresh-expense-total-amount">
                                  <td colspan="9"><span style="padding-left:48% "><b class="fe_total_amount"></b></span></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                  </div>
                </div>
              </div>
            </div>
              <!----------------- Fresh Expense ----------------->

              <!----------------- TA Advanced /Imprest ----------------->
              <div class="col-lg-12 ta-advance payment-type-sub-box" @if($demandAdvice->payment_type != 0 || $demandAdvice->sub_payment_type != 1) style="display: none;" @endif>
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">TA Advanced / Imprest</h3>
                    <div class="row">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Employee code <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ta_employee_code" id="ta_employee_code" class="form-control input" data-val="ta" value="@if($demandAdvice->employee_id) {{ getEmployeeData($demandAdvice->employee_id)->employee_code }} @endif">
                            <input type="hidden" name="ta_employee_id" id="ta_employee_id" value="{{ $demandAdvice->employee_id }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Employee Name <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ta_employee_name" id="ta_employee_name" class="form-control input" value="{{ $demandAdvice->employee_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Particular  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ta_particular" id="ta_particular" class="form-control input" value="{{ $demandAdvice->particular }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Advance Amount   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ta_advance_amount" id="ta_advance_amount" class="form-control input" value="{{ $demandAdvice->advanced_amount }}" min="0">
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
              <!----------------- TA Advanced /Imprest ----------------->

              <!----------------- Salary TA Advanced Imprest ----------------->
              <div class="col-lg-12 advanced-salary payment-type-sub-box" @if($demandAdvice->payment_type != 0 || $demandAdvice->sub_payment_type != 2) style="display: none;" @endif>
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">Advanced Salary</h3>
                    <div class="row">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Employee code <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_employee_code" id="advanced_salary_employee_code" class="form-control input" data-val="advanced_salary" value="@if($demandAdvice->employee_id) {{ getEmployeeData($demandAdvice->employee_id)->employee_code }} @endif">
                            <input type="hidden" name="advanced_salary_employee_id" id="advanced_salary_employee_id" value="{{ $demandAdvice->employee_id }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Employee Name <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_employee_name" id="advanced_salary_employee_name" class="form-control input" value="{{ $demandAdvice->employee_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_mobile_number" id="advanced_salary_mobile_number" class="form-control input" value="{{ $demandAdvice->mobile_number }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Amount   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_amount" id="advanced_salary_amount" class="form-control input" value="{{ $demandAdvice->amount }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Letter's Photo  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="file" name="advanced_salary_letter_photo" id="advanced_salary_letter_photo" class="form-control input">
                            @if($demandAdvice->letter_photo_id)
                              @php
                                $fileName = getFirstFileData($demandAdvice->letter_photo_id)->file_name;
                              @endphp

                              <span>
                                <!-- <a href="{{ URL('core/storage/images/demand-advice/advancedsalary/'.$fileName.'') }}" target="blank">{{ $fileName }}</a> -->
                                <a href="{{ImageUpload::generatePreSignedUrl('demand-advice/advancedsalary/'.$fileName)}}" target="blank">{{ $fileName }}</a>
                              </span>
                            @endif
                            <input type="hidden" name="old_advanced_salary_letter_photo" id="old_advanced_salary_letter_photo" value="{{ $demandAdvice->letter_photo_id }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Narration   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_narration" id="advanced_salary_narration" class="form-control input" value="{{ $demandAdvice->narration }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">SSB Account <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_ssb_account" id="advanced_salary_ssb_account" class="form-control input" value="{{ $demandAdvice->ssb_account }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Bank Name <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_bank_name" id="advanced_salary_bank_name" class="form-control input" value="{{ $demandAdvice->bank_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Bank A/C No.   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_bank_account_number" id="advanced_salary_bank_account_number" value="{{ $demandAdvice->bank_account_number }}" class="form-control input" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">IFSC Code   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_salary_ifsc_code" id="advanced_salary_ifsc_code" class="form-control input" value="{{ $demandAdvice->bank_ifsc }}" readonly="">
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
              <!----------------- Salary TA Advanced Imprest ----------------->

              <!----------------- Rent Advance Rent Rent Security ----------------->
              <div class="col-lg-12 advanced-rent payment-type-sub-box" @if($demandAdvice->payment_type != 0 || $demandAdvice->sub_payment_type != 3) style="display: none;" @endif>
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
                                <option @if($demandAdvice->owner_name == $value->id) selected @endif value="{{ $value->id }}">{{ $value->owner_name }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile Number <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_rent_mobile_number" id="advanced_rent_mobile_number" class="form-control input" value="{{ $demandAdvice->mobile_number }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Advance Rent Amount  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_rent_amount" id="advanced_rent_amount" class="form-control input" value="{{ $demandAdvice->amount }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Narration   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_rent_narration" id="advanced_rent_narration" class="form-control input" value="{{ $demandAdvice->narration }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">SSB Account <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_rent_ssb_account" id="advanced_rent_ssb_account" class="form-control input" value="{{ $demandAdvice->ssb_account }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Bank Name <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_rent_bank_name" id="advanced_rent_bank_name" class="form-control input" value="{{ $demandAdvice->bank_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Bank A/C No.   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_rent_bank_account_number" id="advanced_rent_bank_account_number" class="form-control input" value="{{ $demandAdvice->bank_account_number }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">IFSC Code   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="advanced_rent_ifsc_code" id="advanced_rent_ifsc_code" class="form-control input" value="{{ $demandAdvice->bank_ifsc }}" readonly="">
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
              <!----------------- Rent Advance Rent Rent Security ----------------->
               <!----------------- Maturity / Prematurity ----------------->
					   
            @php
                $stateid = getBranchStateByManagerId(Auth::user()->id);
            @endphp         
              <div class="col-lg-12 maturity-prematurity payment-type-box" @if($demandAdvice->payment_type != 1 && $demandAdvice->payment_type != 2) style="display: none;" @endif>
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">Maturity / Prematurity</h3>
                    <div class="row">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Date<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            @if($demandAdvice->is_reject == 1)
                            <input type="text" name="maturity_prematurity_date" id="maturity_prematurity_date" class="form-control" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}" readonly>
                            @else
                            <input type="text" name="maturity_prematurity_date" id="maturity_prematurity_date" class="form-control" value="{{ date("d/m/Y", strtotime(convertDate($demandAdvice->date))) }}" readonly>
                            @endif
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Type <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                          <select name="maturity_prematurity_type" id="maturity_prematurity_type" class="form-control input-type" @if($demandAdvice->payment_type == 1 || $demandAdvice->payment_type == 2) style="pointer-events:none; background-color:#e9ecef;opacity: 1;" @endif>
                              <option value="">Please Select</option>
                           
                              <option @if($demandAdvice->payment_type == 1) selected @endif data-val="maturity" value="0">Maturity</option>
                              <option @if($demandAdvice->payment_type == 2) selected @endif data-val="prematurity" value="1">Prematurity</option>
                            </select>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
              <!----------------- Maturity / Prematurity ----------------->
				<div class="col-lg-12" id="bank_account_number_div1" @if($demandAdvice->payment_type == "0") style="display:none" @endif>
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
								  <option value="SSB" @if($demandAdvice->maturity_payment_mode == "SSB") selected @endif>SSB</option>
								  <option value="BANK" @if($demandAdvice->maturity_payment_mode == "BANK") selected @endif>Bank</option>
								</select>
							</div>
						  </div>
						</div>
					</div>
				  </div>     
				</div>
              </div>
              <!----------------- Maturity ----------------->
              <div class="col-lg-12 maturity payment-type-sub-box" @if($demandAdvice->payment_type != 1) style="display: none;" @endif>
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">Maturity</h3>
                    <div class="row">
                      <input type="hidden" name="maturity_investmnet_id" id="maturity_investmnet_id">
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Account Number <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_account_number" id="maturity_account_number" class="form-control input" value="{{ $demandAdvice->account_number }}" data-val="maturity" readonly>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Opening Date  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_opening_date" id="maturity_opening_date" class="form-control input" value="{{ date("d/m/Y", strtotime(convertDate($demandAdvice->opening_date))) }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Plan Name   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_plan_name" id="maturity_plan_name" class="form-control input" value="{{ $demandAdvice->plan_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Tenure   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_tenure" id="maturity_tenure" class="form-control input" value="{{ $demandAdvice->tenure }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Account Holder Name   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_account_holder_name" id="maturity_account_holder_name" class="form-control input" value="{{ $demandAdvice->account_holder_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Father Name   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_father_name" id="maturity_father_name" class="form-control input" value="{{ $demandAdvice->father_name }}" readonly="">
                          </div>
                        </div>
                      </div>
                      <!--
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Category   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="maturity_category" id="maturity_category" class="form-control input">
                              <option @if( $demandAdvice->maturity_prematurity_category == 0) selected @else style="display:none;" @endif value="0" class="m_category maturity_regular_category">Regular</option>
                              <option @if( $demandAdvice->maturity_prematurity_category == 1) selected @else style="display:none;" @endif value="1" class="m_category maturity_defaulter_category">Defaulter</option>
                            </select>
                          </div>
                        </div>
                      </div>-->

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Amount   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_amount" id="maturity_amount" class="form-control input" value="{{ $demandAdvice->maturity_prematurity_amount }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_mobile_number" id="maturity_mobile_number" class="form-control input" value="{{ $demandAdvice->mobile_number }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">SSB Account <sup class="required required__ssb_account">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_ssb_account" id="maturity_ssb_account" class="form-control input" value="{{ $demandAdvice->ssb_account }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <!-- <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Bank A/C No.  <sup class="required required__bank_account_number">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_bank_account_number" id="maturity_bank_account_number" class="form-control input" value="{{ $demandAdvice->bank_account_number }}">
                          </div>
                        </div>
                      </div> -->


                      <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Bank A/C No. <sup class="required required__bank_account_number">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="password" name="maturity_bank_account_number" id="maturity_bank_account_number" class="form-control input"  value="{{ $demandAdvice->bank_account_number }}">
                              </div>
                            </div>
                          </div>



                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Confirm Bank A/C No. <sup class="required required__bank_account_number">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="maturity_bank_account_number_confirm" id="maturity_bank_account_number_confirm" class="form-control input"  value="{{ $demandAdvice->bank_account_number }}">
                              </div>
                            </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">IFSC Code  <sup class="required required__ifsc_code">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="maturity_ifsc_code" id="maturity_ifsc_code" class="form-control input" value="{{ $demandAdvice->bank_ifsc }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Passbook / Bond Photo  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="file" name="maturity_letter_photo" id="maturity_letter_photo" class="form-control input" readonly="">

                            @if($demandAdvice->letter_photo_id)
                              @php
                                $fileName = getFirstFileData($demandAdvice->letter_photo_id)->file_name;
                              @endphp

                              <span>
                                <!-- <a href="{{ URL('core/storage/images/demand-advice/maturity-prematurity/'.$fileName.'') }}" target="blank">{{ $fileName }}</a> -->
                                <a href="{{ImageUpload::generatePreSignedUrl('demand-advice/maturity-prematurity/'.$fileName)}}" target="blank">{{ $fileName }}</a>
                              </span>
                            @endif
                            @if(isset($demandAdvice->letter_photo_id ))
                            <input type="hidden" name="maturity_letter_photo_org" id="maturity_letter_photo_org" value="{{ $demandAdvice->letter_photo_id }}">
                            @else
                            <input type="hidden" name="maturity_letter_photo_org" id="maturity_letter_photo_org" value="">
                            @endif
                            <input type="hidden" name="old_maturity_letter_photo" id="old_maturity_letter_photo" value="{{ $demandAdvice->letter_photo_id }}">

                          </div>
                        </div>
                      </div>
                      <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Signature</label>
                                <div class="col-lg-8 error-msg">
                                @if(isset($investmentDetails['member']->signature))
                                <!-- <img src="{{URL::asset('asset/profile/member_signature/'.$investmentDetails['member']->signature)}}" alt="signature" height="200" width="200"> -->
                                <img src="{{ImageUpload::generatePreSignedUrl('profile/member_signature/' .  $investmentDetails['member']->signature)}}" alt="signature" height="200" width="200">
                                @else
                                <img src="{{URL::asset('images/no-image.png')}}" alt="signature" height="200" width="200">

                                @endif
                                </div>
                            </div>
                            </div>



                            <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Photo</label>
                                <div class="col-lg-8 error-msg">
                                @if(isset($investmentDetails['member']->photo))
                                <!-- <img src="{{URL::asset('asset/profile/member_avatar/'.$investmentDetails['member']->photo)}}" alt="photo" height="200" width="200"> -->
                                <img src="{{ImageUpload::generatePreSignedUrl('profile/member_avatar/' .  $investmentDetails['member']->photo)}}" alt="photo" height="200" width="200">
                                @else
                                <img src="{{URL::asset('images/no-image.png')}}" alt="signature" height="200" width="200">

                                @endif
                                </div>
                            </div>
                            </div>
                    </div>
                  </div>
                </div>
              </div>
              <!----------------- Maturity ----------------->

              <!----------------- Prematurity ----------------->
              <div class="col-lg-12 prematurity payment-type-sub-box" @if($demandAdvice->payment_type != 2) style="display: none;" @endif>
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">Prematurity</h3>
                    <div class="row">
                      <input type="hidden" name="prematurity_investmnet_id" id="prematurity_investmnet_id">
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Account Number <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_account_number" id="prematurity_account_number" class="form-control input" value="{{ $demandAdvice->account_number }}" data-val="prematurity" readonly>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Opening Date  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_opening_date" id="prematurity_opening_date" class="form-control input" value="{{ date("d/m/Y", strtotime(convertDate($demandAdvice->opening_date))) }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Plan Name   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_plan_name" id="prematurity_plan_name" class="form-control input" value="{{ $demandAdvice->plan_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Tenure   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_tenure" id="prematurity_tenure" class="form-control input" value="{{ $demandAdvice->tenure }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Account Holder Name   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_account_holder_name" id="prematurity_account_holder_name" class="form-control input" value="{{ $demandAdvice->account_holder_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Father Name   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_father_name" id="prematurity_father_name" class="form-control input" value="{{ $demandAdvice->father_name }}" readonly="">
                          </div>
                        </div>
                      </div>
                      <!--
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Category   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="prematurity_category" id="prematurity_category" class="form-control input">
                              <option @if( $demandAdvice->maturity_prematurity_category == 0) selected @else style="display:none;" @endif value="0" class="m_category prematurity_regular_category">Regular</option>
                              <option @if( $demandAdvice->maturity_prematurity_category == 1) selected @else style="display:none;" @endif value="1" class="m_category prematurity_defaulter_category">Default</option>
                            </select>
                          </div>
                        </div>
                      </div>-->

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Amount   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_amount" id="prematurity_amount" class="form-control input" value="{{ $demandAdvice->maturity_prematurity_amount }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_mobile_number" id="prematurity_mobile_number" class="form-control input" value="{{ $demandAdvice->mobile_number }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">SSB Account <sup class="required required__bank_account_number">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_ssb_account" id="prematurity_ssb_account" class="form-control input" value="{{ $demandAdvice->ssb_account }}" readonly="">
                          </div>
                        </div>
                      </div>

                   


                      <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Bank A/C No. <sup class="required required__bank_account_number">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="password" name="prematurity_bank_account_number" id="prematurity_bank_account_number" class="form-control input"  value="{{ $demandAdvice->bank_account_number }}">
                              </div>
                            </div>
                          </div>



                          <div class="col-lg-6">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-4">Confirm Bank A/C No. <sup class="required required__bank_account_number">*</sup></label>
                              <div class="col-lg-8 error-msg">
                                <input type="text" name="prematurity_bank_account_number_confirm" id="prematurity_bank_account_number_confirm" class="form-control input"  value="{{ $demandAdvice->bank_account_number }}">
                              </div>
                            </div>


                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">IFSC Code  <sup class="required required__ifsc_code">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="prematurity_ifsc_code" id="prematurity_ifsc_code" class="form-control input" value="{{ $demandAdvice->bank_ifsc }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Passbook / Bond Photo  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="file" name="prematurity_letter_photo" id="prematurity_letter_photo" class="form-control input" readonly="">

                            @if($demandAdvice->letter_photo_id)
                              @php
                                $fileName = getFirstFileData($demandAdvice->letter_photo_id)->file_name;
                              @endphp

                              <span>
                                <!-- <a href="{{ URL('core/storage/images/demand-advice/maturity-prematurity/'.$fileName.'') }}" target="blank">{{ $fileName }}</a> -->
                                <a href="{{ImageUpload::generatePreSignedUrl('demand-advice/maturity-prematurity/'.$fileName)}}" target="blank">{{ $fileName }}</a>
                              </span>
                            @endif
                            <input type="hidden" name="old_prematurity_letter_photo" id="old_prematurity_letter_photo" value="{{ $demandAdvice->letter_photo_id }}">

                          </div>
                        </div>
                      </div>
                      <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Signature</label>
                                <div class="col-lg-8 error-msg">
                                @if(isset($investmentDetails['member']->signature))
                                <!-- <img src="{{URL::asset('asset/profile/member_signature/'.$investmentDetails['member']->signature)}}" alt="signature" height="200" width="200"> -->
                                <img src="{{ImageUpload::generatePreSignedUrl('profile/member_signature/' .  $investmentDetails['member']->signature)}}" alt="signature" height="200" width="200">
                                @else
                                <img src="{{URL::asset('images/no-image.png')}}" alt="signature" height="200" width="200">

                                @endif
                                </div>
                            </div>
                            </div>



                            <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Photo</label>
                                <div class="col-lg-8 error-msg">
                                @if(isset($investmentDetails['member']->photo))
                                <!-- <img src="{{URL::asset('asset/profile/member_avatar/'.$investmentDetails['member']->photo)}}" alt="photo" height="200" width="200"> -->
                                <img src="{{ImageUpload::generatePreSignedUrl('profile/member_avatar/' .  $investmentDetails['member']->photo)}}" alt="photo" height="200" width="200">
                                @else
                                <img src="{{URL::asset('images/no-image.png')}}" alt="signature" height="200" width="200">

                                @endif
                                </div>
                            </div>
                            </div>
                    </div>
                  </div>
                </div>
              </div>
              <!----------------- Prematurity ----------------->

              <!----------------- Death Help / Death Claim ----------------->
              <div class="col-lg-12 death-help payment-type-box" @if($demandAdvice->payment_type != 3) style="display: none;" @endif>
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">Death Help/Death Claim</h3>
                    <div class="row">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Date<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_date" id="death_help_date" class="form-control" value="{{ date("d/m/Y", strtotime(convertDate($demandAdvice->date))) }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Select Categories <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <select name="death_help_category" id="death_help_category" class="form-control input-type">
                              <option value="">Please Select</option>
                              <option @if($demandAdvice->death_help_catgeory == 0) selected @endif data-val="death-help-claim" value="0">Death Help</option>
                              <option @if($demandAdvice->death_help_catgeory == 1) selected @endif data-val="death-help-claim" value="1">Death Claim</option>
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
              <div class="col-lg-12 death-help-claim payment-type-sub-box" @if($demandAdvice->payment_type != 3) style="display: none;" @endif>
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">Death Help/Death Claim</h3>
                    <div class="row">
                      <input type="hidden" name="death_help_investmnet_id" id="death_help_investmnet_id">

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Account Number <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_account_number" id="death_help_account_number" class="form-control input" data-val="death_help" value="{{ $demandAdvice->account_number }}" readonly>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Opening Date  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_opening_date" id="death_help_opening_date" class="form-control input" value="{{ date("d/m/Y", strtotime(convertDate($demandAdvice->opening_date))) }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Plan Name   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_plan_name" id="death_help_plan_name" class="form-control input" value="{{ $demandAdvice->plan_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Tenure   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_tenure" id="death_help_tenure" class="form-control input" value="{{ $demandAdvice->tenure }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Account Holder Name   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_account_holder_name" id="death_help_account_holder_name" class="form-control input" value="{{ $demandAdvice->account_holder_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Deno   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_deno" id="death_help_deno" class="form-control input" value="{{ $demandAdvice->deno }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Deposited Amount   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_deposited_amount" id="death_help_deposited_amount" class="form-control input" value="{{ $demandAdvice->deposited_amount }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <!-- <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Death Help/ Death Claim amount   <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_death_claim_amount" id="death_help_death_claim_amount" class="form-control input" value="{{ $demandAdvice->death_claim_amount }}" readonly="">
                          </div>
                        </div>
                      </div> -->

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Nominee Member ID  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_nominee_member_id" id="death_help_nominee_member_id" class="form-control input" value="{{ $demandAdvice->naominee_member_id }}">
                          </div>
                        </div>
                      </div>


                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Nominee Name  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_nominee_name" id="nominee_name" class="form-control input" value="{{ $demandAdvice->nominee_name }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile Number  <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_mobile_number" id="nominee_mobile_number" class="form-control input" value="{{ $demandAdvice->mobile_number }}">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">SSB Account <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="death_help_ssb_account" id="nominee_ssb_account" class="form-control input" value="{{ $demandAdvice->ssb_account }}" readonly="">
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Death Certificate <sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="file" name="death_help_letter_photo" id="death_help_letter_photo" class="form-control input">

                            @if($demandAdvice->death_certificate_id)
                              @php
                                $fileName = getFirstFileData($demandAdvice->death_certificate_id)->file_name;
                              @endphp

                              <span>
                                <!-- <a href="{{ URL('core/storage/images/demand-advice/death-help/'.$fileName.'') }}" target="blank">{{ $fileName }}</a> -->
                                <a href="{{ImageUpload::generatePreSignedUrl('demand-advice/death-help/'.$fileName)}}" target="blank">{{ $fileName }}</a>
                              </span>
                            @endif
                            <input type="hidden" name="old_death_help_letter_photo" id="old_death_help_letter_photo" value="{{ $demandAdvice->death_certificate_id }}">

                          </div>
                        </div>
                      </div>
                      <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Signature</label>
                                <div class="col-lg-8 error-msg">
                                @if(isset($investmentDetails['member']->signature))
                                <!-- <img src="{{URL::asset('asset/profile/member_signature/'.$investmentDetails['member']->signature)}}" alt="signature" height="200" width="200"> -->
                                <img src="{{ImageUpload::generatePreSignedUrl('profile/member_signature/' . $investmentDetails['member']->signature)}}" alt="signature" height="200" width="200">
                                @else
                                <img src="{{URL::asset('images/no-image.png')}}" alt="signature" height="200" width="200">

                                @endif
                                </div>
                            </div>
                            </div>



                            <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Photo</label>
                                <div class="col-lg-8 error-msg">
                                @if(isset($investmentDetails['member']->photo))
                                <!-- <img src="{{URL::asset('asset/profile/member_avatar/'.$investmentDetails['member']->photo)}}" alt="photo" height="200" width="200"> -->
                                <img src="{{ImageUpload::generatePreSignedUrl('profile/member_avatar/' .  $investmentDetails['member']->photo))}}" alt="photo" height="200" width="200">
                                @else
                                <img src="{{URL::asset('images/no-image.png')}}" alt="signature" height="200" width="200">

                                @endif
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
@stop

@section('script')
    @include('templates.branch.demand-advice.partials.script')
@stop
