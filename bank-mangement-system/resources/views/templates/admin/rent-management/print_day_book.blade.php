@extends('templates.admin.master')

@section('content')

<?php
  if(isset($_GET['from_date']))
    {

        $start_date=$_GET['from_date'];
       
      
    }
    if(isset($_GET['to_date']))
    {
        $end_date=$_GET['to_date'];
        
      
    }
    if(isset($_GET['branch']))
    {
      $branch_id=$_GET['branch'];
    } 

?>
<style type="text/css">
  
</style>

<div class="content" >   
       
      <div class="row" id="advice" > 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td style="padding:10px;width: 60%;">
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3 text-center" >Print Received </h3>
                <div class="col-md-12">
                  <h4 class="text-center font-weight-semibold">Samraddh Bestwin Micro Finance Association</h4>
                  <h6 class="text-center mx-auto" style="width:50%;">Corp. Office: 114-115, Pushp Enclave, Sector -5, Pratap Nagar, Tonk Road Jaipur- 302033
                  Info.: Phone No.: +91 9829861860 | Web: www.samraddhbestwin.com
                </h6>
            </div>
                  <div class="row">

                    <table width="25%" border="2"  border="2" cellspacing="" cellpadding="" align="center" style="margin: 20px" title="sdf">
                      <thead>
                        <th colspan="4">Cash</th>
                      </thead>
                      
                       <tr>
                           <td>Opening</td>
                           <td></td>
                           <td>{{number_format((float)getcashopeningBalance($start_date,$branch_id), 2, '.', '')}}</td>
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
                        <td>{{number_format((float)getcashclosingBalance($end_date,$branch_id), 2, '.', '')}}</td>  
                          </tr>
                    </table>
                     <table width="25%" border="2"  border="2" cellspacing="" cellpadding="" align="center" style="margin: 20px">
                       <thead>
                        <th colspan="4">Cheque</th>
                      </thead>
                         <tr>
                            <td>Opening</td>
                           <td></td>
                           <td>{{number_format((float)getchequeopeningBalance($start_date,$branch_id), 2, '.', '')}}</td> 
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
                           <td>{{number_format((float)getchequeclosingBalance($end_date,$branch_id), 2, '.', '')}}</td>
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
                                 <?php
                                    if($value->type == 17)
                                    {
                                      $opening = getbankopeningBalanceLoanFromBankType($start_date,$end_date,$branch_id,$value->id);
                                      $closing = getbankclosingBalanceLoanFromBankType($start_date,$end_date,$branch_id,$value->id);
                                    }
                                      $opening = 0;
                                      $closing = 0;
                                      
                                  ?>
                                 <tr>
                                 <td>{{$value->bank_name}}</td>
                                  <td>{{$value['bankAccount']->account_no}}</td>
                                   <td>{{number_format((float)getbankopeningBalance($start_date,$end_date,$branch_id,$value->id,$opening), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankreceivedBalance($start_date,$end_date,$branch_id,$value->id), 2, '.', '')}}</td>
                                    <td>{{number_format((float)getbankpaymentBalance($start_date,$end_date,$branch_id,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankclosingBalance($start_date,$end_date,$branch_id,$value->id,$closing), 2, '.', '')}}</td>
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
                      <th>Tr.No</th>
                     <th>Tr.Date</th>
                     <th>Receipt.No</th>
                     <th>Associate</th>
                     <th>Member/Employee/Owner/Plan/Loan</th>
                     <th>Account Number</th>
                     <th>Narration</th>
                     <th>CR Narration</th>
                     <th>DR Narration</th>
                     <th>CR Amount</th>
                     <th>DR Amount</th>
                     <th>Ref Id</th>
                     <th>Payment Type</th>
                      
                    <!--  <th>Account Head Code</th>
                     <th>Account Head Name</th> -->
                   </tr>
                   @foreach($samraddhData as $index => $value)
                    <?php
                    $rentPaymentDetail=\App\Models\RentPayment::with('rentLib')->where('id',$value->type_id)->first(); 
                    $salaryDetail=App\Models\EmployeeSalary::with('salary_employee')->where('id',$value->type_id)->first();
                    if($value->type==14)
                    {
                      if($value->sub_type == 144)
                      {
                         $voucherDetail=App\Models\ReceivedVoucher::where('id',$value->type_transaction_id)->first();
                      }
                      else{
                         $voucherDetail=App\Models\ReceivedVoucher::where('id',$value->type_id)->first();

                      }
                    }
                   
                    $memberData = getMemberInvestment($value->type_id);
                    $loanData = getLoanDetail($value->type_id);
                    $groupLoanData = getGroupLoanDetail($value->type_id);
                     $DemandAdviceData = \App\Models\DemandAdvice::with('expenses')->where('id',$value->type_id)->first();
                    $memberName = '';
                    $memberAccount = '';
                    if($value->type == 1)
                    {
                      $memberName =getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ;
                    }
                    elseif($value->type == 2)
                    {
                      $memberName = getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ;
                    }
                    elseif($value->type ==3)
                    {
                      
                      $memberName =getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name;
                    }
                    elseif($value->type ==4)
                    {
                      $memberName = ' SSB Account';
                    }
                    elseif($value->type ==5)
                    {
                     if($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57)
                     {
                      $memberName = getMemberData($loanData->applicant_id)->first_name. ' '.getMemberData($loanData->applicant_id)->last_name;
                     }
                     else{
                      $memberName = getMemberData($groupLoanData->applicant_id)->first_name. ' '.getMemberData($groupLoanData->applicant_id)->last_name;
                     }
                    }
                      elseif($value->type ==6)
                    {
                      $memberName = getEmployeeData($value->type_id)->employee_name;
                    }
                    elseif($value->type ==7)
                    {
                      $memberName = App\Models\SamraddhBank::where('id',$value->transction_bank_to)->first();
                       $memberName =  $memberName->bank_name;
                    }
                    elseif($value->type ==9)
                    {
                      $memberAccount ==getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ; 
                    }
                    elseif($value->type ==10)
                    {
                      $memberName = $rentPaymentDetail['rentLib'];
                    }

                     elseif($value->type ==11)
                    {
                      $memberName = $DemandAdviceData['employee_name'];
                    }
                    elseif($value->type ==12)
                    {
                      $memberName = $EmployeeSalary['salary_employee']->employee_name;
                    }
                     elseif($value->type ==13)
                    {
                      $memberName = $DemandAdviceData['employee_name'];
                    }
                     elseif($value->type ==14)
                    {
                      if($voucherDetail != '')
                      {
                        if($voucherDetail->type == 1 ||$voucherDetail->type == 2 || $voucherDetail->type == 5 )
                        {

                            $memberName = getAcountHeadNameHeadId($voucherDetail->account_head_id);
                        }
                          elseif($voucherDetail->type == 4){


                          $memberName = App\Models\LoanFromBank::where('account_head_id',$voucherDetail->account_head_id)->first();
                            if($memberName)
                            {
                              $memberName = $memberName->bank_name;

                            }
                          }
                         else
                          $memberName = getEmployeeData($value->employee_id)->employee_name;
                      }
                          
                    }
                     elseif($value->type ==15)
                    {
                      $memberName =getAcountHeadNameHeadId($value->type_id);
                    }
                     elseif($value->type ==16)
                    {
                      $memberName =getAcountHeadNameHeadId($value->type_id);
                    }
                     elseif($value->type ==17)
                    {
                      $memberName =App\Models\LoanFromBank::where('account_head_id',$value->type_id)->get()->bank_name;
                    }

                    // Account Number

                    if($value->type == 1)
                    {
                      $memberAccount =getMemberData($value->type_id)->member_id ;
                    }
                    elseif($value->type == 2)
                    {
                      $memberAccount = getMemberData($value->type_id)->associate_no ;
                    }
                    elseif($value->type ==3)
                    {
                      $memberAccount = $memberData->account_number;
                    }
                    elseif($value->type ==4)
                    {
                      $memberAccount = getSsbAccountNumber($value->type_id)->account_no;
                    }
                    elseif($value->type ==5)
                    {
                     if($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57)
                     {
                        $memberAccount    = $loanData->account_number;
                     }
                    
                      else
                      {

                       $memberAccount    = $groupLoanData->account_number;
                      }
                    }
                      elseif($value->type ==6)
                    {
                      $memberAccount = getEmployeeData($value->type_id)->employee_code;
                    }

                    elseif($value->type ==7)
                    {
                      $memberAccount =App\Models\SamraddhBankAccount::where('bank_id',$value->transction_bank_to)->first();
                      $memberAccount = $memberAccount->account_no;

                    }

                    if($value->type == 9)
                    {
                      $memberAccount =getMemberData($value->type_id)->member_id ;
                    }

                    elseif($value->type ==10)
                    {
                      $memberAccount = 'N/A';
                    }
                    elseif($value->type ==12)
                    {
                      $memberAccount = $EmployeeSalary['salary_employee']->employee_name;
                    }
                    elseif($value->type ==13)
                    {
                      $memberAccount = "N/A";
                    }
                     elseif($value->type ==14)
                    {
                      if($voucherDetail!= '')
                      {
                        if($voucherDetail->type == 1 ||$voucherDetail->type == 2 || $voucherDetail->type == 5 )
                            $memberAccount = getAcountHeadNameHeadId($voucherDetail->account_head_id);
                          elseif($voucherDetail->type == 4){


                          $memberAccount = App\Models\LoanFromBank::where('account_head_id',$voucherDetail->account_head_id)->first();
                            if($memberAccount)
                            {
                              $memberAccount = $memberAccount->loan_account_number;

                            }
                          }                         else
                          $memberAccount = getEmployeeData($value->employee_id)->employee_code;
                      }
                          
                    }
                     elseif($value->type ==15)
                    {
                      $memberAccount ="N/A";
                    }
                     elseif($value->type ==16)
                    {
                      $memberAccount ="N/A";
                    }
                     elseif($value->type ==17)
                    {
                      $memberAccount =App\Models\LoanFromBank::where('account_head_id',$value->type_id)->first();
                      if($memberAccount)
                      {
                       $memberAccount = $memberAccount->account_no;
                      }
                    }

                    ?>
                                   <tr>
                                       <td>{{$index+1}}</td>
                                       <td>@if($value->transction_date){{date("d/m/Y", strtotime(convertDate($value->transction_date)))}}@else N/A @endif</td>
                                       <td>N/A</td>
                                       <td>@if($value->associate_id){{getMemberData($value->associate_id)->first_name.' '.getMemberData($value->associate_id)->last_name }}  ({{getMemberData($value->associate_id)->associate_no }})@else N/A @endif</td>
                                       <td>{{$memberName}} </td>
                                      
                                      <td>{{$memberAccount}} </td>
                                       <td>{{$value->description}}</td>
                                       <td>{{$value->description_cr}}</td>
                                       <td>{{$value->description_dr}}</td>
                                       <td>@if($value->payment_type == 'CR'){{number_format((float)$value->amount, 2, '.', '')}}@else N/A @endif </td>
                                       <td>@if($value->payment_type == 'DR'){{number_format((float)$value->amount, 2, '.', '')}}@else N/A @endif </td>
                                       <td>
                                         @if($value->payment_mode == 0 )
                                            N/A
                                            @elseif($value->payment_mode == 1)
                                            {{$value->cheque_no}}
                                            @elseif($value->payment_mode == 2)
                                            {{$value->transction_no}}
                                             @elseif($value->payment_mode == 3)
                                             {{$value->v_no}}
                                             @else
                                            N/A
                                            @endif
                                       </td>
                                       <td>@if($value->payment_mode == 0 )
                                            CASH
                                            @elseif($value->payment_mode == 1)
                                            CHEQUE
                                            @elseif($value->payment_mode == 2)
                                            ONLINE TRANSFER
                                             @elseif($value->payment_mode == 3)
                                            SSB
                                             @elseif($value->payment_mode ==4)
                                            AUTO TRANSFER
                                            @endif

                                       </td>
                                   </tr>
                                   @endforeach
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
                           <th colspan="2" >Renew/Emi Recovery</th>
                           <th colspan="2" >Payment</th>
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
                            <tr>
                              <td>12 DAILY</td>
                              <td>{{$current_daily_new_ac_tenure12}}</td>
                              <td>{{$current_daily_new_ac_amt_sum_tenure12}}</td>
                              <td>{{$current_renew_emi_recovery_12}}</td>
                              <td>{{$current_renew_emi_recovery_amnt_12}}</td>
                              <td>{{$current_mature_account_12}}</td>
                              <td>{{$current_mature_amnt_12}}</td>
                            </tr>
                            <tr>
                              <td>24 DAILY</td>
                              <td>{{$current_daily_new_ac_tenure24}}</td>
                              <td>{{$current_daily_new_ac_amt_sum_tenure24}}</td>
                              <td>{{$current_renew_emi_recovery_24}}</td>
                              <td>{{$current_renew_emi_recovery_amnt_24}}</td>
                              <td>{{$current_mature_account_24}}</td>
                              <td>{{$current_mature_amnt_24}}</td>
                            </tr>
                            <tr>
                              <td>36 DAILY</td>
                              <td>{{$current_daily_new_ac_tenure36}}</td>
                              <td>{{$current_daily_new_ac_amt_sum_tenure36}}</td>
                             <td>{{$current_renew_emi_recovery_36}}</td>
                              <td>{{$current_renew_emi_recovery_amnt_36}}</td>
                              <td>{{$current_mature_account_36}}</td>
                              <td>{{$current_mature_amnt_36}}</td>
                            </tr>
                             <tr>
                              <td>60 DAILY</td>
                               <td>{{$current_daily_new_ac_tenure60}}</td>
                              <td>{{$current_daily_new_ac_amt_sum_tenure60}}</td>
                              <td>{{$current_renew_emi_recovery_60}}</td>
                              <td>{{$current_renew_emi_recovery_amnt_60}}</td>
                              <td>{{$current_mature_account_60}}</td>
                              <td>{{$current_mature_amnt_60}}</td>
                            </tr>
                            <tr>
                              <td>12 MONTH</td>
                              <td>{{$monthly_new_ac_tenure12}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure12}}</td>
                              <td>{{$monthly_renew_emi_recovery_12}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_12}}</td>
                              <td>{{$monthly_mature_ac_tenure12}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure12}}</td>
                            </tr>
                            <tr>
                              <td>36 MONTH</td>
                              <td>{{$monthly_new_ac_tenure36}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure36}}</td>
                              <td>{{$monthly_renew_emi_recovery_36}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_36}}</td>
                              <td>{{$monthly_mature_ac_tenure36}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure36}}</td>
                            </tr>
                            <tr>
                              <td>60 MONTH</td>
                             <td>{{$monthly_new_ac_tenure60}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure60}}</td>
                              <td>{{$monthly_renew_emi_recovery_60}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_60}}</td>
                              <td>{{$monthly_mature_ac_tenure60}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure60}}</td>
                            </tr>
                            <tr>
                              <td>84 MONTH</td>
                              <td>{{$monthly_new_ac_tenure84}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure84}}</td>
                              <td>{{$monthly_renew_emi_recovery_84}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_84}}</td>
                              <td>{{$monthly_mature_ac_tenure84}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure84}}</td>
                            </tr>
                            <tr>
                              <td>120 MONTH</td>
                               <td>{{$monthly_new_ac_tenure120}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure120}}</td>
                              <td>{{$monthly_renew_emi_recovery_120}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_120}}</td>
                              <td>{{$monthly_mature_ac_tenure120}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure120}}</td>
                            </tr>
                           <tr>
                            <td>Kanyadan</td>
                              <td>{{$monthly_new_ac_tenurekanyadan}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenurekanyadan}}</td>
                              <td>{{$monthly_renew_emi_recovery_acnt_kanyadhan}}</td> 
                              <td>{{$monthly_renew_emi_recovery_amnt_kanyadhan}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure_kanyadhan}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure_kanyadhan_amnt}}</td>
                                
                                </tr>
                            <tr>
                              <td>FD.12 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure12}}</td>
                              <td>{{$monthly_new_fd_sum_ac_tenure12}}</td>
                              <td>{{$fd_renew_emi_recovery_12}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_12}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure12}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure12}}</td>
                            </tr>
                            <tr>
                              <td>FD.18 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure18}}</td>
                              <td>{{$monthly_new_fd_sum_ac_tenure18}}</td>
                              <td>{{$fd_renew_emi_recovery_18}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_18}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure18}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure18}}</td>
                            </tr>
                            <tr>
                              <td>FD.48 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure48}}</td>
                              <td>{{$monthly_new_fd_sum_ac_tenure48}}</td>
                              <td>{{$fd_renew_emi_recovery_48}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_48}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure48}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure48}}</td>
                            </tr>
                            <tr>
                              <td>FD.60 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure60}}</td>
                               <td>{{$monthly_new_fd_sum_ac_tenure60}}</td>
                              <td>{{$fd_renew_emi_recovery_60}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_60}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure60}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure60}}</td>
                            </tr>
                            <tr>
                              <td>FD.72 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure72}}</td>
                               <td>{{$monthly_new_fd_sum_ac_tenure72}}</td>
                              <td>{{$fd_renew_emi_recovery_72}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_72}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure72}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure72}}</td>
                            </tr>
                            <tr>
                              <td>FD.96 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure96}}</td>
                               <td>{{$monthly_new_fd_sum_ac_tenure96}}</td>
                              <td>{{$fd_renew_emi_recovery_96}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_96}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure96}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure96}}</td>
                            </tr>
                            <tr>
                              <td>FD.120 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure120}}</td>
                               <td>{{$monthly_new_fd_sum_ac_tenure120}}</td>
                              <td>{{$fd_renew_emi_recovery_120}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_120}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure120}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure120}}</td>
                            </tr>
                            <tr>
                              <td>File Charge</td>
                              <td>{{$file_chrg_total}}</td>
                              <td>{{$file_chrg_amount_total}}</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>N/A</td>
                            </tr>
                            <tr>
                              <td>MI</td>
                              <td>{{$mi_total}}</td>
                              <td>{{$mi_amount_total}}</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>N/A</td>
                            </tr>
                            <tr>
                              <td>STN</td>
                              <td>{{$stn_total}}</td>
                              <td>{{$stn_amount_total}}</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>N/A</td>
                            </tr>
                            <tr>
                              <td>Other</td>
                              <td>{{$other_total__income_account}}</td>
                              <td>{{$other_total__income_amount}}</td>
                              <td>{{$other_total__expense_account}}</td>
                              <td>{{$other_total__expense_amount}}</td>
                              <td>N/A</td>
                              <td>N/A</td>
                            </tr>
                            <tr>
                              <td>LOAN</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>{{$loan_total_account}}</td>
                              <td>{{$loan_total_amount}}</td>
                              <td>N/A</td>
                              <td>N/A</td>
                            </tr>
                            <tr>
                              <td>RECEIVED VOUCHER</td>
                              <td>{{$received_voucher_account}}</td>
                              <td>{{$received_voucher_amount}}</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>N/A</td>
                              <td>N/A</td>
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
                          <td >..................................................................................................................................................................................................................................................................................................................</td>
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
          <div class="card bg-white" >            
            <div class="card-body ">
                <div class="row">
        <div class="col-lg-12 text-center">
              <button type="submit" class="btn btn-primary" onclick="printDiv('advice');"> Print<i class="icon-paperplane ml-2" ></i></button>
            </div> 
            </div>
          </div>
        </div>

      </div>  
</div>

@include('templates.admin.hr_management.salary.script_print')
@stop