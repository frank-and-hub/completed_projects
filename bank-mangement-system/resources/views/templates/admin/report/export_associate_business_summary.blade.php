<h3 class="card-title font-weight-semibold">Associate Business Summary Report
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
                                    <th style="font-weight: bold;">RD N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">RD N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">RD Renew - No. A/C</th>
                                    <th style="font-weight: bold;">RD Renew - Total Amt</th>  
                                    <th style="font-weight: bold;">FRD N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">FRD N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">FRD Renew - No. A/C</th>
                                    <th style="font-weight: bold;">FRD Renew - Total Amt</th> 
                                    <th style="font-weight: bold;">FD N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">FD N.I. - Total Deno</th>
                                <!--    <th>FD Renew - No. A/C</th>
                                    <th>FD Renew - Total Amt</th>  -->
                                    <th style="font-weight: bold;">FFD N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">FFD N.I. - Total Deno</th>
                                <!--    <th>FFD Renew - No. A/C</th>
                                    <th>FFD Renew - Total Amt</th>    -->
                                    <th style="font-weight: bold;">Smaraddh Kanyadhan N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Smaraddh Kanyadhan N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Smaraddh Kanyadhan Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Smaraddh Kanyadhan Renew - Total Amt</th>
                                    <th style="font-weight: bold;">Smaraddh Bhavishya N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Smaraddh Bhavishya N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Smaraddh Bhavishya Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Smaraddh Bhavishya Renew - Total Amt</th>
                                    <th style="font-weight: bold;">Smaraddh Jeevan N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">Smaraddh Jeevan N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">Smaraddh Jeevan Renew - No. A/C</th>
                                    <th style="font-weight: bold;">Smaraddh Jeevan Renew - Total Amt</th>
                                    <th style="font-weight: bold;">SSB N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">SSB N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">SSB Deposit - No. A/C</th>
                                    <th style="font-weight: bold;">SSB Deposit - Total Amt</th> 
                                    <th style="font-weight: bold;">MIS N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">MIS N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">MIS Renew - No. A/C</th>
                                    <th style="font-weight: bold;">MIS Renew - Total Amt</th>  
                                    <th style="font-weight: bold;">MB N.I. - No. A/C</th>
                                    <th style="font-weight: bold;">MB N.I. - Total Deno</th>
                                    <th style="font-weight: bold;">MB Renew - No. A/C</th>
                                    <th style="font-weight: bold;">MB Renew - Total Amt</th>                                  
                                   <!-- <th>Total Business N.I. - No. A/C</th>
                                    <th>Total Business N.I.- Total Amt.</th>
                                    <th>Total Business Renew - No. A/C</th>
                                    <th>Total Business Renew- Total Amt.</th>-->

                                    <th style="font-weight: bold;">Other MI</th>
                                    <th style="font-weight: bold;">Other STN</th>
                                    <th style="font-weight: bold;">NCC_M</th>
                                    <th style="font-weight: bold;">NCC</th>
                                    <th style="font-weight: bold;">TCC_M</th>
                                    <th style="font-weight: bold;">TCC</th>

                                    <th style="font-weight: bold;">Staff Loan - No. A/C</th>
                                    <th style="font-weight: bold;">Staff Loan - Total Amt</th>
                                    <th style="font-weight: bold;">Pl Loan - No. A/C</th>
                                    <th style="font-weight: bold;">Pl Loan - Total Amt</th>
                                    <th style="font-weight: bold;">Loan against Investment - No. A/C</th>
                                    <th style="font-weight: bold;">Loan against Investment - Total Amt</th>
                                    <th style="font-weight: bold;">Group Loan - No. A/C</th>                                    
                                    <th style="font-weight: bold;">Group Loan - Total Amt</th>

                                    <th style="font-weight: bold;">Total Loan - No. A/C</th>
                                    <th style="font-weight: bold;">Total Loan - Total Amt.</th>

                                    <th style="font-weight: bold;">Staff Loan EMI - No. A/C</th>
                                    <th style="font-weight: bold;">Staff Loan EMI- Total Amt</th>
                                    <th style="font-weight: bold;">Pl Loan EMI - No. A/C</th>
                                    <th style="font-weight: bold;">Pl Loan EMI - Total Amt</th>
                                    <th style="font-weight: bold;">Loan against Investment EMI - No. A/C</th>
                                    <th style="font-weight: bold;">Loan against Investment EMI - Total Amt</th>
                                    <th style="font-weight: bold;">Group Loan EMI - No. A/C</th>
                                    <th style="font-weight: bold;">Group Loan EMI - Total Amt</th>

                                    <th style="font-weight: bold;">Total Loan EMI- No. A/C</th>
                                    <th style="font-weight: bold;">Total Loan EMI - Total Amt.</th>

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
                $planSSB=getPlanID('703')->id;
                $planKanyadhan=getPlanID('709')->id;
                $planMB=getPlanID('708')->id;
                $planFFD=getPlanID('705')->id;
                $planFRD=getPlanID('707')->id;
                $planJeevan=getPlanID('713')->id;
                $planMI=getPlanID('712')->id;
                $planFD=getPlanID('706')->id;
                $planRD=getPlanID('704')->id;
                $planBhavhishya=getPlanID('718')->id;
                $planids=array($planDaily,$planSSB,$planKanyadhan,$planMB,$planFFD,$planFRD,$planJeevan,$planMI,$planFD,$planRD,$planBhavhishya,);

                
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
                $daily_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planDaily,$branch_id);
                $daily_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planDaily,$branch_id);

                 $ssb_new_ac=totalInvestSSbAcCountByType($associate_id,$startDate,$endDate,$branch_id,1);
                $ssb_deno_sum=totalInvestSSbAmtByType($associate_id,$startDate,$endDate,$branch_id,1);
                $ssb_renew_ac=totalInvestSSbAcCountByType($associate_id,$startDate,$endDate,$branch_id,1);
                $ssb_renew=totalInvestSSbAmtByType($associate_id,$startDate,$endDate,$branch_id,2);

                 $kanyadhan_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planKanyadhan,$branch_id);
                $kanyadhan_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planKanyadhan,$branch_id);
                $kanyadhan_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planKanyadhan,$branch_id);
                $kanyadhan_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planKanyadhan,$branch_id);

                 $mb_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planMB,$branch_id);
                $mb_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planMB,$branch_id);
                $mb_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planMB,$branch_id);
                $mb_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planMB,$branch_id);

                 $ffd_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planFFD,$branch_id);
                $ffd_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planFFD,$branch_id);
                $ffd_renew_ac=0;
                $ffd_renew=0;
        //$ffd_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planFFD,$branch_id);
        //$ffd_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFFD,$branch_id);

                 $frd_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planFRD,$branch_id);
                $frd_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planFRD,$branch_id);
                $frd_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planFRD,$branch_id);
                $frd_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFRD,$branch_id);

                 $jeevan_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planJeevan,$branch_id);
                $jeevan_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planJeevan,$branch_id);
                $jeevan_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planJeevan,$branch_id);;
                $jeevan_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planJeevan,$branch_id);

                 $mi_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planMI,$branch_id);
                $mi_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planMI,$branch_id);
                $mi_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planMI,$branch_id);;
                $mi_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planMI,$branch_id);

                 $fd_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planFD,$branch_id);
                $fd_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planFD,$branch_id);
        //$fd_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planFD,$branch_id);
        //$fd_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFD,$branch_id);
                $fd_renew_ac=0;
                $fd_renew=0;

                 $rd_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planRD,$branch_id);
                $rd_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planRD,$branch_id);
                $rd_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planRD,$branch_id);
                $rd_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planRD,$branch_id);

                 $bhavhishya_new_ac=investNewAcCount($associate_id,$startDate,$endDate,$planBhavhishya,$branch_id);
                $bhavhishya_deno_sum=investNewDenoSum($associate_id,$startDate,$endDate,$planBhavhishya,$branch_id);
                $bhavhishya_renew_ac=investRenewAcPlan($associate_id,$startDate,$endDate,$planBhavhishya,$branch_id);
                $bhavhishya_renew=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planBhavhishya,$branch_id);

                 $sum_ni_ac=$daily_new_ac+$ssb_new_ac+$kanyadhan_new_ac+$mb_new_ac+$ffd_new_ac+$frd_new_ac+$jeevan_new_ac+$mi_new_ac+$fd_new_ac+$rd_new_ac+$bhavhishya_new_ac;   
                $sum_ni_amount=$daily_deno_sum+$ssb_deno_sum+$kanyadhan_deno_sum+$mb_deno_sum+$ffd_deno_sum+$frd_deno_sum+$jeevan_deno_sum+$mi_deno_sum+$fd_deno_sum+$rd_deno_sum+$bhavhishya_deno_sum;  




                $sum_renew_ac=investRenewAc($associate_id,$startDate,$endDate,$planids,$branch_id);  
                $sum_renew_amount=investRenewAmountSum($associate_id,$startDate,$endDate,$planids,$branch_id); 



                $total_ni_ac=$sum_ni_ac; 
                $total_ni_amount=number_format((float)$sum_ni_amount, 2, '.', '');

                $total_ac=$sum_renew_ac;
                $total_amount=number_format((float)$sum_renew_amount, 2, '.', '');

                $other_mt=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,11);
                $other_stn=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,12);





                 $ni_m=$daily_deno_sum+$kanyadhan_deno_sum+$mb_deno_sum+$ffd_deno_sum+$frd_deno_sum+$jeevan_deno_sum+$mi_deno_sum+$fd_deno_sum+$rd_deno_sum+$bhavhishya_deno_sum;;              

                $tcc_m=$daily_deno_sum+$kanyadhan_deno_sum+$mb_deno_sum+$ffd_deno_sum+$frd_deno_sum+$jeevan_deno_sum+$mi_deno_sum+$fd_deno_sum+$rd_deno_sum+$bhavhishya_deno_sum+$bhavhishya_renew+$rd_renew+$mi_renew+$jeevan_renew+$frd_renew+$mb_renew+$kanyadhan_renew+$daily_renew;               

                $tcc=$daily_deno_sum+$kanyadhan_deno_sum+$mb_deno_sum+$ffd_deno_sum+$frd_deno_sum+$jeevan_deno_sum+$mi_deno_sum+$fd_deno_sum+$rd_deno_sum+$bhavhishya_deno_sum+$ssb_deno_sum+$bhavhishya_renew+$rd_renew+$mi_renew+$jeevan_renew+$frd_renew+$mb_renew+$kanyadhan_renew+$ssb_renew+$daily_renew; 

                $ni_m=number_format((float)$ni_m, 2, '.', '');
                $ni=number_format((float)$sum_ni_amount, 2, '.', '');
                $tcc_m=number_format((float)$tcc_m, 2, '.', '');
                $tcc=number_format((float)$tcc, 2, '.', '');



                $st_loan_ac=associateLoanTypeAC($associate_id,$startDate,$endDate,$branch_id,2);
                $st_loan_amount=associateLoanTypeAmount($associate_id,$startDate,$endDate,$branch_id,2);
                $pl_loan_ac=associateLoanTypeAC($associate_id,$startDate,$endDate,$branch_id,1);
                $pl_loan_amount=associateLoanTypeAmount($associate_id,$startDate,$endDate,$branch_id,1);
                $la_loan_ac=associateLoanTypeAC($associate_id,$startDate,$endDate,$branch_id,4);
                $la_loan_amount=associateLoanTypeAmount($associate_id,$startDate,$endDate,$branch_id,4);
                $gp_loan_ac=associateLoanTypeAC($associate_id,$startDate,$endDate,$branch_id,3);
                $gp_loan_amount=associateLoanTypeAmount($associate_id,$startDate,$endDate,$branch_id,3);

                $loan_ac=$st_loan_ac+$pl_loan_ac+$la_loan_ac+$gp_loan_ac;
                $loan_amount=$st_loan_amount+$pl_loan_amount+$la_loan_amount+$gp_loan_amount;

                $st_loan_recovery_ac=associateLoanTypeRecoverAc($associate_id,$startDate,$endDate,$branch_id,2);
                $st_loan_recovery_amount=associateLoanTypeRecoverAmount($associate_id,$startDate,$endDate,$branch_id,2);
                $pl_loan_recovery_ac=associateLoanTypeRecoverAc($associate_id,$startDate,$endDate,$branch_id,1);
                $pl_loan_recovery_amount=associateLoanTypeRecoverAmount($associate_id,$startDate,$endDate,$branch_id,1);
                $la_loan_recovery_ac=associateLoanTypeRecoverAc($associate_id,$startDate,$endDate,$branch_id,4);
                $la_loan_recovery_amount=associateLoanTypeRecoverAmount($associate_id,$startDate,$endDate,$branch_id,4);
                $gp_loan_recovery_ac=associateLoanTypeRecoverAc($associate_id,$startDate,$endDate,$branch_id,3);
                $gp_loan_recovery_amount=associateLoanTypeRecoverAmount($associate_id,$startDate,$endDate,$branch_id,3);

                $loan_recovery_ac=$st_loan_recovery_ac+$pl_loan_recovery_ac+$la_loan_recovery_ac+$gp_loan_recovery_ac;
                $loan_recovery_amount=$st_loan_recovery_amount+$pl_loan_recovery_amount+$la_loan_recovery_amount+$gp_loan_recovery_amount;

                

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


            <td>{{ $rd_new_ac }}</td>
            <td>{{ $rd_deno_sum }}</td> 
            <td>{{ $rd_renew_ac }}</td>
            <td>{{ $rd_renew }}</td>


            <td>{{ $frd_new_ac }}</td>
            <td>{{ $frd_deno_sum }}</td> 
            <td>{{ $frd_renew_ac }}</td>
            <td>{{ $frd_renew }}</td>

            <td>{{ $fd_new_ac }}</td>
            <td>{{ $fd_deno_sum }}</td>
            <!--<td>{{ $fd_renew_ac }}</td>
            <td>{{ $fd_renew }}</td>-->
            <td>{{ $ffd_new_ac }}</td>
            <td>{{ $ffd_deno_sum }}</td>
           <!-- <td>{{ $ffd_renew_ac }}</td>
            <td>{{ $ffd_renew }}</td>-->

            <td>{{ $kanyadhan_new_ac }}</td>
            <td>{{ $kanyadhan_deno_sum }}</td>
            <td>{{ $kanyadhan_renew_ac }}</td>
            <td>{{ $kanyadhan_renew }}</td>

            <td>{{ $bhavhishya_new_ac }}</td>
            <td>{{ $bhavhishya_deno_sum }}</td>
            <td>{{ $bhavhishya_renew_ac }}</td>
            <td>{{ $bhavhishya_renew }}</td>

            <td>{{ $jeevan_new_ac }}</td>
            <td>{{ $jeevan_deno_sum }}</td>
            <td>{{ $jeevan_renew_ac }}</td>
            <td>{{ $jeevan_renew }}</td>

            <td>{{ $ssb_new_ac }}</td>
            <td>{{ $ssb_deno_sum }}</td>
            <td>{{ $ssb_renew_ac }}</td>
            <td>{{ $ssb_renew }}</td>

            <td>{{ $mi_new_ac }}</td>
            <td>{{ $mi_deno_sum }}</td>
            <td>{{ $mi_renew_ac }}</td>
            <td>{{ $mi_renew }}</td>
            <td>{{ $mb_new_ac }}</td>
            <td>{{ $mb_deno_sum }}</td>
            <td>{{ $mb_renew_ac }}</td>
            <td>{{ $mb_renew }}</td>

    <!--<td>{{ $total_ni_ac }}</td>
    <td>{{ $total_ni_amount }}</td>
    <td>{{ $total_ac }}</td>
    <td>{{ $total_amount }}</td>-->

    <td>{{ $other_mt }}</td>
    <td>{{ $other_stn }}</td>

    <td>{{ $ni_m }}</td>
    <td>{{ $ni }}</td>

    <td>{{ $tcc_m }}</td>
    <td>{{ $tcc }}</td>

    <td>{{ $st_loan_ac }}</td>
    <td>{{ $st_loan_amount }}</td>
    <td>{{ $pl_loan_ac }}</td>
    <td>{{ $pl_loan_amount }}</td>
    <td>{{ $la_loan_ac }}</td>
    <td>{{ $la_loan_amount }}</td>
    <td>{{ $gp_loan_ac }}</td>
    <td>{{ $gp_loan_amount }}</td>

    <td>{{ $loan_ac }}</td>
    <td>{{ $loan_amount }}</td>

    <td>{{ $st_loan_recovery_ac }}</td>
    <td>{{ $st_loan_recovery_amount }}</td>
    <td>{{ $pl_loan_recovery_ac }}</td>
    <td>{{ $pl_loan_recovery_amount }}</td>
    <td>{{ $la_loan_recovery_ac }}</td>
    <td>{{ $la_loan_recovery_amount }}</td>
    <td>{{ $gp_loan_recovery_ac }}</td>
    <td>{{ $gp_loan_recovery_amount }}</td>


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
