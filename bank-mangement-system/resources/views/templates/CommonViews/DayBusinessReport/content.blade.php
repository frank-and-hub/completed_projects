<div class=" col-md-12 ">
    <div class="card-header header-elements-inline">
        <div>
            <button type="submit" class="btn btn-primary" onclick="printDiv('dayBusinessReport');">
                Print<i class="icon-paperplane ml-2"></i></button>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="">
        <div class="col-md-12" id="dayBusinessReport">
            <div class="pr-12">
                <div class="card">
                    <table class="table table-bordered">
                        <tr>
                            <?php if (Auth::user()->role_id == 3) : ?>
                                <h4 class="card-title font-weight-semibold text-center mt-3">Monthly Business Report<br>{{$companyName}}</h4>
                            <?php else : ?>
                                <h6 class="card-title font-weight-semibold text-center mt-3">Monthly Business Report<br>{{$companyName}}</h6>
                            <?php endif; ?>
                        </tr>
                        <th colspan="2">PERIOD</th>
                        <th>FROM DATE:-</th>
                        <td>{{$start_date}}</td>
                        <td></td>
                        <th>TO DATE:-</th>
                        <td>{{$end_date}}</td>
                        </tr>
                        <tr>
                            <th colspan="2">BRANCH:</th>
                            <td>{{$name}}</td>
                            <th>CODE:</th>
                            <td>{{$branch_code}}</td>
                            <th>SECTOR</th>
                            <td>{{$sector}}</td>
                        </tr>
                        <tr>
                            <th colspan="2">REGION:</th>
                            <td>{{$regan}}</td>
                            <th>ZONE:</th>
                            <td>{{$zone}}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pr-12">
                <div class="card">
                    <table class="table  table-bordered">
                        <tr>
                        <tr>
                            <?php if (Auth::user()->role_id == 3) : ?>
                                <h4 class="card-title font-weight-semibold text-center mt-3">Daily Investment
                                </h4>
                            <?php else : ?>
                                <h6 class="card-title font-weight-semibold text-center mt-3">Daily Investment
                                </h6>
                            <?php endif; ?>
                        </tr>
                        <th colspan="2">Tenure</th>
                        <th>No of Ac NCC</th>
                        <th>NCC Amt</th>
                        <th>No of Ac Renewal</th>
                        <th>Renewal Amt</th>
                        <th>Total Amount</th>
                        <th>Maturity Payment</th>
                        </tr>
                        @php
                        $sumncc_D = 0;
                        $sumncc_R = 0;
                        $sumncc_A = 0;
                        $sumncc_Re = 0;
                        $sumncc_T = 0;
                        $sumncc_M = 0;
                        @endphp
                        @foreach($investmentDetails as $row)
                        @if($row->plancategory == 'D')
                        <tr>
                            <th colspan="2">{{$row->plantenure}} {{' Months'}}</th>
                            <td>{{$row->nccAC}}</td>
                            <td>{{$row->ncc}}</td>
                            <td>{{$row->renAC}}</td>
                            <td>{{$row->ren}}</td>
                            <td>{{$row->totalAmount}}</td>
                            <td>{{$row->maturityPayment}}</td>
                        </tr>
                        @php
                        $sumncc_D = $sumncc_D+$row->nccAC;
                        $sumncc_A = $sumncc_A+$row->ncc;
                        $sumncc_R = $sumncc_R+$row->renAC;
                        $sumncc_Re = $sumncc_Re+$row->ren;
                        $sumncc_T = $sumncc_T+$row->totalAmount;
                        $sumncc_M = $sumncc_M+$row->maturityPayment;
                        @endphp
                        @endif
                        @endforeach
                        <tr>
                            <th colspan="2">Total</th>
                            <td>{{$sumncc_D}}</td>
                            <td>{{$sumncc_A}}</td>
                            <td>{{$sumncc_R}}</td>
                            <td>{{$sumncc_Re}}</td>
                            <td>{{$sumncc_T}}</td>
                            <td>{{$sumncc_M}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pr-12">
                <div class="card">
                    <table class="table  table-bordered">
                        <tr>
                        <tr>
                            <?php if (Auth::user()->role_id == 3) : ?>
                                <h4 class="card-title font-weight-semibold text-center mt-3">Monthly Investment
                                </h4>
                            <?php else : ?>
                                <h6 class="card-title font-weight-semibold text-center mt-3">Monthly Investment
                                </h6>
                            <?php endif; ?>
                        </tr>
                        <th colspan="2">Tenure</th>
                        <th>No of Ac NCC</th>
                        <th>NCC Amt</th>
                        <th>No of Ac Renewal</th>
                        <th>Renewal Amt</th>
                        <th>Total Amount</th>
                        <th>Maturity Payment</th>
                        </tr>
                        @php
                        $sumnc_D = 0;
                        $sumnc_R = 0;
                        $sumnc_A = 0;
                        $sumnc_Re= 0;
                        $sumnc_T = 0;
                        $sumnc_M = 0;
                        @endphp
                        @foreach($investmentDetails as $row)
                        @if($row->plancategory == 'M')
                        <tr>
                            <th colspan="2">{{$row->plantenure}} {{' Months'}}</th>
                            <td>{{$row->nccAC}}</td>
                            <td>{{$row->ncc}}</td>
                            <td>{{$row->renAC}}</td>
                            <td>{{$row->ren}}</td>
                            <td>{{$row->totalAmount}}</td>
                            <td>{{$row->maturityPayment}}</td>
                        </tr>
                        @php
                        $sumnc_D = $sumnc_D+$row->nccAC;
                        $sumnc_A = $sumnc_A+$row->ncc;
                        $sumnc_R = $sumnc_R+$row->renAC;
                        $sumnc_Re = $sumnc_Re+$row->ren;
                        $sumnc_T = $sumnc_T+$row->totalAmount;
                        $sumnc_M = $sumnc_M+$row->maturityPayment;
                        @endphp
                        @endif
                        @endforeach
                        <tr>
                            <th colspan="2">Total</th>
                            <td>{{$sumnc_D}}</td>
                            <td>{{number_format((float)$sumnc_A, 2, '.', '')}}</td>
                            <td>{{$sumnc_R}}</td>
                            <td>{{number_format((float)$sumnc_Re, 2, '.', '')}}</td>
                            <td>{{number_format((float)$sumnc_T, 2, '.', '')}}</td>
                            <td>{{number_format((float)$sumnc_M, 2, '.', '')}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pr-12">
                <div class="card">
                    <table class="table  table-bordered">
                        <tr>
                        <tr>
                            <?php if (Auth::user()->role_id == 3) : ?>
                                <h4 class="card-title font-weight-semibold text-center mt-3">Kanyadaan Investment
                                </h4>
                            <?php else : ?>
                                <h6 class="card-title font-weight-semibold text-center mt-3">Kanyadaan Investment
                                </h6>
                            <?php endif; ?>
                        </tr>
                        <th colspan="2">Tenure</th>
                        <th>No of Ac NCC</th>
                        <th>NCC Amt</th>
                        <th>No of Ac Renewal</th>
                        <th>Renewal Amt</th>
                        <th>Total Amount</th>
                        <th>Maturity Payment</th>
                        </tr>
                        @php
                        $sumn_D = 0;
                        $sumn_R = 0;
                        $sumn_A = 0;
                        $sumn_Re = 0;
                        $sumn_T = 0;
                        $sumn_M = 0;
                        @endphp
                        @foreach($investmentDetails as $row)
                        @if($row->plancategory == 'K')
                        <tr>
                            <th colspan="2">{{$row->plantenure}} {{' Months'}}</th>
                            <td>{{$row->nccAC}}</td>
                            <td>{{$row->ncc}}</td>
                            <td>{{$row->renAC}}</td>
                            <td>{{$row->ren}}</td>
                            <td>{{$row->totalAmount}}</td>
                            <td>{{$row->maturityPayment}}</td>
                        </tr>
                        @php
                        $sumn_D = $sumn_D+$row->nccAC;
                        $sumn_A = $sumn_A+$row->ncc;
                        $sumn_R = $sumn_R+$row->renAC;
                        $sumn_Re = $sumn_Re+$row->ren;
                        $sumn_T = $sumn_T+$row->totalAmount;
                        $sumn_M = $sumn_M+$row->maturityPayment;
                        @endphp
                        @endif
                        @endforeach
                        <tr>
                            <th colspan="2">Total</th>
                            <td>{{$sumn_D}}</td>
                            <td>{{number_format((float)$sumn_A, 2, '.', '')}}</td>
                            <td>{{$sumn_R}}</td>
                            <td>{{number_format((float)$sumn_Re, 2, '.', '')}}</td>
                            <td>{{number_format((float)$sumn_T, 2, '.', '')}}</td>
                            <td>{{number_format((float)$sumn_M, 2, '.', '')}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pr-12">
                <div class="card">
                    <table class="table  table-bordered">
                        <tr>
                        <tr>
                            <?php if (Auth::user()->role_id == 3) : ?>
                                <h4 class="card-title font-weight-semibold text-center mt-3">Fixed Deposit Investment
                                </h4>
                            <?php else : ?>
                                <h6 class="card-title font-weight-semibold text-center mt-3">Fixed Deposit Investment
                                </h6>
                            <?php endif; ?>
                        </tr>
                        <th colspan="2">Tenure</th>
                        <th>No of Ac NCC</th>
                        <th>NCC Amt</th>
                        <th>No of Ac Renewal</th>
                        <th>Renewal Amt</th>
                        <th>Total Amount</th>
                        <th>Maturity Payment</th>
                        </tr>
                        @php
                        $sum_D = 0;
                        $sum_R = 0;
                        $sum_A = 0;
                        $sum_Re = 0;
                        $sum_T = 0;
                        $sum_M = 0;
                        @endphp
                        @foreach($investmentDetails as $row)
                        @if($row->plancategory == 'F')
                        <tr>
                            <th colspan="2">{{$row->plantenure}} {{' Months'}}</th>
                            <td>{{$row->nccAC}}</td>
                            <td>{{$row->ncc}}</td>
                            <td>{{$row->renAC}}</td>
                            <td>{{$row->ren}}</td>
                            <td>{{$row->totalAmount}}</td>
                            <td>{{$row->maturityPayment}}</td>
                        </tr>
                        @php
                        $sum_D = $sum_D+$row->nccAC;
                        $sum_A = $sum_A+$row->ncc;
                        $sum_R = $sum_R+$row->renAC;
                        $sum_Re =$sum_Re+$row->ren;
                        $sum_T = $sum_T+$row->totalAmount;
                        $sum_M = $sum_M+$row->maturityPayment;
                        @endphp
                        @endif
                        @endforeach
                        <tr>
                            <th colspan="2">Total</th>
                            <td>{{$sum_D}}</td>
                            <td>{{$sum_A}}</td>
                            <td>{{$sum_R}}</td>
                            <td>{{number_format((float)$sum_Re, 2, '.', '')}}</td>
                            <td>{{number_format((float)$sum_T, 2, '.', '')}}</td>
                            <td>{{number_format((float)$sum_M, 2, '.', '')}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pr-12">
                <div class="card">
                    <table class="table  table-bordered">
                        <tr>
                        <tr>
                            <?php if (Auth::user()->role_id == 3) : ?>
                                <h4 class="card-title font-weight-semibold text-center mt-3">Loan & Group Plans
                                </h4>
                            <?php else : ?>
                                <h6 class="card-title font-weight-semibold text-center mt-3">Loan & Group Plans
                                </h6>
                            <?php endif; ?>
                        </tr>
                        <th colspan="2">Plan</th>
                        <th>New A/c No.</th>
                        <th>New loan AMT</th>
                        <th>Recovery A/c No.</th>
                        <th>Recovery Amount</th>
                        </tr>
                        @php
                        $loan_name = 0;
                        $loan_a_n = 0;
                        $loan_amt = 0;
                        $loan_ren_a = 0;
                        $loan_ren_amount = 0;
                        @endphp
                        @foreach($loanDetails as $value)
                        <tr>
                            <th colspan="2">{{ $value->name . ' (' .  $value->loan_type  . ')' }}</th>
                            <td>{{$value->new_loan_ac_no}}</td>
                            <td>{{$value->new_loan_amt}}</td>
                            <td>{{$value->loan_rec_ac_no}}</td>
                            <td>{{$value->loan_rec_amount}}</td>
                        </tr>
                        @php
                        $loan_name = $value->name;
                        $loan_a_n =$loan_a_n+$value->new_loan_ac_no;
                        $loan_amt = $loan_amt+ $value->new_loan_amt;
                        $loan_ren_a = $loan_ren_a+ $value->loan_rec_ac_no;
                        $loan_ren_amount =$loan_ren_amount+$value->loan_rec_amount;
                        @endphp
                        @endforeach
                        <tr>
                            <th colspan="2">TOTAL</th>
                            <td>{{$loan_a_n}}</td>
                            <td>{{number_format((float)$loan_amt, 2, '.', '')}}</td>
                            <td>{{$loan_ren_a}}</td>
                            <td>{{number_format((float)$loan_ren_amount, 2, '.', '')}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pr-12">
                <div class="card">
                    <table class="table  table-bordered">
                        <tr>
                        <tr>
                            <?php if (Auth::user()->role_id == 3) : ?>
                                <h4 class="card-title font-weight-semibold text-center mt-3">Fund Transfer
                                </h4>
                            <?php else : ?>
                                <h6 class="card-title font-weight-semibold text-center mt-3">Fund Transfer
                                </h6>
                            <?php endif; ?>
                        </tr>
                        <th colspan="2">BANK NAME</th>
                        <th>BANK A/c NO.</th>
                        <th>AMT</th>
                        </tr>
                        @php
                        $fundTransferAMT = 0;
                        @endphp
                        @foreach($fundTransferDetails as $value)
                        <tr>
                            <td colspan="2">{{$value->bank_name}}</td>
                            <td>{{$value->account_no}}</td>
                            <td>{{$value->funds_transfer_amt}}</td>
                        </tr>
                        @php
                        $fundTransferAMT =$fundTransferAMT+$value->funds_transfer_amt;
                        @endphp
                        @endforeach
                        <tr>
                            <th colspan="2">TOTAL</th>
                            <td></td>
                            <td>{{number_format((float)$fundTransferAMT, 2, '.', '')}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pr-12">
                <div class="card">
                    <table class="table  table-bordered">
                        <tr>
                            <th colspan="2">NEW FW JOINING</th>
                            <th>NO. Of A/C THOROUGH <br>NEW WORKER</th>
                            <th>AMT. Of A/C THOROUGH NEW WORKER</th>
                            <th>TOTAL INVOLVEL FW </th>
                        </tr>
                        <tr>
                            <th colspan="2">{{$newFW}}</th>
                            <td> {{$Newaccount}}</td>
                            <td class="p-0">
                                <table width="100%">
                                    <tr>
                                        <th>NCC</th>
                                        <th>Renewal</th>
                                        <th>Total</th>
                                    </tr>
                                    <td>{{$ncc_amount}}</td>
                                    <td>{{$renewal_amt}}</td>
                                    <th>{{$renewal_amt + $ncc_amount}}</th>
                                </table>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pr-12">
                @php
                $daybookresult = getdaybookreportamounts($daybookreportglobal);
                $balance_cash = $daybookresult['balance_cash'];
                $cashInhand = $daybookresult['cashInhand'];
                $C_balance_cash = $daybookresult['C_balance_cash'];
                @endphp
                <div class="card">
                    <table class="table  table-bordered">
                        <tr>
                            <th colspan="2">OPENING CASH IN HAND RS.</th>
                            <td>{{number_format((float)$balance_cash, 2, '.', '')}}</td>
                        </tr>
                        <tr>
                            <th colspan="2">(+) TOTAl RECEVING RS.</th>
                            <td>{{number_format((float)$cashInhand['CR'], 2, '.', '')}}</td>
                        </tr>
                        <tr>
                            <th colspan="2">(-) TOTAl PAYMENTS</th>
                            <td>{{number_format((float)$cashInhand['DR'], 2, '.', '')}}</td>
                        </tr>
                        <tr>
                            <th colspan="2"> ACTUAL CASH IN HAND</th>
                            <td>{{number_format((float)$C_balance_cash, 2, '.', '')}}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function printDiv(elem) {
        $("#" + elem).print({
            //Use Global styles
            globalStyles: true,
            //Add link with attrbute media=print
            mediaPrint: true,
            //Custom stylesheet
            stylesheet: "{{ url('/') }}/asset/print.css",
            //Print in a hidden iframe
            iframe: false,
            //Don't print this
            noPrintSelector: ".avoid-this",
            //Add this at top
            //  prepend : "Hello World!!!<br/>",
            //Add this on bottom
            // append : "<span><br/>Buh Bye!</span>",
            header: null, // prefix to html
            footer: null,
            //Log to console when printing is done via a deffered callback
            deferred: $.Deferred().done(function() {})
        });
    }
</script>