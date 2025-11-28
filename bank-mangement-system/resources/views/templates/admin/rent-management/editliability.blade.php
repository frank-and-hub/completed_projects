@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Edit Branch and Rent type</h6>
                    </div>
                    <form action="{{ url('admin/update-liability') }}" method="post" id="edit-rent-liability"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                        <input type="hidden" name="rent_id" class="rent_id" value="{{ $rentLiability->id }}">
                        <input type="hidden" name="companyDate" class="companyDate" id="companyDate" value="{{ $companyDate }}">
                        <div class="modal-body">
                            <div class="form-group row">
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Register Date<sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <input type="text" name="select_date" id="select_date" class="form-control  "
                                                readonly
                                                value="{{ date('d/m/Y', strtotime(convertDate($rentLiability->created_at))) }}"  readonly>

                                            <input type="hidden" name="create_application_date"
                                                id="create_application_date" class="form-control  create_application_date"
                                                readonly>

                                        </div>
                                    </div>
                                </div>
                                @php
                                    $dropDown = $company;
                                    $filedTitle = 'Company';
                                    $name = 'company_id';
                                    $selectedCompany = $rentLiability->company_id;
                                    $selectedBranch = $rentLiability->branch_id;
                                @endphp

                                @include('templates.GlobalTempletes.new_role_type', [
                                    'dropDown' => $dropDown,
                                    'filedTitle' => $filedTitle,
                                    'name' => $name,
                                    'value' => '',
                                    'multiselect' => 'false',
                                    'design_type' => 4,
                                    'branchShow' => true,
                                    'branchName' => 'branch',
                                    'apply_col_md' => true,
                                    'multiselect' => false,
                                    'placeHolder1' => 'Please Select Company',
                                    'placeHolder2' => 'Please Select Branch',
                                    'selectedBranch' => $selectedBranch,
                                    'selectedCompany' => $selectedCompany,
                                ])
                                {{-- <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select Branch<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <select name="branch" id="branch" class="form-control">
                                            <option value=""  >Please Select</option> 
                                            @foreach ($branches as $key => $val)
                                            <option data-val="{{ $val->state_id }}" @if ($rentLiability->branch_id == $val->id) selected @endif value="{{ $val->id }}"  >{{ $val->name }}</option> 
                                            @endforeach
                                        </select>
                                    </div>
                                </div> --}}

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select Rent Type<sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <select name="rentType" id="rentType" class="form-control">
                                            <option value="">--- Please Select ---</option>
                                            @foreach ($libilityTypes as $key => $val)
                                                <option @if ($rentLiability->rent_type == $val->head_id) selected @endif
                                                    value="{{ $val->head_id }}">{{ $val->sub_head }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-lg-12" style="margin-top: 10px;">
                                    <h6 class="card-title font-weight-semibold">Agreement Period</h6>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">From<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="agreement_from" id="agreement_from"
                                            class="form-control cal-date"
                                            value="{{ date('d/m/Y', strtotime(convertDate($rentLiability->agreement_from))) }}" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">To<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="agreement_to" id="agreement_to"
                                            class="form-control cal-date"
                                            value="{{ date('d/m/Y', strtotime(convertDate($rentLiability->agreement_to))) }}" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-lg-6">
                                        <label class="col-form-label col-lg-12">Select Date<sup class="required">*</sup></label>
                                        <div class="col-lg-10">
                                            <input type="text" name="date" id="date" class="form-control cal-date" value="{{ date('d/m/Y', strtotime(convertDate($rentLiability->date))) }}">
                                        </div>
                                    </div> -->

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Place<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="place" id="place" class="form-control"
                                            value="{{ $rentLiability->place }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Name<sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_name" id="owner_name" class="form-control"
                                            value="{{ $rentLiability->owner_name }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Mobile Number <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_mobile_number" id="owner_mobile_number"
                                            class="form-control" value="{{ $rentLiability->owner_mobile_number }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Pan Card<sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_pen_card" id="owner_pen_card"
                                            class="form-control" value="{{ $rentLiability->owner_pen_number }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Aadhar Card <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_aadhar_card" id="owner_aadhar_card"
                                            class="form-control" value="{{ $rentLiability->owner_aadhar_number }}">
                                    </div>
                                </div>
                                <?php
                                $ssb_id = '';
                                $ssb_ac = '';
                                $ssb_date = '';
                                $rdate = '';
                                
                                if ($rentLiability->owner_ssb_id) {
                                    $ssb_id = $rentLiability->owner_ssb_id;
                                    $ssb_ac = getSsbAccountNumber($rentLiability->owner_ssb_id)->account_no;
                                    $ssb_date = getSsbAccountNumber($rentLiability->owner_ssb_id)->created_at;
                                    $ssb_date = date('d/m/Y', strtotime(convertDate($ssb_date)));
                                    $rdate = date('d/m/Y', strtotime(convertDate($ssb_date))+86400);
                                   
                                }
                                ?>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner SSB account<sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="owner_ssb_account" id="owner_ssb_account"
                                            class="form-control" value="{{ $ssb_ac }}">
                                        <input type="hidden" name="owner_ssb_id" id="owner_ssb_id" class="form-control"
                                            value="{{ $ssb_id }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner SSB account date<sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="ssb_date" id="ssb_date" class="form-control" readonly
                                            value="{{ $ssb_date }}">
                                    </div>
                                </div>
                             

                                <div class="col-lg-12" style="margin-top: 10px;">
                                    <h6 class="card-title font-weight-semibold">Owner bank detail </h6>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Bank name<sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="bank_name" id="bank_name" class="form-control"
                                            value="{{ $rentLiability->owner_bank_name }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Owner Bank account Number <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="bank_account_number" id="bank_account_number"
                                            class="form-control" value="{{ $rentLiability->owner_bank_account_number }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">IFSC code <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="ifsc_code" id="ifsc_code" class="form-control"
                                            value="{{ $rentLiability->owner_bank_ifsc_code }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Security amount <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <div class="rupee-img"></div>
                                        <input type="text" name="security_amount" id="security_amount"
                                            class="form-control rupee-txt"
                                            value="{{ number_format((float) $rentLiability->security_amount, 2, '.', '') }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Rent <sup class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <div class="rupee-img"></div>
                                        <input type="text" name="rent" id="rent"
                                            class="form-control rupee-txt"
                                            value="{{ number_format((float) $rentLiability->rent, 2, '.', '') }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Yearly Increment in % <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="yearly_increment" id="yearly_increment"
                                            class="form-control" value="{{ $rentLiability->yearly_increment }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Office Square feet area <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="office_area" id="office_area" class="form-control"
                                            value="{{ $rentLiability->office_area }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Employee Code <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="employee_code" id="employee_code"
                                            class="form-control"
                                            value="{{ $rentLiability['employee_rent']->employee_code }}">
                                        <input type="hidden" name="employee_id" id="employee_id" class="form-control"
                                            value="{{ $rentLiability['employee_rent']->id }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Employee Date <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="employee_date" id="employee_date"
                                            class="form-control" readonly
                                            value="{{ date('d/m/Y', strtotime(convertDate($rentLiability['employee_rent']->employee_date))) }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Authorized Employee name <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="employee_name" id="employee_name"
                                            class="form-control"
                                            value="{{ $rentLiability['employee_rent']->employee_name }}" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Authorized Employee Designation <sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="employee_designation" id="employee_designation"
                                            class="form-control"
                                            value="{{ getDesignationData('designation_name', $rentLiability['employee_rent']->designation_id)->designation_name }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Mobile Number<sup
                                            class="required">*</sup></label>
                                    <div class="col-lg-10">
                                        <input type="text" name="mobile_number" id="mobile_number"
                                            class="form-control" value="{{ $rentLiability['employee_rent']->mobile_no }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Rent Agreement</label>
                                    <div class="col-lg-10">
                                        <input type="file" name="rent_agreement" id="rent_agreement"
                                            class="form-control">
                                        @if ($rentLiability['liabilityFile'])
                                            <span><a href="{{ URL('core/storage/images/rent-liabilities/' . $rentLiability['liabilityFile']->file_name . '') }}"
                                                    target="blank">{{ $rentLiability['liabilityFile']->file_name }}</a></span>
                                            <input type="hidden" name="hidden_file_id" id="hidden_file_id"
                                                value="{{ $rentLiability['liabilityFile']->id }}">
                                        @else
                                            <input type="hidden" name="hidden_file_id" id="hidden_file_id"
                                                value="">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ url()->previous() }}" type="button" class="btn btn-link"
                                data-dismiss="modal">Back</a>
                            <button type="submit" class="btn bg-dark">Submit<i
                                    class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('templates.admin.rent-management.partials.script')
    <script>
        $('#company_id,#branch').parents('.col-md-4').addClass('col-md-6').removeClass('col-md-4');
        $('#company_id,#branch').closest('.row').removeClass('row');
        $('#company_id,#branch').parents('.col-lg-12').addClass('col-lg-10').removeClass('col-lg-12');
        // $("#select_date").hover(function() {
        //     var date = $('#create_application_date').val();
        //     $('#select_date').datepicker({
        //         format: "dd/mm/yyyy",
        //         todayHighlight: true,
        //         autoclose: true,
        //         orientation: "bottom",
        //         endDate: date,
        //         startDate: '{{$rdate}}',

        //     })
        // })
    </script>
@stop
