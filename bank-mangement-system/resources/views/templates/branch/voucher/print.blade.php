@extends('layouts/branch.dashboard')

@section('content')

    <div class="container-fluid mt--6">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body page-title">
                            <h3 class="">Voucher Print</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="advice">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding:10px;width: 60%;">
                            <div class="card bg-white">
                                <div class="card-body">
                                    <h3 class="card-title mb-3 text-center">Print Receipt</h3>
                                    <div class="row ">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"
                                            style="margin: 20px">
                                            <tr>
                                                <td style="padding: 7px ;width:25%">Company Name :</td>
                                                <td style="padding: 7px;width:25%">{{ $row['company']->short_name }}</td>
                                                <td style="padding: 7px;width:25%"> BR Code : </td>
                                                <td style="padding: 7px;width:25%">{{ $row['rv_branch']->branch_code }}</td>
                                            </tr>
                                            <tr>

                                                <td style="padding: 7px;width:25%"> BR Name :</td>
                                                <td style="padding: 7px;width:25%">{{ $row['rv_branch']->name }} </td>
                                                <td style="padding: 7px;width:25%"> SO Name : </td>
                                                <td style="padding: 7px;width:25%">{{ $row['rv_branch']->sector }}</td>

                                            </tr>
                                            <tr>
                                                <td style="padding: 7px;width:25%"> RO Name : </td>
                                                <td style="padding: 7px;width:25%">{{ $row['rv_branch']->regan }} </td>
                                                <td style="padding: 7px;width:25%"> ZO Name : </td>
                                                <td style="padding: 7px;width:25%"> {{ $row['rv_branch']->zone }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 7px;width:25%"> Particular : </td>
                                                <td style="padding: 7px;width:25%"> {{ $row->particular }}</td>
                                                <td style="padding: 7px;width:25%"> Receive Mode : </td>
                                                <td style="padding: 7px;width:25%">
                                                    @if ($row->received_mode == 1)
                                                        Cheque
                                                    @elseif($row->received_mode == 2)
                                                        Online
                                                    @else
                                                        Cash
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 7px;width:25%">
                                                    @if ($row->type == 1)
                                                        Director :
                                                    @elseif($row->type == 2)
                                                        Shareholder :
                                                    @elseif($row->type == 3)
                                                        Employee Name/Code :
                                                    @elseif($row->type == 4)
                                                        Bank Name/Account No :
                                                    @elseif($row->type == 5)
                                                        Eli Loan :
                                                    @elseif($row->type == 6)
                                                        @if ($row->member_id != null)
                                                            Member Name :
                                                        @endif
                                                    @elseif($row->type == 7)
                                                        Account Sub Head :
                                                    @elseif($row->type == 8)
                                                        Associate Name :
                                                    @endif
                                                </td>
                                                <td style="padding: 7px;width:25%">
                                                    @if ($row->type == 1)
                                                        {{ getAcountHeadNameHeadId($row->director_id) }}
                                                    @elseif($row->type == 2)
                                                        {{ getAcountHeadNameHeadId($row->shareholder_id) }}
                                                    @elseif($row->type == 3)
                                                        {{ $row['rv_employee']->employee_name }} -
                                                        {{ $row['rv_employee']->employee_code }}
                                                    @elseif($row->type == 4)
                                                        {{ getSamraddhBank($row->bank_id)->bank_name }} -
                                                        {{ getSamraddhBankAccountId($row->bank_ac_id)->account_no }}
                                                    @elseif($row->type == 5)
                                                        {{ getAcountHeadNameHeadId($row->eli_loan_id) }}
                                                    @elseif($row->type == 6)
                                                        @if ($row->member_id != null)
                                                            {{ getMemberCustom($row->member_id)->first_name . ' ' . getMemberCustom($row->member_id)->last_name . ' (' . getMemberCompanyDataNew($row->member_id)->member_id . ')' }}
                                                        @endif
                                                    @elseif($row->type == 7)
                                                        {{ getAcountHead($row->expense_head) ? getAcountHead($row->expense_head) : 'N/A' }}
                                                    @elseif($row->type == 8)
                                                        {{ getMemberCustom($row->associate_id) ? getMemberCustom($row->associate_id)->first_name . ' ' . getMemberCustom($row->associate_id)->last_name : 'N/A' }}
                                                    @endif
                                                </td>
                                                <td style="padding: 7px;width:25%"> Account Head : </td>
                                                <td style="padding: 7px;width:25%">
                                                    {{ getAcountHeadNameHeadId($row->account_head_id) }}
                                                </td>
                                            </tr>
                                            @if ($row->received_mode == 1)
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> Cheque No : </td>
                                                    <td style="padding: 7px;width:25%"> {{ $row['rvCheque']->cheque_no }}
                                                    </td>
                                                    <td style="padding: 7px;width:25%"> Cheque Date : </td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ date('d/m/Y', strtotime($row->cheque_date)) }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> Party Name :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ $row['rvCheque']->account_holder_name }}</td>
                                                    <td style="padding: 7px;width:25%"> Party Bank :</td>
                                                    <td style="padding: 7px;width:25%">{{ $row['rvCheque']->bank_name }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> Party Bank A/c :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ $row['rvCheque']->cheque_account_no }}</td>
                                                    <td style="padding: 7px;width:25%"> Receive Bank :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ getSamraddhBank($row['rvCheque']->deposit_bank_id)->bank_name }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> Receive Bank A/c :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ getSamraddhBankAccountId($row['rvCheque']->deposit_account_id)->account_no }}
                                                    </td>
                                                    <td style="padding: 7px;width:25%"> Receive Amount :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ number_format((float) $row->amount, 2, '.', '') }} &#x20B9;</td>
                                                </tr>
                                            @elseif($row->received_mode == 2)
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> UTR/Transaction No :</td>
                                                    <td style="padding: 7px;width:25%"> {{ $row->online_tran_no }}</td>
                                                    <td style="padding: 7px;width:25%"> UTR/Transaction Date :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ date('d/m/Y', strtotime($row->online_tran_date)) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> Transaction Slip :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        @if ($row->slip)
                                                            <a href="{{ url('/') }}/asset/voucher/{{ $row->slip }}"
                                                                target="_blanck">{{ $row->slip }}</a>
                                                        @endif
                                                    </td>
                                                    <td style="padding: 7px;width:25%"> Transaction Bank Name :</td>
                                                    <td style="padding: 7px;width:25%">{{ $row->online_tran_bank_name }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> Transaction Bank A/c :</td>
                                                    <td style="padding: 7px;width:25%">{{ $row->online_tran_bank_ac_no }}
                                                    </td>
                                                    <td style="padding: 7px;width:25%"> Receive Bank :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ getSamraddhBank($row->receive_bank_id)->bank_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> Receive Bank A/c :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ getSamraddhBankAccountId($row->receive_bank_ac_id)->account_no }}
                                                    </td>
                                                    <td style="padding: 7px;width:25%"> Receive Amount :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ number_format((float) $row->amount, 2, '.', '') }} &#x20B9;</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td style="padding: 7px;width:25%"> Receive Amount :</td>
                                                    <td style="padding: 7px;width:25%">
                                                        {{ number_format((float) $row->amount, 2, '.', '') }} &#x20B9;</td>
                                                </tr>
                                            @endif
                                            @if ($row->type == 6)
                                                @if ((getGstTransation($row->id)) != null && getGstTransation($row->id)->amount_of_tax_igst > 0)
                                                    <tr>
                                                        <td style="padding: 7px;width:25%"> IGST Charge :</td>
                                                        <td style="padding: 7px;width:25%">
                                                            {{ getGstTransation($row->id)->amount_of_tax_igst ?? 'N/A' }}
                                                            &#x20B9;</td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td style="padding: 7px;width:25%"> CGST Charge :</td>
                                                        <td style="padding: 7px;width:25%">
                                                            {{ getGstTransation($row->id)->amount_of_tax_cgst ?? 'N/A' }}
                                                            &#x20B9;</td>
                                                        <td style="padding: 7px;width:25%"> SGST Charge :</td>
                                                        <td style="padding: 7px;width:25%">
                                                            {{ getGstTransation($row->id)->amount_of_tax_sgst ?? 'N/A' }}
                                                            &#x20B9;</td>
                                                    </tr>
                                                @endif
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body ">
                            <div class="row">
                                <div class="col-lg-12 text-center">
                                    <button type="submit" class="btn btn-primary" onclick="printDiv('advice');"> Print<i
                                            class="icon-paperplane ml-2"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @stop
    @section('script')
        @include('templates.branch.voucher.partials.script_print')
    @stop
