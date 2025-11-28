<h3 class="card-title font-weight-semibold">Associate Business Compare Report
</h3>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
//echo "<pre>";print_r($data);die;
?>


<thead>
 <tr>
                                    <th  style="font-weight: bold;">S/N</th>
                                    <th  style="font-weight: bold;">BR Name</th>
                                    <th  style="font-weight: bold;">BR Code</th>
                                    <th  style="font-weight: bold;">SO Name</th>
                                    <th  style="font-weight: bold;">RO Name</th>
                                    <th  style="font-weight: bold;">ZO Name</th>

                                    <th style="font-weight: bold;">Associate Code</th>  
                                    <th style="font-weight: bold;">Associate Name</th>  
                                     <th style="font-weight: bold;">Carder</th> 
                                                                      
                                    <th style="font-weight: bold;">Current Daily N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Current Daily N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Current Daily Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Current Daily Renew - Total Amt</th>

                                    <th style="font-weight: bold;">Current Monthly N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Current Monthly N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Current Monthly Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Current Monthly Renew - Total Amt</th>  

                                    <th>Current FD N.I. - No. A/C</th>
                                    <th>Current FD N.I. - Total Deno</th>
                                  <!--  <th>Current FD Renew - No. A/C</th>
                                    <th>Current FD Renew - Total Amt</th>  -->

                                    <th style="font-weight: bold;">Current SSB N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Current SSB N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Current SSB Deposit- No. A/C</th>
                                    <th style="font-weight: bold;">Current SSB Deposit- Total Amt</th> 

                                   <!-- <th>Current Total Business N.I. - No. A/C</th>
                                    <th>Current Total Business N.I.- Total Amt.</th>
                                    <th>Current Total Business Renew - No. A/C</th>
                                    <th>Current Total Business Renew- Total Amt.</th>-->

                                    <th style="font-weight: bold;">Current Other MI</th>
                                    <th style="font-weight: bold;">Current Other STN</th>

                                    <th style="font-weight: bold;">Current NCC_M</th>
                                    <th style="font-weight: bold;">Current NCC</th>
                                    <th style="font-weight: bold;">Current TCC_M</th>
                                    <th style="font-weight: bold;">Current TCC</th>

                                    < <th style="font-weight: bold;">Current Loan - No. A/C</th>
                                    <th style="font-weight: bold;">Current Loan - Total Amt</th>


                                    <th style="font-weight: bold;">Current Loan Recovery - No. A/C</th>
                                    <th style="font-weight: bold;">Current Loan Recovery - Total Amt.</th>

                                    <th style="font-weight: bold;">Current New Associate Joining No.</th>
                                    <th style="font-weight: bold;">Current Total Associate Joining No.</th>  

                                    < <th style="font-weight: bold;">Current New Member Joining No.</th>
                                    <th style="font-weight: bold;">Current Total Member Joining No.</th>     


                                    <th style="font-weight: bold;">Compare  Daily N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Compare  Daily N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Compare  Daily Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Compare  Daily Renew - Total Amt</th>

                                    <th style="font-weight: bold;">Compare  Monthly N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Compare  Monthly N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Compare  Monthly Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Current Monthly Renew - Total Amt</th>   

                                     <th style="font-weight: bold;">Compare  FD N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Compare  FD N.I. - Total Deno</th>
                                  <!--   <th>Compare  FD Renew - No. A/C</th>
                                    <th>Compare  FD Renew - Total Amt</th>  -->

                                    <th style="font-weight: bold;">Compare  rent SSB N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Compare  SSB N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Compare  SSB Deposit- No. A/C</th>
                                    <th style="font-weight: bold;">Compare  SSB Deposit- Total Amt</th>  

                                   <!-- <th>Compare  Total Business N.I. - No. A/C</th>
                                    <th>Compare  Total Business N.I.- Total Amt.</th>
                                    <th>Compare  Total Business Renew - No. A/C</th>
                                    <th>Compare  Total Business Renew- Total Amt.</th>-->
                                    <th style="font-weight: bold;">Compare  Other MI</th>
                                    <th style="font-weight: bold;">Compare  Other STN</th>

                                    <th style="font-weight: bold;">Compare  NCC_M</th>
                                    <th style="font-weight: bold;">Compare  NCC</th>
                                    <th style="font-weight: bold;">Compare  TCC_M</th>
                                    <th style="font-weight: bold;">Compare  TCC</th>


                                    <th style="font-weight: bold;">Compare  Loan - No. A/C</th>
                                    <th style="font-weight: bold;">Compare  Loan - Total Amt</th>

                                    <th style="font-weight: bold;">Compare  Loan Recovery - No. A/C</th>
                                    <th style="font-weight: bold;">Compare  Loan Recovery - Total Amt.</th>

                                   <th style="font-weight: bold;">Compare  New Associate Joining No.</th>
                                    <th style="font-weight: bold;">Compare  Total Associate Joining No.</th> 

                                    <th style="font-weight: bold;">Compare  New Member Joining No.</th>
                                    <th style="font-weight: bold;">Compare  Total Member Joining No.</th>  
                                    
                                    <th style="font-weight: bold;">Result  Daily N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Result  Daily N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Result  Daily Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Result  Daily Renew - Total Amt</th>

                                    <th style="font-weight: bold;">Result  Monthly N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Result  Monthly N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Result  Monthly Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Result Monthly Renew - Total Amt</th>  

                                    <th style="font-weight: bold;">Result  FD N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Result  FD N.I. - Total Deno</th>
                                  <!--   <th>Result  FD Renew - No. A/C</th>
                                    <th>Result  FD Renew - Total Amt</th>  -->

                                     <th style="font-weight: bold;">Result  rent SSB N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Result  SSB N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Result  SSB Deposit - No. A/C</th>
                                    <th style="font-weight: bold;">Result  SSB Deposit - Total Amt</th> 


                                  <!--  <th>Result  Total Business N.I. - No. A/C</th>
                                    <th>Result  Total Business N.I.- Total Amt.</th>
                                    <th>Result  Total Business Renew - No. A/C</th>
                                    <th>Result  Total Business Renew- Total Amt.</th>-->

                                   <th style="font-weight: bold;">Result  Other MI</th>
                                    <th style="font-weight: bold;">Result  Other STN</th>

                                    <th style="font-weight: bold;">Result  NCC_M</th>
                                    <th style="font-weight: bold;">Result  NCC</th>
                                    <th style="font-weight: bold;">Result  TCC_M</th>
                                    <th style="font-weight: bold;">Result  TCC</th>

                                    <th style="font-weight: bold;">Result  Loan - No. A/C</th>
                                    <th style="font-weight: bold;">Result  Loan - Total Amt</th>


                                    <th style="font-weight: bold;">Result  Loan Recovery - No. A/C</th>
                                    <th style="font-weight: bold;">Result  Loan Recovery - Total Amt.</th>

                                    <th style="font-weight: bold;">Result  New Associate Joining No.</th>
                                    <th style="font-weight: bold;">Result  Total Associate Joining No.</th> 

                                    <th style="font-weight: bold;">Result  New Member Joining No.</th>
                                    <th style="font-weight: bold;">Result  Total Member Joining No.</th>     
                                
                            </tr>
