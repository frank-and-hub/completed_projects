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
        $branch_id=hex2bin($_GET['branch']);
        
      
    }
?>

<style type="text/css">
  
</style>
<div class="content" >   
       
      <div class="row" id="advice" > 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td style="padding:20px;width: 60%;">
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3 text-center" >Print Receipt </h3>
                <div class="col-md-12">
                  <h4 class="text-center font-weight-semibold">Samraddh Bestwin Micro Finance Association</h4>
                  <h6 class="text-center mx-auto" style="width:50%;">Corp. Office: 114-115, Pushp Enclave, Sector -5, Pratap Nagar, Tonk Road Jaipur- 302033 Info.: Phone No.: +91 9829861860 | Web: www.samraddhbestwin.com | Mail Us: info@samraddhbestwin.com
                </h6>
            </div>
                  <div class="row">

                    <div class="row">

                    <table width="25%" border="2"  border="2" cellspacing="" cellpadding="" align="center" style="margin: 20px" title="sdf">
                      <thead>
                        <th colspan="4">Cash</th>
                      </thead>
                      
                        <tr>
                           <td>Opening</td>
                           <td></td>
                           <td>{{$balance_cash}}</td>
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
                        <td>{{$C_balance_cash}}</td>  
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
                                
                                  <tr>
                                  <td>{{$value->bank_name}}</td>
                                  <td>{{$value['bankAccount']->account_no}}</td>
                                   <td>{{number_format((float)getbankopeningBalance($start_date,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankreceivedBalance($start_date,$end_date,$branch_id,$value->id), 2, '.', '')}}</td>
                                    <td>{{number_format((float)getbankpaymentBalance($start_date,$end_date,$branch_id,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankclosingBalance($end_date,$value->id), 2, '.', '')}}</td>
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
                     <th>Account Number</th>
                     <th>Plan Name</th>
                     <th>Associate</th>
                     <th>Member/Employee/Owner</th>
                       <th> Narration</th>
                     <th>CR Narration</th>
                     <th>DR Narration</th>
                     <th>CR Amount</th>
                     <th>DR Amount</th>
                      <th>Balance</th>

                     <!-- <th>Account Head Code</th>
                     <th>Account Head Name</th> -->
                      <th>Ref Id</th>
                      <th>Payment Type</th>
                   </tr>
                    <?php
                        $getBranchOpening =getBranchOpeningDetail($branch_id);
                    $balance=0;
                    if($getBranchOpening->date==$start_date)
                    {
                      $balance=$getBranchOpening->total_amount;
                    }
                    if($getBranchOpening->date<$start_date)
                    {
                      $getBranchTotalBalance=getBranchTotalBalanceAllTran($start_date,$getBranchOpening->date,$getBranchOpening->total_amount,$branch_id);
                      $balance=$getBranchTotalBalance;
                    }
    ?>        
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
                     $DemandAdviceData = \App\Models\DemandAdvice::where('id',$value->type_id)->first();
                    $freshExpenseData = \App\Models\DemandAdviceExpense::where('id',$value->type_id)->first();
                    $memberName = '';
                    $memberAccount = '';
                                        $plan_name = '';
                     if($value->type == 1)
                    {
                        if($value->type_id){
                      $memberName =getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ;
                        }
                    }
                    elseif($value->type == 2)
                    {
                         if($value->type_id){
                      $memberName = getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ;
                         }
                    }
                    elseif($value->type ==3)
                    {
                       if($value->member_id){
                      $memberName =getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name;
                        if($memberData)
                        {

                         $plan_name =getPlanDetail($memberData->plan_id)->name;
                        }
                       }
                    }
                    elseif($value->type ==4)
                    {
                       if($value->member_id){
                      $memberName = getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name ;
                       $plan_name =getPlanID('703')->name;
                       }
                    }
                    elseif($value->type ==5)
                    {
                     if($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57)
                     {
                         if($loanData)
                         {
                             $memberName = getMemberData($loanData->applicant_id)->first_name. ' '.getMemberData($loanData->applicant_id)->last_name;
                         }
                          if($loanData->loan_type==1)
                             {
                                $plan_name ='Personal Loan(PL)';
                             }
                             if($loanData->loan_type==2)
                             {
                                $plan_name ='Staff Loan(SL)';
                             }
                             if($loanData->loan_type==4)
                             {
                                $plan_name ='Loan against Investment plan(DL)';
                             }
                      
                     }
                     else{
                         if($groupLoanData)
                         {
                      $memberName = getMemberData($groupLoanData->applicant_id)->first_name. ' '.getMemberData($groupLoanData->applicant_id)->last_name;
                         }
                     }
                    }
                      elseif($value->type ==6)
                    {
                      $memberName = getEmployeeData($value->type_id)->employee_name;
                    }
                    elseif($value->type ==7)
                    {
                      $memberName = App\Models\SamraddhBank::where('id',$value->transction_bank_to)->first();
                      if(isset($memberName))
                      {
                        $memberName =  $memberName->bank_name;
                      }
                       else{
                        $memberName = 'N/A';
                       }
                    }
                    elseif($value->type ==9)
                    {
                      $memberName ==getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ; 
                    }
                    elseif($value->type ==10)
                    {
                      if($rentPaymentDetail['rentLib'])
                      {
                        if($rentPaymentDetail)
                        {
                           $memberName = $rentPaymentDetail['rentLib']->owner_name;

                        }
                      }


                    }

                     elseif($value->type ==11)
                    {
                      if($DemandAdviceData['employee_name'])
                      {
                        $memberName = $DemandAdviceData->party_name;

                      }
                    }
                    elseif($value->type ==12)
                    {
                      if($salaryDetail['salary_employee'])
                      {
                         $memberName = $salaryDetail['salary_employee']->employee_name;
 
                      }
                    }
                     elseif($value->type ==13)
                    {

                      if($value->sub_type == 131 )
                        {
                          if($freshExpenseData)
                          {

                            $memberName = $freshExpenseData->party_name;
                          }
                        }
                        if($value->sub_type == 132 )
                        {
                          if($DemandAdviceData)
                          {
                            $memberName = $DemandAdviceData->employee_name;
                          }
                        }
                        if($value->sub_type == 133 )
                        {
                          $memberName = getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name ;
                          
                        }
                        if($value->sub_type == 134 )
                        {
                          $memberName = getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name ;
                        }
                        if($value->sub_type == 135 )
                        {
                          $memberName = getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name ;
                        }
                        if($value->sub_type == 136 )
                        {
                          $memberName = getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name ;
                        }
                        if($value->sub_type == 137 )
                        {
                          $memberName = getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name ;
                        }

                    }
                     elseif($value->type ==14)
                    {
                      if($voucherDetail != '')
                      {

                        if($voucherDetail->type == 1)
                        {
                            $memberName = App\Models\ShareHolder::where('type',19)->where('head_id',$voucherDetail->director_id)->first();
                            if($memberName)
                            {
                              $memberName = $memberName->name;
                            }
                        }
                        if($voucherDetail->type == 2 )
                        {
                             $memberName = App\Models\ShareHolder::where('type',15)->where('head_id',$voucherDetail->shareholder_id)->first();
                            if($memberName)
                            {
                              $memberName = $memberName->name;
                            }
                        }
                        if($voucherDetail->type == 3 )
                        {
                          $memberName =  getEmployeeData($voucherDetail->employee_id)->employee_name;
                        }
                        if($voucherDetail->type == 4 )
                        {
                          $memberName = getSamraddhBank($voucherDetail->bank_id);
                          if(isset($memberName))
                          {
                            $memberName = $memberName->bank_name;
                          }
                          else{
                            $memberName  = 'N/A';
                          }
                        }
                         if($voucherDetail->type ==5 )
                        {
                          if(isset($voucherDetail->eli_loan_id))
                          {
                            $memberName = getAcountHeadNameHeadId($voucherDetail->eli_loan_id);
                              if(isset($memberName->sub_head))
                              {
                                $memberName = $memberName->sub_head;
                              }
                              else{
                                $memberName = 'N/A';
                              }
                          }
                          else{
                            $memberName = 'N/A';
                          }
                          
                        }
                     
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
                      $memberName =App\Models\LoanFromBank::where('account_head_id',$value->type_id)->first();
                      if(isset($memberName))
                      {
                        $memberName = $memberName->bank_name;
                      }
                      else{
                        $memberName = 'N/A';
                      }
                    }
                    elseif($value->type ==21)
                    {
                     $memberName = getMemberData($value->branch_member_id)->first_name. ' '.getMemberData($value->branch_member_id)->last_name;
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
                      
                        if( $memberData)
                        {
                            $memberAccount = $memberData->account_number;
                        }
                    }
                    elseif($value->type ==4)
                    {
                      $memberAccount = getSsbAccountNumber($value->type_id);
                      if($memberAccount)
                      {
                       $memberAccount = $memberAccount->account_no;

                      }

                    }
                    elseif($value->type ==5)
                    {
                     if($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57)
                     {
                         if($loanData)
                         {
                            $memberAccount    = $loanData->account_number;

                         }
                     }
                    
                      else
                      {
                        if($groupLoanData)
                        {
                           $memberAccount    = $groupLoanData->account_number;

                        }
                      }
                    }
                      elseif($value->type ==6)
                    {
                      $memberAccount = getEmployeeData($value->type_id)->employee_code;
                    }

                    elseif($value->type ==7)
                    {
                      $memberAccount =App\Models\SamraddhBankAccount::where('bank_id',$value->transction_bank_to)->first();
                      if($memberAccount)
                      {
                         $memberAccount = $memberAccount->account_no;

                      }

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
                      $memberAccount = $salaryDetail['salary_employee']->employee_name;
                    }
                   elseif($value->type ==13)
                    {
                     if($value->sub_type == 131 )
                        {
                          if($freshExpenseData)
                          {

                            $memberAccount = '';
                          }
                        }
                        if($value->sub_type == 132 )
                        {
                          if($DemandAdviceData)
                          {
                            $memberAccount ='';
                          }
                        }
                        if($value->sub_type == 133 )
                        {
                            
                          $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                           $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                            $plan_name = getPlanDetail($plan_id->plan_id)->name;
                        }
                        if($value->sub_type == 134 )
                        {
                         $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                         $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                            $plan_name = getPlanDetail($plan_id->plan_id)->name;
                        }
                        if($value->sub_type == 135 )
                        {
                          $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                          $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                            $plan_name = getPlanDetail($plan_id->plan_id)->name;
                        }
                        if($value->sub_type == 136 )
                        {
                         $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                         $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                            $plan_name = getPlanDetail($plan_id->plan_id)->name;
                        }
                        if($value->sub_type == 137 )
                        {
                         $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                         $plan_id = getMemberInvestment($DemandAdviceData->investment_id);

                            $plan_name = getPlanDetail($plan_id->plan_id)->name;
                        }
                    }
                     elseif($value->type ==14)
                    {
                      if($voucherDetail != '')
                      {

                        if($voucherDetail->type == 1)
                        {
                            // $memberName = App\Models\ShareHolder::where('type',19)->where('head_id',$voucherDetail->director_id)->first();
                            // if($memberName)
                            // {
                              $memberAccount = 'N/A';
                            // }
                        }
                        if($voucherDetail->type == 2 )
                        {
                            //  $memberName = App\Models\ShareHolder::where('type',15)->where('head_id',$voucherDetail->shareholder_id)->first();
                            // if($memberName)
                            // {
                              $memberAccount = 'N/A';
                            // }
                        }
                        if($voucherDetail->type == 3 )
                        {
                          $memberAccount =  getEmployeeData($voucherDetail->employee_id)->employee_code;
                        }
                        if($voucherDetail->type == 4 )
                        {
                          $memberAccount = getSamraddhBankAccountId($voucherDetail->bank_ac_id)->account_no;
                        }
                        if($voucherDetail->type ==5 )
                        {
                          $memberAccount= "N/A";
                        }
                      
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
                    
                    elseif($value->type ==21)
                    {
                      $memberAccount = getMemberData($value->branch_member_id)->member_id;
                    }

                    // Narration
                     if($value->branch_payment_type == 'CR')
                    {
                      if($value->branch_payment_mode == 0 && $value->sub_type != 30)
                      {
                          $balance=$balance+$value->amount;
                      }
                      
                    }
                    if($value->branch_payment_type == 'DR')
                    {
                      if($value->branch_payment_mode == 0 && $value->sub_type != 30)
                      {
                          $balance=$balance-$value->amount;
                      }
                      
                    }
                    $tag='';
                    $tag='';
                   $type = '';
                       $type = '';
                         $type = '';
                       if($value->type == 1)
                {
                    if($value->sub_type==11)
                    {
                        $type ="Member - MI";
                    }
                    elseif($value->sub_type==12)
                    {
                        $type = "Member - STN ";
                    }
                     elseif($value->sub_type==13)
                    {
                        $type = $value->description;
                    }
                    elseif($value->sub_type==14)
                    {
                        $type = $value->description;
                    }
                }
                if($value->type == 2)
                {
                    if($value->sub_type == 21)
                    {
                        $type = 'Associate - Associate Commission';
                    }
                     elseif($value->sub_type==22)
                    {
                       $type = $value->description;
                    }
                    elseif($value->sub_type==23)
                    {
                       $type = $value->description;
                    }
                }

                if($value->type == 3)
                {
                    if($value->sub_type == 30)
                    {
                        $type = 'Investment - ELI';
                    }
                    elseif ($value->sub_type == 31) {
                        $type = 'Investment - Register';
                         $tag='N';
                    }
                    elseif ($value->sub_type == 32) {
                          $type = 'Investment - Renew';
                           $tag='R';
                    }
                    elseif ($value->sub_type == 33) {
                          $type = 'Investment - Passbook Print';
                    }
                    elseif ($value->sub_type == 34) {
                          $type = 'Investment - Interest on Deposit';
                    }
                    elseif ($value->sub_type == 35) {
                          $type = 'Investment - Stationary Charge';
                    }
                    elseif ($value->sub_type == 36) {
                          $type = 'Investment - Money Back Interest Deposit';
                    }
                    elseif ($value->sub_type == 37) {
                          $type = 'Investment - Certificate Print';
                    }
                     elseif ($value->sub_type == 38) {
                          $type = $value->description;
                    }
                    elseif ($value->sub_type == 39) {
                          $type = $value->description;
                    }
                    elseif ($value->sub_type == 311) {
                         $type = $value->description;
                    }
                    elseif ($value->sub_type == 312) {
                          $type = $value->description;
                    }
                }

                if($value->type == 4)
                {
                    if($value->sub_type == 41)
                    {
                        $type = "SSB - Register";
                          $tag='N';
                    }
                    elseif ($value->sub_type == 42) {
                        $type = 'SSB - Renew(Deposit)';
                          $tag='R';
                    }
                    elseif ($value->sub_type == 43) {
                          $type = 'SSB - Withdraw';
                            $tag='W';
                    }
                    elseif ($value->sub_type == 44) {
                          $type = 'SSB - Passbook Print';
                    }
                    elseif ($value->sub_type == 45) {
                        $type = 'SSB - Commission';
                    }
                    elseif ($value->sub_type == 46) {
                          $type = 'SSB - Fule';
                    }
                    elseif ($value->sub_type == 47) {
                          $type = 'SSB - Transfer To Investment';
                    }
                    elseif ($value->sub_type == 48) {
                        $type = 'SSB - Transfer To loan';
                    }
                    elseif ($value->sub_type == 49) {
                          $type = 'SSB - Rent Transfer';
                    }
                    elseif ($value->sub_type == 410) {
                          $type = 'SSB - Salary Transfer';
                    }
                     elseif ($value->sub_type == 411) {
                          $type = 'Investment - Certificate Print';
                    }
                    elseif ($value->sub_type == 412) {
                          $type = $value->description;
                    }
                   
                    elseif ($value->sub_type == 413) {
                          $type = $value->description;
                    }
                    elseif ($value->sub_type == 414) {
                          $type = $value->description;
                    } 
                }

                if($value->type == 5)
                {
                     if($value->sub_type == 51)
                    {
                        $type = "Loan ";
                         $tag='LD';
                    }
                    elseif ($value->sub_type == 52) {
                        $type = 'Loan - Emi';
                         $tag='L';
                    }
                    elseif ($value->sub_type == 53) {
                          $type = 'Loan - Panelty';
                    }
                    elseif ($value->sub_type == 54) {
                          $type = 'Loan - Group Loan';
                            $tag='LD';
                    }
                    elseif ($value->sub_type == 55) {
                        $type = 'Loan - Group Loan Emi';
                         $tag='L';
                    }
                    elseif ($value->sub_type == 56) {
                          $type = 'Loan - Group Loan Panelty';
                    }
                    elseif ($value->sub_type == 57) {
                          $type = 'Loan - File Charge';
                    }
                    elseif ($value->sub_type == 58) {
                        $type = 'Loan - Group Loan File Charge';
                    }
                     elseif ($value->sub_type == 511) {
                        $type = $value->description;
                    }
                    elseif ($value->sub_type == 512) {
                        $type = $value->description;
                    }
                    elseif ($value->sub_type == 513) {
                        $type = $value->description;
                    }
                    elseif ($value->sub_type == 514) {
                        $type = $value->description;
                    }
                    elseif ($value->sub_type == 515) {
                       $type = $value->description;
                    }
                    elseif ($value->sub_type == 516) {
                       $type = $value->description;
                    }
                }

                if($value->type == 6)
                {
                    if($value->sub_type == 61)
                    {
                        $type = "Employee - Salary";
                    }
                     if($value->sub_type == 62)
                    {
                        $type = $value->description;
                    }
                }

                if($value->type == 7)
                {
                    if($value->sub_type == 70)
                    {
                         $type = "Transferred Branch To Bank - Branch Cash ";
                    }
                    elseif($value->sub_type == 71)
                    {
                         $type = "Transferred Branch To Bank - Branch Cheque ";
                    }
                    elseif($value->sub_type == 72)
                    {
                         $type = "Transferred Branch To Bank - Branch Online ";
                    }
                    elseif($value->sub_type == 73)
                    {
                         $type = "Transferred Branch To Bank - Branch SSB/JV ";
                    }
                    $tag='B';  
                 }
                  
                  
                 
                    

                 if($value->type == 8)
                {
                    if($value->sub_type = 80)
                    {
                        $type = "Transferred Bank To Bank - Bank Cash   ";
                    }
                    elseif($value->sub_type = 81)
                    {
                         $type = "Transferred Bank To Bank - Bank Cheque  ";
                    }
                    elseif($value->sub_type = 82)
                    {
                         $type = "Transferred Bank To Bank - Bank Online  ";
                    }
                    elseif($value->sub_type = 83)
                    {
                         $type = "Transferred Bank To Bank - Bank SSB/JV ";
                    }

                   
                   
                    
                }

                if($value->type == 9)
                {
                    if($value->sub_type == 90)
                    {
                        $type = "Tds - Commission";
                    }
                }

                if($value->type == 10)
                {
                    if($value->sub_type == 101)
                    {
                        $type = "Rent - Ledger";
                    }
                    elseif ($value->sub_type == 102) {
                        $type = 'Rent - Payment';
                    }
                    elseif ($value->sub_type == 103) {
                          $type = 'Rent - Security';
                    }
                    elseif ($value->sub_type == 104) {
                          $type = 'Rent - Advance';
                    }
                    elseif ($value->sub_type == 105) {
                          $type = $value->description;
                    }
                    elseif ($value->sub_type == 106) {
                          $type = $value->description;
                    }
                }

                if($value->type == 11)
                {
                    $type ="Demand";
                }

                 if($value->type ==12)
                {
                    if($value->sub_type == 121)
                    {
                        $type = "Salary - Ledger";
                    }
                    elseif ($value->sub_type == 122) {
                        $type = 'Salary - Transfer';
                    }
                    elseif ($value->sub_type == 123) {
                          $type = 'Salary - Advance';
                    }
                }

                if($value->type ==13)
                {
                    if($value->sub_type == 131)
                    {
                        //$type = "Demand Advice - Fresh Expense";
                        $type = $value->description;
                        $tag='E';
                        
                    }
                    elseif ($value->sub_type == 132) {
                        $type = 'Demand Advice - Ta Advance';
                    }
                    elseif ($value->sub_type == 133) {
                          $type = 'Demand Advice - Maturity';
                            $tag='M';
                    }
                    elseif ($value->sub_type == 134) {
                          $type = 'Demand Advice - Prematurity';
                            $tag='M';
                    }
                    elseif ($value->sub_type == 135) {
                        $type = 'Demand Advice - Death Help';
                          $tag='M';
                    }
                    elseif ($value->sub_type == 136) {
                          $type = 'Demand Advice - Death Claim';
                            $tag='M';
                    }
                    elseif ($value->sub_type == 137) {
                          $type = 'Demand Advice - EM';
                            $tag='M';
                    }
                    elseif ($value->sub_type == 138) {
                          $type = 'Demand Advice - Advance Salary';
                           
                    }
                    elseif ($value->sub_type == 139) {
                          $type = 'Demand Advice - Advance Rent';
                            
                    }
                    elseif ($value->sub_type == 138) {
                          $type = $value->description;
                    }
                }

                if($value->type == 14)
                {
                    if($value->sub_type == 141)
                    {
                        $type = "Voucher - Director ";
                    }
                    elseif ($value->sub_type == 142) {
                        $type = 'Voucher  - ShareHolder';
                    }
                    elseif ($value->sub_type == 143) {
                          $type = 'Voucher  - Penal Interest';
                    }
                    elseif ($value->sub_type == 144) {
                          $type = 'Voucher  - Bank';
                    }
                    elseif ($value->sub_type == 145) {
                        $type = 'Voucher  - Eli Loan';
                    }
                }

                if($value->type == 15)
                {
                    if($value->sub_type == 151)
                    {
                        $type = 'Director - Deposit';
                    }

                    elseif($value->sub_type == 152)
                    {
                        $type = 'Director - Withdraw';
                    }
                    elseif($value->sub_type == 153)
                    {
                       $type = $value->description;
                    }
                }

                if($value->type == 16)
                {
                     if($value->sub_type == 161)
                    {
                        $type = 'ShareHolder - Deposit';
                    }
                     elseif($value->sub_type == 162)
                    {
                        $type = 'ShareHolder - Transfer';
                    }
                     elseif($value->sub_type == 163)
                    {
                        $type = $value->description;
                    }
                }
                
                if($value->type == 17)
                {
                     if($value->sub_type == 171)
                    {
                        $type = 'Loan From Bank  - Create Loan';
                    }
                     elseif($value->sub_type == 172)
                    {
                        $type = 'Loan From Bank  - Emi Payment';
                    }
                     elseif($value->sub_type == 173)
                    {
                        $type = $value->description;
                    }
                }
                if($value->type == 18)
                {
                     if($value->sub_type == 181)
                    {
                        $type = 'Bank Charge  - Create';
                    }
                    
                }
                if($value->type == 19)
                {
                     if($value->sub_type == 191)
                    {
                        $type = 'Assets  - Assets';
                    }
                    elseif($value->sub_type == 192)
                    {
                        $type = 'Assets  - Depreciation';
                    }
                    
                }
                if($value->type == 20)
                {
                     if($value->sub_type == 201)
                    {
                        $type = 'Expense Booking  - Create Expense';
                    }
                    
                    
                }
                if($value->type == 21)
                {
                     $record = \App\Models\ReceivedVoucher::where('id',$value->type_id)->first();
                     if($record )
                     {
                         $type= $record->particular;
                    
                     }
                    
                    else{
                        $type="N/A";
                    }
                    
                }
                 if($value->type == 22)
                {
                     if($value->sub_type == 222)
                    {
                        $type = $value->description;
                    }
                    
                    
                }

                if($value->type == 23)
                {
                     if($value->sub_type == 232)
                    {
                        $type = $value->description;
                    }
                    
                    
                }
                if($value->sub_type==43)
                {
                  $associate_code = App\Models\SavingAccount::where('id',$value->type_id)->first();
                    $associate_name = App\Models\Member::where('id',$associate_code->associate_id)->first();
                }
                if($value->type==13  || $value->sub_type ==35 || $value->sub_type ==37 || $value->sub_type ==33 || $value->sub_type==34 || $value->type == 21)
                {
                  $associate_code = getAssociateId($value->member_id);
                  $associate_name = App\Models\Member::where('associate_no',$associate_code)->first();
                }
                 if($value->type == 20)
                {
                    $record = \App\Models\Expense::where('id',$value->type_id)->first();
                    if(isset($record->account_head_id) && isset($record->sub_head1) && isset($record->sub_head2))
                    {
                       $mainHead =  getAcountHeadData($record->account_head_id);
                       $subHead =  getAcountHeadData($record->sub_head1);
                       $subHead2 =  getAcountHeadData($record->sub_head2);
                       $plan_name = 'INDIRECT EXPENSE /'.$mainHead.'/'.$subHead.'/'.$subHead2;
                    }
                    elseif(isset($record->account_head_id) && isset($record->sub_head1) )
                    {
                        $mainHead =  getAcountHeadData($record->account_head_id);
                       $subHead =  getAcountHeadData($record->sub_head1);
                       
                       $plan_name = 'INDIRECT EXPENSE /'.$mainHead.'/'.$subHead;
                    }
                    elseif(isset($record->account_head_id))
                    {
                        $mainHead =  getAcountHeadData($record->account_head_id);
                     
                       
                       $plan_name = 'INDIRECT EXPENSE /'.$mainHead;
                    }

                }
                
                    ?>
                                   <tr>
                                       <td>{{$index+1}}</td>
                                       <td>@if($value->entry_date){{date("d/m/Y", strtotime(convertDate($value->entry_date)))}}@else  @endif</td>
                                       <td>{{ $value->btid }}</td>
                                      
                                      <td>{{$memberAccount}} </td>
                                      <td>{{$plan_name}} </td>
                                       <td>
                                          @if($value->sub_type==43 || $value->type == 13 ||  $value->sub_type ==35 || $value->sub_type ==37 || $value->sub_type ==33 || $value->sub_type==34 || $value->type == 21)
                                            @if($associate_name)
                                            {{$associate_name->first_name.' '.$associate_name->last_name }} ({{$associate_name->associate_no }}) 
                                            @endif
                                        @else
                                            @if($value->branch_associate_id)
                                              {{getMemberData($value->branch_associate_id)->first_name.' '.getMemberData($value->branch_associate_id)->last_name }} ({{getMemberData($value->branch_associate_id)->associate_no }})
                                            @endif
                                        @endif

                                       </td>
                                     <td>{{$memberName}} </td>
                                       <td>{{$type}}</td>
                                       <td>{{$value->description_cr}}</td>
                                       <td>{{$value->description_dr}}</td>
                                       <td>@if($value->payment_type == 'CR'){{number_format((float)$value->amount, 2, '.', '')}}@else N/A @endif </td>
                                       <td>@if($value->payment_type == 'DR'){{number_format((float)$value->amount, 2, '.', '')}}@else N/A @endif </td>
                                       <td>
                                        
                                        @if($value->branch_payment_mode == 0 && $value->sub_type != 30 ) 

                                          {{number_format((float)$balance, 2, '.', '')}}
                                        @endif
                                       </td>
                                       
                                      <td>
                                         @if($value->branch_payment_mode == 0 )
                                            N/A
                                            @elseif($value->branch_payment_mode == 1)
                                            {{$value->cheque_no}}
                                            @elseif($value->branch_payment_mode == 2)
                                            {{$value->transction_no}}
                                             @elseif($value->branch_payment_mode == 3)
                                             {{$value->v_no}}
                                             @elseif($value->branch_payment_mode == 6)
                                             {{$value->jv_unique_id }} 
                                             @else
                                            N/A
                                            @endif
                                       </td>
                                       <td> @if($value->sub_type == 30)
                                            ELI
                                        @else
                                        @if($value->branch_payment_mode == 0 )
                                            CASH
                                            @elseif($value->branch_payment_mode == 1)
                                            CHEQUE
                                            @elseif($value->branch_payment_mode == 2)
                                            ONLINE TRANSFER
                                             @elseif($value->branch_payment_mode == 3)
                                            SSB
                                             @elseif($value->branch_payment_mode ==4)
                                            AUTO TRANSFER
                                             @elseif($value->branch_payment_mode ==6)
                                           JV
                                         @endif   
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
                              <td>{{$monthly_renew_emi_recovery_amnt_sum}}</td>
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
                              <td>Investment Stationary Chrg</td>
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