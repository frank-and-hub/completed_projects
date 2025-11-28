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
                <div class="col-md-12" id='hide_div'>
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h6 class="card-title font-weight-semibold">Rent List</h6>
                        </div>
                        <form action="{!! route('admin.rent.rent_ledger_save') !!}" method="post" enctype="multipart/form-data" name="rent_generate"
                            id="rent_generate" class="rent_generate">
                            @csrf
                            <input type="hidden" name="created_at" class="created_at" id="created_at">
                            <input type="hidden" name="create_application_date" class="create_application_date"
                                id="create_application_date">
                            <input type="hidden" name="rent_ID" value="{{$rent_ID}}">


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
                                                        <th style="border: 1px solid #ddd;">Rent Type</th>
                                                        <th style="border: 1px solid #ddd;">Address</th>
                                                        <th style="border: 1px solid #ddd;">Owner Name</th>
                                                        <th style="border: 1px solid #ddd;">Security amount</th>
                                                        <th style="border: 1px solid #ddd;">Rent</th>
                                                        <th style="border: 1px solid #ddd;">Amount</th>
                                                        <th style="border: 1px solid #ddd;">Tds Amount</th>
                                                        <th style="border: 1px solid #ddd;">Transfer Amount</th>
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
                                                                <td style="border: 1px solid #ddd;">{{ $rent_record['rentCompany']->name }}</td>
                                                                <td style="border: 1px solid #ddd;">
                                                                    {{ $row['liabilityBranch']->name }}</td>
                                                                <td style="border: 1px solid #ddd;">
                                                                    {{ $row['AcountHeadCustom']->sub_head }}</td>
                                                                <td style="border: 1px solid #ddd;">{{ $row->place }}</td>
                                                                <td style="border: 1px solid #ddd;">{{ $row->owner_name }}
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
                                                                            min="0"
                                                                            value="{{ number_format((float) $rent_record->transfer_amount+$rent_record->tds_amount, 2, '.', '') }}">
                                                                    </div>
                                                                </td>
                                                                <td style="border: 1px solid #ddd;">
                                                                   
                                                                    <input type="text" name="tds_amount[]"
                                                                    id="tds_amount_{{ $index }}"
                                                                    class="form-control tds_amount"
                                                                    style="width: 100px"
                                                                    data-index="{{ $index }}"
                                                                    min="0"
                                                                    value="{{ number_format((float) $rent_record->tds_amount, 2, '.', '') }}">
                                                                </td>
                                                                <td style="border: 1px solid #ddd;">


                                                                    <div class="col-lg-12 error-msg">
                                                                        <input type="text" name="transfer_amount[]"
                                                                            readonly id="transfer_amount_{{ $index }}"
                                                                            class="form-control transfer_amount"
                                                                            style="width: 100px"
                                                                            value="{{ number_format((float) $rent_record->transfer_amount, 2, '.', '') }}">

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
                                                       


                                                        <input type="hidden" class="form-control created_at "
                                                            name="created_at" id="created_at">
                                                        <input type="hidden" class="form-control create_application_date "
                                                            name="create_application_date" id="create_application_date">
                                                </tbody>
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
                                                id="submit_transfer">Update Ledger</button>
                                        @endif

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

    @include('templates.admin.rent-management.partials.ledger_edit_script')
@endsection