</thead>
<tbody>
@foreach($data as $index => $row)  
<?php
 $associate_id=$row->id;
                $planDaily=getPlanID('710')->id;

                $dailyId=array($planDaily);

                $planSSB=getPlanID('703')->id;


                $planKanyadhan=getPlanID('709')->id;
                $planMB=getPlanID('708')->id;
                $planFRD=getPlanID('707')->id;
                $planJeevan=getPlanID('713')->id;  
                $planRD=getPlanID('704')->id;
                $planBhavhishya=getPlanID('718')->id;

                $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);


                $planMI=getPlanID('712')->id;
                $planFFD=getPlanID('705')->id;
                $planFD=getPlanID('706')->id;

                $fdId=array($planMI,$planFFD,$planFD);

               $join_date=date("d/m/Y", strtotime($row->associate_join_date));
               $branch=$row['associate_branch']->name;

               $branch_code=$row['associate_branch']->branch_code;
               $sector_name=$row['associate_branch']->sector;
               $region_name=$row['associate_branch']->regan;
               $zone_name=$row['associate_branch']->zone;


               $member_id=$row->associate_no;
               $name=$row->first_name.' '.$row->last_name;



               $current_daily_new_ac=investNewAcCount($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
               $current_daily_deno_sum=investNewDenoSum($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
               $current_daily_renew_ac=investRenewAc($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
               $current_daily_renew=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);

               $current_monthly_new_ac=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
               $current_monthly_deno_sum=investNewDenoSumType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
               $current_monthly_renew_ac=investRenewAc($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
               $current_monthly_renew=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);

               $current_fd_new_ac=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
               $current_fd_deno_sum=investNewDenoSumType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
               $current_fd_renew_ac=investRenewAc($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
               $current_fd_renew=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);

               $current_ssb_new_ac=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1);
               $current_ssb_deno_sum=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
               $current_ssb_renew_ac=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
               $current_ssb_renew=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);

                $current_sum_ni_ac=$current_daily_new_ac+$current_monthly_new_ac+$current_fd_new_ac+$current_ssb_new_ac;   
                $current_sum_ni_amount=$current_daily_deno_sum+$current_monthly_deno_sum+$current_fd_deno_sum+$current_ssb_deno_sum;

               $current_total_ni_ac=$current_sum_ni_ac; 
               $current_total_ni_amount=number_format((float)$current_sum_ni_amount, 2, '.', '');

                //$current_sum_renew_ac=$current_daily_renew_ac+$current_monthly_renew_ac+$current_fd_renew_ac;   
                //$current_sum_renew_amount=$current_daily_renew+$current_monthly_renew+$current_fd_renew; 

                $current_sum_renew_ac=$current_daily_renew_ac+$current_monthly_renew_ac;   
                $current_sum_renew_amount=$current_daily_renew+$current_monthly_renew; 

               $current_total_ac=$current_sum_renew_ac;
               $current_total_amount=number_format((float)$current_sum_renew_amount, 2, '.', '');

               $current_other_mt=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,11);
               $current_other_stn=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,12);

               $current_ni_m=$current_daily_deno_sum+$current_monthly_deno_sum+$current_fd_deno_sum;              

                $current_tcc_m=$current_daily_deno_sum+$current_monthly_deno_sum+$current_fd_deno_sum+$current_daily_renew+$current_monthly_renew;               

                $current_tcc=$current_daily_deno_sum+$current_monthly_deno_sum+$current_fd_deno_sum+$current_ssb_deno_sum+$current_daily_renew+$current_monthly_renew+$current_ssb_renew;

                $current_ni_m=number_format((float)$current_ni_m, 2, '.', '');
                $current_ni=number_format((float)$current_sum_ni_amount, 2, '.', '');
                $current_tcc_m=number_format((float)$current_tcc_m, 2, '.', '');
                $current_tcc=number_format((float)$current_tcc, 2, '.', '');





                $current_loan_ac=totalLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);

                $current_loan_amount=totalLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id);

                $current_loan_recovery_ac=totalRenewLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);

                $current_loan_recovery_amount=totalRenewLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id);





               $current_new_associate=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,0);
               $current_total_associate=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,1);

               $current_new_member=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,0);
               $current_total_member=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,1); 

                

               $compare_daily_new_ac=investNewAcCount($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);
               $compare_daily_deno_sum=investNewDenoSum($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);
               $compare_daily_renew_ac=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
               $compare_daily_renew=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);

               $compare_monthly_new_ac=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
               $compare_monthly_deno_sum=investNewDenoSumType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
               $compare_monthly_renew_ac=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
               $compare_monthly_renew=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);

               $compare_fd_new_ac=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
               $compare_fd_deno_sum=investNewDenoSumType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
               $compare_fd_renew_ac=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
               $compare_fd_renew=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);

               $compare_ssb_new_ac=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1);
               $compare_ssb_deno_sum=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1);
               $compare_ssb_renew_ac=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
               $compare_ssb_renew=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);

                $compare_sum_ni_ac=$compare_daily_new_ac+$compare_monthly_new_ac+$compare_fd_new_ac+$compare_ssb_new_ac;   
                $compare_sum_ni_amount=$compare_daily_deno_sum+$compare_monthly_deno_sum+$compare_fd_deno_sum+$compare_ssb_deno_sum;

               $compare_total_ni_ac=$compare_sum_ni_ac; 
               $compare_total_ni_amount=number_format((float)$compare_sum_ni_amount, 2, '.', '');

                //$compare_sum_renew_ac=$compare_daily_renew_ac+$compare_monthly_renew_ac+$compare_fd_renew_ac;   
                //$compare_sum_renew_amount=$compare_daily_renew+$compare_monthly_renew+$compare_fd_renew; 

               $compare_sum_renew_ac=$compare_daily_renew_ac+$compare_monthly_renew_ac;   
                $compare_sum_renew_amount=$compare_daily_renew+$compare_monthly_renew;

               $compare_total_ac=$compare_sum_renew_ac;
               $compare_total_amount=number_format((float)$compare_sum_renew_amount, 2, '.', '');
               

               $compare_other_mt=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,11);
               $compare_other_stn=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,12);

               $compare_ni_m=$compare_daily_deno_sum+$compare_monthly_deno_sum+$compare_fd_deno_sum;              

                $compare_tcc_m=$compare_daily_deno_sum+$compare_monthly_deno_sum+$compare_fd_deno_sum+$compare_daily_renew+$compare_monthly_renew;               

                $compare_tcc=$compare_daily_deno_sum+$compare_monthly_deno_sum+$compare_fd_deno_sum+$compare_ssb_deno_sum+$compare_daily_renew+$compare_monthly_renew+$compare_ssb_renew;

                $compare_ni_m=number_format((float)$compare_ni_m, 2, '.', '');
                $compare_ni=number_format((float)$compare_sum_ni_amount, 2, '.', '');
                $compare_tcc_m=number_format((float)$compare_tcc_m, 2, '.', '');
                $compare_tcc=number_format((float)$compare_tcc, 2, '.', '');





                $compare_loan_ac=totalLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);

                $compare_loan_amount=totalLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);

                $compare_loan_recovery_ac=totalRenewLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);

                $compare_loan_recovery_amount=totalRenewLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);







                

               $compare_new_associate=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,0);
               $compare_total_associate=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,1);

               $compare_new_member=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,0);
               $compare_total_member=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,1); 




               $result_daily_new_ac=$current_daily_new_ac-$compare_daily_new_ac;
               $result_daily_deno_sum=$current_daily_deno_sum-$compare_daily_deno_sum;
               $result_daily_renew_ac=$current_daily_renew_ac-$compare_daily_renew_ac;
               $result_daily_renew=$current_daily_renew-$compare_daily_renew;

               $result_monthly_new_ac=$current_monthly_new_ac-$compare_monthly_new_ac;
               $result_monthly_deno_sum=$current_monthly_deno_sum-$compare_monthly_deno_sum;
               $result_monthly_renew_ac=$current_monthly_renew_ac-$compare_monthly_renew_ac;
               $result_monthly_renew=$current_monthly_renew-$compare_monthly_renew;

               $result_fd_new_ac=$current_fd_new_ac-$compare_fd_new_ac;
               $result_fd_deno_sum=$current_fd_deno_sum-$compare_fd_deno_sum;
               $result_fd_renew_ac='';
               $result_fd_renew='';
              // $result_fd_renew_ac=$current_fd_renew_ac-$compare_fd_renew_ac;
               //$result_fd_renew=$current_fd_renew-$compare_fd_renew;

               $result_ssb_new_ac=$current_ssb_new_ac-$compare_ssb_new_ac;
               $result_ssb_deno_sum=$current_ssb_deno_sum-$compare_ssb_deno_sum;
              $result_ssb_renew_ac=$current_ssb_renew_ac-$compare_ssb_renew;

                $result_ssb_renew=$current_ssb_renew-$compare_ssb_deno_sum;

                $result_sum_ni_ac=$current_sum_ni_ac-$compare_sum_ni_ac;   
                $result_sum_ni_amount=$current_sum_ni_amount-$compare_sum_ni_amount;

               $result_total_ni_ac=$result_sum_ni_ac; 
               $result_total_ni_amount=number_format((float)$result_sum_ni_amount, 2, '.', '');

                $result_sum_renew_ac=$current_sum_renew_ac-$compare_sum_renew_ac;   
                $result_sum_renew_amount=$current_sum_renew_amount-$compare_sum_renew_amount; 

               $result_total_ac=$result_sum_renew_ac;
               $result_total_amount=number_format((float)$result_sum_renew_amount, 2, '.', '');

               $result_other_mt=$current_other_mt-$compare_other_mt;

                $result_other_stn=$current_other_stn-$compare_other_stn;



                $result_ni_m=$current_ni_m-$compare_ni_m;

                $result_ni=$current_ni-$compare_ni;

                $result_tcc_m=$current_tcc_m-$compare_tcc_m;

                $result_tcc=$current_tcc-$compare_tcc;



                $result_loan_ac=$current_loan_ac-$compare_loan_ac;

                $result_loan_amount=$current_loan_amount-$compare_loan_amount;



                $result_loan_recovery_ac=$current_loan_recovery_ac-$compare_loan_recovery_ac;

                $result_loan_recovery_amount=$current_loan_recovery_amount-$compare_loan_recovery_amount;

                

               $result_new_associate=$current_new_associate-$compare_new_associate;
               $result_total_associate=$current_total_associate-$compare_total_associate;

               $result_new_member=$current_new_member-$compare_new_member;
               $result_total_member=$current_total_member-$compare_total_member; 
               
