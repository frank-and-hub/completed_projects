@extends('templates.admin.master')
@php
$dropDown = $allCompany;
$filedTitle = 'Company';
$name = 'company_id';
@endphp
@section('content')
<div class="content">
    <div class="row">
        @if($errors->any())
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
                    <h6 class="card-title font-weight-semibold">Employee Salary </h6>
                </div>
                <form action="{!! route('admin.hr.salary_regenerate') !!}" method="post" enctype="multipart/form-data" name="salary_generate" id="salary_generate" class="salary_generate">
                    @csrf
                    <input type="hidden" name="created_at" class="created_at" id="created_at">
                    <input type="hidden" name="employee_salary_id" id="employee_salary_id" value="{{$employee_salary_id}}">

                    <input type="hidden" name="company" id="company" value=" {{$company_id}}">
                    <div class="card-body">
                        <div class="row justify-content-between">

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date <sup>*</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" name="select_date" id="select_date" class="form-control  " readonly value="{{ $ledgerDate }}">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-4">
								<div class="form-group row">									
									<div class="col-lg-12 error-msg">
										<div class="input-group d-flex flex-row-reverse">
											<button type="button" class="btn bg-dark legitRipple employee_salary_payable_export ml-2" data-extension="0" style="float: right;">Export xslx</button>
									    </div>
									</div>
								</div>
							</div>  --}}
                            <div class="col-md-12">
                                <div class="table-responsive py-4">
                                    <table class="table table-flush  table-striped">
                                        <thead>
                                            <tr>
                                                <th style="border: 1px solid #ddd;">S.No</th>
                                                <th style="border: 1px solid #ddd;">Company Name</th>
                                                <th style="border: 1px solid #ddd;">BR Name</th>
                                                <th style="border: 1px solid #ddd;">Employee Name </th>
                                                <th style="border: 1px solid #ddd;">Designation</th>
                                                <th style="border: 1px solid #ddd;">Gross Salary</th>
                                                <th style="border: 1px solid #ddd;">Leave</th>
                                                <th style="border: 1px solid #ddd;">Total Salary</th>
                                                <th style="border: 1px solid #ddd;">Deduction</th>
                                                <th style="border: 1px solid #ddd;">Incentive / Bonus </th>
                                                <th style="border: 1px solid #ddd;">Payable Amount </th>
                                                <th style="border: 1px solid #ddd;">ESI Amount </th>
                                                <th style="border: 1px solid #ddd;">PF Amount </th>
                                                <th style="border: 1px solid #ddd;">TDS Amount </th>
                                                <th style="border: 1px solid #ddd;">Transferred salary </th>
                                                <th style="border: 1px solid #ddd;">Bank Name</th>
                                                <th style="border: 1px solid #ddd;">Bank A/c No.</th>
                                                <th style="border: 1px solid #ddd;">IFSC code </th>
                                                <th style="border: 1px solid #ddd;">SSB A/c No.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($employee)>0)
                                            <?php
                                            $total = 0;
                                            $totalFinalAmount = 0;
                                            $totalCollection = 0;
                                            $totalFuleAmount = 0;
                                            ?>
                                            @foreach($employee as $index => $row)
                                            <?php
                                            $category = '';
                                            if ($row->category == 1) {
                                                $category = 'On-rolled';
                                            }
                                            if ($row->category == 2) {
                                                $category = 'Contract';
                                            }
                                            $total = $total + $row->salary;
                                            ?>
                                            <tr>
                                                <td style="border: 1px solid #ddd;">{{ $index+1 }}</td>
                                                <td style="border: 1px solid #ddd;">{{ isset($row['company']->name) ? $row['company']->name : 'N/A' }}</td>
                                                <td style="border: 1px solid #ddd;">{{ isset($row['branch']->name) ? $row['branch']->name : 'N/A' }}</td>

                                                <td style="border: 1px solid #ddd;">{{ $row->employee_name }}</td>
                                                <!--<td style="border: 1px solid #ddd;">{{ $row->employee_code }}</td>-->
                                                <td style="border: 1px solid #ddd;">{{ $row['designation']->designation_name }}</td>
                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="salary[{{$index}}]" id="salary_{{$index}}" class="form-control salary " style="width: 100px" readonly value="{{ number_format((float)$row->salary, 2, '.', '') }}
