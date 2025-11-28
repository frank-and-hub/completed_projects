<h3 class="card-title font-weight-semibold">Associate Business Report
</h3>
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
<?php
//echo "<pre>";print_r($data);die;
?>


<thead>
 <tr>
                                    <th style="font-weight: bold;">S/N</th>
                                    <th style="font-weight: bold;">BR Name</th>
                                    <th style="font-weight: bold;">BR Code</th>
                                    <th style="font-weight: bold;">SO Name</th>
                                    <th style="font-weight: bold;">RO Name</th>
                                    <th style="font-weight: bold;">ZO Name</th>

                                    <th style="font-weight: bold;">Associate Code</th>  
                                    <th style="font-weight: bold;">Associate Name</th>  
                                     <th style="font-weight: bold;">Carder</th> 
                                                                      
                                    <th style="font-weight: bold;">Daily N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Daily N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Daily Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Daily Renew - Total Amt</th>

                                    <th style="font-weight: bold;">Monthly N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Monthly N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Monthly Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Monthly Renew - Total Amt</th>  

                                    <th style="font-weight: bold;">FD N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">FD N.I. - Total Deno</th>
                                   <!-- <th>FD Renew - No. A/C</th>
                                    <th>FD Renew - Total Amt</th>  -->

                                    <th style="font-weight: bold;">SSB N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">SSB N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">SSB Deposit - No. A/C</th>
                                    <th style="font-weight: bold;">SSB Deposit - Total Amt</th> 

                                  <!--  <th>Total Business N.I. - No. A/C</th>
                                    <th>Total Business N.I.- Total Amt.</th>
                                    <th>Total Business Renew - No. A/C</th>
                                    <th>Total Business Renew- Total Amt.</th>-->

                                    <th style="font-weight: bold;">Other MI</th>
                                    <th style="font-weight: bold;">Other STN</th>

                                    <th style="font-weight: bold;">NCC_M</th>
                                    <th style="font-weight: bold;">NCC</th>
                                    <th style="font-weight: bold;">TCC_M</th>
                                    <th style="font-weight: bold;">TCC</th>

                                    <th style="font-weight: bold;">Loan - No. A/C</th>
                                    <th style="font-weight: bold;">Loan - Total Amt</th>


                                    <th style="font-weight: bold;">Loan Recovery - No. A/C</th>
                                    <th style="font-weight: bold;">Loan Recovery - Total Amt.</th>

                                    <th style="font-weight: bold;">New Associate Joining No.</th>
                                    <th style="font-weight: bold;">Total Associate Joining No.</th> 

                                    <th style="font-weight: bold;">New Member Joining No.</th>
                                    <th style="font-weight: bold;">Total Member Joining No.</th>    
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

                $daily_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planDaily,$branch_id);
                $daily_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planDaily,$branch_id);
                $daily_renew_ac=investRenewAc($associate_id,$startDate,$endDate,$dailyId,$branch_id);
                $daily_renew=investRenewAmountSum($associate_id,$startDate,$endDate,$dailyId,$branch_id);

                $monthly_new_ac=investNewAcCountType($associate_id,$startDate,$endDate,$monthlyId,$branch_id);
                $monthly_deno_sum=investNewDenoSumType($associate_id,$startDate,$endDate,$monthlyId,$branch_id);
                $monthly_renew_ac=investRenewAc($associate_id,$startDate,$endDate,$monthlyId,$branch_id);
                $monthly_renew=investRenewAmountSum($associate_id,$startDate,$endDate,$monthlyId,$branch_id);

                $fd_new_ac=investNewAcCountType($associate_id,$startDate,$endDate,$fdId,$branch_id);
                $fd_deno_sum=investNewDenoSumType($associate_id,$startDate,$endDate,$fdId,$branch_id);
                $fd_renew_ac=0;
                $fd_renew=0;
              //  $fd_renew_ac=investRenewAc($associate_id,$startDate,$endDate,$fdId,$branch_id);
              //  $fd_renew=investRenewAmountSum($associate_id,$startDate,$endDate,$fdId,$branch_id);

               


                $ssb_new_ac=totalInvestSSbAcCountByType($associate_id,$startDate,$endDate,$branch_id,1);
                $ssb_deno_sum=totalInvestSSbAmtByType($associate_id,$startDate,$endDate,$branch_id,1);
                $ssb_renew_ac=totalInvestSSbAcCountByType($associate_id,$startDate,$endDate,$branch_id,2);
                $ssb_renew=totalInvestSSbAmtByType($associate_id,$startDate,$endDate,$branch_id,2);


                $sum_ni_ac=$daily_new_ac+$monthly_new_ac+$fd_new_ac+$ssb_new_ac;   
                $sum_ni_amount=$daily_deno_sum+$monthly_deno_sum+$fd_deno_sum+$ssb_deno_sum;

                $total_ni_ac=$sum_ni_ac; 
                $total_ni_amount=number_format((float)$sum_ni_amount, 2, '.', '');

                $sum_renew_ac=$daily_renew_ac+$monthly_renew_ac+$fd_renew_ac;   
                $sum_renew_amount=$daily_renew+$monthly_renew+$fd_renew; 

                $total_ac=$sum_renew_ac;
                $total_amount=number_format((float)$sum_renew_amount, 2, '.', '');

                $other_mt=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,11);
                $other_stn=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,12);

                $ni_m=$daily_deno_sum+$monthly_deno_sum+$fd_deno_sum;
                
                $tcc_m=$daily_deno_sum+$monthly_deno_sum+$fd_deno_sum+$daily_renew+$monthly_renew;
                
                $tcc=$daily_deno_sum+$monthly_deno_sum+$fd_deno_sum+$ssb_deno_sum+$daily_renew+$monthly_renew+$ssb_renew;
                

                $ni_m=number_format((float)$ni_m, 2, '.', '');
                $ni=number_format((float)$sum_ni_amount, 2, '.', '');
                $tcc_m=number_format((float)$tcc_m, 2, '.', '');
                $tcc=number_format((float)$tcc, 2, '.', '');
                $loan_ac=totalLoanAc($associate_id,$startDate,$endDate,$branch_id);
                $loan_amount=totalLoanAmount($associate_id,$startDate,$endDate,$branch_id);

                $loan_recovery_ac=totalRenewLoanAc($associate_id,$startDate,$endDate,$branch_id);
                $loan_recovery_amount=totalRenewLoanAmount($associate_id,$startDate,$endDate,$branch_id);

                

                $new_associate=memberCountByType($associate_id,$startDate,$endDate,$branch_id,1,0);
                $total_associate=memberCountByType($associate_id,$startDate,$endDate,$branch_id,1,1);

                $new_member=memberCountByType($associate_id,$startDate,$endDate,$branch_id,0,0);
                $total_member=memberCountByType($associate_id,$startDate,$endDate,$branch_id,0,1);  
               
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
    <td>{{ $daily_new_ac }}</td>
    <td>{{ $daily_deno_sum }}</td>
    <td>{{ $daily_renew_ac }}</td>
    <td>{{ $daily_renew }}</td>
    <td>{{ $monthly_new_ac }}</td>
    <td>{{ $monthly_deno_sum }}</td>
    <td>{{ $monthly_renew_ac }}</td>
    <td>{{ $monthly_renew }}</td>
    <td>{{ $fd_new_ac }}</td>
    <td>{{ $fd_deno_sum }}</td>
   <!-- <td>{{ $fd_renew_ac }}</td>
    <td>{{ $fd_renew }}</td>-->
    <td>{{ $ssb_new_ac }}</td>
    <td>{{ $ssb_deno_sum }}</td>
    <td>{{ $ssb_renew_ac }}</td>
    <td>{{ $ssb_renew }}</td>
   <!-- <td>{{ $total_ni_ac }}</td>
    <td>{{ $total_ni_amount }}</td>
    <td>{{ $total_ac }}</td>
    <td>{{ $total_amount }}</td>-->
    <td>{{ $other_mt }}</td>
    <td>{{ $other_stn }}</td>
    <td>{{ $ni_m }}</td>
    <td>{{ $ni }}</td>
    <td>{{ $tcc_m }}</td>
    <td>{{ $tcc }}</td>
    <td>{{ $loan_ac }}</td>
    <td>{{ $loan_amount }}</td>
    <td>{{ $loan_recovery_ac }}</td> 



    <td>{{ $loan_recovery_amount }}</td>
    <td>{{ $new_associate }}</td>
    <td>{{ $total_associate }}</td>
    <td>{{ $new_member }}</td>
    <td>{{ $total_member }}</td> 
    
    
    
  </tr>
@endforeach
</tbody>
</table>
