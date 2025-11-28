@extends('templates.admin.master')

@section('content')
<?php
$f1 = 0;
$f2 = 0;
if (isset($_GET['from_date'])) {

  $start_date = $_GET['from_date'];
}
if (isset($_GET['to_date'])) {
  $end_date = $_GET['to_date'];
}
if (isset($_GET['branch'])) {
  $branch_id = hex2bin($_GET['branch']);
}
$companydetails = getCompanyDetail($company_id);
$tanuredata = getTanureDetail($company_id);
?>

<style type="text/css">

</style>

<div class="content">

  <div class="row" id="daybook_print">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td style="padding:20px;width: 60%;">
          <div class="card bg-white">
            <div class="card-body">
              <h3 class="card-title mb-3 text-center">Print Receipt </h3>
              <div class="col-md-12">
                <h4 class="text-center font-weight-semibold">{{$companydetails->name}}</h4>
                <h6 class="text-center mx-auto" style="width:50%;">Corp. Office: {{$companydetails->address}} Info.: Phone No.: +91 {{$companydetails->mobile_no}} | Web: www.samraddhbestwin.com | Mail Us: {{$companydetails->email}}</h6>
              </div>
              <div class="row">

                <div class="row">

                  <table width="25%" border="2" border="2" cellspacing="" cellpadding="" align="center" style="margin: 20px" title="sdf">
                    <thead>
                      <th colspan="4">Cash</th>
                    </thead><?php
                            $getBranchOpening_cash = getBranchOpeningDetail($branch_id);
                            $balance_cash = 0;
                            $C_balance_cash = 0;

                            if ($getBranchOpening_cash->date == $start_date) {
                              $balance_cash = $getBranchOpening_cash->total_amount;
                            }
                            if ($getBranchOpening_cash->date < $start_date) {
                              $getBranchTotalBalance_cash = getBranchTotalBalanceAllTran($start_date, $getBranchOpening_cash->date, $getBranchOpening_cash->total_amount, $branch_id, $company_id);
                              $balance_cash = $getBranchTotalBalance_cash;
                            }
                            $getTotal_DR = getBranchTotalBalanceAllTranDR($start_date, $end_date, $branch_id, $company_id);
                            $getTotal_CR = getBranchTotalBalanceAllTranCR($start_date, $end_date, $branch_id, $company_id);
                            $totalBalance = $getTotal_CR - $getTotal_DR;
                            $C_balance_cash = $balance_cash + $totalBalance;
                            ?>

                    <tr>
                      <td>Opening</td>
                      <td></td>
                      <td> {{number_format((float)$balance_cash, 2, '.', '')}}</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>Received</td>
                      <td>Payment</td>
                    </tr>
                    <tr>
                      <td>Cash</td>
                      <td>{{number_format((float)$cash_in_hand_cr, 2, '.', '')}}</td>
                      <td>{{number_format((float)$cash_in_hand_dr, 2, '.', '')}}</td>
                    </tr>
                    <tr>
                      <td>Closing</td>
                      <td></td>
                      <td>{{number_format((float)$C_balance_cash, 2, '.', '')}} </td>
                    </tr>

                  </table>
                  <table width="25%" border="2" border="2" cellspacing="" cellpadding="" align="center" style="margin: 20px">
                    <thead>
                      <th colspan="4">Cheque</th>
                    </thead>
                    <tr>
                      <td>Opening</td>
                      <td></td>
                      <td>{{number_format((float)getchequeopeningBalance($start_date,$branch_id,$company_id), 2, '.', '')}}</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>Received</td>
                      <td>Payment</td>
                    </tr>
                    <tr>
                      <td>Cheque</td>
                      <td>{{number_format((float)$cheque_cr, 2, '.', '')}}</td>
                      <td>{{number_format((float)$cheque_dr, 2, '.', '')}}</td>
                    </tr>
                    <tr>
                      <td>Closing</td>
                      <td></td>
                      <td>{{number_format((float)getchequeclosingBalance($end_date,$branch_id,$company_id), 2, '.', '')}}</td>
                    </tr>

                  </table>
                  <div class="col-md-12">
                    <h3 class="card-title font-weight-semibold">Bank</h3>
                    <div class="card">
                      <table class="table table-flush">
                        <thead>
                          <tr>
                            <th>Bank Name</th>
                            <th>Account Number</th>
                            <th>Opening</th>
                            <th>Receiving</th>
                            <th>Payment</th>
                            <th>Closing</th>

                            <!-- <th>Account Head Code</th>
                                   <th>Account Head Name</th> -->
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($bank as $value)

                          <tr>
                            <td>{{$value->bank_name}}</td>
                            <td>@if(!empty($value['bankAccount'])) {{$value['bankAccount']->account_no}} @else N/A @endif</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div class="card-body">
                    <div class="row ">
                      <table width="100%" border="2" cellspacing="0" cellpadding="4" align="center" style="margin: 20px">
                        <tr>
                        <tr>
                          <th>Tr.No</th>
                          <th>Tr.Date</th>
                          <th>Receipt.No</th>
                          <th>Account Number</th>
                          <th>Company Name</th>
                          <th>Plan Name</th>
                          <th>Associate</th>
                          <th>Member/Employee/Owner</th>
                          <th>Narration</th>
                          <th>CR Narration</th>
                          <th>DR Narration</th>
                          <th>CR Amount</th>
                          <th>DR Amount</th>
                          <th>Balance</th>
                          <th>Ref Id</th>
                          <th>Payment Type</th>
                          <th>Tag</th>
                        </tr>
                        <?php
                        $a1 = 0;
                        $a11 = 0;
                        $getBranchOpening = getBranchOpeningDetail($branch_id);
                        $balance = 0;
                        if ($getBranchOpening->date == $start_date) {
                          $balance = $getBranchOpening->total_amount;
                        }
                        if ($getBranchOpening->date < $start_date) {
                          $getBranchTotalBalance = getBranchTotalBalanceAllTran($start_date, $getBranchOpening->date, $getBranchOpening->total_amount, $branch_id, $company_id);
                          $balance = $getBranchTotalBalance;
                        }

                        ?>

                        <tr>
                          <td class="child" colspan="12" style=" text-align: right; ">Opening Balance </td>
                          <td class="child" colspan="4"> {{number_format((float)$balance, 2, '.', '')}}</td>
                        </tr>

                        @foreach($alltransaction as $index => $value)

                        <?php
                                $types = getTransactionTypeCustom();

                      //  dd($value);
                        $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('company_id', $company_id)->where('id', $value->type_id)->first();
                        $salaryDetail = \App\Models\EmployeeSalary::with('salary_employee')->where('company_id', $company_id)->where('id', $value->type_id)->first();
                        if ($value->type == 14) {
                          if ($value->sub_type == 144) {
                            $voucherDetail = \App\Models\ReceivedVoucher::where('id', $value->type_transaction_id)->where('company_id', $company_id)->first();
                          } else {
                            $voucherDetail = \App\Models\ReceivedVoucher::where('id', $value->type_id)->where('company_id', $company_id)->first();
                          }
                        }

                        $memberName = 'N/A';
                        $memberAccount = 'N/A';
                        $plan_name = 'N/A';
                        $a_name = 'N/A';
                        $data = getCompleteDetail($value);
                        $memberAccount = $data['memberAccount'];
                        $type = $data['type'];
                        $plan_name = $data['plan_name'];
                        $memberId = $data['memberId'];
                        $memberName = $data['memberName'];
                        $a_name = $data['associate_name'];
            
                        $f1 = 0;
                        $f2 = 0;
                        
                        if (!empty($value->type) && $value->type != 21) {
                            if (array_key_exists($value->type . '_' . $value->sub_type, $types)) {
                                $type = $types[$value->type . '_' . $value->sub_type];
                            }
                        }
                        if (!empty($value->company_id)) {
                            $companyname = \App\Models\Companies::where('id', $value->company_id)->value('name');
                        } else {
                            $companyname = 'N/A';
                        }
                        if ($value->type == 21 && $value->sub_type == '') {
                            $record =  \App\Models\ReceivedVoucher::where('id', $value->type_id)->first();
                            if ($record) {
                                $type = $record->particular;
                            } else {
                                $type = "N/A";
                            }
                        }
                        if ($value->type == 22 || $value->type == 23) {
                            if ($value->sub_type == 222) {
                                $type = $value->description;
                            }
                        }
                        // Member Name, Member Account and Member Id
                        $is_eli = 0;
                        if ($value->sub_type == 30) {
                            $is_eli =  \App\Models\Daybook::where('id', $value->type_transaction_id)->first();
                            if (isset($is_eli->is_eli)) {
                                $is_eli = $is_eli->is_eli;
                            }
                        }
                       
                      
                        if ($value->payment_mode == 6) {
                            $rentPaymentDetail = $value['RentLiabilityLedger'];
                            $salaryDetail = $value['EmployeeSalary'];
                        } else {
                            $rentPaymentDetail = $value['RentPayment'];
                            $salaryDetail = $value['EmployeeSalaryBytype_id'];
                        }

                        $cr_amount = 0;
                        $dr_amnt = 0;
                        if ($value->payment_type == 'CR') {
                            $cr_amount = number_format((float)$value->amount, 2, '.', '');
                        }
                        if ($value->payment_type == 'DR') {
                            $dr_amnt =  number_format((float)$value->amount, 2, '.', '');
                        }
                        // Balance
                        if ($value->payment_mode == 0 && $is_eli == 0) {
                            $balance = number_format((float)$balance, 2, '.', '');
                        }
                        // Ref Number
                        if ($value->payment_mode == 0) {
                            $ref_no = 'N/A';
                        } elseif ($value->payment_mode == 1) {
                            $ref_no = $value->cheque_no;
                        } elseif ($value->payment_mode == 2) {
                            $ref_no = $value->transction_no;
                        } elseif ($value->payment_mode == 3) {
                            $ref_no = $value->v_no;
                        } elseif ($value->payment_mode == 6) {
                            $ref_no = $value->jv_unique_id;
                        } else {
                            $ref_no = 'N/A';
                        }
                        // Payment Mode
                        if ($value->sub_type == 30) {
                            $pay_mode = 'ELI';
                        } else
                            if ($value->payment_mode == 0) {
                            $pay_mode = 'CASH';
                        } elseif ($value->payment_mode == 1) {
                            $pay_mode = 'CHEQUE';
                        } elseif ($value->payment_mode == 2) {
                            $pay_mode = 'ONLINE TRANSFER';
                        } elseif ($value->payment_mode == 3) {
                            $pay_mode = 'SSB';
                        } elseif ($value->payment_mode == 4) {
                            $pay_mode = 'AUTO TRANSFER';
                        } elseif ($value->payment_mode == 5) {
                            $pay_mode = 'Loan';
                        } elseif ($value->payment_mode == 6) {
                            $pay_mode = 'JV';
                        }
                        if ($value->entry_date) {
                            $date = date("d/m/Y", strtotime(convertDate($value->entry_date)));
                        } else {
                            $date = 'N/A';
                        }

                        $tag = '';
                        if ($value->type = 3) {
                          if ($value->sub_type == 31) {
                            $tag = 'N';
                          }
                          if ($value->sub_type == 32) {
                            $tag = 'R';
                          }
                        }
                        if ($value->type == 4) {
                          if ($value->sub_type == 41) {
                            $tag = 'N';
                          }
                          if ($value->sub_type == 42) {
                            $tag = 'R';
                          }
                          if ($value->sub_type == 43) {
                            $tag = 'W';
                          }
                        }
                        if ($value->type == 5) {
                          if ($value->sub_type == 51) {
                            $tag = 'LD';
                          }
                          if ($value->sub_type == 52) {
                            $tag = 'L';
                          }
                          if ($value->sub_type == 54) {
                            $tag = 'LD';
                          }
                          if ($value->sub_type == 55) {
                            $tag = 'L';
                          }
                        }
                        if ($value->type == 7) {
                          $tag = 'B';
                        }
                        if ($value->type == 13) {
                          if ($value->sub_type == 131) {
                            $tag = 'E';
                          }
                          if ($value->sub_type == 133) {
                            $tag = 'M';
                          }
                          if ($value->sub_type == 134) {
                            $tag = 'M';
                          }
                          if ($value->sub_type == 135) {
                            $tag = 'M';
                          }
                          if ($value->sub_type == 136) {
                            $tag = 'M';
                          }
                          if ($value->sub_type == 137) {
                            $tag = 'M';
                          }
                        }
                        if ($value->payment_type == 'CR') {
                          if ($value->payment_mode == 0 && $is_eli == 0) {
                            $balance = $balance + $value->amount;
                          }
                        }
                        if ($value->payment_type == 'DR') {
                          if ($value->payment_mode == 0 && $is_eli == 0) {
                            $balance = $balance - $value->amount;
                          }
                        }

                        ?>
                        <tr>
                          <td>{{$index+1}}</td>
                          <td>@if($value->entry_date){{date("d/m/Y", strtotime(convertDate($value->entry_date)))}}@else @endif</td>
                          <td>{{ $value->id }}</td>
                          <td>{{$memberAccount}} </td>
                          <td>{{$companyname}} </td>
                          <td>{{$plan_name}} </td>
                          <td>
                            
                          {{$a_name}}
                          </td>
                          <td>{{$memberName}} </td>
                          <td>{{$type}}</td>
                          <td>{{$value->description_cr}}</td>
                          <td>{{$value->description_dr}}</td>
                          <td>@if($value->payment_type == 'CR'){{number_format((float)$value->amount, 2, '.', '')}}@endif </td>
                          <td>@if($value->payment_type == 'DR'){{number_format((float)$value->amount, 2, '.', '')}}@endif </td>
                          <td>
                            @if($value->payment_mode == 0 && $is_eli == 0 )

                            {{number_format((float)$balance, 2, '.', '')}}
                            @endif
                          </td>

                          <td>
                            @if($value->payment_mode == 0 )
                            N/A
                            @elseif($value->payment_mode == 1)
                            {{$value->cheque_no}}
                            @elseif($value->payment_mode == 2)
                            {{$value->transction_no}}
                            @elseif($value->payment_mode == 3)
                            {{$value->v_no}}
                            @elseif($value->payment_mode == 6)
                            {{$value->jv_unique_id }}
                            @else
                            N/A
                            @endif
                          </td>
                          <td> @if($value->sub_type == 30)
                            ELI
                            @else
                            @if($value->payment_mode == 0 )
                            CASH
                            @elseif($value->payment_mode == 1)
                            CHEQUE
                            @elseif($value->payment_mode == 2)
                            ONLINE TRANSFER
                            @elseif($value->payment_mode == 3)
                            SSB
                            @elseif($value->payment_mode ==4)
                            AUTO TRANSFER
                            @elseif($value->payment_mode ==6)
                            JV
                            @endif
                            @endif

                          </td>
                          <td>{{$tag}}</td>
                        </tr>
                        @endforeach

                        <tr>
                          <td class="child" colspan="12" style=" text-align: right; ">Closing Balance </td>
                          <td class="child" colspan="4"> {{number_format((float)$balance, 2, '.', '')}} </td>
                        </tr>
                      </table>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <table width="100%" border="2" cellspacing="" cellpadding="" align="center" style="margin: 20px">
                        <thead>
                          <tr>
                            <th></th>
                            <th colspan="2">NI</th>
                            <th colspan="2">Renew/Emi Recovery</th>
                            <th colspan="2">Payment</th>
                          </tr>
                        </thead>
                        <thead>
                          <tr>
                            <th>Plan</th>
                            <th>Total No A/C</th>
                            <th>Amount</th>
                            <th>Total No A/C</th>
                            <th>Amount</th>
                            <th>Total No A/C</th>
                            <th>Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                        @php 
                            $planDaily=getPlanID('710')->id;
                            $dailyId=array($planDaily);
                            $planSSB=getPlanID('703')->id;
                            $planKanyadhan=getPlanID('709')->id;
                            $planMB=getPlanID('708')->id;
                            $planFRD=getPlanID('707')->id;
                            $planJeevan=getPlanID('713')->id;
                            $planRD=getPlanID('704')->id;
                            $planBhavhishya=getPlanID('718')->id;
                            $planMI = getPlanID('712')->id;
                            $planFFD = getPlanID('705')->id;
                            $planFD = getPlanID('706')->id;
                            $fdId=array($planMI,$planFFD,$planFD);
		                        $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
                            $totalmonthly_new_ac_tenure = 0;
                            $totalmonthly_new_fd_ac_tenure = 0;
                            $totalbranchBusinessTenureInvestNewAcCount = 0;
                            $totalbranchBusinessTenureInvestNewDenoSum = 0;
                            $totalgetrenewemirecovertotalAccount = 0;
                            $totalgetrenewemirecovertotalAmount = 0;
                            $totalmonthly_renew_emi_recovery_amnt = 0;
                            $totalfd_renew_emi_recovery_amnt = 0;
                            $gettotalmatureAccount = 0;
                            $gettotalmatureAmount = 0;
                            $totalbranchBusinessInvestTenureNewDenoSumType = 0;
                            $totalfdbranchBusinessInvestTenureNewDenoSumType = 0;
                            $matureInvestTenureNewAcCountType = 0;
                            $monthly_mature_fd_ac_tenure = 0;
                            $monthly_mature_ac_amt_sum_tenure = 0;
                            $monthly_mature_fd_sum_ac_tenure = 0;
                            $totalmonthlygetrenewemirecovertotalAccount = 0;
                            $totalfddgetrenewemirecovertotalAccount = 0;
                            $tenureids = [];
                        @endphp
                        @if(!empty($tanuredata))
                          @foreach($tanuredata as $tenure)
                           @php 
                            if($tenure->plan_category_code == 'D'){
                              $plancategory = 'DAILY';
                            }elseif($tenure->plan_category_code == 'M'){
                              $plancategory = 'Monthly';
                            }elseif($tenure->plan_category_code == 'S'){
                              $plancategory = 'SAVING';
                            }elseif($tenure->plan_category_code == 'F'){
                              $plancategory = 'FIXED DEPOSIT';
                            }
                           
                            $branchBusinessTenureInvestNewAcCount = branchBusinessTenureInvestNewAcCount($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
                           
                            $branchBusinessTenureInvestNewDenoSum = branchBusinessTenureInvestNewDenoSum($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
                            $totalbranchBusinessTenureInvestNewDenoSum += $branchBusinessTenureInvestNewDenoSum;

                            

                            $getrenewemirecovertotalAccount = getrenewemirecovertotalAccount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
                            $totalgetrenewemirecovertotalAccount += $getrenewemirecovertotalAccount;

                            $getrenewemirecovertotalAmount = getrenewemirecovertotalAmount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
                            $totalgetrenewemirecovertotalAmount += $getrenewemirecovertotalAmount;

                            $totalmatureAccount = totalmatureAccount($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
                            $gettotalmatureAccount +=  $totalmatureAccount;

                            $totalmatureAmount = totalmatureAmount($start_date, $end_date, $branch_id,  $tenure->id, $tenure->tenure,$company_id);
                            
                            $gettotalmatureAmount +=  $totalmatureAmount;

                            $totalbranchBusinessTenureInvestNewAcCount += branchBusinessTenureInvestNewAcCount($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
                            

                            if($tenure->plan_category_code == 'M'){
                              $totalmonthly_new_ac_tenure +=  branchBusinessInvestTenureNewAcCountType($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
                              $totalmonthly_renew_emi_recovery_amnt += getrenewemirecovertotalAmount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
                              $matureInvestTenureNewAcCountType += matureInvestTenureNewAcCountType($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->tenure,$company_id);
                              $monthly_mature_ac_amt_sum_tenure += matureInvestTenureNewDenoSumType($start_date, $end_date, $branch_id,  $tenure->id, $tenure->tenure,$company_id);
                              $branchBusinessInvestTenureNewDenoSumType = branchBusinessInvestTenureNewDenoSumType($start_date,$end_date,$branch_id,$tenure->id,$tenure->tenure,$company_id=null);
                              $totalbranchBusinessInvestTenureNewDenoSumType +=  $branchBusinessInvestTenureNewDenoSumType;
                              $totalmonthlygetrenewemirecovertotalAccount += getrenewemirecovertotalAccount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
                            }  
                            if($tenure->plan_category_code == 'F'){
                              $totalfd_renew_emi_recovery_amnt +=  getrenewemirecovertotalAmount($start_date, $end_date, $branch_id, $tenure->tenure, $fdId,$company_id);
                              $totalmonthly_new_fd_ac_tenure += branchBusinessInvestTenureNewAcCountType($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure ,$company_id); 
                              $monthly_mature_fd_ac_tenure += matureInvestTenureNewAcCountType($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->tenure,$company_id);
                              $monthly_mature_fd_sum_ac_tenure += matureInvestTenureNewDenoSumType($start_date, $end_date, $branch_id, $fdId, $tenure->tenure,$company_id);
                              $totalfdbranchBusinessInvestTenureNewDenoSumType += branchBusinessInvestTenureNewDenoSumType($start_date,$end_date,$branch_id,$tenure->id,$tenure->tenure,$company_id=null);
                              $totalfddgetrenewemirecovertotalAccount += getrenewemirecovertotalAccount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
                            }
                            $tenureids [] = $tenure->tenure;
                          @endphp

                            <tr>
                              <td>{{$tenure->tenure}} {{$plancategory}}</td>
                              <td>{{$branchBusinessTenureInvestNewAcCount}}</td>
                              <td>{{$branchBusinessTenureInvestNewDenoSum}}</td>
                              <td>{{$getrenewemirecovertotalAccount}}</td>
                              <td>{{$getrenewemirecovertotalAmount}}</td>
                              <td>{{$totalmatureAccount}}</td>
                              <td>{{$totalmatureAmount}}</td>
                            </tr>
                          @endforeach
                          @else 
                          <tr>
                              <td colspan="7" class="text-center">No Record Found</td>
                            </tr>
                          @endif
                          @php
                             $tenureids = array(1, 3, 5, 7, 10);
                             $renew_emi_monthly_recovery_kanyadhan  = getmemberinvestementKanyadhanId($start_date, $end_date, $branch_id, $monthlyId, $tenureids,$company_id);
                          @endphp
                          @php 
                          $monthly_new_ac_tenurekanyadan = branchBusinessInvestTenureKanyadhan($start_date, $end_date, $branch_id, $monthlyId, $tenureids,$company_id);
                          $branchBusinessInvestKanyadhanTenureNewDenoSumType = branchBusinessInvestKanyadhanTenureNewDenoSumType($start_date, $end_date, $branch_id, $monthlyId, $tenureids,$company_id);
                          $getmemberinvestement_emi_recoverKanyadhan = getmemberinvestement_emi_recoverKanyadhan($start_date, $end_date, $branch_id, $renew_emi_monthly_recovery_kanyadhan,$company_id);
                          $getmemberinvestement_emi_recoverKanyadhan_sum = getmemberinvestement_emi_recoverKanyadhan_sum($start_date, $end_date, $branch_id, $renew_emi_monthly_recovery_kanyadhan,$company_id);
                          $matureInvestTenureKanyadhanNewAcCountType = matureInvestTenureKanyadhanNewAcCountType($start_date, $end_date, $branch_id, $fdId, $tenureids,$company_id);
                          $matureInvestTenureKanyadhanAmount = matureInvestTenureKanyadhanAmount($start_date, $end_date, $branch_id, $fdId, $tenureids,$company_id);
                          @endphp
                          <tr>
                              <td>Kanyadan</td>
                              <td>{{$monthly_new_ac_tenurekanyadan}}</td>
                              <td>{{$branchBusinessInvestKanyadhanTenureNewDenoSumType}}</td>
                              <td>{{$getmemberinvestement_emi_recoverKanyadhan}}</td>
                              <td>{{$getmemberinvestement_emi_recoverKanyadhan_sum}}</td>
                              <td>{{$matureInvestTenureKanyadhanNewAcCountType}}</td>
                              <td>{{$matureInvestTenureKanyadhanAmount}}</td>
                            </tr>
                            <tr>
                              <td>SSB</td>
                              <td>{{$ssbNiAc}}</td>
                              <td>{{$ssbNiAmount}}</td>
                              <td>{{$ssbRenewAc}}</td>
                              <td>{{$ssbRenewAmount}}</td>
                              <td>{{$ssbWAc}}</td>
                              <td>{{$ssbWAmount}}</td>
                            </tr>
                            <tr>
                              <td>Total</td>
                              <?php
                              $t1 = $totalbranchBusinessTenureInvestNewAcCount + $totalmonthly_new_ac_tenure + $monthly_new_ac_tenurekanyadan
                                + $totalmonthly_new_fd_ac_tenure + $ssbNiAc;
                                     
                              $t2 = $totalbranchBusinessTenureInvestNewDenoSum
                                + $totalbranchBusinessInvestTenureNewDenoSumType
                                + $totalfdbranchBusinessInvestTenureNewDenoSumType
                                + $branchBusinessInvestKanyadhanTenureNewDenoSumType
                                + $ssbNiAmount;


                              $t3 = $totalgetrenewemirecovertotalAccount  
                                    + $totalmonthlygetrenewemirecovertotalAccount
                                    + $totalfddgetrenewemirecovertotalAccount
                                    + $getmemberinvestement_emi_recoverKanyadhan
                                    + $ssbRenewAc;

                              $t4 = $totalgetrenewemirecovertotalAmount
                                + $totalmonthly_renew_emi_recovery_amnt
                                + $totalfd_renew_emi_recovery_amnt
                                + $ssbRenewAc;

                              $t5 = $gettotalmatureAccount
                                + $matureInvestTenureNewAcCountType
                                + $matureInvestTenureKanyadhanNewAcCountType
                                + $monthly_mature_fd_ac_tenure 
                                + $ssbWAc;


                              $t6 = $gettotalmatureAmount
                                + $monthly_mature_ac_amt_sum_tenure
                                + $matureInvestTenureKanyadhanAmount
                                + $monthly_mature_fd_sum_ac_tenure 
                                + $ssbWAmount;

                              ?>
                              <td>{{ $t1 }}</td>

                              <td>{{number_format((float)$t2, 2, '.', '')}}</td>

                              <td>{{$t3}}</td>

                              <td>{{number_format((float)$t4, 2, '.', '')}}</td>

                              <td>{{$t5}}</td>

                              <td>{{number_format((float)$t6, 2, '.', '')}}</td>
                            </tr>
                          <tr>
                            <td>Fund Transfer</td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td>{{$fund_transfer_total}}</td>
                            <td>{{number_format((float)$fund_transfer_amount_total, 2, '.', '')}}</td>
                          </tr>

                          <tr>
                            <td>File Charge</td>
                            <td>{{$file_chrg_total}}</td>
                            <td>{{number_format((float)$file_chrg_amount_total, 2, '.', '')}}</td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                          </tr>
                          <tr>
                            <td>MI</td>
                            <td>{{$mi_total}}</td>
                            <td>{{number_format((float)$mi_amount_total, 2, '.', '')}}</td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                          </tr>
                          <tr>
                            <td>STN</td>
                            <td>{{$stn_total}}</td>
                            <td>{{number_format((float)$stn_amount_total, 2, '.', '')}}</td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                          </tr>
                          <tr>
                            <td>Other</td>
                            <td>{{$other_total__income_account}}</td>
                            <td>{{number_format((float)$other_total__income_amount, 2, '.', '')}}</td>
                            <td>{{$other_total__expense_account}}</td>
                            <td>{{number_format((float)$other_total__expense_amount, 2, '.', '')}}</td>
                            <td> </td>
                            <td> </td>
                          </tr>
                          <tr>
                            <td>Investment Stationery Charge</td>
                            <td></td>
                            <td></td>
                            <td>{{$investment_stationary_chrg_account}}</td>
                            <td>{{number_format((float)$investment_stationary_chrg_amount, 2, '.', '')}}</td>
                            <td> </td>
                            <td> </td>
                          </tr>

                          <tr>
                            <td>LOAN</td>
                            <td> </td>
                            <td> </td>
                            <td>{{$loan_total_account}}</td>
                            <td>{{number_format((float)$loan_total_amount, 2, '.', '')}}</td>
                            <td> </td>
                            <td> </td>
                          </tr>
                          <tr>
                            <td>RECEIVED VOUCHER</td>
                            <td>{{$received_voucher_account}}</td>
                            <td>{{number_format((float)$received_voucher_amount, 2, '.', '')}}</td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

        </td>
      </tr>
    </table>
    <div class="col-md-12">
      <table class="col-md-12">
        <tr>
          <th class="">REMARKS:</th>
          <td>..................................................................................................................................................................................................................................................................................................................</td>
        </tr>
      </table>
    </div>

    <div class="d-flex justify-content-between col-md-12">
      <div class="col-md-4 mt-4 ">
        <table class="">
          <tr>
            <th>CHECKED BY SIG:</th>
            <td>....................................................................</td>
          </tr>
          <tr>
            <th>FULL NAME:</th>
            <td>....................................................................</td>
          </tr>
          <tr>
            <th>EMPLOYEE CODE:</th>
            <td>....................................................................</td>
          </tr>
        </table>
      </div>
      <div class="col-md-4 mt-4 ">
        <table class="">
          <tr>
            <th>PREPARED BY SIG:</th>
            <td>....................................................................</td>
          </tr>
          <tr>
            <th>FULL NAME:</th>
            <td>....................................................................</td>
          </tr>
          <tr>
            <th>EMPLOYEE CODE:</th>
            <td>....................................................................</td>
          </tr>
        </table>
      </div>
    </div>
    <div class="col-md-6 d-flex flex-column mt-4 mx-2">
      <h6>CERTIFIED THAT CASH & BANK BALANCE IS OK</h6>
      <table>
        <tr>
          <th>SERVICE CENTRE HEAD SIG:</th>
          <td>....................................................................</td>
        </tr>
        <tr>
          <th>FULL NAME:</th>
          <td>....................................................................</td>
        </tr>
        <tr>
          <th>EMPLOYEE CODE:</th>
          <td>....................................................................</td>
        </tr>
      </table>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card bg-white">
        <div class="card-body ">
          <div class="row">
            <div class="col-lg-12 text-center">
              <button type="submit" class="btn btn-primary" onclick="printDiv('daybook_print');"> Print<i class="icon-paperplane ml-2"></i></button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  @include('templates.admin.report.dublicate.script_print')
  @stop