">
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="leave[{{$index}}]" id="leave_{{$index}}" class="form-control leave " style="width: 100px" value="0.00">
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="total_salary[{{$index}}]" id="total_salary_{{$index}}" class="form-control  total_salary" readonly style="width: 100px" value="{{ number_format((float)$row->salary, 2, '.', '') }}
">
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="deduction[{{$index}}]" id="deduction_{{$index}}" class="form-control  deduction" style="width: 100px" value="0.00">
                                                    </div>
                                                </td>

                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="incentive_bonus[{{$index}}]" id="incentive_bonus_{{$index}}" class="form-control  incentive_bonus" style="width: 100px" value="0.00">
                                                    </div>
                                                </td>




                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="transfer_salary[{{$index}}]" id="transfer_salary_{{$index}}" class="form-control  transfer_salary" readonly style="width: 100px" value="{{ number_format((float)$row->salary, 2, '.', '') }} ">
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="esi_amount[{{$index}}]" id="esi_amount_{{$index}}" class="form-control  esi_amount" style="width: 100px" value="0.00" @if($row->esi_account_no=='') readonly @endif>
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="pf_amount[{{$index}}]" id="pf_amount_{{$index}}" class="form-control  pf_amount" style="width: 100px" value="0.00" @if($row->pf_account_no =='') readonly @endif>
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="tds_amount[{{$index}}]" id="tds_amount_{{$index}}" class="form-control  tds_amount" style="width: 100px" value="0.00" @if($row->pen_card=='') readonly @endif>
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid #ddd;">
                                                    <div class="col-lg-12 error-msg">
                                                        <input type="text" name="final_payable_amount[{{$index}}]" id="final_payable_amount_{{$index}}" class="form-control  final_payable_amount" style="width: 100px" value="{{ number_format((float)$row->salary, 2, '.', '') }} " readonly>
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid #ddd;">{{ $row->bank_name }}</td>
                                                <td style="border: 1px solid #ddd;"> {{ $row->bank_account_no }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row->bank_ifsc_code }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row->ssb_account }}</td>
                                                <input type="hidden" name="employee_id[{{$index}}]" value="{{ $row->id }}">



                                            </tr>
                                            @endforeach

                                            <input type="hidden" name="salary_to_sum" id="salary_to_sum" value="{{ number_format((float)$total, 2, '.', '')}}">

                                            <input type="hidden" name="esi_to_sum" id="esi_to_sum" value="">

                                            <input type="hidden" name="pf_to_sum" id="pf_to_sum" value="">

                                            <input type="hidden" name="tds_to_sum" id="tds_to_sum" value="">
                                            <input type="hidden" name="transfer_to_sum" id="transfer_to_sum" value="{{ number_format((float)$total, 2, '.', '')}}">

                                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at">
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date">

                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td colspan="3" align="right" style="border: 1px solid #ddd;"><strong>Total Paybale Amount</strong> </td>
                                                <td colspan="1" align="left" style="border: 1px solid #ddd;"><span id='sum'><strong>{{ number_format((float)$total, 2, '.', '') }}</strong></span> </td>

                                                <td colspan="2" align="right" style="border: 1px solid #ddd;"><strong>Total ESI Amount</strong> </td>
                                                <td colspan="1" align="left" style="border: 1px solid #ddd;"><span id='sum_esi'><strong>0.00</strong></span> </td>

                                                <td colspan="2" align="right" style="border: 1px solid #ddd;"><strong>Total PF Amount</strong> </td>
                                                <td colspan="1" align="left" style="border: 1px solid #ddd;"><span id='sum_pf'><strong>0.00</strong> </span> </td>

                                                <td colspan="2" align="right" style="border: 1px solid #ddd;"><strong>Total TDS Amount</strong> </td>
                                                <td colspan="1" align="left" style="border: 1px solid #ddd;"><span id='sum_tds'><strong>0.00</strong> </span> </td>

                                                <td colspan="2" align="right" style="border: 1px solid #ddd;"><strong>Total Transferred s Amount</strong> </td>
                                                <td colspan="3" align="left" style="border: 1px solid #ddd;"><span id='sum_transfer'><strong>{{ number_format((float)$total, 2, '.', '') }}</strong> </span> </td>
                                            </tr>

                                        </tfoot>



                                        @else
                                        <tfoot>
                                            <tr>
                                                <td colspan="16" align="center" style="border: 1px solid #ddd;">No Record Found!</td>
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
                                @if(count($employee)>0)
                                <button type="submit" class=" btn bg-dark legitRipple" id="submit_transfer">Salary Re-Generate</button>
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
@include('templates.admin.hr_management.salary.regenerate_script')
@stop