?>
  <tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $branch }}</td>
    <td>{{ $branch_code }}</td>
    <td>{{ $sector_name }}</td>
    <td>{{ $region_name }}</td>
    <td>{{ $zone_name }}</td>
    <td>{{ $member_id }}</td>
    <td>{{ $name }}</td>
    <td>{{getCarderName($row->current_carder_id)}}</td>


   <td>{{ $current_daily_new_ac }}</td>
            <td>{{ $current_daily_deno_sum }}</td>
            <td>{{ $current_daily_renew_ac }}</td>
            <td>{{ $current_daily_renew }}</td> 


            <td>{{ $current_monthly_new_ac }}</td>
            <td>{{ $current_monthly_deno_sum }}</td>
            <td>{{ $current_monthly_renew_ac }}</td>
            <td>{{ $current_monthly_renew }}</td>

            <td>{{ $current_fd_new_ac }}</td>
            <td>{{ $current_fd_deno_sum }}</td>
           <!-- <td>{{ $current_fd_renew_ac }}</td>
            <td>{{ $current_fd_renew }}</td>-->
            

            <td>{{ $current_ssb_new_ac }}</td>
            <td>{{ $current_ssb_deno_sum }}</td> 
            <td>{{ $current_ssb_renew_ac }}</td>
            <td>{{ $current_ssb_renew }}</td>

           <!-- <td>{{ $current_total_ni_ac }}</td>
            <td>{{ $current_total_ni_amount }}</td>

            <td>{{ $current_total_ac }}</td>
            <td>{{ $current_total_amount }}</td> -->

            <td>{{ $current_other_mt }}</td>
            <td>{{ $current_other_stn }}</td>

            <td>{{ $current_ni_m }}</td>
            <td>{{ $current_ni }}</td>
            <td>{{ $current_tcc_m }}</td>
            <td>{{ $current_tcc }}</td>

            <td>{{ $current_loan_ac }}</td>
            <td>{{ $current_loan_amount }}</td>

            <td>{{ $current_loan_recovery_ac }}</td>
            <td>{{ $current_loan_recovery_amount }}</td> 

            <td>{{ $current_new_associate }}</td>
            <td>{{ $current_total_associate }}</td>

            <td>{{ $current_new_member }}</td> 
            <td>{{ $current_total_member }}</td>



            <td>{{ $compare_daily_new_ac }}</td>
            <td>{{ $compare_daily_deno_sum }}</td>
            <td>{{ $compare_daily_renew_ac }}</td>
            <td>{{ $compare_daily_renew }}</td>


            <td>{{ $compare_monthly_new_ac }}</td>
            <td>{{ $compare_monthly_deno_sum }}</td>
            <td>{{ $compare_monthly_renew_ac }}</td>
            <td>{{ $compare_monthly_renew }}</td>

            <td>{{ $compare_fd_new_ac }}</td>
            <td>{{ $compare_fd_deno_sum }}</td> 
          <!--  <td>{{ $compare_fd_renew_ac }}</td>
            <td>{{ $compare_fd_renew }}</td>-->

            

            <td>{{ $compare_ssb_new_ac }}</td>
            <td>{{ $compare_ssb_deno_sum }}</td>
            <td>{{ $compare_ssb_renew_ac }}</td>
            <td>{{ $compare_ssb_renew }}</td> 

           <!--<td>{{ $compare_total_ni_ac }}</td>
            <td>{{ $compare_total_ni_amount }}</td> 

            <td>{{ $compare_total_ac }}</td>
            <td>{{ $compare_total_amount }}</td>  -->

            <td>{{ $compare_other_mt }}</td>
            <td>{{ $compare_other_stn }}</td> 

            <td>{{ $compare_ni_m }}</td>
            <td>{{ $compare_ni }}</td>
            <td>{{ $compare_tcc_m }}</td>
            <td>{{ $compare_tcc }}</td>

            <td>{{ $compare_loan_ac }}</td>
            <td>{{ $compare_loan_amount }}</td>

            <td>{{ $compare_loan_recovery_ac }}</td>
            <td>{{ $compare_loan_recovery_amount }}</td>  

            <td>{{ $compare_new_associate }}</td>
            <td>{{ $compare_total_associate }}</td> 

            <td>{{ $compare_new_member }}</td>
            <td>{{ $compare_total_member }}</td> 


            <td>{{ $result_daily_new_ac }}</td>
            <td>{{ $result_daily_deno_sum }}</td> 
            <td>{{ $result_daily_renew_ac }}</td>
            <td>{{ $result_daily_renew }}</td>


            <td>{{ $result_monthly_new_ac }}</td>
            <td>{{ $result_monthly_deno_sum }}</td> 
            <td>{{ $result_monthly_renew_ac }}</td>
            <td>{{ $result_monthly_renew }}</td>

            <td>{{ $result_fd_new_ac }}</td>
            <td>{{ $result_fd_deno_sum }}</td>
           <!-- <td>{{ $result_fd_renew_ac }}</td>
            <td>{{ $result_fd_renew }}</td>-->

            

            <td>{{ $result_ssb_new_ac }}</td>
            <td>{{ $result_ssb_deno_sum }}</td>
            <td>{{ $result_ssb_renew_ac }}</td>
            <td>{{ $result_ssb_renew }}</td>

          <!--  <td>{{ $result_total_ni_ac }}</td>
            <td>{{ $result_total_ni_amount }}</td>

            <td>{{ $result_total_ac }}</td>
            <td>{{ $result_total_amount }}</td>  -->

            <td>{{ $result_other_mt }}</td>
            <td>{{ $result_other_stn }}</td> 

            <td>{{ $result_ni_m }}</td>
            <td>{{ $result_ni }}</td>
            <td>{{ $result_tcc_m }}</td>
            <td>{{ $result_tcc }}</td>

            <td>{{ $result_loan_ac }}</td>
            <td>{{ $result_loan_amount }}</td>

            <td>{{ $result_loan_recovery_ac }}</td>
            <td>{{ $result_loan_recovery_amount }}</td>  

            <td>{{ $result_new_associate }}</td> 
            <td>{{ $result_total_associate }}</td>

            <td>{{ $result_new_member }}</td> 
            <td>{{ $result_total_member }}</td>
    
    
    
  </tr>
@endforeach
</tbody>
</table>
