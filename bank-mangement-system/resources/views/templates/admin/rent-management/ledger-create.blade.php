@extends('templates.admin.master')

@section('content')
    <?php
    $finacialYear = getFinacialYear();
    $fenddate = \Carbon\Carbon::parse($finacialYear['dateEnd']);
    $fstrtdate = date('Y', strtotime(convertDate($finacialYear['dateStart'])));
    $re_month1 = '';
    $re_year1 = '';
    $re_company1 = '';
    $re_month11 = 1;
    if (old('rent_month')) {
        $re_month1 = old('rent_month');
    }
    if (old('rent_year')) {
        $re_year1 = old('rent_year');
    }
    if (old('re_company')) {
        $re_company1 = old('re_company');
    }
    
    if (isset($re_month)) {
        $re_month1 = $re_month;
        $re_month11 = $re_month1;
    }
    if (isset($re_year)) {
        $re_year1 = $re_year;
    }
    if (isset($re_company)) {
        $re_company1 = $re_company;
    }
    
    ?>

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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="{!! route('admin.rent.ledger-create') !!}" method="post" enctype="multipart/form-data" id="filter"
                            name="filter">
                            <input type="hidden" name="created_at1" class="created_at" id="created_at1">
                            @csrf
                            <div class="row">
                                @php
                                    $dropDown = $company;
                                    $filedTitle = 'Company';
                                    $name = 'company_id';
                                @endphp

                                @include('templates.GlobalTempletes.new_role_type', [
                                    'dropDown' => $dropDown,
                                    'filedTitle' => $filedTitle,
                                    'name' => $name,
                                    'value' => '',
                                    'multiselect' => 'false',
                                    'design_type' => 4,
                                    'branchShow' => false,
                                    'branchName' => 'branch_id',
                                    'apply_col_md' => true,
                                    'multiselect' => false,
                                    'placeHolder1' => 'Please Select Company',
                                    'placeHolder2' => 'Please Select Branch',
                                    'selectedCompany' => $re_company1,
                                ])
                                <input type="hidden" name="currentYear" value="<?php echo date('Y') ?>" id="currentYear">
                                <input type="hidden" name="currentMonth" value="<?php echo date('n') ?>" id="currentMonth">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Year <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_year" name="rent_year">
                                                    <option value="">----Select Year----</option>
                                                    <!--  @for ($i = 2000; $i <= 2050; $i++)
    <option value="{{ $i }}"  >{{ $i }}</option>
    @endfor-->

                                                    {{ $last = date('Y') - 1 }}
                                                    {{ $now = date('Y') }}

                                                    @for ($i = $now; $i >= $last; $i--)
                                                        <option value="{{ $i }}"
                                                            @if ($i == $re_year1) selected @endif>
                                                            {{ $i }}</option>
                                                    @endfor

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Month <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_month" name="rent_month">
                                                    <option value="">----Select Month----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>






                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right">
                                            <button type="submit" class=" btn bg-dark legitRipple">Submit</button>
                                            <button type="reset" class="btn btn-gray legitRipple" id="reset_form"
                                                onclick="resetForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @isset($code)
                <div class="col-md-12" id='hide_div'>
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h6 class="card-title font-weight-semibold">Rent List</h6>
                        </div>
                        <form action="{!! route('admin.rent.ledger-save') !!}" method="post" enctype="multipart/form-data" name="rent_generate"
                            id="rent_generate" class="rent_generate">
                            @csrf
                            <input type="hidden" name="created_at" class="created_at" id="created_at">
                            <input type="hidden" name="create_application_date" class="create_application_date"
                                id="create_application_date">


                            <input type="hidden" name="company_id" class="company_id" value="{{ $c_id }}"
                                id="company_id">
                            <input type="hidden" name="company_idd" class="company_idd" value="{{ $c_id }}"
                                id="company_idd">

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Date <sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    <input type="text" name="select_date" id="select_date"
                                                        class="form-control  " value="{{$lastDate}}"  readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group d-flex flex-row-reverse">
                                                    <button type="button"
                                                        class="btn bg-dark legitRipple ledger_create_export ml-2"
                                                        data-extension="0" style="float: right;">Export xslx</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="table-responsive py-4">
                                            <table class="table table-flush  table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="border: 1px solid #ddd;">S.No</th>
                                                        <th style="border: 1px solid #ddd;">Company Name</th>
                                                        <th style="border: 1px solid #ddd;">BR Name</th>
                                                        <!-- <th style="border: 1px solid #ddd;">BR Code</th>
                                                <th style="border: 1px solid #ddd;">SO Name</th>
                                                <th style="border: 1px solid #ddd;">RO Name</th>
                                                <th style="border: 1px solid #ddd;">ZO Name</th>-->
                                                        <th style="border: 1px solid #ddd;">Rent Type</th>
                                                        <!-- <th style="border: 1px solid #ddd;">Period From </th>
                                                <th style="border: 1px solid #ddd;">Period To</th>-->
                                                        <th style="border: 1px solid #ddd;">Address</th>
                                                        <th style="border: 1px solid #ddd;">Owner Name</th>
                                                        <!-- <th  style="border: 1px solid #ddd;">Owner Mobile Number</th>
                                                <th style="border: 1px solid #ddd;">Owner Pan Card</th>
                                                <th style="border: 1px solid #ddd;">Owner Aadhar Card </th>
                                                <th style="border: 1px solid #ddd;">Owner SSB account </th>

                                                <th style="border: 1px solid #ddd;"> Owner Bank Name</th>
                                                <th style="border: 1px solid #ddd;" >Owner Bank A/c No.</th>
                                                <th style="border: 1px solid #ddd;" >Owner IFSC code </th>-->

                                                        <th style="border: 1px solid #ddd;">Security amount</th>

                                                        <!-- <th style="border: 1px solid #ddd;">Yearly Increment</th>
                                                <th style="border: 1px solid #ddd;">Office Square feet area</th>-->
                                                        <th style="border: 1px solid #ddd;">Rent</th>
                                                        <th style="border: 1px solid #ddd;">Amount</th>
                                                        <th style="border: 1px solid #ddd;">Tds Amount</th>
                                                        <th style="border: 1px solid #ddd;">Transfer Amount</th>

                                                        <!--<th style="border: 1px solid #ddd;">Employee Code</th>-->
                                                        <th style="border: 1px solid #ddd;">Employee Name</th>
                                                        <th style="border: 1px solid #ddd;">Employee Designation</th>
                                                        <th style="border: 1px solid #ddd;">Employee Mobile No.</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (count($rent) > 0)
                                                        <?php
                                                        $total = 0;
                                                        $totalTds = 0;
                                                        $totalRent = 0;
                                                        ?>
                                                        @foreach ($rent as $index => $row)
                                                            <?php
                                                            // print_r($row);die;
                                                            $total = $total + $row->rent;
                                                            ?>

                                                            <tr>
                                                                <td style="border: 1px solid #ddd;">{{ $index + 1 }}</td>
                                                                <td style="border: 1px solid #ddd;">{{ $c_name }}</td>

                                                                <td style="border: 1px solid #ddd;">
                                                                    {{ $row['liabilityBranch']->name }}</td>
                                                                <!-- <td style="border: 1px solid #ddd;">{{ $row['liabilityBranch']->branch_code }}</td>
                                                     <td style="border: 1px solid #ddd;">{{ $row['liabilityBranch']->sector }}</td>
                                                     <td style="border: 1px solid #ddd;">{{ $row['liabilityBranch']->regan }}</td>
                                                     <td style="border: 1px solid #ddd;">{{ $row['liabilityBranch']->zone }}</td>-->

                                                                <td style="border: 1px solid #ddd;">
                                                                    {{ $row['AcountHeadCustom']->sub_head }}</td>
                                                                <!-- <td style="border: 1px solid #ddd;">{{ date('d/m/Y', strtotime(convertDate($row->agreement_from))) }}</td>
                                                     <td style="border: 1px solid #ddd;">{{ date('d/m/Y', strtotime(convertDate($row->agreement_to))) }}</td>-->

                                                                <td style="border: 1px solid #ddd;">{{ $row->place }}</td>

                                                                <td style="border: 1px solid #ddd;">{{ $row->owner_name }}
                                                                </td>
                                                                <!--<td style="border: 1px solid #ddd;">{{ $row->owner_mobile_number }}</td>
                                                     <td style="border: 1px solid #ddd;">{{ $row->owner_pen_number }}</td>
                                                     <td style="border: 1px solid #ddd;">{{ $row->owner_aadhar_number }}</td>
                                                     <td style="border: 1px solid #ddd;">
        @if ($row->owner_ssb_id)
        {{ $row['SsbAccountNumberCustom']->account_no }}
        @endif
        </td>
                                                     <td style="border: 1px solid #ddd;">{{ $row->owner_bank_name }}</td>
                                                     <td style="border: 1px solid #ddd;">{{ $row->owner_bank_account_number }}</td>
                                                     <td style="border: 1px solid #ddd;">{{ $row->owner_bank_ifsc_code }}</td>-->
                                                                <td style="border: 1px solid #ddd;">
                                                                    {{ number_format((float) $row->security_amount, 2, '.', '') }}
                                                                </td>

                                                                <!--<td style="border: 1px solid #ddd;">{{ number_format((float) $row->yearly_increment, 2, '.', '') }}%</td>
                                                     <td style="border: 1px solid #ddd;">{{ $row->office_area }}</td>-->
                                                                <td style="border: 1px solid #ddd;">

                                                                    <div class="col-lg-12 error-msg">
                                                                        <input type="text" name="rent_amount[]"
                                                                            id="rent_amount_{{ $index }}"
                                                                            class="form-control rent_amount "
                                                                            style="width: 100px" readonly
                                                                            value="{{ number_format((float) $row->rent, 2, '.', '') }}">

                                                                    </div>
                                                                </td>
                                                                <td style="border: 1px solid #ddd;">
                                                                    <div class="col-lg-12 error-msg">
                                                                        <input type="text" name="amount[]"
                                                                            id="amount_{{ $index }}"
                                                                            class="form-control amount " style="width: 100px"
                                                                            data-index="{{ $index }}"
                                                                            value="{{ number_format((float) $row->rent, 2, '.', '') }}">
                                                                    </div>
                                                                </td>
                                                                <td style="border: 1px solid #ddd;">
                                                                    @php
                                                                        $createdDate = \Carbon\Carbon::parse($row->created_at);
                                                                        $totalMonth = $createdDate->diffInMonths($fenddate);
                                                                        $currInterest = $row->rent * $totalMonth;
                                                                        $amount = 0;
                                                                        /* $tdsData = tdsCalculate($currInterest,$row,$createdDate,'rent',$fstrtdate,$fenddate); */
                                                                        /* $total=$total+$row->rent - $tdsData['tdsAmount']; */
                                                                        /*$total=$total+$row->rent - $tdsData['tdsAmount'];*/
                                                                        $total = $total + $row->rent;
                                                                        /* $totalTds = $tdsData['tdsAmount'] + $totalTds;*/
                                                                        $totalTds = $totalTds;
                                                                        $totalRent = $totalRent + $row->rent;
                                                                    @endphp
                                                                    <!-- Tds Present on Uat !-->
                                                                    <input type="hidden" name="tdsPercentage "
                                                                        value="0"
                                                                        id="tdsPercentage_{{ $index }}">
                                                                    <input type="hidden" name="tdsdAmount " value="0"
                                                                        id="tdsAmount_{{ $index }}">
                                                                    <input type="hidden" name="tdsApplicatble"
                                                                        value="0"
                                                                        id="tdsApplicable_{{ $index }}">
                                                                    <input type="hidden" name="currInterest"
                                                                        value="{{ $currInterest }}"
                                                                        id="currInterest_{{ $index }}">
                                                                    <input type="hidden" name="data"
                                                                        value="{{ json_encode($row) }}"
                                                                        id="data_{{ $index }}">
                                                                    <div class="col-lg-12 error-msg">
                                                                        <input type="hidden" name="tds_amount_actual[]"
                                                                            id="tds_amount_actual_{{ $index }}"
                                                                            class="form-control tds_amount_actual"
                                                                            style="width: 100px" readonly
                                                                            value="{{ number_format((float) 0, 2, '.', '') }}">

                                                                        <input type="text" name="tds_amount[]"
                                                                            id="tds_amount_{{ $index }}"
                                                                            class="form-control tds_amount"
                                                                            style="width: 100px"
                                                                            data-index="{{ $index }}"
                                                                            value="{{ number_format((float) 0, 2, '.', '') }}">
                                                                    </div>
                                                                </td>
                                                                <td style="border: 1px solid #ddd;">


                                                                    <div class="col-lg-12 error-msg">
                                                                        <input type="text" name="transfer_amount[]"
                                                                            readonly id="transfer_amount_{{ $index }}"
                                                                            class="form-control transfer_amount"
                                                                            style="width: 100px"
                                                                            value="{{ number_format((float) $row->rent, 2, '.', '') }}">

                                                                    </div>
                                                                </td>
                                                                <!--<td style="border: 1px solid #ddd;">{{ $row['employee_rent']->employee_code }}</td>-->
                                                                <td style="border: 1px solid #ddd;">
                                                                    {{ $row['employee_rent']->employee_name }}</td>
                                                                <td style="border: 1px solid #ddd;">
                                                                    {{ $row['employee_rent']['designation']->designation_name }}
                                                                </td>
                                                                <td style="border: 1px solid #ddd;">
                                                                    {{ $row['employee_rent']->mobile_no }}</td>

                                                                <input type="hidden" class="form-control  "
                                                                    name="rent_lib_id[]" value="{{ $row->id }}">

                                                            </tr>
                                                        @endforeach
                                                        <input type="hidden" class="form-control  " name="ledger_month"
                                                            value="{{ $re_month1 }}">
                                                        <input type="hidden" class="form-control  " name="ledger_year"
                                                            value="{{ $re_year1 }}">


                                                        <input type="hidden" class="form-control created_at "
                                                            name="created_at" id="created_at">
                                                        <input type="hidden" class="form-control create_application_date "
                                                            name="create_application_date" id="create_application_date">
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2" align="right" style="border: 1px solid #ddd;">
                                                            <strong>Total Rent</strong> </td>
                                                        <td colspan="2" align="left" style="border: 1px solid #ddd;">
                                                            <span
                                                                id='rentAmount'><strong>{{ number_format((float) $totalRent, 2, '.', '') }}</strong>
                                                            </span> </td>
                                                        <td colspan="2" align="right" style="border: 1px solid #ddd;">
                                                            <strong>Total Tds</strong> </td>
                                                        <!-- <td colspan="2" align="left" style="border: 1px solid #ddd;"><span id='totalTds'><strong>{{ number_format((float) $totalTds, 2, '.', '') }}</strong> </span> </td> -->
                                                        <td colspan="2" align="left" style="border: 1px solid #ddd;">
                                                            <span
                                                                id='totalTds'><strong>{{ number_format((float) 0, 2, '.', '') }}</strong>
                                                            </span> </td>
                                                        <td colspan="2" align="right" style="border: 1px solid #ddd;">
                                                            <strong>Total Amount</strong> </td>
                                                        <td colspan="2" align="left" style="border: 1px solid #ddd;">
                                                            <span
                                                                id='sum'><strong>{{ number_format((float) $total, 2, '.', '') }}</strong>
                                                            </span> </td>
                                                    </tr>
                                                </tfoot>
                                            @else
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="11" align="center" style="border: 1px solid #ddd;">No
                                                            Record Found!</td>
                                                    </tr>
                                                </tfoot>
                                                @endif

                                            </table>
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        @if (count($rent) > 0)
                                            <button type="submit" class=" btn bg-dark legitRipple"
                                                id="submit_transfer">Create Ledger</button>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endisset
        </div>
    </div>
@stop

@section('script')

    @include('templates.admin.rent-management.partials.ledger_script')
@endsection
